@extends('admin.layouts.admin')

@section('title', 'Utilisateurs')
@section('page-title', 'Utilisateurs')

@section('content')

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Administration</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            Utilisateurs
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">{{ $users->total() }}</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Gérez les membres, leurs rôles et leurs abonnements.</p>
    </div>
</div>

{{-- ── KPI strip ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-5">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-slate-50">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-500"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500 uppercase tracking-wide">Total</span>
        </div>
        <p class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($counts['total']) }}</p>
        <p class="text-[11px] text-gray-400 mt-1">utilisateurs inscrits</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-teal-50">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-teal-600"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-teal-50 text-teal-700 uppercase tracking-wide">Admins</span>
        </div>
        <p class="text-3xl font-extrabold text-teal-600 tracking-tight">{{ $counts['admin'] }}</p>
        <p class="text-[11px] text-gray-400 mt-1">rôles privilégiés</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-amber-50">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-500"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            @if($counts['unverified'] > 0)
            <a href="{{ route('admin.users.index', ['verified' => '0']) }}" class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-600 uppercase tracking-wide hover:bg-amber-100 transition">Voir</a>
            @endif
        </div>
        <p class="text-3xl font-extrabold text-amber-500 tracking-tight">{{ $counts['unverified'] }}</p>
        <p class="text-[11px] text-gray-400 mt-1">email en attente</p>
    </div>

