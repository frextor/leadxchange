@extends('layouts.app')
@section('title', 'Gestion Consuls — ' . $regionName)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-widest text-amber-600">Ambassadeur</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des Consuls</h1>
            <p class="text-sm text-gray-400 mt-0.5 flex items-center gap-1">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/></svg>
                {{ $regionName }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-center px-4 py-2 bg-teal-50 rounded-2xl border border-teal-100">
                <p class="text-xl font-bold text-teal-700">{{ $counts['consuls'] }}</p>
                <p class="text-[10px] text-teal-500 font-semibold uppercase tracking-wider">Consuls actifs</p>
            </div>
            @if($counts['pending'] > 0)
            <div class="text-center px-4 py-2 rounded-2xl border" style="background:#FFFBEB; border-color:#FDE68A;">
                <p class="text-xl font-bold text-amber-600">{{ $counts['pending'] }}</p>
                <p class="text-[10px] text-amber-500 font-semibold uppercase tracking-wider">En attente</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-3 text-sm font-medium">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── SECTION 1 : Demandes en attente ──────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <h2 class="text-sm font-bold text-gray-800">Demandes Consul en attente</h2>
            @if($counts['pending'] > 0)
            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold text-white" style="background:#F59E0B;">{{ $counts['pending'] }}</span>
            @endif
        </div>

        @if($pendingRequests->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 px-5 py-8 text-center shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.5"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-400">Aucune demande en attente dans votre région.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden">
            @foreach($pendingRequests as $u)
            <div class="flex items-center gap-4 px-5 py-4 border-b border-gray-50 last:border-0 hover:bg-amber-50/30 transition">
                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                    {{ strtoupper(substr($u->first_name, 0, 1)) }}
                </div>
                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-gray-900">{{ $u->first_name }} {{ $u->last_name }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-indigo-50 text-indigo-600">
                            {{ $u->subscription?->plan?->label ?? '—' }}
                        </span>
                        @if($u->city)
                        <span class="text-xs text-gray-400">📍 {{ $u->city->name }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $u->email }}</p>
                </div>
                {{-- Badge --}}
                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 flex-shrink-0">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> En attente
                </span>
                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    <form method="POST" action="{{ route('ambassador.consuls.approve', $u) }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition"
                                style="background:#059669;">
                            ✓ Approuver
                        </button>
                    </form>
                    <form method="POST" action="{{ route('ambassador.consuls.reject', $u) }}"
                          onsubmit="return confirm('Refuser la demande de {{ addslashes($u->first_name) }} ?')">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold border border-red-200 text-red-600 hover:bg-red-50 transition">
                            ✕ Refuser
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── SECTION 2 : Membres éligibles à nommer ───────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <h2 class="text-sm font-bold text-gray-800">Membres Premium éligibles</h2>
            <span class="text-xs text-gray-400 font-medium">{{ $counts['eligible'] }} membre{{ $counts['eligible'] > 1 ? 's' : '' }}</span>
        </div>

        @if($eligibleUsers->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 px-5 py-8 text-center shadow-sm">
            <p class="text-sm font-semibold text-gray-400">Aucun membre Premium éligible dans votre région.</p>
        </div>
        @else
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            @foreach($eligibleUsers as $u)
            <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                    {{ strtoupper(substr($u->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-sm font-semibold text-gray-800">{{ $u->first_name }} {{ $u->last_name }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-teal-50 text-teal-700">
                            {{ $u->subscription?->plan?->label ?? '—' }}
                        </span>
                        @if($u->city)
                        <span class="text-xs text-gray-400">📍 {{ $u->city->name }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $u->email }}</p>
                </div>
                <form method="POST" action="{{ route('ambassador.consuls.nominate', $u) }}"
                      onsubmit="return confirm('Nommer {{ addslashes($u->first_name . ' ' . $u->last_name) }} comme Consul ?')">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white hover:opacity-90 transition flex-shrink-0"
                            style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Nommer Consul
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── SECTION 3 : Consuls actifs dans la région (lecture) ─────────────── --}}
    @if($consuls->isNotEmpty())
    <div>
        <h2 class="text-sm font-bold text-gray-800 mb-3">Consuls actifs dans votre région</h2>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            @foreach($consuls as $u)
            <div class="flex items-center gap-4 px-5 py-3 border-b border-gray-50 last:border-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                    {{ strtoupper(substr($u->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-800">{{ $u->first_name }} {{ $u->last_name }}</p>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-700">
                            <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            Consul
                        </span>
                        @if($u->isAmbassador())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">
                            <svg width="8" height="8" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Ambassadeur
                        </span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400">{{ $u->city?->name ?? '—' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
