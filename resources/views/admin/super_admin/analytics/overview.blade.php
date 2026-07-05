@extends('admin.layouts.admin')
@section('title', 'Analytics — Vue d\'ensemble')
@section('page-title', 'Analytics')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';

document.addEventListener('DOMContentLoaded', function () {

    // ── User Growth ──────────────────────────────────────────────────────────
    new Chart(document.getElementById('chartUserGrowth'), {
        type: 'line',
        data: {
            labels: {!! json_encode($userGrowth['labels']) !!},
            datasets: [{
                label: 'Nouveaux membres',
                data: {!! json_encode($userGrowth['monthly']) !!},
                borderColor: '#14B8A6',
                backgroundColor: 'rgba(20,184,166,0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#14B8A6',
                pointRadius: 3,
                pointHoverRadius: 5,
            }, {
                label: 'Total cumulé',
                data: {!! json_encode($userGrowth['cumulative']) !!},
                borderColor: '#818CF8',
                backgroundColor: 'rgba(129,140,248,0.05)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 4,
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { boxWidth: 10, padding: 15, font: { size: 12 } } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { grid: { color: '#F1F5F9' }, ticks: { font: { size: 11 } }, title: { display: true, text: 'Inscriptions', font: { size: 11 } } },
                y1: { position: 'right', grid: { display: false }, ticks: { font: { size: 11 } }, title: { display: true, text: 'Total', font: { size: 11 } } },
            }
        }
    });

    // ── Leads ────────────────────────────────────────────────────────────────
    new Chart(document.getElementById('chartLeads'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($leadsChart['labels']) !!},
            datasets: [{
                label: 'Envoyés',
                data: {!! json_encode($leadsChart['sent']) !!},
                backgroundColor: 'rgba(59,130,246,0.15)',
                borderColor: '#3B82F6',
                borderWidth: 1.5,
                borderRadius: 6,
            }, {
                label: 'Acceptés',
                data: {!! json_encode($leadsChart['accepted']) !!},
                backgroundColor: 'rgba(34,197,94,0.15)',
                borderColor: '#22C55E',
                borderWidth: 1.5,
                borderRadius: 6,
            }, {
                label: 'Rejetés',
                data: {!! json_encode($leadsChart['rejected']) !!},
                backgroundColor: 'rgba(239,68,68,0.15)',
                borderColor: '#EF4444',
                borderWidth: 1.5,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { boxWidth: 10, padding: 15, font: { size: 12 } } } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#F1F5F9' }, beginAtZero: true }
            }
        }
    });

    // ── Subscription Distribution (Donut) ────────────────────────────────────
    @php
    $distLabels = collect($subDistribution)->pluck('label')->toArray();
    $distData   = collect($subDistribution)->pluck('count')->toArray();
    $colors     = ['#7C3AED','#14B8A6','#F59E0B','#3B82F6','#EF4444','#8B5CF6'];
    @endphp
    new Chart(document.getElementById('chartSubDist'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($distLabels) !!},
            datasets: [{
                data: {!! json_encode($distData) !!},
                backgroundColor: {!! json_encode(array_slice($colors, 0, count($distLabels))) !!},
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 14, font: { size: 12 } } }
            }
        }
    });
});
</script>
@endpush

@section('content')

@include('admin.super_admin.analytics._nav')

