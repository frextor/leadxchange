@extends('admin.layouts.admin')
@section('title', 'Analytics — Abonnements')
@section('page-title', 'Analytics · Abonnements')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';
document.addEventListener('DOMContentLoaded', function () {

    new Chart(document.getElementById('chartGrowth'), {
        type: 'line',
        data: {
            labels: {!! json_encode($growthChart['labels']) !!},
            datasets: [{
                label: 'Nouveaux abonnements',
                data: {!! json_encode($growthChart['data']) !!},
                borderColor: '#7C3AED', backgroundColor: 'rgba(124,58,237,0.08)',
                borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 4, pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#F1F5F9' }, beginAtZero: true } }
        }
    });

    @php
    $dColors = ['#7C3AED','#14B8A6','#F59E0B','#3B82F6','#EF4444'];
    $dLabels = collect($distribution)->pluck('label')->toArray();
    $dData   = collect($distribution)->pluck('count')->toArray();
    @endphp
    new Chart(document.getElementById('chartDist'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($dLabels) !!},
            datasets: [{
                data: {!! json_encode($dData) !!},
                backgroundColor: {!! json_encode(array_slice($dColors, 0, count($dLabels))) !!},
                borderWidth: 0, hoverOffset: 8,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 12 } } } }
        }
    });
});
</script>
@endpush

@section('content')
@include('admin.super_admin.analytics._nav')

{{-- KPIs --}}
<div class="grid grid-cols-4 gap-3 mb-5">
    <div class="rounded-2xl p-5 shadow-sm border" style="background:linear-gradient(135deg,#7C3AED,#6D28D9); border-color:#5B21B6;">
        <p class="text-[11px] font-bold uppercase tracking-widest text-violet-200">Abonnements actifs</p>
        <p class="text-4xl font-bold text-white mt-2">{{ number_format($total) }}</p>
        <p class="text-xs text-violet-200 mt-2">Tous les plans confondus</p>
    </div>
    <div class="rounded-2xl p-5 shadow-sm border" style="background:linear-gradient(135deg,#0D9488,#0F766E); border-color:#0F766E;">
        <p class="text-[11px] font-bold uppercase tracking-widest" style="color:#99F6E4;">Revenus mensuels</p>
        <p class="text-3xl font-bold text-white mt-2">{{ number_format($revenue, 2) }} €</p>
        <p class="text-xs mt-2" style="color:#99F6E4;">MRR estimé</p>
    </div>
    @foreach($plans->take(2) as $plan)
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">{{ $plan->label }}</p>
        <p class="text-3xl font-bold text-slate-900 mt-2">{{ number_format($plan->active_subscriptions_count) }}</p>
        <p class="text-xs text-slate-400 mt-1">abonnés actifs</p>
        @if($total > 0)
        <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full bg-violet-400" style="width:{{ round($plan->active_subscriptions_count/$total*100) }}%"></div>
        </div>
        <p class="text-[11px] text-violet-500 font-medium mt-1">{{ $total > 0 ? round($plan->active_subscriptions_count/$total*100,1) : 0 }}%</p>
        @endif
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="text-sm font-bold text-slate-800">Croissance des abonnements</h3><p class="text-xs text-slate-400">12 derniers mois</p></div>
        </div>
        <div style="height:240px;"><canvas id="chartGrowth"></canvas></div>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="text-sm font-bold text-slate-800">Répartition des plans</h3><p class="text-xs text-slate-400">Abonnements actifs</p></div>
        </div>
        @if(count($distribution) > 0)
        <div style="height:220px;"><canvas id="chartDist"></canvas></div>
        @else
        <div class="h-52 flex items-center justify-center text-sm text-slate-400">Aucune donnée</div>
        @endif
    </div>
</div>

{{-- Plans Table --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-slate-800">Détail par plan</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Plan</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Abonnés actifs</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Part</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Prix mensuel</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Rev. mensuel</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Distribution</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                @php $planShare = $total > 0 ? round($plan->active_subscriptions_count/$total*100, 1) : 0; @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background:#7C3AED;"></div>
                            <span class="text-sm font-semibold text-slate-800">{{ $plan->label }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-center text-sm font-bold text-slate-700">{{ number_format($plan->active_subscriptions_count) }}</td>
                    <td class="px-5 py-3 text-center text-sm font-semibold text-violet-600">{{ $planShare }}%</td>
                    <td class="px-5 py-3 text-center text-sm text-slate-600">{{ number_format($plan->price, 2) }} €</td>
                    <td class="px-5 py-3 text-center text-sm font-semibold text-slate-700">{{ number_format($plan->active_subscriptions_count * $plan->price, 2) }} €</td>
                    <td class="px-5 py-3">
                        <div class="h-2 bg-gray-100 rounded-full w-32 overflow-hidden">
                            <div class="h-full rounded-full bg-violet-400" style="width:{{ $planShare }}%;"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Stripe Integration Placeholder --}}
<div class="rounded-2xl border-2 border-dashed border-violet-200 p-6" style="background:#FAFAFF;">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:#7C3AED;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div class="flex-1">
            <h3 class="text-sm font-bold text-slate-800">Intégration Stripe avancée</h3>
            <p class="text-xs text-slate-500 mt-0.5">Quand Stripe sera connecté, vous verrez ici : MRR, ARR, Churn Rate, LTV, revenus par période, remboursements et historique des paiements.</p>
        </div>
        <a href="{{ route('admin.super.plans.stripe') }}" class="flex-shrink-0 px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#7C3AED;">
            Configurer Stripe →
        </a>
    </div>
    <div class="grid grid-cols-4 gap-3 mt-4">
        @foreach(['MRR (Revenus récurrents mensuels)','ARR (Revenus annuels)','Churn Rate','LTV moyen'] as $placeholder)
        <div class="bg-white rounded-xl p-3 border border-violet-100">
            <p class="text-xs text-slate-400">{{ $placeholder }}</p>
            <p class="text-lg font-bold text-slate-200 mt-1">——</p>
        </div>
        @endforeach
    </div>
</div>

@endsection
