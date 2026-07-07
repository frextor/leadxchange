@extends('ambassador.layouts.ambassador')
@section('title', 'Performance')
@section('page-title', 'Performance')
@section('page-subtitle', 'Objectifs ' . now()->isoFormat('MMMM YYYY'))

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = {!! json_encode(array_column($monthlyHistory, 'label')) !!};
    new Chart(document.getElementById('evChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Nouveaux membres', data: {!! json_encode(array_column($monthlyHistory, 'members')) !!}, borderColor: '#14B8A6', borderWidth: 2.5, fill: false, tension: .4, pointRadius: 4 },
                { label: 'Leads générés', data: {!! json_encode(array_column($monthlyHistory, 'leads')) !!}, borderColor: '#6366F1', borderWidth: 2.5, fill: false, tension: .4, pointRadius: 4 },
                { label: 'Événements', data: {!! json_encode(array_column($monthlyHistory, 'events')) !!}, borderColor: '#F59E0B', borderWidth: 2.5, fill: false, tension: .4, pointRadius: 4 },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#F1F5F9' }, beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
});
</script>
@endpush

@section('content')

{{-- Progress cards ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">

    @php
    $items = [
        ['label' => 'Nouveaux Membres', 'key' => 'members', 'color' => '#14B8A6', 'icon' => '👥'],
        ['label' => 'Événements',       'key' => 'events',  'color' => '#6366F1', 'icon' => '📅'],
        ['label' => 'Leads Générés',    'key' => 'leads',   'color' => '#F59E0B', 'icon' => '⚡'],
    ];
    @endphp

    @foreach($items as $item)
    @php
    $current = $progress[$item['key']]['current'];
    $target  = $progress[$item['key']]['target'];
    $pct     = $target > 0 ? min(100, round($current / $target * 100)) : 0;
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <span class="text-2xl">{{ $item['icon'] }}</span>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wide mt-2">{{ $item['label'] }}</p>
            </div>
            <span class="text-[13px] font-bold px-2.5 py-1 rounded-full text-white"
                  style="background:{{ $item['color'] }};">{{ $pct }}%</span>
        </div>

        <div class="flex items-end gap-2 mb-3">
            <span class="text-4xl font-bold text-slate-800">{{ $current }}</span>
            <span class="text-slate-400 text-lg mb-1">/ {{ $target }}</span>
        </div>

        <div class="amb-progress-bar">
            <div class="amb-progress-fill" style="width:{{ $pct }}%;background:{{ $item['color'] }};"></div>
        </div>

        <p class="text-[11px] text-slate-400 mt-2">
            @if($pct >= 100)
                ✅ Objectif atteint !
            @else
                Encore {{ $target - $current }} {{ strtolower($item['label']) }} pour atteindre l'objectif
            @endif
        </p>
    </div>
    @endforeach
</div>

{{-- Update objectives form ──────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
    <h3 class="text-sm font-bold text-slate-800 mb-4">Modifier les objectifs du mois</h3>
    <form method="POST" action="{{ route('ambassador.performance.update') }}" class="flex flex-wrap gap-4 items-end">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Membres cible</label>
            <input type="number" name="target_members" value="{{ $objective->target_members }}" min="1"
                   class="w-32 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Événements cible</label>
            <input type="number" name="target_events" value="{{ $objective->target_events }}" min="1"
                   class="w-32 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Leads cible</label>
            <input type="number" name="target_leads" value="{{ $objective->target_leads }}" min="1"
                   class="w-32 rounded-xl border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
        </div>
        <button type="submit" class="px-5 py-2 rounded-xl text-sm font-bold text-white"
                style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
            Mettre à jour
        </button>
    </form>
</div>

{{-- Monthly evolution chart ─────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
    <h3 class="text-sm font-bold text-slate-800 mb-4">Évolution sur 6 mois</h3>
    <div style="height:240px;"><canvas id="evChart"></canvas></div>
</div>

{{-- Monthly history table ────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-slate-800">Historique mensuel</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Mois</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Membres</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Leads</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Événements</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_reverse($monthlyHistory) as $row)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-3 text-sm font-semibold text-slate-800">{{ $row['label'] }}</td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-sm font-bold text-teal-600">{{ $row['members'] }}</span>
                            <span class="text-xs text-slate-400">/ {{ $row['target_members'] }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-sm font-bold text-indigo-600">{{ $row['leads'] }}</span>
                            <span class="text-xs text-slate-400">/ {{ $row['target_leads'] }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-sm font-bold text-amber-600">{{ $row['events'] }}</span>
                            <span class="text-xs text-slate-400">/ {{ $row['target_events'] }}</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@php $monthlyHistory = $monthlyHistory; @endphp
