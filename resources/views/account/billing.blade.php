@extends('layouts.app')
@section('title', 'Mon abonnement — LeadXchange')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
<style>
/* ── Token palette ── */
:root {
    --bl-bg:         #F5F3F0;
    --bl-surface:    #FFFFFF;
    --bl-border:     #E4E0DA;
    --bl-border2:    #CFC9C0;
    --bl-text1:      #1A1714;
    --bl-text2:      #726860;
    --bl-text3:      #A89E94;
    --bl-teal:       #14A98C;
    --bl-teal-bg:    #EAF7F3;
    --bl-teal-mid:   #B8E8DC;
    --bl-teal-dark:  #0D8A72;
    --bl-amber:      #C07020;
    --bl-amber-bg:   #FEF3DC;
    --bl-amber-bdr:  #F0C870;
    --bl-red:        #CC2222;
    --bl-red-bg:     #FEE8E8;
    --bl-red-bdr:    #F0A0A0;
    --bl-green:      #187A38;
    --bl-green-bg:   #E0F4E8;
    --bl-green-bdr:  #8ECFA8;
    --bl-display:    'Plus Jakarta Sans', system-ui, sans-serif;
}
@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
        --bl-bg:        #131110;
        --bl-surface:   #1E1B18;
        --bl-border:    #2C2720;
        --bl-border2:   #3A342A;
        --bl-text1:     #EDE8E0;
        --bl-text2:     #9C9288;
        --bl-text3:     #635C54;
        --bl-teal-bg:   #0B2820;
        --bl-teal-mid:  #164034;
        --bl-amber-bg:  #281A04;
        --bl-amber-bdr: #6B4A10;
        --bl-red-bg:    #260A0A;
        --bl-red-bdr:   #6B2020;
        --bl-green-bg:  #0A1E10;
        --bl-green-bdr: #1E5030;
    }
}
:root[data-theme="dark"] {
    --bl-bg:        #131110;
    --bl-surface:   #1E1B18;
    --bl-border:    #2C2720;
    --bl-border2:   #3A342A;
    --bl-text1:     #EDE8E0;
    --bl-text2:     #9C9288;
    --bl-text3:     #635C54;
    --bl-teal-bg:   #0B2820;
    --bl-teal-mid:  #164034;
    --bl-amber-bg:  #281A04;
    --bl-amber-bdr: #6B4A10;
    --bl-red-bg:    #260A0A;
    --bl-red-bdr:   #6B2020;
    --bl-green-bg:  #0A1E10;
    --bl-green-bdr: #1E5030;
}

/* ── Layout ── */
.bl-wrap {
    max-width: 660px;
    margin: 0 auto;
    padding: 36px 16px 80px;
    background: var(--bl-bg);
    min-height: 100vh;
}
.bl-section { margin-bottom: 14px; }

/* ── Page title ── */
.bl-page-title {
    font-family: var(--bl-display);
    font-size: 20px;
    font-weight: 800;
    color: var(--bl-text1);
    letter-spacing: -0.3px;
    margin-bottom: 2px;
}
.bl-page-sub {
    font-size: 13px;
    color: var(--bl-text2);
    margin-bottom: 24px;
}

/* ── Card ── */
.bl-card {
    background: var(--bl-surface);
    border: 1px solid var(--bl-border);
    border-radius: 16px;
    overflow: hidden;
}

/* ── Flash banners ── */
.bl-flash {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 13px;
    line-height: 1.5;
    margin-bottom: 14px;
    border: 1px solid;
}
.bl-flash-ok  { background: var(--bl-green-bg);  color: var(--bl-green);  border-color: var(--bl-green-bdr); }
.bl-flash-err { background: var(--bl-red-bg);    color: var(--bl-red);    border-color: var(--bl-red-bdr);   }
.bl-flash svg { flex-shrink: 0; margin-top: 1px; }

