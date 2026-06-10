@extends('admin.layouts.admin')
@section('title', 'Administrateurs')
@section('page-title', 'Admins')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            Administrateurs
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600">
                {{ $admins->where('role', 'admin')->count() }}
            </span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Gérez les comptes admin et leurs permissions d'accès.</p>
    </div>
    <a href="{{ route('admin.super.admins.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
       style="background:linear-gradient(135deg,#6366F1,#4338CA);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        Nouvel admin
    </a>
</div>

{{-- ── Admin list ───────────────────────────────────────────────────────── --}}
<div class="space-y-3">

@foreach($admins as $admin)
@php
    $isSelf      = $admin->id === auth()->id();
    $isSA        = $admin->isSuperAdmin();
    $perms       = $admin->admin_permissions;
    $allPerms    = empty($perms);
    $permLabels  = \App\Models\User::ADMIN_PERMISSIONS;
@endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-start gap-5 px-6 py-5">

        {{-- Avatar --}}
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
             style="background:{{ $isSA ? 'linear-gradient(135deg,#7C3AED,#4C1D95)' : 'linear-gradient(135deg,#6366F1,#4338CA)' }};">
            {{ strtoupper(mb_substr($admin->first_name, 0, 1)) }}{{ strtoupper(mb_substr($admin->last_name, 0, 1)) }}
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="font-bold text-gray-900">{{ $admin->first_name }} {{ $admin->last_name }}</span>
                @if($isSelf)
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-teal-50 text-teal-600 border border-teal-100">Vous</span>
                @endif
                @if($isSA)
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full text-white" style="background:linear-gradient(135deg,#7C3AED,#4C1D95);">Super Admin</span>
                @else
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">Admin</span>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-0.5">{{ $admin->email }}</p>

            {{-- Permissions --}}
            <div class="mt-3">
                @if($isSA)
                <div class="flex items-center gap-1.5">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span class="text-xs text-purple-600 font-semibold">Accès complet — toutes les permissions</span>
                </div>
                @elseif($allPerms)
                <div class="flex items-center gap-1.5">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="m5 12 5 5L20 7"/></svg>
                    <span class="text-xs text-emerald-600 font-semibold">Toutes les permissions (aucune restriction)</span>
                </div>
                @else
                <div class="flex flex-wrap gap-1.5">
                    @foreach($permLabels as $key => $label)
                    @php $has = in_array($key, $perms ?? []); @endphp
                    <span class="inline-flex items-center gap-1 text-[10px] font-medium px-2 py-0.5 rounded-md
                                 {{ $has ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-gray-50 text-gray-300 border border-gray-100 line-through' }}">
                        @if($has)
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                        @else
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        @endif
                        {{ $label }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            @if(!$isSA || $isSelf)
            <a href="{{ route('admin.super.admins.edit', $admin) }}"
               class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Permissions
            </a>
            @endif

            @if(!$isSA && !$isSelf)
            <form method="POST" action="{{ route('admin.super.admins.demote', $admin) }}">
                @csrf
                <button type="button"
                        data-name="{{ $admin->first_name }} {{ $admin->last_name }}"
                        onclick="swalDelete(this, this.dataset.name)"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 border border-red-100 transition">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
                    Rétrograder
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
@endforeach

</div>

{{-- ── Promote existing user ────────────────────────────────────────────── --}}
<div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#F5F3FF,#EDE9FE);">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-violet-900">Promouvoir un utilisateur existant</p>
                <p class="text-[10px] text-violet-400 font-medium">Entrez l'ID ou l'email d'un utilisateur pour le passer admin</p>
            </div>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.super.admins.promote') }}" class="px-6 py-5">
        @csrf
        <div class="grid grid-cols-[1fr_auto] gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">ID utilisateur</label>
                <input type="number" name="user_id" required min="1" placeholder="ex: 42"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-50 transition placeholder-gray-300">
            </div>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                    style="background:linear-gradient(135deg,#7C3AED,#6D28D9);">
                Promouvoir
            </button>
        </div>
        <p class="text-[10px] text-gray-400 mt-2">Les permissions seront vides (= accès complet). Vous pourrez les restreindre ensuite.</p>
    </form>
</div>

@endsection
