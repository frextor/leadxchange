@extends('admin.layouts.admin')
@section('title', 'Analytics — Événements')
@section('page-title', 'Analytics · Événements')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';
document.addEventListener('DOMContentLoaded', function () {

    new Chart(document.getElementById('chartEvents'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chart['labels']) !!},
            datasets: [{
                label: 'Événements créés',
                data: {!! json_encode($chart['created']) !!},
                backgroundColor: 'rgba(249,115,22,0.15)',
                borderColor: '#F97316', borderWidth: 2, borderRadius: 6,
                yAxisID: 'y',
            }, {
                type: 'line',
                label: 'Participations',
                data: {!! json_encode($chart['participation']) !!},
                borderColor: '#8B5CF6', backgroundColor: 'rgba(139,92,246,0.06)',
                borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3,
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { boxWidth: 10, font: { size: 12 } } } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#F1F5F9' }, title: { display: true, text: 'Événements', font: { size: 11 } } },
                y1: { position: 'right', grid: { display: false }, title: { display: true, text: 'Participations', font: { size: 11 } } },
            }
        }
    });
});
</script>
@endpush

@section('content')
@include('admin.super_admin.analytics._nav')

<div class="grid grid-cols-3 gap-3 mb-5">
    @php
    $cards = [
        ['label' => 'Total événements', 'value' => $total,     'sub' => 'Tous les statuts',   'color' => '#F97316', 'bg' => '#FFF7ED'],
        ['label' => 'À venir',          'value' => $upcoming,  'sub' => 'Prochainement',       'color' => '#3B82F6', 'bg' => '#EFF6FF'],
        ['label' => 'Terminés',         'value' => $completed, 'sub' => 'Événements passés',   'color' => '#22C55E', 'bg' => '#F0FDF4'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-bold flex-shrink-0" style="background:{{ $c['bg'] }}; color:{{ $c['color'] }};">
            {{ number_format($c['value']) }}
        </div>
        <div>
            <p class="text-sm font-bold text-slate-700">{{ $c['label'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $c['sub'] }}</p>
        </div>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-5">
    <div class="flex items-center justify-between mb-4">
        <div><h3 class="text-sm font-bold text-slate-800">Événements par mois</h3><p class="text-xs text-slate-400">12 derniers mois — créations et participations</p></div>
    </div>
    <div style="height:240px;"><canvas id="chartEvents"></canvas></div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-slate-800">Top événements par participation</h3>
        <p class="text-xs text-slate-400">Les événements les plus fréquentés</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-5 py-3">#</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-5 py-3">Événement</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Organisateur</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Ville</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Date</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Participants</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topEvents as $i => $ev)
                @php $ev = (object)$ev; @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3"><span class="text-sm font-bold text-slate-400">{{ $i+1 }}</span></td>
                    <td class="px-5 py-3">
                        <p class="text-sm font-semibold text-slate-800">{{ $ev->title }}</p>
                        <span class="text-[11px] px-2 py-0.5 rounded-md font-medium"
                              style="background:#FFF7ED; color:#C2410C;">{{ ucfirst($ev->type ?? '—') }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">{{ $ev->creator ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-500">{{ $ev->city_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $ev->starts_at ? \Carbon\Carbon::parse($ev->starts_at)->format('d/m/Y') : '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center justify-center w-10 h-7 rounded-xl text-sm font-bold" style="background:#FFF7ED; color:#F97316;">{{ $ev->attendees_count }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-slate-400">Aucun événement</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
