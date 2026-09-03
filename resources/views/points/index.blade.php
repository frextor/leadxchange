@extends('layouts.app')
@section('title', 'Mes Points')

@push('styles')
<style>
/* ── Tokens ── */
:root {
    --pts-bg: #F8FAFC;
    --pts-card: #fff;
    --pts-border: #E8EDF2;
    --pts-text: #0F172A;
    --pts-muted: #64748B;
    --pts-accent: #1E8F88;
    --pts-accent-light: #E6F5F4;
    --pts-amber: #F59E0B;
    --pts-red: #EF4444;
    --pts-red-light: #FEF2F2;
    --pts-green: #10B981;
    --pts-green-light: #ECFDF5;
}

.pts-hero {
    background: linear-gradient(135deg, #1E8F88 0%, #0D6E68 100%);
    border-radius: 20px;
    padding: 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.pts-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,.07);
}
.pts-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; right: 60px;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}
.pts-balance-num {
    font-size: 52px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: -2px;
}
.pts-card {
    background: var(--pts-card);
    border: 1.5px solid var(--pts-border);
    border-radius: 16px;
    overflow: hidden;
}
.pts-card-head {
    padding: 16px 20px;
    border-bottom: 1px solid var(--pts-border);
    display: flex; align-items: center; gap: 10px;
}
.pts-card-head h3 { font-size: 14px; font-weight: 700; color: var(--pts-text); }

