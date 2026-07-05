@extends('layouts.app')
@section('title', 'Mon abonnement — LeadXchange')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Mon abonnement</h1>
        <p class="text-sm text-gray-500 mt-1">Gérez votre plan et consultez votre situation financière (CGU §12).</p>
    </div>

    @if(session('success'))
    <div class="mb-5 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="m9 11 3 3L22 4"/></svg>
        <p>{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-4 text-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
        <p>{{ session('error') }}</p>
    </div>
    @endif

    {{-- Plan actuel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-lg font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4B0,#14A98C);">
                    {{ strtoupper(substr($subscription?->plan?->name ?? 'B', 0, 1)) }}
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900">
                        Plan {{ $subscription?->plan?->label ?? 'Basic (gratuit)' }}
                    </p>
                    <p class="text-sm text-gray-500 mt-0.5">
                        @if($subscription && (float)($subscription->plan?->price ?? 0) > 0)
                            {{ currency_format($subscription->plan->price) }}/mois · Paiement mensuel
                        @else
                            Gratuit — sans engagement
                        @endif
                    </p>
                </div>
            </div>

            {{-- Statut --}}
            <div class="text-right">
                @if($subscription?->cancel_at_period_end)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Résiliation programmée
                </span>
                @elseif($subscription?->status === 'active')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Actif
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">
                    Inactif
                </span>
                @endif
            </div>
        </div>

        {{-- Détails période --}}
        @if($subscription)
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-gray-100">
            @if($subscription->current_period_end)
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">
                    {{ $subscription->cancel_at_period_end ? 'Accès jusqu\'au' : 'Prochain renouvellement' }}
                </p>
                <p class="text-sm font-semibold text-gray-900">
                    {{ $subscription->current_period_end->format('d/m/Y') }}
                </p>
            </div>
            @endif
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Membre depuis</p>
                <p class="text-sm font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Paiement</p>
                <p class="text-sm font-semibold text-gray-900">Carte bancaire (sécurisé PCI-DSS)</p>
            </div>
        </div>
        @endif

        {{-- Résiliation en attente --}}
        @if($subscription?->cancel_at_period_end)
        <div class="mt-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-sm text-amber-800">
            <p class="font-semibold mb-1">⚠ Résiliation programmée</p>
            <p>Votre abonnement sera résilié le <strong>{{ $subscription->current_period_end?->format('d/m/Y') }}</strong>.
            Vous conservez l'accès à toutes les fonctionnalités jusqu'à cette date.</p>
            <form method="POST" action="{{ route('billing.reactivate') }}" class="mt-3">
                @csrf
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-xs font-bold border border-amber-300 text-amber-800 hover:bg-amber-100 transition">
                    ↩ Annuler la résiliation
                </button>
            </form>
        </div>
        @endif
    </div>

    {{-- Changer de plan --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
        <h2 class="text-sm font-bold text-gray-900 mb-4">Changer de plan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-{{ min($plans->count(), 3) }} gap-3">
            @foreach($plans as $plan)
            @php $isCurrent = $subscription?->plan_id === $plan->id; @endphp
            <div class="rounded-xl border p-4 {{ $isCurrent ? 'border-teal-300 bg-teal-50' : 'border-gray-200' }}">
                <p class="text-sm font-bold text-gray-900">{{ $plan->label }}</p>
                <p class="text-lg font-extrabold mt-1 {{ $isCurrent ? 'text-teal-600' : 'text-gray-900' }}">
                    {{ $plan->price > 0 ? currency_format($plan->price).'/mois' : 'Gratuit' }}
                </p>
                @if($isCurrent)
                <span class="inline-block mt-2 text-[10px] font-bold text-teal-600 bg-teal-100 px-2 py-0.5 rounded-full">Plan actuel</span>
                @elseif($plan->stripe_price_id)
                <form method="POST" action="{{ route('checkout', $plan) }}" class="mt-3">
                    @csrf
                    <button type="submit"
                            class="w-full py-2 rounded-lg text-xs font-bold text-white transition hover:opacity-90"
                            style="background:#14A98C;">
                        Passer à ce plan
                    </button>
                </form>
                @else
                <a href="{{ route('upgrade') }}"
                   class="mt-3 block w-full py-2 rounded-lg text-xs font-bold text-center border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    Voir ce plan
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- §12.3 — Factures --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-sm font-bold text-gray-900">Factures</h2>
            @if(count($invoices) > 0)
            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700">
                {{ count($invoices) }} facture{{ count($invoices) > 1 ? 's' : '' }}
            </span>
            @endif
        </div>
        <p class="text-xs text-gray-400 mb-4">Les factures sont émises par Stripe (CGU §12.3). Cliquez sur PDF pour télécharger.</p>

        @if(count($invoices) > 0)
        <div class="rounded-xl border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left text-[11px] font-bold text-gray-400 uppercase px-4 py-2.5">N° Facture</th>
                        <th class="text-left text-[11px] font-bold text-gray-400 uppercase px-4 py-2.5 hidden sm:table-cell">Description</th>
                        <th class="text-center text-[11px] font-bold text-gray-400 uppercase px-4 py-2.5">Date</th>
                        <th class="text-right text-[11px] font-bold text-gray-400 uppercase px-4 py-2.5">Montant</th>
                        <th class="px-4 py-2.5"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-gray-600">{{ $invoice['number'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-[180px] truncate hidden sm:table-cell">
                            {{ $invoice['description'] }}
                            @if($invoice['period'])
                            <span class="text-gray-300 ml-1">({{ $invoice['period'] }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500 whitespace-nowrap">
                            {{ $invoice['date'] }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-800 whitespace-nowrap">
                            {{ number_format($invoice['amount'], 2, ',', ' ') }} {{ $invoice['currency'] }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($invoice['pdf_url'])
                                <a href="{{ $invoice['pdf_url'] }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 transition whitespace-nowrap">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    PDF
                                </a>
                                @endif
                                @if($invoice['receipt_url'])
                                <a href="{{ $invoice['receipt_url'] }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-400 hover:text-gray-600 transition whitespace-nowrap">
                                    Voir ↗
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($subscription && (float)($subscription->plan?->price ?? 0) > 0)
        <div class="text-sm text-gray-400 text-center py-8 bg-gray-50 rounded-xl border border-gray-100">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5" class="mx-auto mb-2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p class="font-medium text-gray-500">Aucune facture disponible pour le moment</p>
            <p class="text-xs text-gray-400 mt-1">Les factures apparaîtront ici après votre premier paiement.</p>
        </div>
        @else
        <div class="text-sm text-gray-400 text-center py-6 bg-gray-50 rounded-xl border border-gray-100">
            <p>Plan Basic — aucune facturation</p>
        </div>
        @endif
    </div>

    {{-- §12.5 — Résiliation --}}
    @if($subscription && $subscription->status === 'active' && !$subscription->cancel_at_period_end && (float)($subscription->plan?->price ?? 0) > 0)
    <div class="bg-white rounded-2xl border border-red-100 shadow-sm p-6">
        <h2 class="text-sm font-bold text-gray-900 mb-1">Résilier l'abonnement</h2>
        <p class="text-xs text-gray-500 mb-4">
            La résiliation prend effet à la <strong>fin de la période en cours</strong>
            @if($subscription->current_period_end)
            ({{ $subscription->current_period_end->format('d/m/Y') }})
            @endif.
            Aucun remboursement prorata (CGU §12.5).
        </p>

        @php $cancelEndDate = $subscription->current_period_end?->format('d/m/Y') ?? 'la fin de la période'; @endphp
        <form method="POST" action="{{ route('billing.cancel') }}"
              onsubmit="return confirm('Êtes-vous sûr de vouloir résilier votre abonnement ?\n\nVotre accès restera actif jusqu\'au {{ $cancelEndDate }}.')">
            @csrf
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-red-200 text-red-600 hover:bg-red-50 transition">
                Résilier mon abonnement
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
