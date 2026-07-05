@extends('admin.layouts.admin')
@section('title', 'Analytics — Utilisateurs')
@section('page-title', 'Analytics · Utilisateurs')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';
document.addEventListener('DOMContentLoaded', function () {

    new Chart(document.getElementById('chartGrowth'), {
        type: 'line',
        data: {
            labels: {!! json_encode($userGrowth['labels']) !!},
            datasets: [{
                label: 'Nouveaux / mois',
                data: {!! json_encode($userGrowth['monthly']) !!},
                borderColor: '#14B8A6', backgroundColor: 'rgba(20,184,166,0.1)',
                borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 6,
            }, {
                label: 'Total cumulé',
                data: {!! json_encode($userGrowth['cumulative']) !!},
                borderColor: '#818CF8', backgroundColor: 'rgba(129,140,248,0.06)',
                borderWidth: 2, fill: true, tension: 0.4, pointRadius: 0, yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { boxWidth: 10, font: { size: 12 } } } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#F1F5F9' }, title: { display: true, text: 'Inscriptions/mois', font: { size: 11 } } },
                y1: { position: 'right', grid: { display: false }, title: { display: true, text: 'Total membres', font: { size: 11 } } },
            }
        }
    });

    new Chart(document.getElementById('chartRegion'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($usersByRegion)->pluck('name')->toArray()) !!},
            datasets: [{
                label: 'Utilisateurs',
                data: {!! json_encode(collect($usersByRegion)->pluck('count')->toArray()) !!},
                backgroundColor: 'rgba(20,184,166,0.2)',
                borderColor: '#14B8A6',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: '#F1F5F9' }, beginAtZero: true },
                y: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
});
</script>
@endpush

@section('content')
@include('admin.super_admin.analytics._nav')

{{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-5 gap-3 mb-5">
    @php
    $cards = [
        ['label' => 'Total membres',  'value' => $kpis['totalUsers'],  'sub' => null,                    'color' => '#14B8A6', 'bg' => '#F0FDFA'],
        ['label' => 'Actifs (30j)',   'value' => $kpis['activeUsers'], 'sub' => $kpis['totalUsers'] > 0 ? round($kpis['activeUsers']/$kpis['totalUsers']*100,1).'%' : '—', 'color' => '#3B82F6', 'bg' => '#EFF6FF'],
        ['label' => 'Vérifiés',       'value' => $kpis['verified'],    'sub' => $kpis['totalUsers'] > 0 ? round($kpis['verified']/$kpis['totalUsers']*100,1).'%' : '—',    'color' => '#22C55E', 'bg' => '#F0FDF4'],
        ['label' => 'Non vérifiés',   'value' => $kpis['unverified'],  'sub' => 'Email en attente',       'color' => '#F59E0B', 'bg' => '#FFFBEB'],
        ['label' => 'Ce mois',        'value' => $kpis['newMonth'],    'sub' => 'Nouvelles inscriptions', 'color' => '#8B5CF6', 'bg' => '#F5F3FF'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-2" style="background:{{ $c['bg'] }};">
            <div class="w-3 h-3 rounded-full" style="background:{{ $c['color'] }};"></div>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($c['value']) }}</p>
        <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ $c['label'] }}</p>
        @if($c['sub'])<p class="text-[11px] font-medium mt-1" style="color:{{ $c['color'] }};">{{ $c['sub'] }}</p>@endif
    </div>
    @endforeach
</div>

{{-- ── Extra stats row ─────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-2xl px-5 py-4 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl font-bold flex-shrink-0" style="background:#F0FDFA; color:#14B8A6;">
            {{ $kpis['newToday'] }}
        </div>
        <div><p class="text-sm font-bold text-slate-700">Inscriptions aujourd'hui</p><p class="text-xs text-slate-400">{{ now()->format('d/m/Y') }}</p></div>
    </div>
    <div class="bg-white rounded-2xl px-5 py-4 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl font-bold flex-shrink-0" style="background:#EFF6FF; color:#3B82F6;">
            {{ $kpis['newWeek'] }}
        </div>
        <div><p class="text-sm font-bold text-slate-700">Inscriptions cette semaine</p><p class="text-xs text-slate-400">{{ now()->startOfWeek()->format('d/m') }} – {{ now()->format('d/m') }}</p></div>
    </div>
    <div class="bg-white rounded-2xl px-5 py-4 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl font-bold flex-shrink-0" style="background:#F5F3FF; color:#8B5CF6;">
            {{ $kpis['ambassadors'] }}
        </div>
        <div><p class="text-sm font-bold text-slate-700">Ambassadeurs actifs</p><p class="text-xs text-slate-400">{{ $kpis['consuls'] }} consuls</p></div>
    </div>
</div>

{{-- ── Charts ───────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-5 gap-4 mb-5">
    <div class="col-span-3 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="text-sm font-bold text-slate-800">Croissance des membres</h3><p class="text-xs text-slate-400">12 derniers mois</p></div>
        </div>
        <div style="height:240px;"><canvas id="chartGrowth"></canvas></div>
    </div>
    <div class="col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="text-sm font-bold text-slate-800">Top régions</h3><p class="text-xs text-slate-400">Membres par ville</p></div>
        </div>
        <div style="height:240px;"><canvas id="chartRegion"></canvas></div>
    </div>
</div>

{{-- ── Profile Completion ───────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Complétion des profils</h3>
            <p class="text-xs text-slate-400">Sur {{ number_format($profileCompletion['total']) }} membres enregistrés</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-xl" style="background:#F0FDFA;">
            <span class="text-xl font-bold" style="color:#14B8A6;">{{ $profileCompletion['average'] }}%</span>
            <span class="text-xs text-slate-500 font-medium">complétion moyenne</span>
        </div>
    </div>
    <div class="grid grid-cols-4 gap-4">
        @foreach($profileCompletion['details'] as $d)
        <div class="rounded-2xl p-4 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-600">{{ $d['label'] }}</span>
                <span class="text-lg font-bold {{ $d['pct'] >= 70 ? 'text-emerald-600' : ($d['pct'] >= 40 ? 'text-amber-600' : 'text-red-500') }}">{{ $d['pct'] }}%</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all" style="width:{{ $d['pct'] }}%; background:{{ $d['pct'] >= 70 ? '#22C55E' : ($d['pct'] >= 40 ? '#F59E0B' : '#EF4444') }};"></div>
            </div>
            <div class="flex justify-between text-[11px] text-slate-400 mt-1.5">
                <span>{{ number_format($d['with']) }} remplis</span>
                <span class="text-red-400">{{ number_format($d['missing']) }} manquants</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