</div>

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<form method="GET" id="filterForm">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
    <div class="flex flex-wrap gap-2.5 items-center">

        {{-- Search --}}
        <div class="relative flex-1 min-w-52">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nom, prénom, email…"
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
        </div>

        {{-- Rôle --}}
        <div class="flex flex-col gap-0.5">
            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest px-1">Rôle</label>
            <select name="role" onchange="document.getElementById('filterForm').submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 bg-white transition min-w-28">
                <option value="">Tous</option>
                <option value="user"  {{ request('role') === 'user'  ? 'selected' : '' }}>Utilisateur</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        {{-- Région --}}
        <div class="flex flex-col gap-0.5">
            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest px-1">Région</label>
            <select name="city_id" onchange="document.getElementById('filterForm').submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 bg-white transition min-w-36">
                <option value="">Toutes</option>
                @foreach($regions as $r)
                <option value="{{ $r->id }}" {{ request('city_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Plan --}}
        <div class="flex flex-col gap-0.5">
            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest px-1">Plan</label>
            <select name="plan" onchange="document.getElementById('filterForm').submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 bg-white transition min-w-28">
                <option value="">Tous</option>
                @foreach($plans as $p)
                <option value="{{ $p->name }}" {{ request('plan') === $p->name ? 'selected' : '' }}>{{ $p->label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Email --}}
        <div class="flex flex-col gap-0.5">
            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest px-1">Email</label>
            <select name="verified" onchange="document.getElementById('filterForm').submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 bg-white transition min-w-28">
                <option value="">Tous</option>
                <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Vérifiés</option>
                <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Non vérifiés</option>
            </select>
        </div>

        <div class="flex items-end gap-2 ml-auto">
            <button type="submit"
                    class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                    style="background:#0D9488;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                Filtrer
            </button>
            @if(request()->hasAny(['search','role','city_id','plan','verified']))
            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50 transition">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Reset
            </a>
            @endif
        </div>

    </div>
</div>
</form>

{{-- ── Table ───────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Table header info --}}
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
        <p class="text-xs font-medium text-gray-400">
            <span class="font-bold text-gray-700">{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</span>
            sur {{ $users->total() }} utilisateurs
        </p>
        <p class="text-xs text-gray-400">Page {{ $users->currentPage() }} / {{ $users->lastPage() }}</p>
    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Utilisateur</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Région</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Plan</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Points</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Rôle</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Inscrit le</th>
                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $u)
            @php
                $planName = $u->subscription?->plan?->name;
                $planLabel = $u->subscription?->plan?->label;
                $planStyle = match($planName) {
                    'basic'         => 'bg-indigo-50 text-indigo-600',
                    'vip'           => 'bg-amber-50 text-amber-600',
                    'enterprise'    => 'bg-teal-50 text-teal-700',
                    default         => null,
                };
                $planDot = match($planName) {
                    'basic'         => 'bg-indigo-400',
                    'vip'           => 'bg-amber-400',
                    'enterprise'    => 'bg-teal-500',
                    default         => null,
                };
            @endphp
            <tr class="hover:bg-slate-50/60 transition-colors group">

                {{-- User --}}
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 ring-2 ring-white"
                             style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                            {{ strtoupper(substr($u->first_name,0,1).substr($u->last_name,0,1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 flex items-center gap-1.5">
                                {{ $u->first_name }} {{ $u->last_name }}
                                @if(!$u->email_verified_at)
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-amber-50 text-amber-600 uppercase tracking-wide flex-shrink-0">Non vérifié</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400 truncate max-w-xs">{{ $u->email }}</div>
                        </div>
                    </div>
                </td>

                {{-- Région --}}
                <td class="px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $u->region?->name ?? '—' }}</span>
                </td>

                {{-- Plan --}}
                <td class="px-4 py-3">
                    @if($planLabel && $planStyle)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $planStyle }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $planDot }}"></span>
                        {{ $planLabel }}
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-100 text-gray-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                        Free
                    </span>
                    @endif
                </td>

                {{-- Points --}}
                <td class="px-4 py-3">
                    <div class="flex items-baseline gap-1">
                        <span class="font-bold text-gray-800">{{ number_format($u->points_balance) }}</span>
                        <span class="text-[10px] font-semibold text-gray-300 uppercase">pts</span>
                    </div>
                </td>

                {{-- Rôle --}}
                <td class="px-4 py-3">
                    @if($u->role === 'admin')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-teal-50 text-teal-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                        Admin
                    </span>
                    @elseif($u->role === 'super_admin')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-violet-50 text-violet-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                        Super Admin
                    </span>
                    @else
                    <span class="text-xs text-gray-400 font-medium">Membre</span>
                    @endif
                </td>

                {{-- Date --}}
                <td class="px-4 py-3">
                    <span class="text-sm text-gray-500">{{ $u->created_at->format('d/m/Y') }}</span>
                </td>

                {{-- Actions --}}
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('admin.users.show', $u) }}"
                           title="Voir le profil"
                           class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-teal-600 hover:bg-teal-50 transition">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <a href="{{ route('admin.users.edit', $u) }}"
                           title="Modifier"
                           class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        @if($u->id !== auth()->id() && $u->role !== 'super_admin')
                        <form method="POST" action="{{ route('admin.users.destroy', $u) }}">
                            @csrf @method('DELETE')
                            <button type="button"
                                    data-name="{{ $u->first_name }} {{ $u->last_name }}"
                                    onclick="swalDelete(this, this.dataset.name)"
                                    title="Supprimer"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-400">Aucun utilisateur trouvé</p>
                    <p class="text-xs text-gray-300 mt-0.5">Essayez d'élargir les filtres</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400">
            Affichage de <span class="font-semibold text-gray-600">{{ $users->firstItem() }}</span>
            à <span class="font-semibold text-gray-600">{{ $users->lastItem() }}</span>
            sur <span class="font-semibold text-gray-600">{{ $users->total() }}</span>
        </p>
        <div class="flex items-center gap-1">
            {{-- Previous --}}
            @if($users->onFirstPage())
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 cursor-not-allowed">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </span>
            @else
            <a href="{{ $users->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            @endif

            {{-- Pages --}}
            @foreach($users->getUrlRange(max(1, $users->currentPage()-2), min($users->lastPage(), $users->currentPage()+2)) as $page => $url)
            @if($page == $users->currentPage())
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white" style="background:#0D9488;">{{ $page }}</span>
            @else
            <a href="{{ $url }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-medium text-gray-500 hover:bg-gray-100 transition">{{ $page }}</a>
            @endif
            @endforeach

            {{-- Next --}}
            @if($users->hasMorePages())
            <a href="{{ $users->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-100 transition">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            @else
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 cursor-not-allowed">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </span>
            @endif
        </div>
    </div>
    @endif

</div>

@endsection