/* Buy form */
.qty-btn {
    width: 36px; height: 36px; border-radius: 10px;
    border: 1.5px solid var(--pts-border);
    background: var(--pts-bg); cursor: pointer;
    font-size: 18px; font-weight: 700; color: var(--pts-text);
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
}
.qty-btn:hover { border-color: var(--pts-accent); color: var(--pts-accent); background: var(--pts-accent-light); }
.qty-input {
    width: 64px; text-align: center;
    border: 1.5px solid var(--pts-border); border-radius: 10px;
    font-size: 18px; font-weight: 700; color: var(--pts-text);
    padding: 6px; outline: none;
    transition: border-color .15s;
}
.qty-input:focus { border-color: var(--pts-accent); }
.preset-btn {
    padding: 6px 14px; border-radius: 99px;
    border: 1.5px solid var(--pts-border);
    background: var(--pts-bg); font-size: 12px; font-weight: 600;
    color: var(--pts-muted); cursor: pointer; transition: all .15s;
}
.preset-btn:hover, .preset-btn.active {
    border-color: var(--pts-accent); color: var(--pts-accent);
    background: var(--pts-accent-light);
}
.btn-buy {
    width: 100%; padding: 14px;
    background: linear-gradient(135deg, #1E8F88, #0D6E68);
    color: #fff; font-size: 15px; font-weight: 700;
    border: none; border-radius: 12px; cursor: pointer;
    transition: opacity .15s; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-buy:hover { opacity: .92; }
.btn-buy:disabled { opacity: .45; cursor: default; }

/* History */
.hist-row {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 20px;
    border-bottom: 1px solid var(--pts-border);
    transition: background .1s;
}
.hist-row:last-child { border-bottom: none; }
.hist-row:hover { background: var(--pts-bg); }
.hist-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
}
.hist-delta {
    font-size: 15px; font-weight: 700; margin-left: auto; flex-shrink: 0;
}
.badge-balance {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 99px;
    font-size: 12px; font-weight: 700;
}
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    {{-- Fil d'ariane --}}
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('dashboard') }}" class="hover:text-gray-600 transition">Accueil</a>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        <span class="text-gray-700 font-semibold">Mes Points</span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm bg-emerald-50 text-emerald-700 border border-emerald-100 mb-6">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm bg-red-50 text-red-700 border border-red-100 mb-6">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        {{ session('error') }}
    </div>
    @endif
    @if(request('canceled'))
    <div class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm bg-amber-50 text-amber-700 border border-amber-100 mb-6">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        Paiement annulé.
    </div>
    @endif

    {{-- Hero solde --}}
    <div class="pts-hero mb-6">
        <div style="position:relative;z-index:1;">
            <p class="text-sm font-semibold mb-1" style="opacity:.8;">Solde actuel</p>
            <div class="flex items-end gap-4 mb-4">
                <span class="pts-balance-num">{{ $balance }}</span>
                <span class="text-lg font-semibold mb-2" style="opacity:.7;">point{{ abs($balance) > 1 ? 's' : '' }}</span>
            </div>
            @if($balance < 0)
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold"
                 style="background:rgba(239,68,68,.2);color:#FCA5A5;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                Solde négatif — rechargez pour continuer à recevoir des leads
            </div>
            @elseif($balance === 0)
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold"
                 style="background:rgba(245,158,11,.2);color:#FDE68A;">
                Solde à zéro
            </div>
            @else
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold"
                 style="background:rgba(16,185,129,.2);color:#6EE7B7;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                Solde positif
            </div>
            @endif
        </div>
    </div>

    <div class="grid gap-5" style="grid-template-columns:1fr 1fr;">

        {{-- Comment fonctionnent les points --}}
        <div class="pts-card">
            <div class="pts-card-head">
                <span style="font-size:18px;">💡</span>
                <h3>Comment ça marche ?</h3>
            </div>
            <div class="p-5 space-y-3 text-sm text-gray-600">
                <div class="flex items-start gap-3">
                    <span class="hist-icon" style="background:#ECFDF5;font-size:14px;">+</span>
                    <div>
                        <p class="font-semibold text-gray-800">Envoyer un lead accepté</p>
                        <p class="text-xs text-gray-400">+2 points crédités</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="hist-icon" style="background:#FEF2F2;font-size:14px;">−</span>
                    <div>
                        <p class="font-semibold text-gray-800">Recevoir un lead</p>
                        <p class="text-xs text-gray-400">−1 point débité</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="hist-icon" style="background:#FFF7ED;font-size:14px;">🎁</span>
                    <div>
                        <p class="font-semibold text-gray-800">Parrainer un membre</p>
                        <p class="text-xs text-gray-400">+5 points par filleul</p>
                    </div>
                </div>
                <div class="mt-2 px-3 py-2 rounded-xl text-xs" style="background:#F8FAFC;color:#64748B;">
                    Plafond : <strong>30 points</strong> maximum
                </div>
            </div>
        </div>

        {{-- Acheter des points --}}
        <div class="pts-card">
            <div class="pts-card-head">
                <span style="font-size:18px;">🛒</span>
                <h3>Acheter des points</h3>
            </div>
            <div class="p-5">
                @if($stripeEnabled)
                <form method="POST" action="{{ route('points.buy.free') }}" id="buyForm">
                    @csrf
                    <p class="text-xs text-gray-400 mb-4">
                        1 point = <strong class="text-gray-700">{{ number_format($pricePerPoint, 2, ',', ' ') }} €</strong>
                    </p>

                    {{-- Presets --}}
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach([1, 3, 5, 10] as $p)
                        <button type="button" class="preset-btn" onclick="setQty({{ $p }})">{{ $p }} pt{{ $p > 1 ? 's' : '' }}</button>
                        @endforeach
                    </div>

                    {{-- Quantité --}}
                    <div class="flex items-center gap-3 mb-4">
                        <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                        <input type="number" name="quantity" id="qty" class="qty-input"
                               value="{{ $balance < 0 ? abs($balance) : 1 }}" min="1" max="50"
                               oninput="updateTotal()">
                        <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                        <span class="text-sm text-gray-400">pts</span>
                    </div>

                    {{-- Total --}}
                    <div class="flex items-center justify-between mb-5 px-3 py-2.5 rounded-xl"
                         style="background:#F0FDF4;border:1px solid #BBF7D0;">
                        <span class="text-sm font-semibold text-gray-700">Total</span>
                        <span class="text-base font-bold text-emerald-700" id="totalDisplay">
                            {{ number_format(($balance < 0 ? abs($balance) : 1) * $pricePerPoint, 2, ',', ' ') }} €
                        </span>
                    </div>

                    <button type="submit" class="btn-buy" id="buyBtn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                        Payer par carte
                    </button>
                </form>
                @else
                <div class="flex flex-col items-center justify-center py-8 text-center text-gray-400 gap-3">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                    <p class="text-sm">Paiement non configuré.<br>Contactez l'administrateur.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Historique --}}
    <div class="pts-card mt-5">
        <div class="pts-card-head">
            <span style="font-size:18px;">📋</span>
            <h3>Historique des transactions</h3>
            <span class="ml-auto text-xs text-gray-400 font-medium">{{ $history->count() }} entrée(s)</span>
        </div>

        @if($history->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 gap-3">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p class="text-sm">Aucune transaction pour l'instant</p>
        </div>
        @else
        <div>
            @foreach($history as $entry)
            @php
                $isPositive = $entry->delta > 0;
                $label = match($entry->reason) {
                    'initial_balance'    => 'Solde initial',
                    'lead_accepted'      => 'Lead envoyé accepté',
                    'lead_received'      => 'Lead reçu',
                    'lead_expired'       => 'Lead expiré',
                    'lead_fraud'         => 'Lead signalé frauduleux',
                    'referral_reward'    => 'Parrainage',
                    'points_purchased'   => 'Achat de points',
                    'malus_uq'           => 'Malus qualité',
                    'admin_adjustment'   => 'Ajustement admin',
                    default              => ucfirst(str_replace('_', ' ', $entry->reason ?: 'Transaction')),
                };
                $icon = match($entry->reason) {
                    'initial_balance'    => '🎉',
                    'lead_accepted'      => '📤',
                    'lead_received'      => '📥',
                    'lead_expired'       => '⏰',
                    'lead_fraud'         => '⚠️',
                    'referral_reward'    => '🎁',
                    'points_purchased'   => '💳',
                    'malus_uq'           => '📉',
                    default              => '📌',
                };
            @endphp
            <div class="hist-row">
                <div class="hist-icon" style="background:{{ $isPositive ? '#ECFDF5' : '#FEF2F2' }};">
                    {{ $icon }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                    @if($entry->created_at)
                    <p class="text-xs text-gray-400">{{ $entry->created_at->diffForHumans() }}</p>
                    @endif
                </div>
                <span class="hist-delta" style="color:{{ $isPositive ? '#10B981' : '#EF4444' }};">
                    {{ $isPositive ? '+' : '' }}{{ $entry->delta }}
                </span>
                <span class="badge-balance ml-2"
                      style="background:{{ ($entry->balance_after ?? 0) >= 0 ? '#ECFDF5' : '#FEF2F2' }};
                             color:{{ ($entry->balance_after ?? 0) >= 0 ? '#059669' : '#DC2626' }};">
                    {{ $entry->balance_after ?? '?' }} pts
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
var pricePerPoint = {{ $pricePerPoint }};

function updateTotal() {
    var qty = parseInt(document.getElementById('qty').value) || 0;
    if (qty < 1) qty = 1;
    if (qty > 50) qty = 50;
    var total = (qty * pricePerPoint).toFixed(2).replace('.', ',');
    document.getElementById('totalDisplay').textContent = total + ' €';
    document.querySelectorAll('.preset-btn').forEach(function(b) { b.classList.remove('active'); });
}
function changeQty(d) {
    var el = document.getElementById('qty');
    var v  = parseInt(el.value) || 1;
    el.value = Math.max(1, Math.min(50, v + d));
    updateTotal();
}
function setQty(n) {
    document.getElementById('qty').value = n;
    updateTotal();
    document.querySelectorAll('.preset-btn').forEach(function(b) {
        b.classList.toggle('active', parseInt(b.textContent) === n);
    });
}
</script>
@endpush