{{-- ── Today's Pulse ───────────────────────────────────────────────────── --}}
<div class="grid grid-cols-5 gap-3 mb-5">
    @php
    $pulse = [
        ['label' => 'Nouveaux membres', 'value' => $todayActivity['new_users'],       'color' => '#14B8A6', 'bg' => '#F0FDFA'],
        ['label' => 'Leads créés',      'value' => $todayActivity['new_leads'],       'color' => '#3B82F6', 'bg' => '#EFF6FF'],
        ['label' => 'Connexions',       'value' => $todayActivity['new_connections'], 'color' => '#22C55E', 'bg' => '#F0FDF4'],
        ['label' => 'Événements',       'value' => $todayActivity['new_events'],      'color' => '#F97316', 'bg' => '#FFF7ED'],
        ['label' => 'Groupes créés',    'value' => $todayActivity['new_groups'],      'color' => '#8B5CF6', 'bg' => '#F5F3FF'],
    ];
    @endphp
    @foreach($pulse as $p)
    <div class="rounded-2xl px-4 py-3 flex items-center gap-3" style="background:{{ $p['bg'] }}; border:1px solid {{ $p['color'] }}20;">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-lg" style="background:{{ $p['color'] }}20; color:{{ $p['color'] }};">
            {{ $p['value'] }}
        </div>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest" style="color:{{ $p['color'] }};">Aujourd'hui</p>
            <p class="text-xs font-semibold text-slate-600 mt-0.5">{{ $p['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ── KPI Grid ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-4 gap-3 mb-5">

    {{-- Users --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Utilisateurs</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($kpis['totalUsers']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#F0FDFA;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 text-center">
            <div class="rounded-xl py-1.5 px-2" style="background:#F0FDFA;">
                <p class="text-lg font-bold" style="color:#14B8A6;">{{ $kpis['newToday'] }}</p>
                <p class="text-[9px] font-semibold text-slate-400 uppercase">Aujourd'hui</p>
            </div>
            <div class="rounded-xl py-1.5 px-2" style="background:#F0FDFA;">
                <p class="text-lg font-bold" style="color:#14B8A6;">{{ $kpis['newWeek'] }}</p>
                <p class="text-[9px] font-semibold text-slate-400 uppercase">Semaine</p>
            </div>
            <div class="rounded-xl py-1.5 px-2" style="background:#F0FDFA;">
                <p class="text-lg font-bold" style="color:#14B8A6;">{{ $kpis['newMonth'] }}</p>
                <p class="text-[9px] font-semibold text-slate-400 uppercase">Mois</p>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
            <span>Actifs (30j) : <strong class="text-slate-700">{{ $kpis['activeUsers'] }}</strong></span>
            <span class="text-amber-600">Non vérifiés : {{ $kpis['unverified'] }}</span>
        </div>
    </div>

    {{-- Leads --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Leads</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($kpis['totalLeads']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#EFF6FF;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-500">Acceptés</span>
                <div class="flex items-center gap-2">
                    <div class="h-1.5 rounded-full" style="background:#22C55E; width:{{ $kpis['totalLeads'] > 0 ? round($kpis['accepted']/$kpis['totalLeads']*80) : 0 }}px; min-width:4px;"></div>
                    <span class="text-xs font-semibold text-slate-700">{{ $kpis['accepted'] }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-500">Rejetés</span>
                <div class="flex items-center gap-2">
                    <div class="h-1.5 rounded-full" style="background:#EF4444; width:{{ $kpis['totalLeads'] > 0 ? round($kpis['rejected']/$kpis['totalLeads']*80) : 0 }}px; min-width:4px;"></div>
                    <span class="text-xs font-semibold text-slate-700">{{ $kpis['rejected'] }}</span>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-500">Expirés</span>
                <div class="flex items-center gap-2">
                    <div class="h-1.5 rounded-full" style="background:#94A3B8; width:{{ $kpis['totalLeads'] > 0 ? round($kpis['expired']/$kpis['totalLeads']*80) : 0 }}px; min-width:4px;"></div>
                    <span class="text-xs font-semibold text-slate-700">{{ $kpis['expired'] }}</span>
                </div>
            </div>
        </div>
        <div class="mt-3 flex items-center gap-2 px-3 py-2 rounded-xl" style="background:#F0FDF4;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
            <span class="text-xs font-bold" style="color:#15803D;">Taux de conversion : {{ $kpis['convRate'] }}%</span>
        </div>
    </div>

    {{-- Connections --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Connexions</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($kpis['totalConn']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#F0FDF4;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between text-xs"><span class="text-slate-500">Invitations totales</span><strong class="text-slate-700">{{ $kpis['totalInv'] }}</strong></div>
            <div class="flex justify-between text-xs"><span class="text-slate-500">En attente</span><strong class="text-amber-600">{{ $kpis['pendingConn'] }}</strong></div>
            <div class="flex justify-between text-xs"><span class="text-slate-500">Refusées</span><strong class="text-red-500">{{ $kpis['rejectedConn'] }}</strong></div>
        </div>
        <div class="mt-3 flex items-center gap-2 px-3 py-2 rounded-xl" style="background:#F0FDF4;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
            <span class="text-xs font-bold" style="color:#15803D;">Taux d'acceptation : {{ $kpis['connRate'] }}%</span>
        </div>
    </div>

    {{-- Events & Groups --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Événements</p>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ number_format($kpis['totalEvents']) }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FFF7ED;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F97316" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-3">
            <div class="rounded-xl p-2 text-center" style="background:#FFF7ED;">
                <p class="text-base font-bold text-orange-600">{{ $kpis['upcoming'] }}</p>
                <p class="text-[10px] text-slate-400 font-semibold">À venir</p>
            </div>
            <div class="rounded-xl p-2 text-center" style="background:#F0FDF4;">
                <p class="text-base font-bold text-green-600">{{ $kpis['completed'] }}</p>
                <p class="text-[10px] text-slate-400 font-semibold">Terminés</p>
            </div>
        </div>
        <div class="flex items-center justify-between text-xs border-t border-gray-100 pt-2">
            <span class="text-slate-500">Groupes / Pôles</span>
            <strong class="text-violet-600 text-base">{{ $kpis['totalGroups'] }}</strong>
        </div>
    </div>
</div>

{{-- ── Second KPI Row ───────────────────────────────────────────────────── --}}
<div class="grid grid-cols-4 gap-3 mb-5">

    {{-- Subscriptions --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-3">Abonnements actifs</p>
        <p class="text-3xl font-bold text-slate-900 mb-3">{{ number_format($kpis['activeSubs']) }}</p>
        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs text-slate-600"><span class="w-2 h-2 rounded-full bg-violet-500"></span>Payants</span>
                <span class="text-xs font-bold text-violet-600">{{ $kpis['premiumSubs'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-1.5 text-xs text-slate-600"><span class="w-2 h-2 rounded-full bg-slate-300"></span>Gratuits</span>
                <span class="text-xs font-semibold text-slate-500">{{ $kpis['freeSubs'] }}</span>
            </div>
        </div>
    </div>

    {{-- Revenue --}}
    <div class="rounded-2xl p-5 shadow-sm border" style="background:linear-gradient(135deg,#7C3AED,#6D28D9); border-color:#5B21B6;">
        <p class="text-[11px] font-bold uppercase tracking-widest text-violet-200 mb-1">Revenus mensuels</p>
        <p class="text-3xl font-bold text-white mt-2">{{ number_format($kpis['revenue'], 2) }} €</p>
        <p class="text-xs text-violet-200 mt-2">Basé sur les abonnements actifs</p>
        <div class="mt-3 flex items-center gap-2 text-xs text-violet-200">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Stripe · Paiements en ligne
        </div>
    </div>

    {{-- Ambassadors --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-3">Ambassadeurs</p>
        <p class="text-3xl font-bold text-slate-900 mb-3">{{ $kpis['ambassadors'] }}</p>
        <div class="space-y-2">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">Consuls</span>
                <strong class="text-teal-600">{{ $kpis['consuls'] }}</strong>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">Demandes en attente</span>
                @if($kpis['pendingAmb'] > 0)
                <a href="{{ route('admin.super.consul.index') }}" class="font-bold text-amber-600 hover:underline">{{ $kpis['pendingAmb'] }} →</a>
                @else
                <strong class="text-slate-400">0</strong>
                @endif
            </div>
        </div>
    </div>

    {{-- Profile Completion --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-3">Complétion des profils</p>
        <div class="flex items-center gap-3 mb-3">
            <div class="relative w-14 h-14 flex-shrink-0">
                <svg viewBox="0 0 36 36" class="w-14 h-14 -rotate-90">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#F1F5F9" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#14B8A6" stroke-width="3"
                        stroke-dasharray="{{ $profileCompletion['average'] }} {{ 100 - $profileCompletion['average'] }}"
                        stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-sm font-bold text-slate-700">{{ $profileCompletion['average'] }}%</span>
                </div>
            </div>
            <div class="flex-1 space-y-1.5">
                @foreach($profileCompletion['details'] as $d)
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-slate-500 truncate">{{ $d['label'] }}</span>
                    <span class="font-bold ml-2 {{ $d['pct'] >= 70 ? 'text-emerald-600' : ($d['pct'] >= 40 ? 'text-amber-600' : 'text-red-500') }}">{{ $d['pct'] }}%</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4">

    {{-- User Growth --}}
    <div class="col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Croissance des utilisateurs</h3>
                <p class="text-xs text-slate-400">12 derniers mois</p>
            </div>
            <a href="{{ route('admin.super.analytics.users') }}" class="text-xs font-semibold text-teal-600 hover:underline">Voir détails →</a>
        </div>
        <div style="height:220px;"><canvas id="chartUserGrowth"></canvas></div>
    </div>

    {{-- Subscription Donut --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Répartition abonnements</h3>
                <p class="text-xs text-slate-400">Abonnements actifs</p>
            </div>
            <a href="{{ route('admin.super.analytics.subscriptions') }}" class="text-xs font-semibold text-violet-600 hover:underline">Voir →</a>
        </div>
        @if(count($subDistribution) > 0)
        <div style="height:220px;"><canvas id="chartSubDist"></canvas></div>
        @else
        <div class="h-52 flex items-center justify-center text-sm text-slate-400">Aucune donnée</div>
        @endif
    </div>

    {{-- Leads Bar --}}
    <div class="col-span-3 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Activité des leads</h3>
                <p class="text-xs text-slate-400">6 derniers mois — envoyés / acceptés / rejetés</p>
            </div>
            <a href="{{ route('admin.super.analytics.leads') }}" class="text-xs font-semibold text-blue-600 hover:underline">Voir détails →</a>
        </div>
        <div style="height:200px;"><canvas id="chartLeads"></canvas></div>
    </div>
</div>

@endsection
