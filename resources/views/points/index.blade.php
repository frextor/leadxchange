@extends('layouts.app2')

@section('title', 'Mes points — LeadXchange')

{{-- Écran « Mes points » — design lx2. Solde, règles, achat de points et historique. --}}

@php
    $cap         = 30;
    $sendCredit  = \App\Services\PointsService::sendCredit();
    $barPercent  = $balance > 0 ? min(100, round($balance / $cap * 100)) : 0;
    $balanceHelp = $balance < 0
        ? 'Solde négatif : rechargez pour continuer à recevoir des leads.'
        : ($balance === 0
            ? 'Solde nul : envoyez des leads ou achetez des points.'
            : 'Vous pouvez envoyer et recevoir des leads.');
    $defaultQty  = $balance < 0 ? abs($balance) : 1;
    $reasonLabels = [
        'initial_balance'  => 'Solde initial',
        'lead_accepted'    => 'Lead envoyé accepté',
        'lead_received'    => 'Lead reçu',
        'lead_expired'     => 'Lead expiré',
        'lead_fraud'       => 'Lead signalé frauduleux',
        'referral_reward'  => 'Parrainage',
        'points_purchased' => 'Achat de points',
        'malus_uq'         => 'Malus qualité',
        'admin_adjustment' => 'Ajustement admin',
    ];
    $reasonIcons = [
        'initial_balance'  => 'star',
        'lead_accepted'    => 'arrow-up-right',
        'lead_received'    => 'inbox',
        'lead_expired'     => 'clock',
        'lead_fraud'       => 'flag',
        'referral_reward'  => 'user-round-plus',
        'points_purchased' => 'credit-card',
        'malus_uq'         => 'trending-up',
        'admin_adjustment' => 'settings',
    ];
@endphp

