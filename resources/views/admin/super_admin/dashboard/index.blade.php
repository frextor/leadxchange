@extends('admin.layouts.admin')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── Header greeting ────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
            Bonjour, <span style="color:#0D9488;">{{ auth()->user()->first_name }}</span> 👋
        </h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>
    <div class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-xs text-gray-500 shadow-sm">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-teal-500"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Dernière mise à jour : {{ now()->format('H:i') }}
    </div>
</div>

{{-- ── Alerts ──────────────────────────────────────────────────────────── --}}
@php
    $alertBanners = [];
    if ($stats['pending_videos'] > 0)     $alertBanners[] = ['msg' => $stats['pending_videos'].' vidéo(s) en attente',          'route' => 'admin.videos.index',              'cls' => 'bg-amber-50 border-amber-200 text-amber-700'];
    if ($stats['fraud_leads'] > 0)        $alertBanners[] = ['msg' => $stats['fraud_leads'].' lead(s) signalé(s) fraude',        'route' => 'admin.leads.index',               'cls' => 'bg-red-50 border-red-200 text-red-600'];
    if ($stats['unverified_users'] > 0)   $alertBanners[] = ['msg' => $stats['unverified_users'].' utilisateur(s) non vérifiés', 'route' => 'admin.users.index',               'cls' => 'bg-blue-50 border-blue-200 text-blue-700'];
    if ($stats['pending_ambassadors'] > 0)$alertBanners[] = ['msg' => $stats['pending_ambassadors'].' demande(s) ambassadeur',   'route' => 'admin.super.ambassadors.index',   'cls' => 'bg-violet-50 border-violet-200 text-violet-700'];
@endphp
@if(count($alertBanners))
<div class="flex flex-wrap gap-2 mb-6">
    @foreach($alertBanners as $ab)
    <a href="{{ route($ab['route']) }}"
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border text-xs font-semibold transition hover:shadow-sm {{ $ab['cls'] }}">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        {{ $ab['msg'] }}
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
    </a>
    @endforeach
</div>
@endif

