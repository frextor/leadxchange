@extends('ambassador.layouts.ambassador')
@section('title', 'Leads Analytics')
@section('page-title', 'Leads')
@section('page-subtitle', 'Analyse de votre activité leads')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('leadsChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chart['labels']) !!},
            datasets: [
                {
                    label: 'Générés',
                    data: {!! json_encode($chart['generated']) !!},
                    backgroundColor: 'rgba(20,184,166,.7)', borderRadius: 5,
                },
                {
                    label: 'Acceptés',
                    data: {!! json_encode($chart['accepted']) !!},
                    backgroundColor: 'rgba(99,102,241,.7)', borderRadius: 5,
                },
                {
                    label: 'Rejetés',
                    data: {!! json_encode($chart['rejected']) !!},
                    backgroundColor: 'rgba(239,68,68,.5)', borderRadius: 5,
                },
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

{{-- KPIs ─────────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="amb-kpi-card text-center">
        <p class="text-3xl font-bold text-slate-800">{{ number_format($kpis['generated']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Leads générés</p>
    </div>
    <div class="amb-kpi-card text-center">
        <p class="text-3xl font-bold text-teal-600">{{ number_format($kpis['received']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Leads reçus</p>
    </div>
    <div class="amb-kpi-card text-center">
        <p class="text-3xl font-bold text-indigo-600">{{ $kpis['conversion_rate'] }}%</p>
        <p class="text-xs text-slate-400 mt-1">Taux de conversion</p>
    </div>
</div>

{{-- Chart ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
    <h3 class="text-sm font-bold text-slate-800 mb-4">Évolution mensuelle des leads</h3>
    <div style="height:240px;"><canvas id="leadsChart"></canvas></div>
</div>

{{-- Sent leads table ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Leads envoyés</h3>
        <form method="GET" class="flex gap-2">
            <select name="status" class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-teal-300"
                    onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="new" @selected(request('status')=='new')>Nouveau</option>
                <option value="accepted" @selected(request('status')=='accepted')>Accepté</option>
                <option value="rejected" @selected(request('status')=='rejected')>Rejeté</option>
                <option value="converted" @selected(request('status')=='converted')>Converti</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Entreprise</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden md:table-cell">Contact</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden lg:table-cell">Destinataire</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Qualification</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Statut</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sentLeads as $lead)
                @php
                $statusColors = ['new'=>'bg-blue-50 text-blue-700','accepted'=>'bg-emerald-50 text-emerald-700','rejected'=>'bg-red-50 text-red-600','converted'=>'bg-purple-50 text-purple-700'];
                $qualColors = ['chaud'=>'bg-red-50 text-red-600','tiede'=>'bg-amber-50 text-amber-600','froid'=>'bg-blue-50 text-blue-600'];
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="px-5 py-3 text-sm font-semibold text-slate-800">{{ $lead->company_name }}</td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        <p class="text-sm text-slate-700">{{ $lead->contact_name }}</p>
                        <p class="text-[11px] text-slate-400">{{ $lead->contact_email }}</p>
                    </td>
                    <td class="px-5 py-3 hidden lg:table-cell text-sm text-slate-600">
                        {{ $lead->receiver?->first_name }} {{ $lead->receiver?->last_name }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        @if($lead->qualification)
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $qualColors[$lead->qualification] ?? '' }}">
                            {{ ucfirst($lead->qualification) }}
                        </span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $statusColors[$lead->status] ?? 'bg-gray-100 text-slate-500' }}">
                            {{ ucfirst($lead->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center text-xs text-slate-400">{{ $lead->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">Aucun lead généré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sentLeads->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $sentLeads->links() }}</div>
    @endif
</div>

@endsection