/* ── Status pill ── */
.bl-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1px;
}
.bl-pill-active { background: var(--bl-green-bg); color: var(--bl-green); }
.bl-pill-cancel { background: var(--bl-amber-bg); color: var(--bl-amber); }
.bl-pill-off    { background: var(--bl-border);   color: var(--bl-text2); }
.bl-pill-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}
.bl-pill-active .bl-pill-dot {
    animation: bl-pulse 2s ease-in-out infinite;
}
@keyframes bl-pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.35; }
}

/* ── Plan hero (top of first card) ── */
.bl-plan-hero {
    padding: 22px 22px 0;
}
.bl-plan-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}
.bl-avatar {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--bl-display);
    font-size: 18px; font-weight: 800;
    color: #fff;
    background: linear-gradient(135deg, #2DD4B0, #14A98C);
    flex-shrink: 0;
}
.bl-plan-name {
    font-family: var(--bl-display);
    font-size: 16px;
    font-weight: 800;
    color: var(--bl-text1);
    letter-spacing: -0.2px;
}
.bl-plan-price { font-size: 12px; color: var(--bl-text2); margin-top: 2px; }

/* ── Expiry spotlight ── */
.bl-expiry {
    margin-bottom: 20px;
    padding: 16px 18px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid;
}
.bl-expiry-teal  {
    background: var(--bl-teal-bg);
    border-color: var(--bl-teal-mid);
}
.bl-expiry-amber {
    background: var(--bl-amber-bg);
    border-color: var(--bl-amber-bdr);
}
.bl-expiry-muted {
    background: var(--bl-border);
    border-color: var(--bl-border2);
}
.bl-expiry-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.bl-expiry-icon-teal  { background: var(--bl-teal); }
.bl-expiry-icon-amber { background: var(--bl-amber); }
.bl-expiry-icon-muted { background: var(--bl-text3); }
.bl-expiry-eyebrow {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.9px;
    margin-bottom: 3px;
}
.bl-expiry-eyebrow-teal  { color: var(--bl-teal-dark); }
.bl-expiry-eyebrow-amber { color: var(--bl-amber); }
.bl-expiry-eyebrow-muted { color: var(--bl-text2); }
.bl-expiry-date {
    font-family: var(--bl-display);
    font-size: 21px;
    font-weight: 800;
    color: var(--bl-text1);
    letter-spacing: -0.4px;
    line-height: 1;
    font-variant-numeric: tabular-nums;
}
.bl-expiry-sub { font-size: 12px; color: var(--bl-text2); margin-top: 3px; }
.bl-expiry-sub-teal  strong { color: var(--bl-teal-dark); }
.bl-expiry-sub-amber strong { color: var(--bl-amber); }
.bl-expiry-sub-red   strong { color: var(--bl-red); }

/* ── Date strip ── */
.bl-date-strip {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    border-top: 1px solid var(--bl-border);
    margin: 0 -22px;
}
.bl-date-item {
    padding: 14px 22px;
    border-right: 1px solid var(--bl-border);
}
.bl-date-item:last-child { border-right: none; }
.bl-date-eyebrow {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.9px;
    color: var(--bl-text3);
    margin-bottom: 4px;
}
.bl-date-val {
    font-family: var(--bl-display);
    font-size: 13px;
    font-weight: 700;
    color: var(--bl-text1);
    font-variant-numeric: tabular-nums;
}
.bl-date-hint { font-size: 11px; color: var(--bl-text2); margin-top: 1px; }

