@extends('layouts.app2')

@section('title', 'Mon abonnement — LeadXchange')

{{-- Écran « Mon abonnement » — design lx2. Plan, échéance, changement de plan, factures et résiliation. --}}

@php
    $hasSub          = ! is_null($subscription);
    $isActive        = $hasSub && $subscription->status === 'active';
    $isCancelled     = $hasSub && (bool) $subscription->cancel_at_period_end;
    $isPaid          = $hasSub && (float) ($subscription->plan?->price ?? 0) > 0;
    $isEnterprisePlan = $subscription?->plan?->is_enterprise ?? false;
    $endDate         = $subscription?->current_period_end;

    $daysLeft = $endDate ? (int) now()->diffInDays($endDate, false) : null;

    $billingLabel = match ($subscription?->billing_period ?? '') {
        'yearly'  => 'Annuel',
        'monthly' => 'Mensuel',
        default   => '—',
    };
    $periodDays = ($subscription?->billing_period === 'yearly') ? 365 : 30;
    $progress   = ($hasSub && $endDate && $daysLeft !== null && $daysLeft >= 0)
        ? max(0, min(100, (int) round(($periodDays - $daysLeft) / $periodDays * 100)))
        : 0;

    $planInitial = strtoupper(substr($subscription?->plan?->name ?? 'Basic', 0, 1));
    $planLabel   = $subscription?->plan?->label ?? 'Basic';
    $planPrice   = $isPaid
        ? currency_format($subscription->plan->price) . ($subscription->billing_period === 'yearly' ? ' / an' : ' / mois')
        : ($isEnterprisePlan ? 'Sur devis' : 'Gratuit · sans engagement');

    if ($isCancelled) {
        $statusLabel = 'Résiliation programmée';
        $statusClass = 'b-warm';
        $expiryEyebrow = 'Accès maintenu jusqu’au';
        $tone = 'var(--warm-fg)';
    } elseif ($isActive) {
        $statusLabel = 'Actif';
        $statusClass = 'b-ok';
        $expiryEyebrow = 'Prochain renouvellement';
        $tone = 'var(--primary)';
    } elseif ($hasSub) {
        $statusLabel = 'Inactif';
        $statusClass = 'b-muted';
        $expiryEyebrow = 'Échéance';
        $tone = 'var(--muted-fg)';
    } else {
        $statusLabel = 'Basic';
        $statusClass = 'b-muted';
        $expiryEyebrow = 'Abonnement';
        $tone = 'var(--muted-fg)';
    }
@endphp

