@extends('admin.layouts.admin')
@section('title', 'Analytics — Connexions')
@section('page-title', 'Analytics · Connexions')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';
document.addEventListener('DOMContentLoaded', function () {

    new Chart(document.getElementById('chartConnections'), {
        type: 'line',
        data: {
            labels: {!! json_encode($chart['labels']) !!},
            datasets: [{
                label: 'Invitations envoyées',
                data: {!! json_encode($chart['sent']) !!},
                borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,0.08)',
                borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 3, pointHoverRadius: 5,
            }, {
                label: 'Connexions établies',
                data: {!! json_encode($chart['accepted']) !!},
                borderColor: '#22C55E', backgroundColor: 'rgba(34,197,94,0.08)',
                borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 3, pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { boxWidth: 10, font: { size: 12 } } } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#F1F5F9' }, beginAtZero: true } }
        }
    });

    new Chart(document.getElementById('chartRate'), {
        type: 'doughnut',
        data: {
            labels: ['Acceptées','Rejetées','En attente'],
            datasets: [{
                data: [{{ $accepted }}, {{ $rejected }}, {{ $pending }}],
                backgroundColor: ['#22C55E','#EF4444','#F59E0B'],
                borderWidth: 0, hoverOffset: 8,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
        }
    });
});
</script>
@endpush

@section('content')
@include('admin.super_admin.analytics._nav')

<div class="grid grid-cols-4 gap-3 mb-5">
    @php
    $cards = [
        ['label' => 'Invitations totales',  'value' => $total,    'color' => '#3B82F6', 'bg' => '#EFF6FF'],
        ['label' => 'Connexions établies',  'value' => $accepted, 'color' => '#22C55E', 'bg' => '#F0FDF4'],
        ['label' => 'Refusées',             'value' => $rejected, 'color' => '#EF4444', 'bg' => '#FEF2F2'],
        ['label' => 'En attente',           'value' => $pending,  'color' => '#F59E0B', 'bg' => '#FFFBEB'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="w-10 h-10 rounded-xl mb-3" style="background:{{ $c['bg'] }};"></div>
        <p class="text-3xl font-bold text-slate-900">{{ number_format($c['value']) }}</p>
        <p class="text-xs font-semibold text-slate-500 mt-1">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-3 gap-4">
    <div class="col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="text-sm font-bold text-slate-800">Évolution des connexions</h3><p class="text-xs text-slate-400">12 derniers mois</p></div>
        </div>
        <div style="height:260px;"><canvas id="chartConnections"></canvas></div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="text-sm font-bold text-slate-800">Répartition des statuts</h3><p class="text-xs text-slate-400">Toutes les invitations</p></div>
        </div>
        <div style="height:200px;"><canvas id="chartRate"></canvas></div>
        <div class="mt-4 text-center">
            <div class="inline-flex flex-col items-center gap-1 px-5 py-3 rounded-2xl" style="background:#F0FDF4;">
                <span class="text-2xl font-bold" style="color:#22C55E;">{{ $rate }}%</span>
                <span class="text-xs font-medium text-slate-500">Taux d'acceptation</span>
            </div>
        </div>
    </div>
</div>

@endsection