{{-- ── Primary KPI cards (4) ───────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">

    {{-- Membres --}}
    <a href="{{ route('admin.users.index') }}"
       class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-teal-50 group-hover:bg-teal-100 transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-teal-600"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            @if($stats['member_growth'] != 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $stats['member_growth'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                {{ $stats['member_growth'] >= 0 ? '+' : '' }}{{ $stats['member_growth'] }}%
            </span>
            @endif
        </div>
        <p class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total_members']) }}</p>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-1.5">Membres</p>
        <p class="text-[11px] text-gray-300 mt-0.5">30 derniers jours</p>
    </a>

    {{-- Leads --}}
    <a href="{{ route('admin.leads.index') }}"
       class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-indigo-50 group-hover:bg-indigo-100 transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-500"><path d="M7 16V4m0 0L3 8m4-4 4 4M17 8v12m0 0 4-4m-4 4-4-4"/></svg>
            </div>
            @if($stats['leads_growth'] != 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $stats['leads_growth'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                {{ $stats['leads_growth'] >= 0 ? '+' : '' }}{{ $stats['leads_growth'] }}%
            </span>
            @endif
        </div>
        <p class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total_leads']) }}</p>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-1.5">Leads échangés</p>
        <p class="text-[11px] text-gray-300 mt-0.5">30 derniers jours</p>
    </a>

    {{-- Connexions --}}
    <a href="{{ route('admin.users.index') }}"
       class="group bg-white rounded-2xl p-5 border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50 group-hover:bg-blue-100 transition-colors">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            </div>
            @if($stats['conn_growth'] != 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $stats['conn_growth'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500' }}">
                {{ $stats['conn_growth'] >= 0 ? '+' : '' }}{{ $stats['conn_growth'] }}%
            </span>
            @endif
        </div>
        <p class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total_connections']) }}</p>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-1.5">Connexions</p>
        <p class="text-[11px] text-gray-300 mt-0.5">relations acceptées</p>
    </a>

    {{-- Revenu --}}
    <a href="{{ route('admin.super.subscribers.index') }}"
       class="group rounded-2xl p-5 border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block"
       style="background:linear-gradient(135deg,#0D9488,#0F766E); border-color:#0D9488;">
        <div class="flex items-start justify-between mb-4">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,.15);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            @if($stats['subs_growth'] != 0)
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full" style="background:rgba(255,255,255,.2);color:#fff;">
                {{ $stats['subs_growth'] >= 0 ? '+' : '' }}{{ $stats['subs_growth'] }}%
            </span>
            @endif
        </div>
        <p class="text-3xl font-extrabold text-white tracking-tight">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} €</p>
        <p class="text-xs font-semibold uppercase tracking-wider mt-1.5" style="color:rgba(255,255,255,.65);">Revenu mensuel</p>
        <p class="text-[11px] mt-0.5" style="color:rgba(255,255,255,.45);">{{ number_format($stats['total_subscribers']) }} abonnés actifs</p>
    </a>

</div>

{{-- ── Secondary KPI strip (4 compact) ───────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-4">

        <a href="{{ route('admin.events.index') }}"
           class="flex items-center gap-3 px-5 py-4 border-r border-b border-gray-100 hover:bg-gray-50 transition group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-amber-50 flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-500"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xl font-bold text-gray-900">{{ number_format($stats['total_events']) }}</p>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Événements</p>
                <p class="text-[10px] text-gray-300">{{ $stats['upcoming_events'] }} à venir</p>
            </div>
        </a>

        <a href="{{ route('admin.groups.index') }}"
           class="flex items-center gap-3 px-5 py-4 border-r border-b border-gray-100 hover:bg-gray-50 transition group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-pink-50 flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-pink-500"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0-3-3.85"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xl font-bold text-gray-900">{{ number_format($stats['total_groups']) }}</p>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Groupes</p>
                <p class="text-[10px] text-gray-300">groupes actifs</p>
            </div>
        </a>

        <a href="{{ route('admin.super.ambassadors.index') }}"
           class="flex items-center gap-3 px-5 py-4 border-r border-b border-gray-100 hover:bg-gray-50 transition group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-violet-50 flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-violet-600"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xl font-bold text-gray-900">{{ $stats['total_ambassadors'] }}</p>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Ambassadeurs</p>
                @if($stats['pending_ambassadors'] > 0)
                <p class="text-[10px] text-violet-500 font-semibold">{{ $stats['pending_ambassadors'] }} en attente</p>
                @else
                <p class="text-[10px] text-gray-300">actifs</p>
                @endif
            </div>
        </a>

        <a href="{{ route('admin.super.subscribers.index') }}"
           class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 hover:bg-gray-50 transition group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-emerald-50 flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-xl font-bold text-gray-900">{{ number_format($stats['total_subscribers']) }}</p>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Abonnés</p>
                <p class="text-[10px] text-gray-300">abonnements actifs</p>
            </div>
        </a>

    </div>
</div>

{{-- ── Charts ────────────────────────────────────────────────────────────── --}}
@php
    $donutTotal   = $planDistribution->sum('active_subscriptions_count');
    $planColors   = ['basic' => '#6366F1', 'vip' => '#F59E0B', 'enterprise' => '#0D9488'];
    $planDotClass = ['basic' => 'bg-indigo-500', 'vip' => 'bg-amber-400', 'enterprise' => 'bg-teal-600'];
@endphp
<div id="dash-data" class="hidden"
     data-labels="{{ json_encode($chartLabels) }}"
     data-reg="{{ json_encode($chartRegistrations) }}"
     data-leads="{{ json_encode($chartLeads) }}"
     data-plan-labels="{{ json_encode($planDistribution->pluck('label')) }}"
     data-plan-counts="{{ json_encode($planDistribution->pluck('active_subscriptions_count')) }}"
     data-plan-colors="{{ json_encode($planDistribution->map(fn($p) => $planColors[$p->name] ?? '#9CA3AF')->values()) }}"></div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Inscriptions</h2>
                <p class="text-[11px] text-gray-400 mt-0.5">6 derniers mois</p>
            </div>
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-teal-50">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-teal-600"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </span>
        </div>
        <div class="relative h-36"><canvas id="chartReg"></canvas></div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Leads échangés</h2>
                <p class="text-[11px] text-gray-400 mt-0.5">6 derniers mois</p>
            </div>
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-50">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-500"><rect x="2" y="3" width="4" height="18"/><rect x="10" y="8" width="4" height="13"/><rect x="18" y="13" width="4" height="8"/></svg>
            </span>
        </div>
        <div class="relative h-36"><canvas id="chartLeads"></canvas></div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Plans actifs</h2>
                <p class="text-[11px] text-gray-400 mt-0.5">abonnements en cours</p>
            </div>
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
            </span>
        </div>
        <div class="flex items-center justify-center relative" style="height:110px;">
            <canvas id="chartPlans"></canvas>
        </div>
        <div class="space-y-1.5 mt-3">
            @foreach($planDistribution as $plan)
            @php
                $cnt    = $plan->active_subscriptions_count;
                $pct    = $donutTotal > 0 ? round(($cnt / $donutTotal) * 100) : 0;
                $dotCls = $planDotClass[$plan->name] ?? 'bg-gray-400';
            @endphp
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $dotCls }}"></span>
                    <span class="text-gray-500">{{ $plan->label }}</span>
                </div>
                <span class="font-semibold text-gray-900">{{ $cnt }}<span class="text-gray-300 font-normal ml-1">{{ $pct }}%</span></span>
            </div>
            @endforeach
        </div>
        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Revenu mensuel</span>
            <span class="text-base font-extrabold text-gray-900">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} €</span>
        </div>
    </div>

</div>

{{-- ── Bottom panels ───────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Derniers inscrits --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Derniers inscrits</h2>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-teal-600 hover:text-teal-700 flex items-center gap-1">
                Voir tous <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentUsers as $u)
            <div class="flex items-center gap-3 px-5 py-2.5 hover:bg-gray-50/60 transition">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                    {{ strtoupper(substr($u->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $u->first_name }} {{ $u->last_name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $u->email }}</p>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    @if($u->subscription?->plan)
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold bg-teal-50 text-teal-700">{{ $u->subscription->plan->label }}</span>
                    @endif
                    @if(!$u->email_verified_at)
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold bg-amber-50 text-amber-700">non vérifié</span>
                    @endif
                    <span class="text-[10px] text-gray-300 whitespace-nowrap">{{ $u->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-sm text-gray-400 text-center">Aucun utilisateur.</p>
            @endforelse
        </div>
    </div>

    {{-- Demandes ambassadeur --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">
                Demandes <span class="text-teal-600">ambassadeur</span>
                @if($stats['pending_ambassadors'] > 0)
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold text-white bg-violet-500">{{ $stats['pending_ambassadors'] }}</span>
                @endif
            </h2>
            <a href="{{ route('admin.super.ambassadors.index') }}" class="text-xs font-medium text-gray-400 hover:text-gray-600 flex items-center gap-1">
                Tout voir <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
        @if($pendingList->isEmpty())
        <div class="px-5 py-12 text-center">
            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"><path d="m5 12 5 5L20 7"/></svg>
            </div>
            <p class="text-sm text-gray-400 font-medium">Aucune demande en attente</p>
            <p class="text-xs text-gray-300 mt-0.5">Tout est à jour</p>
        </div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($pendingList as $applicant)
            @php
                $badgeColors = ['bronze' => 'text-amber-600', 'argent' => 'text-slate-500', 'or' => 'text-yellow-500'];
                $badgeCls = $badgeColors[$applicant->badge_level ?? 'bronze'] ?? 'text-amber-600';
            @endphp
            <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50/60 transition" id="amb-row-{{ $applicant->id }}">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                    {{ strtoupper(substr($applicant->first_name, 0, 1).substr($applicant->last_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $applicant->first_name }} {{ $applicant->last_name }}</p>
                    <p class="text-xs text-gray-400 truncate mt-0.5">
                        {{ $applicant->company?->name ?? '—' }}
                        @if($applicant->region) · {{ $applicant->region->name }}@endif
                        · <span class="font-semibold {{ $badgeCls }}">{{ ucfirst($applicant->badge_level ?? 'bronze') }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button onclick="openAmbReject(this)"
                            data-id="{{ $applicant->id }}"
                            data-reject-url="{{ route('admin.super.ambassadors.reject', $applicant) }}"
                            class="w-7 h-7 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 hover:border-red-200 hover:text-red-500 hover:bg-red-50 transition">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                    <form method="POST" action="{{ route('admin.super.ambassadors.approve', $applicant) }}"
                          onsubmit="fadeAmbRow(this)" data-id="{{ $applicant->id }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                                style="background:#0D9488;" onmouseover="this.style.background='#0F766E'" onmouseout="this.style.background='#0D9488'">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                            Approuver
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ── Reject modal ────────────────────────────────────────────────────── --}}
<div id="ambRejectModal" class="fixed inset-0 z-50 hidden items-center justify-center" style="background:rgba(2,6,23,.6); backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 border border-gray-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Rejeter la demande</h3>
                <p class="text-xs text-gray-400">Cette raison sera visible par l'utilisateur.</p>
            </div>
        </div>
        <form id="ambRejectForm" method="POST">
            @csrf
            <textarea name="reason" rows="3" required maxlength="500"
                      placeholder="Profil incomplet, secteur non éligible…"
                      class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-red-300 focus:ring-2 focus:ring-red-50 resize-none mb-4 transition"></textarea>
            <div class="flex gap-2.5">
                <button type="submit" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-500 hover:bg-red-600 transition">Rejeter</button>
                <button type="button" onclick="closeAmbReject()" class="flex-1 py-2.5 rounded-xl text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 transition">Annuler</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openAmbReject(el) {
    document.getElementById('ambRejectForm').action = el.dataset.rejectUrl;
    const modal = document.getElementById('ambRejectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.querySelector('textarea').focus();
}
function closeAmbReject() {
    const modal = document.getElementById('ambRejectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('ambRejectModal').addEventListener('click', e => {
    if (e.target === e.currentTarget) closeAmbReject();
});
function fadeAmbRow(form) {
    const row = document.getElementById('amb-row-' + form.dataset.id);
    if (row) { row.style.opacity = '.3'; row.style.pointerEvents = 'none'; }
    return true;
}

const dd         = document.getElementById('dash-data').dataset;
const labels     = JSON.parse(dd.labels);
const dataReg    = JSON.parse(dd.reg);
const dataLeads  = JSON.parse(dd.leads);
const planLabels = JSON.parse(dd.planLabels);
const planCounts = JSON.parse(dd.planCounts);
const planColors = JSON.parse(dd.planColors);

const baseOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            mode: 'index', intersect: false,
            backgroundColor: '#0F172A', titleColor: '#E2E8F0', bodyColor: '#94A3B8',
            padding: 10, cornerRadius: 8, titleFont: { size: 11 }, bodyFont: { size: 11 },
        }
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF' }, border: { display: false } },
        y: { beginAtZero: true, grid: { color: '#F1F5F9' }, ticks: { font: { size: 10 }, color: '#9CA3AF', precision: 0 }, border: { display: false } },
    },
};

new Chart(document.getElementById('chartReg'), {
    type: 'line',
    data: {
        labels,
        datasets: [{
            data: dataReg,
            borderColor: '#0D9488',
            backgroundColor: (ctx) => {
                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 144);
                g.addColorStop(0, 'rgba(13,148,136,.18)');
                g.addColorStop(1, 'rgba(13,148,136,0)');
                return g;
            },
            borderWidth: 2.5, pointRadius: 3, pointBackgroundColor: '#0D9488',
            pointHoverRadius: 5, fill: true, tension: 0.4,
        }],
    },
    options: baseOpts,
});

new Chart(document.getElementById('chartLeads'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            data: dataLeads,
            backgroundColor: 'rgba(99,102,241,.65)',
            hoverBackgroundColor: 'rgba(99,102,241,.9)',
            borderRadius: 6, borderSkipped: false,
        }],
    },
    options: baseOpts,
});

new Chart(document.getElementById('chartPlans'), {
    type: 'doughnut',
    data: {
        labels: planLabels,
        datasets: [{ data: planCounts, backgroundColor: planColors, borderWidth: 3, borderColor: '#fff', hoverOffset: 6 }],
    },
    options: {
        responsive: true, maintainAspectRatio: true, cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: '#0F172A', titleColor: '#E2E8F0', bodyColor: '#94A3B8', padding: 8, cornerRadius: 8 },
        },
    },
});
</script>
@endpush
@endsection