@push('styles')
<style>
    .bl-two{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:20px;align-items:start;margin-top:18px}
    .bl-col{display:flex;flex-direction:column;gap:18px;min-width:0}
    .bl-h{margin:0;font-size:15px;font-weight:600;letter-spacing:-.01em}
    .bl-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
    .bl-cap{font-size:11.5px;font-weight:500;letter-spacing:.04em;text-transform:uppercase;color:var(--muted-fg)}
    .bl-help{margin:0;font-size:12.5px;color:var(--muted-fg);line-height:1.55}

    /* En-tête du plan */
    .bl-plan{display:flex;align-items:center;gap:14px;min-width:0}
    .bl-plan-ico{width:52px;height:52px;border-radius:14px;display:grid;place-items:center;flex:none;color:#fff;font-size:22px;font-weight:600;background:var(--lx-accent-grad)}
    .bl-plan-name{font-size:20px;font-weight:600;letter-spacing:-.02em;line-height:1.2;margin-top:3px}

    /* Échéance */
    .bl-expiry{margin-top:22px;padding:18px 20px;border-radius:var(--radius-lg);background:var(--bg);border:1px solid var(--border)}
    .bl-expiry-row{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap}
    .bl-date{font-size:26px;font-weight:600;letter-spacing:-.02em;font-variant-numeric:tabular-nums;line-height:1.1;margin-top:4px}
    .bl-days{font-size:13.5px;color:var(--fg-2);text-align:right}
    .bl-days b{font-size:15px;font-weight:600}
    .bl-progress{height:8px;border-radius:999px;background:var(--muted);overflow:hidden;margin-top:16px}
    .bl-progress > span{display:block;height:100%;border-radius:999px}

    /* Bandeau de dates */
    .bl-strip{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));margin-top:18px;border-top:1px solid var(--border)}
    .bl-strip > div{padding:14px 0 0}
    .bl-strip > div + div{padding-left:18px;border-left:1px solid var(--border)}
    .bl-strip .v{font-size:14px;font-weight:500;margin-top:4px;font-variant-numeric:tabular-nums}

    /* Plans */
    .bl-plans{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px}
    .bl-tile{border:1.5px solid var(--border);border-radius:var(--radius-lg);padding:16px;display:flex;flex-direction:column;gap:10px;background:#fff;transition:border-color .15s}
    .bl-tile:hover{border-color:var(--border)}
    .bl-tile.current{border-color:var(--primary);background:var(--primary-soft)}
    .bl-tile-price{font-size:22px;font-weight:600;letter-spacing:-.02em;line-height:1}
    .bl-tile-price small{font-size:12px;font-weight:500;color:var(--muted-fg);margin-left:2px}
    .bl-tile .btn{margin-top:auto}

    /* Factures */
    .bl-table-wrap{overflow-x:auto}
    table.bl-table{width:100%;border-collapse:collapse;font-size:13.5px}
    .bl-table th{text-align:left;font-size:11px;font-weight:500;letter-spacing:.04em;text-transform:uppercase;color:var(--muted-fg);padding:10px 18px;border-bottom:1px solid var(--border);white-space:nowrap;background:var(--bg)}
    .bl-table td{padding:13px 18px;border-bottom:1px solid var(--border);vertical-align:middle}
    .bl-table tr:last-child td{border-bottom:0}
    .bl-table tbody tr:hover td{background:var(--bg)}
    .bl-num{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:12px;color:var(--fg-2)}
    .bl-amt{font-weight:600;text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
    .bl-actions{display:flex;justify-content:flex-end;gap:6px;white-space:nowrap}

    /* Zone de résiliation */
    .bl-danger h3{margin:0 0 6px;font-size:14px;font-weight:600}

    @media (max-width:1020px){
        .bl-two{grid-template-columns:minmax(0,1fr)}
    }
    @media (max-width:640px){
        .bl-strip{grid-template-columns:1fr 1fr}
        .bl-strip > div:nth-child(3){grid-column:1/-1;border-left:0;padding-left:0;margin-top:14px;border-top:1px solid var(--border);padding-top:14px}
        .bl-table .bl-hide-sm{display:none}
    }
</style>
@endpush

@section('content')
<x-lx2-header title="Mon abonnement" sub="Votre plan, votre facturation et votre résiliation" :back="route('dashboard')" />

@if (session('success'))
<div class="lx2-flash b-ok" role="status" style="margin-top:18px"><span>{{ session('success') }}</span></div>
@endif
@if (session('error'))
<div class="lx2-flash b-hot" role="alert" style="margin-top:18px"><span>{{ session('error') }}</span></div>
@endif

{{-- ── Plan actuel et échéance ── --}}
<section class="card card-pad" style="margin-top:18px">
    <div class="bl-head" style="margin-bottom:0">
        <div class="bl-plan">
            <span class="bl-plan-ico">{{ $planInitial }}</span>
            <div style="min-width:0">
                <div class="bl-cap">Votre plan</div>
                <div class="bl-plan-name">Plan {{ $planLabel }}</div>
                <div class="bl-help" style="margin-top:2px">{{ $planPrice }}</div>
            </div>
        </div>
        <span class="badge {{ $statusClass }}" style="height:28px;padding:0 12px;font-size:12.5px">{{ $statusLabel }}</span>
    </div>

    @if ($hasSub && $endDate)
    <div class="bl-expiry">
        <div class="bl-expiry-row">
            <div>
                <div class="bl-cap">{{ $expiryEyebrow }}</div>
                <div class="bl-date">{{ $endDate->format('d/m/Y') }}</div>
            </div>
            <div class="bl-days">
                @if ($daysLeft === null)
                    —
                @elseif ($daysLeft > 0)
                    <b>{{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }}</b> restant{{ $daysLeft > 1 ? 's' : '' }}
                @elseif ($daysLeft === 0)
                    <b>Échéance aujourd’hui</b>
                @else
                    <b style="color:var(--destructive)">Expiré depuis {{ abs($daysLeft) }} jour{{ abs($daysLeft) > 1 ? 's' : '' }}</b>
                @endif
            </div>
        </div>
        <div class="bl-progress" aria-hidden="true"><span style="width:{{ $progress }}%;background:{{ $tone }}"></span></div>
    </div>
    @elseif (! $hasSub)
    <div class="bl-expiry">
        <div class="bl-cap">{{ $expiryEyebrow }}</div>
        <div class="bl-date" style="font-size:20px">{{ $isEnterprisePlan ? 'Sur devis' : 'Gratuit' }}</div>
        <p class="bl-help" style="margin-top:6px">{{ $isEnterprisePlan ? 'Pack négocié : contactez-nous pour en discuter.' : 'Sans engagement, sans date d’expiration.' }}</p>
    </div>
    @endif

    <div class="bl-strip">
        <div>
            <div class="bl-cap">Membre depuis</div>
            <div class="v">{{ $user->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
        </div>
        <div>
            <div class="bl-cap">Début de l’abonnement</div>
            <div class="v">{{ $hasSub ? $subscription->created_at->locale('fr')->isoFormat('D MMMM YYYY') : '—' }}</div>
        </div>
        <div>
            <div class="bl-cap">Facturation</div>
            <div class="v">{{ $isPaid ? $billingLabel : '—' }}</div>
        </div>
    </div>

    @if ($isCancelled)
    <div class="lx2-flash b-warm" role="note" style="margin:20px 0 0;align-items:center">
        <span>Votre abonnement sera résilié le <b>{{ $endDate?->format('d/m/Y') }}</b>. Vous gardez l’accès jusqu’à cette date.</span>
        <form method="POST" action="{{ route('billing.reactivate') }}">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">Annuler la résiliation</button>
        </form>
    </div>
    @endif
</section>

<div class="bl-two">

    {{-- ── Colonne principale : changer de plan ── --}}
    <div class="bl-col">
        <section class="card card-pad">
            <div class="bl-head">
                <div>
                    <h2 class="bl-h">Changer de plan</h2>
                    <p class="bl-help" style="margin-top:4px">Passez à un plan supérieur à tout moment.</p>
                </div>
            </div>

            <div class="bl-plans">
                @foreach ($plans as $plan)
                    @php $isCurrent = $subscription?->plan_id === $plan->id; @endphp
                    <div class="bl-tile {{ $isCurrent ? 'current' : '' }}">
                        <div class="bl-cap">{{ $plan->label }}</div>
                        <div class="bl-tile-price">
                            @if ($plan->price > 0)
                                {{ currency_format($plan->price) }}<small>/ mois</small>
                            @else
                                {{ $plan->is_enterprise ? 'Sur devis' : 'Gratuit' }}
                            @endif
                        </div>

                        @if ($isCurrent)
                            <span class="badge b-ok" style="align-self:flex-start">Plan actuel</span>
                        @elseif ($plan->stripe_price_id)
                            <form method="POST" action="{{ route('checkout', $plan) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm btn-block">Choisir</button>
                            </form>
                        @else
                            <a href="{{ route('upgrade') }}" class="btn btn-outline btn-sm btn-block">Voir l’offre</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Factures --}}
        <section class="card" style="overflow:hidden">
            <div class="bl-head" style="padding:18px 20px 0;margin-bottom:12px">
                <div>
                    <h2 class="bl-h">Factures</h2>
                    <p class="bl-help" style="margin-top:4px">Émises via Stripe. Téléchargez le PDF de chaque facture.</p>
                </div>
                @if (count($invoices) > 0)
                    <span class="badge b-muted">{{ count($invoices) }}</span>
                @endif
            </div>

            @if (count($invoices) > 0)
            <div class="bl-table-wrap">
                <table class="bl-table">
                    <thead>
                        <tr>
                            <th>N° de facture</th>
                            <th class="bl-hide-sm">Description</th>
                            <th>Date</th>
                            <th style="text-align:right">Montant</th>
                            <th style="text-align:right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                        <tr>
                            <td><span class="bl-num">{{ $invoice['number'] }}</span></td>
                            <td class="bl-hide-sm" title="{{ $invoice['description'] }}" style="color:var(--fg-2);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $invoice['description'] }}</td>
                            <td style="white-space:nowrap;color:var(--muted-fg)">{{ $invoice['date'] }}</td>
                            <td class="bl-amt">{{ number_format($invoice['amount'], 2, ',', ' ') }} {{ $invoice['currency'] }}</td>
                            <td>
                                <div class="bl-actions">
                                    @if ($invoice['pdf_url'])
                                        <a href="{{ $invoice['pdf_url'] }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">PDF</a>
                                    @endif
                                    @if ($invoice['receipt_url'])
                                        <a href="{{ $invoice['receipt_url'] }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">Voir</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @elseif ($isPaid)
                <div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:36px 20px;text-align:center;color:var(--muted-fg);font-size:13.5px">
                    <x-lx2-icon name="file-text" />
                    <span>Aucune facture pour l’instant.</span>
                    <span class="help" style="margin:0">Vos factures apparaîtront après votre premier paiement.</span>
                </div>
            @else
                <div style="padding:28px 20px;text-align:center;color:var(--muted-fg);font-size:13.5px">
                    Plan Basic : aucune facturation.
                </div>
            @endif
        </section>
    </div>

    {{-- ── Colonne latérale : paiement et résiliation ── --}}
    <aside class="bl-col">
        <section class="card card-pad">
            <h2 class="bl-h" style="margin-bottom:14px">Paiement</h2>
            <div style="display:flex;align-items:center;gap:12px">
                <span class="av-fb" style="width:40px;height:40px;border-radius:10px"><x-lx2-icon name="credit-card" /></span>
                <div style="min-width:0">
                    <div style="font-size:14px;font-weight:500">{{ $isPaid ? 'Carte bancaire' : 'Aucun moyen de paiement' }}</div>
                    <div class="bl-help">{{ $isPaid ? 'Paiement sécurisé par Stripe' : 'Requis uniquement pour un plan payant' }}</div>
                </div>
            </div>
            @if ($isPaid && $isActive && ! $isCancelled && $endDate)
            <div class="bl-strip" style="margin-top:16px;grid-template-columns:1fr">
                <div style="border-left:0;padding-left:0">
                    <div class="bl-cap">Prochain paiement</div>
                    <div class="v">{{ $endDate->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
                </div>
            </div>
            @endif
        </section>

        @if ($hasSub && $isActive && ! $isCancelled && $isPaid)
        <section class="card card-pad bl-danger" style="border-color:var(--destructive-soft)">
            <h3>Résilier l’abonnement</h3>
            <p class="bl-help" style="margin-bottom:14px">
                La résiliation prend effet à la fin de la période en cours
                @if ($endDate)({{ $endDate->format('d/m/Y') }})@endif.
                Vous gardez l’accès jusqu’à cette date. Aucun remboursement au prorata (CGU §12.5).
            </p>
            @php $cancelEndDate = $endDate?->format('d/m/Y') ?? 'la fin de la période'; @endphp
            <form method="POST" action="{{ route('billing.cancel') }}"
                  onsubmit="return confirm('Résilier votre abonnement ? Accès maintenu jusqu’au {{ $cancelEndDate }}.')">
                @csrf
                <button type="submit" class="btn btn-danger-soft btn-block">Résilier mon abonnement</button>
            </form>
        </section>
        @endif
    </aside>
</div>
@endsection
