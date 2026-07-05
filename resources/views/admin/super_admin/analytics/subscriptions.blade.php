@extends('admin.layouts.admin')
@section('title', 'Analytics — Abonnements & Paiements')
@section('page-title', 'Analytics · Abonnements & Paiements')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94A3B8';
document.addEventListener('DOMContentLoaded', function () {

    // Revenue chart (Stripe or DB fallback)
    new Chart(document.getElementById('chartRevenue'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($revenueChart['labels']) !!},
            datasets: [{
                label: 'Revenus (€)',
                data: {!! json_encode($revenueChart['data']) !!},
                backgroundColor: 'rgba(124,58,237,0.15)',
                borderColor: '#7C3AED',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { grid: { color: '#F1F5F9' }, beginAtZero: true,
                     ticks: { callback: v => v + ' €' } }
            }
        }
    });

    // Growth (subscriptions)
    new Chart(document.getElementById('chartGrowth'), {
        type: 'line',
        data: {
            labels: {!! json_encode($growthChart['labels']) !!},
            datasets: [{
                label: 'Nouveaux abonnements',
                data: {!! json_encode($growthChart['data']) !!},
                borderColor: '#14B8A6', backgroundColor: 'rgba(20,184,166,0.08)',
                borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 3,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { grid: { display: false } }, y: { grid: { color: '#F1F5F9' }, beginAtZero: true } }
        }
    });

    // Distribution
    @php
    $dColors = ['#7C3AED','#14B8A6','#F59E0B','#3B82F6','#EF4444','#EC4899'];
    $dLabels = collect($distribution)->pluck('label')->toArray();
    $dData   = collect($distribution)->pluck('count')->toArray();
    @endphp
    @if(count($distribution) > 0)
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
    @endif
});
</script>
@endpush

@section('content')
@include('admin.super_admin.analytics._nav')

{{-- Source indicator --}}
<div class="flex items-center gap-2 mb-4">
    @if($stripeConnected)
    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
        Données synchronisées avec Stripe
    </span>
    @else
    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-100">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Stripe non connecté — données estimées depuis la base locale
    </span>
    <a href="{{ route('admin.super.plans.stripe') }}" class="text-xs font-semibold text-violet-600 hover:underline">Connecter Stripe →</a>
    @endif
</div>

