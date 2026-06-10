@extends('admin.layouts.admin')
@section('title', 'Permissions — ' . $user->first_name . ' ' . $user->last_name)
@section('page-title', 'Admins')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-4 mb-6">
    <a href="{{ route('admin.super.admins.index') }}"
       class="w-8 h-8 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 hover:bg-gray-50 transition">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M5 12l7 7M5 12l7-7"/></svg>
    </a>
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Super Admin &rsaquo; Admins</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            {{ $user->first_name }} {{ $user->last_name }}
            @if($user->isSuperAdmin())
            <span class="text-sm font-bold px-2.5 py-0.5 rounded-full text-white" style="background:linear-gradient(135deg,#7C3AED,#4C1D95);">Super Admin</span>
            @else
            <span class="text-sm font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">Admin</span>
            @endif
        </h1>
        <p class="text-xs text-gray-400">{{ $user->email }}</p>
    </div>
</div>

@php $isSelf = $user->id === auth()->id(); @endphp

<div class="max-w-2xl">
<form method="POST" action="{{ route('admin.super.admins.update', $user) }}">
@csrf
@method('PUT')

{{-- ── Permissions card ────────────────────────────────────────────────── --}}
@if(!$user->isSuperAdmin())
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#F5F3FF,#EDE9FE);">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-violet-900">Permissions d'accès</p>
                    <p class="text-[10px] text-violet-400 font-medium">Laissez tout décoché pour un accès complet</p>
                </div>
            </div>
            <button type="button" id="toggleAll"
                    class="text-[10px] font-semibold text-violet-600 hover:text-violet-800 underline underline-offset-2">
                Tout cocher
            </button>
        </div>
    </div>
    <div class="px-6 py-5">
        @php
            $currentPerms = $user->admin_permissions ?? [];
            $noRestriction = empty($currentPerms);
        @endphp
        @if($noRestriction)
        <div class="mb-4 flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-emerald-50 border border-emerald-100">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="m5 12 5 5L20 7"/></svg>
            <span class="text-xs text-emerald-700 font-semibold">Cet admin a actuellement accès à tout (aucune restriction).</span>
        </div>
        @endif
        <div class="grid grid-cols-1 gap-3">
            @foreach(\App\Models\User::ADMIN_PERMISSIONS as $key => $label)
            @php $checked = !$noRestriction && in_array($key, $currentPerms); @endphp
            <label class="flex items-center gap-3 group cursor-pointer">
                <div class="relative">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                           {{ $checked ? 'checked' : '' }}
                           class="peer sr-only perm-checkbox">
                    <div class="w-5 h-5 rounded-md border-2 border-gray-200 peer-checked:border-violet-500 peer-checked:bg-violet-500 transition flex items-center justify-center">
                        <svg class="w-3 h-3 text-white {{ $checked ? '' : 'hidden' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    </div>
                </div>
                <span class="text-sm text-gray-700 font-medium group-hover:text-gray-900 transition">{{ $label }}</span>
            </label>
            @endforeach
        </div>
        <p class="text-[10px] text-gray-400 mt-4 flex items-start gap-1.5">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            Si aucune case n'est cochée, l'admin aura accès à toutes les sections.
        </p>
    </div>
</div>
@else
<div class="mb-5 flex items-start gap-3 px-5 py-4 rounded-2xl bg-purple-50 border border-purple-100">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    <div>
        <p class="text-sm font-bold text-purple-800">Super Admin — accès complet</p>
        <p class="text-xs text-purple-500">Les super-admins ont toutes les permissions et ne peuvent pas être restreints.</p>
    </div>
</div>
@endif

{{-- ── Password card ───────────────────────────────────────────────────── --}}
@if($isSelf)
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#FFF7ED,#FFEDD5);">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-amber-900">Changer le mot de passe</p>
                <p class="text-[10px] text-amber-400 font-medium">Laissez vide pour ne pas modifier</p>
            </div>
        </div>
    </div>
    <div class="px-6 py-5 space-y-4">
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Nouveau mot de passe</label>
            <input type="password" name="password" autocomplete="new-password"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-50 transition"
                   placeholder="Minimum 8 caractères, lettres + chiffres">
            @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Confirmer</label>
            <input type="password" name="password_confirmation" autocomplete="new-password"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-50 transition"
                   placeholder="Répéter le mot de passe">
        </div>
    </div>
</div>
@endif

{{-- ── Submit ──────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3">
    <button type="submit"
            class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
            style="background:linear-gradient(135deg,#7C3AED,#6D28D9);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
        Enregistrer
    </button>
    <a href="{{ route('admin.super.admins.index') }}"
       class="px-5 py-3 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 hover:bg-gray-50 transition">
        Retour
    </a>
</div>

</form>
</div>

<script>
(function () {
    const btn = document.getElementById('toggleAll');
    if (!btn) return;
    const boxes = document.querySelectorAll('.perm-checkbox');

    function updateBtn() {
        const allChecked = Array.from(boxes).every(b => b.checked);
        btn.textContent = allChecked ? 'Tout décocher' : 'Tout cocher';
    }

    boxes.forEach(b => b.addEventListener('change', () => {
        updateBtn();
        syncCheckmarks();
    }));

    btn.addEventListener('click', () => {
        const allChecked = Array.from(boxes).every(b => b.checked);
        boxes.forEach(b => { b.checked = !allChecked; });
        updateBtn();
        syncCheckmarks();
    });

    function syncCheckmarks() {
        boxes.forEach(b => {
            const mark = b.closest('label').querySelector('svg');
            if (mark) mark.classList.toggle('hidden', !b.checked);
        });
    }

    updateBtn();
}());
</script>

@endsection