/* ── Cancel banner (inside card) ── */
.bl-cancel-banner {
    margin: 0;
    padding: 16px 22px;
    background: var(--bl-amber-bg);
    border-top: 1px solid var(--bl-amber-bdr);
}
.bl-cancel-banner p {
    font-size: 12px;
    color: var(--bl-amber);
    line-height: 1.55;
    margin-bottom: 10px;
}
.bl-btn-reactivate {
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid var(--bl-amber-bdr);
    color: var(--bl-amber);
    background: transparent;
    cursor: pointer;
    transition: background 0.15s;
    font-family: inherit;
}
.bl-btn-reactivate:hover { background: #fff3dc; }

/* ── Section header inside card ── */
.bl-card-header {
    padding: 16px 22px 14px;
    border-bottom: 1px solid var(--bl-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.bl-card-title {
    font-family: var(--bl-display);
    font-size: 13px;
    font-weight: 700;
    color: var(--bl-text1);
}
.bl-card-sub { font-size: 11px; color: var(--bl-text2); margin-top: 1px; }
.bl-badge-count {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
    background: var(--bl-teal-bg);
    color: var(--bl-teal-dark);
    flex-shrink: 0;
}

/* ── Plans grid ── */
.bl-plans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 10px;
    padding: 16px;
}
.bl-plan-card {
    border: 1.5px solid var(--bl-border);
    border-radius: 12px;
    padding: 14px;
    transition: border-color 0.15s;
}
.bl-plan-card:hover:not(.bl-plan-card--active) {
    border-color: var(--bl-teal);
}
.bl-plan-card--active {
    border-color: var(--bl-teal);
    background: var(--bl-teal-bg);
}
.bl-plan-card-label {
    font-size: 11px;
    font-weight: 700;
    color: var(--bl-text2);
    margin-bottom: 6px;
}
.bl-plan-card-price {
    font-family: var(--bl-display);
    font-size: 19px;
    font-weight: 800;
    color: var(--bl-text1);
    letter-spacing: -0.4px;
    line-height: 1;
}
.bl-plan-card-price span {
    font-size: 11px;
    font-weight: 500;
    color: var(--bl-text2);
}
.bl-current-tag {
    display: inline-block;
    margin-top: 8px;
    font-size: 10px;
    font-weight: 700;
    color: var(--bl-teal-dark);
    background: var(--bl-teal-mid);
    padding: 2px 8px;
    border-radius: 999px;
}
.bl-plan-btn {
    display: block;
    width: 100%;
    margin-top: 10px;
    padding: 7px;
    border-radius: 8px;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    font-family: inherit;
    text-decoration: none;
    transition: opacity 0.15s;
}
.bl-plan-btn-primary { background: var(--bl-teal); color: #fff; }
.bl-plan-btn-primary:hover { opacity: 0.85; }
.bl-plan-btn-ghost {
    background: transparent;
    color: var(--bl-text2);
    border: 1.5px solid var(--bl-border);
}
.bl-plan-btn-ghost:hover { border-color: var(--bl-border2); color: var(--bl-text1); }

/* ── Invoice table ── */
.bl-table-wrap { overflow-x: auto; }
table.bl-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    font-variant-numeric: tabular-nums;
}
.bl-table th {
    text-align: left;
    padding: 10px 18px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.9px;
    color: var(--bl-text3);
    border-bottom: 1px solid var(--bl-border);
    white-space: nowrap;
    background: var(--bl-surface);
}
.bl-table td {
    padding: 11px 18px;
    border-bottom: 1px solid var(--bl-border);
    color: var(--bl-text1);
    vertical-align: middle;
}
.bl-table tr:last-child td { border-bottom: none; }
.bl-table tr:hover td { background: var(--bl-teal-bg); }
.bl-inv-num   { font-family: monospace; font-size: 11px; color: var(--bl-text2); }
.bl-inv-desc  { color: var(--bl-text2); max-width: 160px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bl-inv-amt   { font-family: var(--bl-display); font-weight: 700; white-space: nowrap; text-align: right; }
.bl-inv-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 9px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    color: var(--bl-teal-dark);
    background: var(--bl-teal-bg);
    text-decoration: none;
    border: 1px solid var(--bl-teal-mid);
    white-space: nowrap;
    transition: background 0.12s;
}
.bl-inv-link:hover { background: var(--bl-teal-mid); }
.bl-inv-ghost {
    font-size: 11px;
    color: var(--bl-text2);
    text-decoration: none;
    padding: 4px 6px;
    border-radius: 6px;
}
.bl-inv-ghost:hover { color: var(--bl-text1); }

.bl-empty {
    padding: 36px 20px;
    text-align: center;
    color: var(--bl-text2);
    font-size: 13px;
}
.bl-empty p { font-size: 11px; color: var(--bl-text3); margin-top: 6px; }

/* ── Cancel zone ── */
.bl-danger { padding: 20px 22px; }
.bl-danger h3 {
    font-family: var(--bl-display);
    font-size: 13px;
    font-weight: 700;
    color: var(--bl-text1);
    margin-bottom: 4px;
}
.bl-danger p { font-size: 12px; color: var(--bl-text2); line-height: 1.55; margin-bottom: 14px; }
.bl-btn-cancel {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid var(--bl-red-bdr);
    color: var(--bl-red);
    background: transparent;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}
.bl-btn-cancel:hover { background: var(--bl-red-bg); }

@media (max-width: 480px) {
    .bl-hidden-xs { display: none !important; }
    .bl-date-strip { grid-template-columns: 1fr 1fr; }
    .bl-date-item:nth-child(3) { border-top: 1px solid var(--bl-border); grid-column: span 2; border-right: none; }
    .bl-plans-grid { grid-template-columns: 1fr 1fr; }
}
@media (prefers-reduced-motion: reduce) {
    .bl-pill-dot { animation: none !important; }
}
</style>
@endpush

@section('content')
@php
    $hasSub     = !is_null($subscription);
    $isActive   = $hasSub && $subscription->status === 'active';
    $isCancelled= $hasSub && (bool)$subscription->cancel_at_period_end;
    $isPaid     = $hasSub && (float)($subscription->plan?->price ?? 0) > 0;
    $endDate    = $subscription?->current_period_end;

    $daysLeft = null;
    if ($endDate) {
        $daysLeft = (int) now()->diffInDays($endDate, false);
    }

    $billingLabel = match($subscription?->billing_period ?? '') {
        'yearly'  => 'Annuel',
        'monthly' => 'Mensuel',
        default   => '—',
    };

    $planInitial = strtoupper(substr($subscription?->plan?->name ?? 'Basic', 0, 1));
    $planLabel   = $subscription?->plan?->label ?? 'Basic';
    $isEnterprisePlan = $subscription?->plan?->is_enterprise ?? false;
    $planPrice   = $isPaid
        ? currency_format($subscription->plan->price) . ($subscription->billing_period === 'yearly' ? '/an' : '/mois')
        : ($isEnterprisePlan ? 'Sur Devis' : 'Gratuit · sans engagement');

    // expiry style
    if ($isCancelled) {
        $expiryStyle = 'amber';
        $expiryEyebrow = 'Accès jusqu\'au';
    } elseif ($daysLeft !== null && $daysLeft < 0) {
        $expiryStyle = 'amber';
        $expiryEyebrow = 'Expiré le';
    } else {
        $expiryStyle = 'teal';
        $expiryEyebrow = 'Prochain renouvellement';
    }
@endphp

<div class="bl-wrap">

    {{-- Page header --}}
    <div class="bl-page-title">Mon abonnement</div>
    <div class="bl-page-sub">Gérez votre plan et consultez votre historique de facturation.</div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="bl-flash bl-flash-ok">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bl-flash bl-flash-err">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ── Card 1 : Plan + expiry ── --}}
    <div class="bl-card bl-section">
        <div class="bl-plan-hero">

            {{-- Plan header --}}
            <div class="bl-plan-top">
                <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                    <div class="bl-avatar">{{ $planInitial }}</div>
                    <div>
                        <div class="bl-plan-name">Plan {{ $planLabel }}</div>
                        <div class="bl-plan-price">{{ $planPrice }}</div>
                    </div>
                </div>

                @if($isCancelled)
                <div class="bl-pill bl-pill-cancel"><span class="bl-pill-dot"></span>Résiliation prog.</div>
                @elseif($isActive)
                <div class="bl-pill bl-pill-active"><span class="bl-pill-dot"></span>Actif</div>
                @elseif($hasSub)
                <div class="bl-pill bl-pill-off">Inactif</div>
                @else
                <div class="bl-pill bl-pill-off">Basic</div>
                @endif
            </div>

            {{-- Expiry spotlight --}}
            @if($hasSub && $endDate)
            <div class="bl-expiry bl-expiry-{{ $expiryStyle }}">
                <div class="bl-expiry-icon bl-expiry-icon-{{ $expiryStyle }}">
                    @if($isCancelled)
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="9" y1="16" x2="15" y2="16"/></svg>
                    @else
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                    @endif
                </div>
                <div>
                    <div class="bl-expiry-eyebrow bl-expiry-eyebrow-{{ $expiryStyle }}">{{ $expiryEyebrow }}</div>
                    <div class="bl-expiry-date">{{ $endDate->format('d/m/Y') }}</div>
                    <div class="bl-expiry-sub bl-expiry-sub-{{ $daysLeft < 0 ? 'red' : $expiryStyle }}">
                        @if($daysLeft === null)
                            —
                        @elseif($daysLeft > 0)
                            <strong>{{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }}</strong> restant{{ $daysLeft > 1 ? 's' : '' }}
                        @elseif($daysLeft === 0)
                            <strong>Expire aujourd'hui</strong>
                        @else
                            <strong>Expiré depuis {{ abs($daysLeft) }} jour{{ abs($daysLeft) > 1 ? 's' : '' }}</strong>
                        @endif
                    </div>
                </div>
            </div>
            @elseif(!$hasSub)
            <div class="bl-expiry bl-expiry-muted" style="margin-bottom:20px;">
                <div class="bl-expiry-icon bl-expiry-icon-muted">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                </div>
                <div>
                    <div class="bl-expiry-eyebrow bl-expiry-eyebrow-muted">Abonnement</div>
                    <div class="bl-expiry-date" style="font-size:15px;">{{ $isEnterprisePlan ? 'Sur Devis' : 'Gratuit · Basic' }}</div>
                    <div class="bl-expiry-sub">{{ $isEnterprisePlan ? 'Pack négocié · contactez-nous' : 'Sans engagement · pas d\'expiration' }}</div>
                </div>
            </div>
            @endif

            {{-- Date strip --}}
            <div class="bl-date-strip">
                <div class="bl-date-item">
                    <div class="bl-date-eyebrow">Membre depuis</div>
                    <div class="bl-date-val">{{ $user->created_at->format('d/m/Y') }}</div>
                    <div class="bl-date-hint">{{ $user->created_at->format('Y') }}</div>
                </div>
                <div class="bl-date-item">
                    <div class="bl-date-eyebrow">Début abonnement</div>
                    @if($hasSub)
                    <div class="bl-date-val">{{ $subscription->created_at->format('d/m/Y') }}</div>
                    <div class="bl-date-hint">{{ $billingLabel }}</div>
                    @else
                    <div class="bl-date-val">—</div>
                    <div class="bl-date-hint">Plan gratuit</div>
                    @endif
                </div>
                <div class="bl-date-item">
                    <div class="bl-date-eyebrow">Paiement</div>
                    <div class="bl-date-val" style="font-size:11px;">{{ $isPaid ? 'Carte bancaire' : '—' }}</div>
                    <div class="bl-date-hint">{{ $isPaid ? 'PCI-DSS' : ($isEnterprisePlan ? 'Sur Devis' : 'Gratuit') }}</div>
                </div>
            </div>
        </div>

        {{-- Cancel warning --}}
        @if($isCancelled)
        <div class="bl-cancel-banner">
            <p>
                ⚠ Votre abonnement sera <strong>résilié le {{ $endDate?->format('d/m/Y') }}</strong>.
                Vous conservez l'accès à toutes les fonctionnalités jusqu'à cette date.
            </p>
            <form method="POST" action="{{ route('billing.reactivate') }}">
                @csrf
                <button type="submit" class="bl-btn-reactivate">↩ Annuler la résiliation</button>
            </form>
        </div>
        @endif
    </div>

    {{-- ── Card 2 : Plans ── --}}
    <div class="bl-card bl-section">
        <div class="bl-card-header">
            <div>
                <div class="bl-card-title">Changer de plan</div>
                <div class="bl-card-sub">Passez à un plan supérieur à tout moment.</div>
            </div>
        </div>
        <div class="bl-plans-grid">
            @foreach($plans as $plan)
            @php $isCurrent = $subscription?->plan_id === $plan->id; @endphp
            <div class="bl-plan-card {{ $isCurrent ? 'bl-plan-card--active' : '' }}">
                <div class="bl-plan-card-label">{{ $plan->label }}</div>
                <div class="bl-plan-card-price">
                    {{ $plan->price > 0 ? currency_format($plan->price) : ($plan->is_enterprise ? 'Sur Devis' : 'Gratuit') }}<span>{{ $plan->price > 0 ? '/mois' : '' }}</span>
                </div>
                @if($isCurrent)
                    <div class="bl-current-tag">Plan actuel</div>
                @elseif($plan->stripe_price_id)
                    <form method="POST" action="{{ route('checkout', $plan) }}">
                        @csrf
                        <button type="submit" class="bl-plan-btn bl-plan-btn-primary">Choisir</button>
                    </form>
                @else
                    <a href="{{ route('upgrade') }}" class="bl-plan-btn bl-plan-btn-ghost">Voir</a>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Card 3 : Invoices ── --}}
    <div class="bl-card bl-section">
        <div class="bl-card-header">
            <div>
                <div class="bl-card-title">Factures</div>
                <div class="bl-card-sub">Émises via Stripe (CGU §12.3) · Cliquez PDF pour télécharger</div>
            </div>
            @if(count($invoices) > 0)
            <span class="bl-badge-count">{{ count($invoices) }}</span>
            @endif
        </div>

        @if(count($invoices) > 0)
        <div class="bl-table-wrap">
            <table class="bl-table">
                <thead>
                    <tr>
                        <th>N° Facture</th>
                        <th class="bl-hidden-xs">Description</th>
                        <th>Date</th>
                        <th style="text-align:right;">Montant</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td><span class="bl-inv-num">{{ $invoice['number'] }}</span></td>
                        <td class="bl-inv-desc bl-hidden-xs" title="{{ $invoice['description'] }}">
                            {{ $invoice['description'] }}
                        </td>
                        <td style="white-space:nowrap;color:var(--bl-text2);">{{ $invoice['date'] }}</td>
                        <td class="bl-inv-amt">{{ number_format($invoice['amount'], 2, ',', ' ') }} {{ $invoice['currency'] }}</td>
                        <td style="text-align:right;white-space:nowrap;">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;">
                                @if($invoice['pdf_url'])
                                <a href="{{ $invoice['pdf_url'] }}" target="_blank" class="bl-inv-link">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    PDF
                                </a>
                                @endif
                                @if($invoice['receipt_url'])
                                <a href="{{ $invoice['receipt_url'] }}" target="_blank" class="bl-inv-ghost">Voir ↗</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @elseif($isPaid)
        <div class="bl-empty">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--bl-border2)" stroke-width="1.5" style="display:block;margin:0 auto 8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Aucune facture pour l'instant
            <p>Vos factures apparaîtront après votre premier paiement.</p>
        </div>
        @else
        <div class="bl-empty">
            <p>Plan Basic — aucune facturation.</p>
        </div>
        @endif
    </div>

    {{-- ── Card 4 : Cancel ── --}}
    @if($hasSub && $isActive && !$isCancelled && $isPaid)
    <div class="bl-card bl-section" style="border-color: var(--bl-red-bdr);">
        <div class="bl-danger">
            <h3>Résilier l'abonnement</h3>
            <p>
                La résiliation prend effet à la fin de la période en cours
                @if($endDate)({{ $endDate->format('d/m/Y') }})@endif.
                Vous conservez l'accès jusqu'à cette date. Aucun remboursement prorata (CGU §12.5).
            </p>
            @php $cancelEndDate = $endDate?->format('d/m/Y') ?? 'la fin de la période'; @endphp
            <form method="POST" action="{{ route('billing.cancel') }}"
                  onsubmit="return confirm('Résilier votre abonnement ?\n\nAccès maintenu jusqu\'au {{ $cancelEndDate }}.')">
                @csrf
                <button type="submit" class="bl-btn-cancel">Résilier mon abonnement</button>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