{{-- KPIs row 1 : Stripe metrics ──────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
    {{-- MRR --}}
    <div class="rounded-2xl p-5 shadow-sm border" style="background:linear-gradient(135deg,#7C3AED,#6D28D9);border-color:#5B21B6;">
        <p class="text-[11px] font-bold uppercase tracking-widest text-violet-200">MRR</p>
        <p class="text-3xl font-bold text-white mt-2">{{ number_format($mrr, 2) }} €</p>
        <p class="text-xs text-violet-200 mt-1">Revenus récurrents mensuels</p>
    </div>
    {{-- ARR --}}
    <div class="rounded-2xl p-5 shadow-sm border" style="background:linear-gradient(135deg,#0D9488,#0F766E);border-color:#0F766E;">
        <p class="text-[11px] font-bold uppercase tracking-widest" style="color:#99F6E4;">ARR</p>
        <p class="text-3xl font-bold text-white mt-2">{{ number_format($arr, 2) }} €</p>
        <p class="text-xs mt-1" style="color:#99F6E4;">Revenus annuels estimés</p>
    </div>
    {{-- Churn --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Churn Rate</p>
        <p class="text-3xl font-bold mt-2 {{ $churnRate > 5 ? 'text-red-600' : ($churnRate > 2 ? 'text-amber-600' : 'text-emerald-600') }}">
            {{ $churnRate > 0 ? $churnRate . ' %' : '—' }}
        </p>
        <p class="text-xs text-slate-400 mt-1">Annulations — 30 derniers jours</p>
    </div>
    {{-- LTV --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">LTV moyen</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">
            {{ $ltv ? number_format($ltv, 0) . ' €' : '—' }}
        </p>
        <p class="text-xs text-slate-400 mt-1">Valeur vie client estimée</p>
    </div>
</div>

{{-- KPIs row 2 : activity ────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Abonnements actifs</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($total) }}</p>
        <p class="text-xs text-slate-400 mt-1">Tous plans confondus</p>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Nouveaux (30j)</p>
        <p class="text-3xl font-bold text-emerald-600 mt-2">+{{ number_format($newVsCancelled['new']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Nouveaux abonnements</p>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Annulés (30j)</p>
        <p class="text-3xl font-bold text-red-500 mt-2">-{{ number_format($newVsCancelled['cancelled']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Abonnements annulés</p>
    </div>
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Net (30j)</p>
        @php $net = $newVsCancelled['new'] - $newVsCancelled['cancelled']; @endphp
        <p class="text-3xl font-bold mt-2 {{ $net >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
            {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}
        </p>
        <p class="text-xs text-slate-400 mt-1">Croissance nette</p>
    </div>
</div>

{{-- Charts ──────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    {{-- Revenue chart --}}
    <div class="col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Revenus mensuels</h3>
                <p class="text-xs text-slate-400">
                    {{ $stripeConnected ? 'Données réelles Stripe — factures payées' : 'Estimé depuis les abonnements locaux' }}
                </p>
            </div>
        </div>
        <div style="height:240px;"><canvas id="chartRevenue"></canvas></div>
    </div>
    {{-- Doughnut distribution --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="mb-4">
            <h3 class="text-sm font-bold text-slate-800">Répartition des plans</h3>
            <p class="text-xs text-slate-400">Abonnements actifs</p>
        </div>
        @if(count($distribution) > 0)
        <div style="height:200px;"><canvas id="chartDist"></canvas></div>
        @else
        <div class="h-48 flex items-center justify-center text-sm text-slate-400">Aucune donnée</div>
        @endif
    </div>
</div>

{{-- Subscription growth --}}
<div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-5">
    <div class="flex items-center justify-between mb-4">
        <div><h3 class="text-sm font-bold text-slate-800">Croissance des abonnements</h3><p class="text-xs text-slate-400">12 derniers mois</p></div>
    </div>
    <div style="height:180px;"><canvas id="chartGrowth"></canvas></div>
</div>

{{-- Plans table ─────────────────────────────────────────────────────────── --}}
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
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Prix / mois</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">MRR contrib.</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Distribution</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                @php
                    $planShare = $total > 0 ? round($plan->active_subscriptions_count / $total * 100, 1) : 0;
                    $planColors = ['#7C3AED','#14B8A6','#F59E0B','#3B82F6','#EF4444','#EC4899'];
                    $pci = $loop->index % count($planColors);
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full" style="background:{{ $planColors[$pci] }};"></div>
                            <span class="text-sm font-semibold text-slate-800">{{ $plan->label }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-center text-sm font-bold text-slate-700">{{ number_format($plan->active_subscriptions_count) }}</td>
                    <td class="px-5 py-3 text-center text-sm font-semibold text-violet-600">{{ $planShare }}%</td>
                    <td class="px-5 py-3 text-center text-sm text-slate-600">{{ number_format($plan->price, 2) }} €</td>
                    <td class="px-5 py-3 text-center text-sm font-semibold text-slate-700">
                        {{ number_format($plan->active_subscriptions_count * $plan->price, 2) }} €
                    </td>
                    <td class="px-5 py-3">
                        <div class="h-2 bg-gray-100 rounded-full w-32 overflow-hidden">
                            <div class="h-full rounded-full" style="width:{{ $planShare }}%;background:{{ $planColors[$pci] }};"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Recent payments ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Derniers paiements</h3>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $stripeConnected ? 'Factures payées — données Stripe en temps réel' : 'Abonnements actifs récents (Stripe non connecté)' }}
            </p>
        </div>
        @if($stripeConnected)
        <a href="https://dashboard.stripe.com/payments" target="_blank"
           class="text-xs font-semibold text-violet-600 hover:underline flex items-center gap-1">
            Voir dans Stripe
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
        @endif
    </div>
    @if(count($recentPayments) > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Client</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Description</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Date</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Statut</th>
                    <th class="text-right text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Montant</th>
                    @if($stripeConnected)
                    <th class="px-5 py-3"></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($recentPayments as $payment)
                <tr class="border-b border-gray-50 hover:bg-gray-50">
                    <td class="px-5 py-3 text-sm text-slate-700 max-w-[180px] truncate">{{ $payment['customer'] }}</td>
                    <td class="px-5 py-3 text-sm text-slate-500 max-w-[200px] truncate">{{ $payment['description'] }}</td>
                    <td class="px-5 py-3 text-center text-xs text-slate-400 whitespace-nowrap">{{ $payment['date'] }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full
                            {{ $payment['status'] === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $payment['status'] === 'paid' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            {{ $payment['status'] === 'paid' ? 'Payé' : ucfirst($payment['status']) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right text-sm font-bold text-slate-800 whitespace-nowrap">
                        {{ number_format($payment['amount'], 2) }} {{ $payment['currency'] }}
                    </td>
                    @if($stripeConnected)
                    <td class="px-5 py-3 text-right">
                        @if($payment['stripe_url'])
                        <a href="{{ $payment['stripe_url'] }}" target="_blank"
                           class="text-[11px] text-violet-500 hover:text-violet-700 hover:underline">
                            Facture ↗
                        </a>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-5 py-12 text-center text-sm text-slate-400">Aucun paiement trouvé.</div>
    @endif
</div>

@endsection
