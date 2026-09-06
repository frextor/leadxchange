@extends('admin.layouts.admin')
@section('title', 'Analytics — Entreprise')
@section('page-title', 'Analytics · Entreprise')

@section('content')
@include('admin.super_admin.analytics._nav')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @php
    $cards = [
        ['label' => 'Packs actifs',        'value' => $stats['active'],          'color' => '#4338CA', 'bg' => '#EEF2FF',
         'icon' => '<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>'],
        ['label' => 'Packs expirés',       'value' => $stats['expired'],         'color' => '#EF4444', 'bg' => '#FEF2F2',
         'icon' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
        ['label' => 'Expirent sous 30j',   'value' => $stats['expiring_soon'],   'color' => '#F59E0B', 'bg' => '#FFFBEB',
         'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
        ['label' => 'Invitations en attente', 'value' => $stats['pending_invites'], 'color' => '#0D9488', 'bg' => '#F0FDFA',
         'icon' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $c['bg'] }};">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $c['color'] }}" stroke-width="1.8">{!! $c['icon'] !!}</svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $c['value'] }}</p>
        <p class="text-xs font-semibold text-slate-500 mt-1">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- Occupancy --}}
<div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-5">
    <div class="flex items-center justify-between mb-3">
        <div>
            <p class="text-sm font-bold text-slate-800">Occupation des licences</p>
            <p class="text-xs text-slate-400">{{ $stats['seats_used'] }} / {{ $stats['seats_total'] }} sièges attribués sur l'ensemble des packs</p>
        </div>
        <p class="text-2xl font-extrabold" style="color:#4338CA;">{{ $stats['occupancy_rate'] }}%</p>
    </div>
    <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden">
        <div class="h-full rounded-full" style="width:{{ $stats['occupancy_rate'] }}%; background:linear-gradient(90deg,#6366F1,#4338CA);"></div>
    </div>
    <div class="flex items-center gap-4 mt-3 text-xs text-slate-500">
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background:#4338CA;"></span>{{ $stats['seats_used'] }} utilisées</span>
        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-200"></span>{{ $stats['seats_available'] }} libres</span>
        <span class="ml-auto font-semibold text-slate-700">{{ $stats['total_packs'] }} pack{{ $stats['total_packs'] > 1 ? 's' : '' }} au total</span>
    </div>
</div>

<div class="grid grid-cols-5 gap-4">

    {{-- Growth chart --}}
    <div class="col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-sm font-bold text-slate-800">Nouveaux packs Entreprise</p>
                <p class="text-xs text-slate-400">12 derniers mois</p>
            </div>
        </div>
        <div style="height:240px;"><canvas id="chartEnterpriseGrowth"></canvas></div>
    </div>

    {{-- Top licenses --}}
    <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="text-sm font-bold text-slate-800">Plus gros packs</p>
                <p class="text-xs text-slate-400">Par sièges utilisés</p>
            </div>
            <a href="{{ route('admin.super.enterprise.quotes') }}" class="text-xs font-semibold text-indigo-600 hover:underline">Voir tout →</a>
        </div>
        <div class="divide-y divide-slate-50 max-h-[280px] overflow-y-auto">
            @forelse($topLicenses as $l)
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0"
                     style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    {{ strtoupper(substr($l['company_name'],0,2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $l['company_name'] }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ $l['holder_name'] ?: '—' }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-sm font-bold text-slate-700">{{ $l['seats_used'] }}/{{ $l['seats_total'] }}</p>
                    @if($l['is_expired'])
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-red-50 text-red-500">Expiré</span>
                    @else
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-600">Actif</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-sm text-slate-400">Aucun pack Entreprise pour le moment.</div>
            @endforelse
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';

document.addEventListener('DOMContentLoaded', function () {
    new Chart(document.getElementById('chartEnterpriseGrowth'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($growthChart['labels']) !!},
            datasets: [{
                label: 'Nouveaux packs',
                data: {!! json_encode($growthChart['data']) !!},
                backgroundColor: '#818CF8',
                hoverBackgroundColor: '#6366F1',
                borderRadius: 6,
                maxBarThickness: 28,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, grid: { color: '#F1F5F9' }, ticks: { font: { size: 11 }, precision: 0 } },
            }
        }
    });
});
</script>
@endpush
