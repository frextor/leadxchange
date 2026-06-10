@extends('admin.layouts.admin')
@section('title', 'Créer un admin')
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
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Créer un administrateur</h1>
    </div>
</div>

<div class="max-w-2xl">
<form method="POST" action="{{ route('admin.super.admins.store') }}">
@csrf

{{-- ── Identity card ──────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-5">
    <div class="px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF);">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-indigo-900">Identité</p>
                <p class="text-[10px] text-indigo-400 font-medium">Informations personnelles du nouveau compte admin</p>
            </div>
        </div>
    </div>
    <div class="px-6 py-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Prénom *</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition @error('first_name') border-red-300 @enderror"
                       placeholder="Jean">
                @error('first_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Nom *</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition @error('last_name') border-red-300 @enderror"
                       placeholder="Dupont">
                @error('last_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition @error('email') border-red-300 @enderror"
                   placeholder="jean.dupont@example.com">
            @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Mot de passe *</label>
            <input type="password" name="password" required
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition @error('password') border-red-300 @enderror"
                   placeholder="Minimum 8 caractères, lettres + chiffres">
            @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- ── Permissions card ────────────────────────────────────────────────── --}}
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
        <div class="grid grid-cols-1 gap-3">
            @foreach(\App\Models\User::ADMIN_PERMISSIONS as $key => $label)
            <label class="flex items-center gap-3 group cursor-pointer">
                <div class="relative">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                           {{ is_array(old('permissions')) && in_array($key, old('permissions')) ? 'checked' : '' }}
                           class="peer sr-only">
                    <div class="w-5 h-5 rounded-md border-2 border-gray-200 peer-checked:border-violet-500 peer-checked:bg-violet-500 transition flex items-center justify-center">
                        <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    </div>
                </div>
                <span class="text-sm text-gray-700 font-medium group-hover:text-gray-900 transition">{{ $label }}</span>
            </label>
            @endforeach
        </div>
        <p class="text-[10px] text-gray-400 mt-4 flex items-start gap-1.5">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            Si aucune permission n'est sélectionnée, l'admin aura accès à toutes les sections du panel.
        </p>
    </div>
</div>

{{-- ── Submit ──────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3">
    <button type="submit"
            class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
        Créer l'administrateur
    </button>
    <a href="{{ route('admin.super.admins.index') }}"
       class="px-5 py-3 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 hover:bg-gray-50 transition">
        Annuler
    </a>
</div>

</form>
</div>

<script>
(function () {
    const btn = document.getElementById('toggleAll');
    const boxes = document.querySelectorAll('input[name="permissions[]"]');
    let allChecked = false;

    function updateChecked() {
        allChecked = Array.from(boxes).every(b => b.checked);
        btn.textContent = allChecked ? 'Tout décocher' : 'Tout cocher';
    }

    boxes.forEach(b => b.addEventListener('change', () => {
        updateChecked();
        syncCheckmarks();
    }));

    btn.addEventListener('click', () => {
        allChecked = !allChecked;
        boxes.forEach(b => { b.checked = allChecked; });
        btn.textContent = allChecked ? 'Tout décocher' : 'Tout cocher';
        syncCheckmarks();
    });

    function syncCheckmarks() {
        boxes.forEach(b => {
            const mark = b.closest('label').querySelector('svg');
            if (mark) mark.classList.toggle('hidden', !b.checked);
        });
    }

    updateChecked();
    syncCheckmarks();
}());
</script>

@endsection
