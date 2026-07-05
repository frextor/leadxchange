@extends('admin.layouts.admin')
@section('title', 'Analytics — Régions')
@section('page-title', 'Analytics · Régions')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';
document.addEventListener('DOMContentLoaded', function () {
    @php
    $top10 = array_slice($regionalStats, 0, 10);
    @endphp
    new Chart(document.getElementById('chartRegional'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(collect($top10)->pluck('name')->toArray()) !!},
            datasets: [{
                label: 'Utilisateurs',
                data: {!! json_encode(collect($top10)->pluck('users_count')->toArray()) !!},
                backgroundColor: 'rgba(20,184,166,0.2)', borderColor: '#14B8A6', borderWidth: 2, borderRadius: 6,
            }, {
                label: 'Ambassadeurs',
                data: {!! json_encode(collect($top10)->pluck('ambassadors_count')->toArray()) !!},
                backgroundColor: 'rgba(245,158,11,0.2)', borderColor: '#F59E0B', borderWidth: 2, borderRadius: 6,
            }, {
                label: 'Événements',
                data: {!! json_encode(collect($top10)->pluck('events_count')->toArray()) !!},
                backgroundColor: 'rgba(249,115,22,0.2)', borderColor: '#F97316', borderWidth: 2, borderRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { boxWidth: 10, font: { size: 12 } } } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#F1F5F9' }, beginAtZero: true } }
        }
    });
});
</script>
@endpush

@section('content')
@include('admin.super_admin.analytics._nav')

<div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-5">
    <div class="flex items-center justify-between mb-4">
        <div><h3 class="text-sm font-bold text-slate-800">Top 10 régions</h3><p class="text-xs text-slate-400">Utilisateurs, ambassadeurs et événements par région</p></div>
    </div>
    <div style="height:280px;"><canvas id="chartRegional"></canvas></div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Classement des régions</h3>
            <p class="text-xs text-slate-400">Top 20 régions actives · {{ number_format($total) }} membres au total</p>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-5 py-3">Rang</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-5 py-3">Région</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Utilisateurs</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Ambassadeurs</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Événements</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider px-4 py-3">Leads</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider px-5 py-3">Part utilisateurs</th>
                </tr>
            </thead>
            <tbody>
                @forelse($regionalStats as $i => $reg)
                @php $reg = (object)$reg; $share = $total > 0 ? round($reg->users_count / $total * 100, 1) : 0; @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-slate-100 text-slate-500' : ($i === 2 ? 'bg-orange-100 text-orange-600' : 'bg-gray-50 text-gray-400')) }}">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/></svg>
                            <span class="text-sm font-semibold text-slate-800">{{ $reg->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-bold text-teal-600">{{ number_format($reg->users_count) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-semibold {{ $reg->ambassadors_count > 0 ? 'text-amber-600' : 'text-slate-300' }}">{{ $reg->ambassadors_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-semibold {{ $reg->events_count > 0 ? 'text-orange-500' : 'text-slate-300' }}">{{ $reg->events_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-sm font-semibold {{ $reg->leads_count > 0 ? 'text-blue-500' : 'text-slate-300' }}">{{ $reg->leads_count }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden" style="max-width:100px;">
                                <div class="h-full rounded-full bg-teal-400" style="width:{{ $share }}%;"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-500 w-10">{{ $share }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-slate-400">Aucune région avec des utilisateurs</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