@push('styles')
<style>
    .pts-grid{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:20px;align-items:start;margin-top:18px}
    .pts-col{display:flex;flex-direction:column;gap:18px;min-width:0}

    /* Solde */
    .pts-hero{position:relative;overflow:hidden;color:#fff;background:var(--lx-accent-grad);border-radius:var(--radius-lg);padding:26px 28px;box-shadow:var(--shadow-md)}
    .pts-hero::before,.pts-hero::after{content:"";position:absolute;border-radius:999px;background:rgba(255,255,255,.1);pointer-events:none}
    .pts-hero::before{width:300px;height:300px;right:-100px;top:-160px}
    .pts-hero::after{width:170px;height:170px;right:180px;bottom:-120px}
    .pts-hero > *{position:relative}
    .pts-label{font-size:12px;font-weight:500;letter-spacing:.04em;text-transform:uppercase;opacity:.85}
    .pts-balance{display:flex;align-items:baseline;gap:10px;margin-top:8px}
    .pts-num{font-size:60px;font-weight:600;letter-spacing:-.04em;line-height:1}
    .pts-unit{font-size:16px;font-weight:500;opacity:.85}
    .pts-pill{display:inline-flex;align-items:center;gap:6px;height:28px;padding:0 12px;border-radius:999px;background:rgba(255,255,255,.2);font-size:13px;font-weight:500;margin-top:14px}
    .pts-bar{height:8px;border-radius:999px;background:rgba(255,255,255,.25);overflow:hidden;margin-top:20px}
    .pts-bar > span{display:block;height:100%;background:#fff;border-radius:999px}
    .pts-bar-meta{display:flex;justify-content:space-between;font-size:12px;opacity:.85;margin-top:8px}

    /* Cartes */
    .pts-h{margin:0;font-size:15px;font-weight:600;letter-spacing:-.01em}
    .pts-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
    .pts-help{margin:0;font-size:12.5px;color:var(--muted-fg)}

    /* Règles */
    .pts-rule{display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid var(--border)}
    .pts-rule:first-of-type{border-top:0;padding-top:0}
    .pts-ico{width:36px;height:36px;border-radius:10px;display:grid;place-items:center;flex:none}
    .pts-ico.in{background:var(--ok-soft);color:var(--ok-fg)}
    .pts-ico.out{background:var(--hot-soft);color:var(--hot-fg)}
    .pts-ico.soft{background:var(--primary-soft);color:var(--primary)}
    .pts-ico svg{width:17px;height:17px}

    /* Achat */
    .pts-presets{display:flex;flex-wrap:wrap;gap:8px}
    .pts-step{display:flex;align-items:center;gap:10px;margin-top:16px}
    .pts-qty{width:88px;height:40px;text-align:center;font-size:18px;font-weight:600;border:1px solid var(--input);border-radius:var(--radius);background:#fff;outline:none;color:var(--fg)}
    .pts-qty:focus{border-color:var(--primary);box-shadow:0 0 0 3px var(--primary-soft)}
    .pts-total{display:flex;align-items:center;justify-content:space-between;margin-top:16px;padding:14px 16px;border-radius:var(--radius);background:var(--primary-soft);color:var(--primary)}
    .pts-total b{font-size:22px;font-weight:600;letter-spacing:-.02em}
    #buyForm .btn-primary{margin-top:16px}
    #buyForm .btn-primary:disabled{opacity:.5;cursor:not-allowed}

    /* Historique */
    .pts-row{display:flex;align-items:center;gap:12px;padding:13px 20px;border-top:1px solid var(--border)}
    .pts-row:first-child{border-top:0}
    .pts-row:hover{background:var(--bg)}
    .pts-delta{font-size:15px;font-weight:600;margin-left:auto;flex:none}
    .pts-delta.in{color:var(--ok-fg)}
    .pts-delta.out{color:var(--destructive)}
    .pts-after{font-size:12px;color:var(--muted-fg);min-width:64px;text-align:right;flex:none}
    .pts-empty{display:flex;flex-direction:column;align-items:center;gap:8px;padding:36px 20px;text-align:center;color:var(--muted-fg);font-size:13.5px}

    @media (max-width:1020px){
        .pts-grid{grid-template-columns:minmax(0,1fr)}
    }
    @media (max-width:640px){
        .pts-hero{padding:22px 20px}
        .pts-num{font-size:48px}
        .pts-row{padding:12px 16px;flex-wrap:wrap}
        .pts-after{display:none}
    }
</style>
@endpush

@section('content')
<x-lx2-header title="Mes points" sub="Votre solde, son historique et l'achat de points" :back="route('dashboard')" />

@if (session('success'))
<div class="lx2-flash b-ok" role="status" style="margin-top:18px"><span>{{ session('success') }}</span></div>
@endif
@if (session('error'))
<div class="lx2-flash b-hot" role="alert" style="margin-top:18px"><span>{{ session('error') }}</span></div>
@endif
@if (request('canceled'))
<div class="lx2-flash b-warm" role="status" style="margin-top:18px"><span>Paiement annulé.</span></div>
@endif

<section class="pts-hero" style="margin-top:18px">
    <div class="pts-label">Solde actuel</div>
    <div class="pts-balance">
        <span class="pts-num">{{ $balance }}</span>
        <span class="pts-unit">point{{ abs($balance) > 1 ? 's' : '' }}</span>
    </div>
    <div class="pts-pill">{{ $balanceHelp }}</div>
    <div class="pts-bar" aria-hidden="true"><span style="width:{{ $barPercent }}%"></span></div>
    <div class="pts-bar-meta">
        <span>0</span>
        <span>Plafond : {{ $cap }} points</span>
    </div>
</section>

<div class="pts-grid">

    {{-- ── Colonne principale : historique ── --}}
    <div class="pts-col">
        <section class="card" style="overflow:hidden">
            <div class="pts-head" style="padding:18px 20px 0;margin-bottom:12px">
                <h2 class="pts-h">Historique</h2>
                <span class="badge b-muted">{{ $history->count() }} entrée{{ $history->count() > 1 ? 's' : '' }}</span>
            </div>

            @if ($history->isEmpty())
                <div class="pts-empty">
                    <x-lx2-icon name="file-text" />
                    <span>Aucune transaction pour l'instant.</span>
                </div>
            @else
                @foreach ($history as $entry)
                    @php
                        $isIn   = $entry->delta > 0;
                        $label  = $reasonLabels[$entry->reason] ?? ucfirst(str_replace('_', ' ', $entry->reason ?: 'Transaction'));
                        $icon   = $reasonIcons[$entry->reason] ?? 'file-text';
                    @endphp
                    <div class="pts-row">
                        <span class="pts-ico {{ $isIn ? 'in' : 'out' }}"><x-lx2-icon :name="$icon" /></span>
                        <div style="min-width:0;flex:1">
                            <div style="font-weight:500;font-size:14px">{{ $label }}</div>
                            @if ($entry->created_at)
                                <div class="pts-help">{{ $entry->created_at->locale('fr')->diffForHumans() }}</div>
                            @endif
                        </div>
                        <span class="pts-delta {{ $isIn ? 'in' : 'out' }}">{{ $isIn ? '+' : '' }}{{ $entry->delta }}</span>
                        <span class="pts-after">solde {{ $entry->balance_after ?? '—' }}</span>
                    </div>
                @endforeach
            @endif
        </section>
    </div>

    {{-- ── Colonne latérale : achat et règles ── --}}
    <aside class="pts-col">

        <section class="card card-pad">
            <div class="pts-head" style="margin-bottom:6px">
                <h2 class="pts-h">Acheter des points</h2>
            </div>

            @if (!$stripeEnabled)
                <div class="pts-empty" style="padding:24px 8px">
                    <x-lx2-icon name="credit-card" />
                    <span>Le paiement en ligne n'est pas disponible pour le moment.<br>Contactez l'administrateur.</span>
                </div>
            @elseif ($balance >= 0)
                <div class="pts-empty" style="padding:24px 8px">
                    <x-lx2-icon name="check" />
                    <span>Votre solde n'est pas négatif — l'achat de points n'est utile que pour se remettre à flot après un solde négatif.</span>
                </div>
            @else
                <p class="pts-help" style="margin-bottom:16px">
                    1 point = <b style="color:var(--fg)">{{ number_format($pricePerPoint, 2, ',', ' ') }} €</b>
                </p>

                <form method="POST" action="{{ route('points.buy.free') }}" id="buyForm">
                    @csrf

                    <div class="pts-presets">
                        @foreach ([1, 3, 5, 10] as $p)
                        <button type="button" class="chip preset-btn" data-qty="{{ $p }}" onclick="setQty({{ $p }})">{{ $p }} pt{{ $p > 1 ? 's' : '' }}</button>
                        @endforeach
                    </div>

                    <div class="pts-step">
                        <button type="button" class="btn btn-outline" style="width:40px;padding:0" onclick="changeQty(-1)" aria-label="Retirer un point">−</button>
                        <input type="number" name="quantity" id="qty" class="pts-qty"
                               value="{{ $defaultQty }}" min="1" max="50" oninput="updateTotal()">
                        <button type="button" class="btn btn-outline" style="width:40px;padding:0" onclick="changeQty(1)" aria-label="Ajouter un point">+</button>
                        <span class="pts-help">points</span>
                    </div>

                    <div class="pts-total">
                        <span style="font-size:13.5px;font-weight:500">Total</span>
                        <b id="totalDisplay">{{ number_format($defaultQty * $pricePerPoint, 2, ',', ' ') }} €</b>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="buyBtn">
                        <x-lx2-icon name="credit-card" />Payer par carte
                    </button>
                </form>
            @endif
        </section>

        <section class="card card-pad">
            <div class="pts-head" style="margin-bottom:8px">
                <h2 class="pts-h">Comment ça marche</h2>
            </div>

            <div class="pts-rule">
                <span class="pts-ico in"><x-lx2-icon name="arrow-up-right" /></span>
                <div style="flex:1;min-width:0"><div style="font-size:14px;font-weight:500">Envoyer un lead accepté</div><div class="pts-help">Vous gagnez {{ $sendCredit }} point{{ $sendCredit > 1 ? 's' : '' }}</div></div>
                <span class="badge b-ok">+{{ $sendCredit }}</span>
            </div>
            <div class="pts-rule">
                <span class="pts-ico out"><x-lx2-icon name="inbox" /></span>
                <div style="flex:1;min-width:0"><div style="font-size:14px;font-weight:500">Recevoir un lead</div><div class="pts-help">Vous perdez 1 point</div></div>
                <span class="badge b-hot">−1</span>
            </div>
            <div class="pts-rule">
                <span class="pts-ico soft"><x-lx2-icon name="user-round-plus" /></span>
                <div style="flex:1;min-width:0"><div style="font-size:14px;font-weight:500">Parrainer un membre</div><div class="pts-help">5 points par filleul</div></div>
                <span class="badge b-soft">+5</span>
            </div>
            <p class="pts-help" style="margin:14px 0 0">Plafond de <b style="color:var(--fg)">30 points</b> maximum.</p>
        </section>

    </aside>
</div>
@endsection

@push('scripts')
<script>
    const pricePerPoint = {{ $pricePerPoint }};

    function clampQty(v) { return Math.max(1, Math.min(50, v)); }

    function updateTotal() {
        const el  = document.getElementById('qty');
        const qty = clampQty(parseInt(el.value) || 1);
        document.getElementById('totalDisplay').textContent =
            (qty * pricePerPoint).toFixed(2).replace('.', ',') + ' €';
        document.querySelectorAll('.preset-btn').forEach(b => b.classList.toggle('on', parseInt(b.dataset.qty) === qty));
    }

    function changeQty(d) {
        const el = document.getElementById('qty');
        el.value = clampQty((parseInt(el.value) || 1) + d);
        updateTotal();
    }

    function setQty(n) {
        document.getElementById('qty').value = n;
        updateTotal();
    }

    document.addEventListener('DOMContentLoaded', updateTotal);
</script>
@endpush
