@extends('admin.layouts.admin')
@section('title', 'Analytics — Leads')
@section('page-title', 'Analytics · Leads')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';
document.addEventListener('DOMContentLoaded', function () {

    new Chart(document.getElementById('chartLeads'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chart['labels']) !!},
            datasets: [{
                label: 'Total envoyés',
                data: {!! json_encode($chart['sent']) !!},
                backgroundColor: 'rgba(59,130,246,0.15)',
                borderColor: '#3B82F6', borderWidth: 1.5, borderRadius: 5,
            }, {
                label: 'Acceptés',
                data: {!! json_encode($chart['accepted']) !!},
                backgroundColor: 'rgba(34,197,94,0.2)',
                borderColor: '#22C55E', borderWidth: 1.5, borderRadius: 5,
            }, {
                label: 'Rejetés',
                data: {!! json_encode($chart['rejected']) !!},
                backgroundColor: 'rgba(239,68,68,0.15)',
                borderColor: '#EF4444', borderWidth: 1.5, borderRadius: 5,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { boxWidth: 10, font: { size: 12 } } } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#F1F5F9' }, beginAtZero: true } }
        }
    });

    new Chart(document.getElementById('chartConvRate'), {
        type: 'doughnut',
        data: {
            labels: ['Acceptés','Rejetés','Expirés','Autres'],
            datasets: [{
                data: [{{ $accepted }}, {{ $rejected }}, {{ $expired }}, {{ max(0, $total - $accepted - $rejected - $expired) }}],
                backgroundColor: ['#22C55E','#EF4444','#94A3B8','#E2E8F0'],
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

{{-- KPI Cards --}}
<div class="grid grid-cols-5 gap-3 mb-5">
    @php
    $cards = [
        ['label' => 'Total leads',     'value' => $total,     'color' => '#3B82F6', 'bg' => '#EFF6FF', 'icon' => '📊'],
        ['label' => 'Acceptés',        'value' => $accepted,  'color' => '#22C55E', 'bg' => '#F0FDF4', 'icon' => '✅'],
        ['label' => 'Rejetés',         'value' => $rejected,  'color' => '#EF4444', 'bg' => '#FEF2F2', 'icon' => '❌'],
        ['label' => 'Expirés',         'value' => $expired,   'color' => '#94A3B8', 'bg' => '#F8FAFC', 'icon' => '⏱'],
        ['label' => 'Taux conversion', 'value' => $rate.'%',  'color' => '#F59E0B', 'bg' => '#FFFBEB', 'icon' => '📈'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg mb-2" style="background:{{ $c['bg'] }};">{{ $c['icon'] }}</div>
        <p class="text-2xl font-bold text-slate-900">{{ is_numeric($c['value']) ? number_format($c['value']) : $c['value'] }}</p>
        <p class="text-xs font-semibold text-slate-500 mt-0.5">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Charts row --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="text-sm font-bold text-slate-800">Activité des leads / mois</h3><p class="text-xs text-slate-400">12 derniers mois</p></div>
        </div>
        <div style="height:240px;"><canvas id="chartLeads"></canvas></div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="text-sm font-bold text-slate-800">Répartition par statut</h3><p class="text-xs text-slate-400">Tous les leads</p></div>
        </div>
        <div style="height:180px;"><canvas id="chartConvRate"></canvas></div>
        <div class="mt-3 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl" style="background:#FFFBEB;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                <span class="text-sm font-bold text-amber-700">Conversion : {{ $rate }}%</span>
            </div>
        </div>
    </div>
</div>

{{-- Top Lead Generators --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div><h3 class="text-sm font-bold text-slate-800">Top générateurs de leads</h3><p class="text-xs text-slate-400">Classé par nombre de leads envoyés</p></div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-5 py-3">#</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-5 py-3">Utilisateur</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Total</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Acceptés</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Rejetés</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Taux</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-5 py-3">Performance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topGenerators as $i => $g)
                @php $g = (object)$g; $gRate = $g->total > 0 ? round($g->accepted/$g->total*100) : 0; @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-slate-100 text-slate-600' : ($i === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500')) }}">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.users.show', $g->id) }}" class="font-semibold text-sm text-slate-800 hover:text-teal-600">{{ $g->name }}</a>
                        <p class="text-xs text-slate-400">{{ $g->email }}</p>
                    </td>
                    <td class="px-4 py-3 text-center"><span class="text-sm font-bold text-slate-700">{{ $g->total }}</span></td>
                    <td class="px-4 py-3 text-center"><span class="text-sm font-semibold text-emerald-600">{{ $g->accepted }}</span></td>
                    <td class="px-4 py-3 text-center"><span class="text-sm font-semibold text-red-500">{{ $g->rejected }}</span></td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs font-bold px-2 py-1 rounded-lg {{ $gRate >= 70 ? 'bg-emerald-50 text-emerald-700' : ($gRate >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }}">
                            {{ $gRate }}%
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="h-2 bg-gray-100 rounded-full w-24 overflow-hidden">
                            <div class="h-full rounded-full" style="width:{{ $gRate }}%; background:{{ $gRate >= 70 ? '#22C55E' : ($gRate >= 40 ? '#F59E0B' : '#EF4444') }};"></div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-slate-400">Aucun lead enregistré</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
