@extends('admin.layouts.admin')
@section('title', 'Gestion Ambassadeurs')
@section('page-title', 'Ambassadeurs')

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900">Gestion des Ambassadeurs</h1>
        <p class="text-sm text-gray-400 mt-1">
            Un membre <strong>Prémium</strong> peut être promu Ambassadeur sans nouveau paiement — le plan bascule automatiquement.
        </p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- KPI strip --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900">{{ $counts['ambassador'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Ambassadeurs actifs</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900">{{ $counts['eligible'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Membres éligibles</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900">{{ $counts['total'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Membres avec plan payant</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex items-center gap-3 mb-5">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, prénom, email…"
               class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-teal-400 transition">
    </div>
    <select name="status" onchange="this.form.submit()"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white text-gray-700 focus:outline-none focus:border-teal-400 transition">
        <option value="">Tous les membres payants</option>
        <option value="ambassador" {{ request('status') === 'ambassador' ? 'selected' : '' }}>Ambassadeurs uniquement</option>
        <option value="eligible"   {{ request('status') === 'eligible'   ? 'selected' : '' }}>Éligibles uniquement</option>
    </select>
    @if(request()->hasAny(['search','status']))
    <a href="{{ route('admin.super.ambassadors.manage') }}"
       class="px-3 py-2 rounded-xl text-sm text-gray-500 border border-gray-200 hover:bg-gray-50 transition">
        Reset
    </a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
        <span class="text-sm text-gray-500">{{ $users->total() }} membre{{ $users->total() > 1 ? 's' : '' }}</span>
        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
            <span class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                = Ambassadeur
            </span>
        </div>
    </div>

    <div class="divide-y divide-gray-50">
        @forelse($users as $user)
        @php $isAmbassador = $user->isAmbassador(); @endphp
        <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/50 transition {{ $isAmbassador ? 'bg-amber-50/30' : '' }}">

            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                 style="background: {{ $isAmbassador ? 'linear-gradient(135deg,#F59E0B,#D97706)' : 'linear-gradient(135deg,#34d4bf,#1E8F88)' }};">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                    @if($isAmbassador)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Ambassadeur
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $user->email }}</p>
            </div>

            {{-- Plan actuel --}}
            <div class="hidden sm:block text-right flex-shrink-0">
                <p class="text-xs font-semibold text-gray-700">{{ $user->subscription?->plan?->label ?? '—' }}</p>
                <p class="text-[10px] text-gray-400">{{ $user->city?->name ?? '—' }}</p>
            </div>

            {{-- Action --}}
            <div class="flex-shrink-0">
                @if($isAmbassador)
                <form method="POST" action="{{ route('admin.super.ambassadors.revoke', $user) }}"
                      onsubmit="return confirm('Retirer le rôle Ambassadeur à {{ $user->first_name }} {{ $user->last_name }} et ramener au plan Prémium ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-red-200 text-red-600 hover:bg-red-50 transition">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        Retirer
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.super.ambassadors.promote', $user) }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition"
                            style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Nommer Ambassadeur
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="px-5 py-16 text-center">
            <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-400">Aucun membre éligible trouvé.</p>
            <p class="text-xs text-gray-300 mt-1">Seuls les membres avec un plan payant apparaissent ici.</p>
        </div>
        @endforelse
    </div>

    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $users->links() }}</div>
    @endif
</div>

{{-- Rule reminder --}}
<div class="mt-4 flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-2xl px-5 py-4 text-sm text-blue-700">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
    <div>
        <p class="font-semibold">Règles de promotion</p>
        <ul class="mt-1 space-y-0.5 text-blue-600 text-xs">
            <li>• Seuls les membres avec un abonnement payant (Prémium, Consul…) sont affichés.</li>
            <li>• La promotion attribue automatiquement le plan <strong>Ambassadeur</strong> à l'utilisateur.</li>
            <li>• Le retrait du rôle ramène l'utilisateur au plan <strong>Prémium</strong>.</li>
            <li>• Un utilisateur Basic ne peut pas devenir Ambassadeur.</li>
        </ul>
    </div>
</div>

@endsection
