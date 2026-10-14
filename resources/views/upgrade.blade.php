@extends('layouts.app2')

@section('title', 'Plans & tarifs — LeadXchange')

{{-- Écran « Plans & tarifs » — design lx2. Grille de plans, bascule mensuel/annuel, devis Entreprise. --}}

@php
    $themes = [
        'basic'      => ['color' => 'var(--muted-fg)', 'soft' => 'var(--muted)',       'grad' => null],
        'premium'    => ['color' => 'var(--primary)',  'soft' => 'var(--primary-soft)', 'grad' => 'var(--lx-accent-grad)'],
        'enterprise' => ['color' => 'var(--purple)',    'soft' => 'var(--purple-soft)',  'grad' => 'linear-gradient(135deg,#B394F8,#9E6EF5)'],
    ];
    $annualBillingEnabled = (bool) \App\Models\SystemSetting::get('billing.annual_enabled', true);
    $annualDiscountPct    = (int)  \App\Models\SystemSetting::get('billing.annual_discount_pct', 0);
    $hasAnnualPlans       = $plans->filter(fn($p) => $p->annual_price > 0)->isNotEmpty();
    $isSpecialRole        = $currentPlan && in_array($currentPlan->name, ['consul', 'ambassadeur'], true);
@endphp

@push('styles')
<style>
    .up-seg{display:inline-flex;background:var(--muted);border-radius:var(--radius-lg);padding:4px;gap:2px}
    .up-seg button{height:36px;padding:0 20px;border:0;background:transparent;border-radius:7px;font-size:13.5px;font-weight:500;color:var(--muted-fg);cursor:pointer;transition:background .15s,color .15s;position:relative}
    .up-seg button:hover{color:var(--fg-2)}
    .up-seg button.on{background:#fff;color:var(--fg);box-shadow:var(--shadow-sm)}
    .up-seg .save-tag{position:absolute;top:-9px;right:-8px;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:999px;background:var(--green);color:#fff;line-height:1.5}

    .up-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;align-items:stretch;margin-top:28px}
    .up-card{position:relative;display:flex;flex-direction:column;border:1.5px solid var(--border);border-radius:var(--radius-lg);background:#fff;overflow:hidden;transition:border-color .15s,box-shadow .15s}
    .up-card.featured{border-color:var(--primary);box-shadow:0 8px 28px rgba(65,84,244,.12)}
    .up-card.current{border-color:var(--primary)}
    .up-ribbon{position:absolute;top:14px;right:14px;z-index:1}
    .up-top{height:4px;width:100%}
    .up-body{padding:24px 22px;display:flex;flex-direction:column;flex:1}
    .up-name{font-size:17px;font-weight:600;letter-spacing:-.01em}
    .up-desc{font-size:12.5px;color:var(--muted-fg);margin-top:3px;line-height:1.5;min-height:32px}
    .up-price-row{display:flex;align-items:baseline;gap:5px;margin-top:18px}
    .up-price{font-size:34px;font-weight:600;letter-spacing:-.03em;line-height:1;font-variant-numeric:tabular-nums}
    .up-period{font-size:13px;color:var(--muted-fg)}
    .up-subprice{font-size:12px;color:var(--muted-fg);margin-top:6px}
    .up-sep{height:1px;background:var(--border);margin:20px 0}
    .up-features{display:flex;flex-direction:column;gap:10px;flex:1}
    .up-feat{display:flex;align-items:flex-start;gap:9px;font-size:13px;color:var(--fg-2);line-height:1.5}
    .up-feat-ico{width:17px;height:17px;border-radius:999px;display:grid;place-items:center;flex:none;margin-top:1px}
    .up-feat-ico svg{width:9px;height:9px}
    .up-cta-wrap{margin-top:22px}
    .up-cta-note{text-align:center;font-size:11px;color:var(--muted-fg);margin-top:9px;display:flex;align-items:center;justify-content:center;gap:5px}

    .up-role-banner{display:flex;align-items:center;gap:14px;padding:18px 20px;border-radius:var(--radius-lg);background:var(--lx-accent-grad);color:#fff;margin-top:18px}
    .up-role-ico{width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.18);display:grid;place-items:center;flex:none}

    .up-qform .field{margin-bottom:14px}
    .up-qform label{display:block;font-size:12.5px;font-weight:500;color:var(--fg-2);margin-bottom:6px}

    @media (max-width:640px){
        .up-grid{grid-template-columns:1fr}
    }
</style>
@endpush

@section('content')
<x-lx2-header title="Plans & tarifs" sub="Choisissez l'offre adaptée à votre activité" :back="route('dashboard')" />

{{-- Raison du redirect (limite plan atteinte) --}}
@if(session('upgrade_reason'))
<div class="lx2-flash b-warm" role="alert" style="margin-top:18px">
    <span><b>Limite de votre plan atteinte.</b> {{ session('upgrade_reason') }}</span>
</div>
@endif

{{-- Statut actuel --}}
@if($isSpecialRole)
<div class="up-role-banner">
    <span class="up-role-ico"><x-lx2-icon name="{{ $currentPlan->name === 'ambassadeur' ? 'star' : 'shield' }}" /></span>
    <div>
        <b style="font-size:14.5px">Badge {{ $currentPlan->label }} actif</b>
        <p style="margin:2px 0 0;font-size:12.5px;opacity:.9">Ce statut vous donne accès à toutes les fonctionnalités Premium. Attribué par l'administration — pas lié à un abonnement payant.</p>
    </div>
</div>
@elseif($currentPlan)
<div class="lx2-flash b-soft" role="note" style="margin-top:18px">
    <span>Plan actuel : <b>{{ $currentPlan->label }}</b>
        @if($currentPlan->is_enterprise) · Entreprise
        @elseif($currentPlan->price) · {{ currency_format($currentPlan->price) }}/mois
        @else · Gratuit
        @endif
    </span>
</div>
@endif

{{-- Bascule mensuel / annuel --}}
@if($annualBillingEnabled && $hasAnnualPlans)
<div style="display:flex;justify-content:center;margin-top:26px">
    <div class="up-seg" role="group" aria-label="Période de facturation">
        <button type="button" id="btn-monthly" class="on" onclick="setBilling('monthly')">Mensuel</button>
        <button type="button" id="btn-annual" onclick="setBilling('annual')" style="{{ $annualDiscountPct > 0 ? 'margin-right:4px' : '' }}">
            Annuel
            @if($annualDiscountPct > 0)<span class="save-tag">-{{ $annualDiscountPct }}%</span>@endif
        </button>
    </div>
</div>
@endif

{{-- Grille des plans --}}
<div class="up-grid">
    @foreach($plans as $plan)
    @php
        $t = $themes[$plan->name] ?? $themes['basic'];
        $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
        $isFeatured = $plan->name === 'premium' && !$isCurrent;
        $raw = $plan->features;
        $planFeatures = (is_array($raw) && array_values($raw) === $raw) ? array_filter(array_map('strval', $raw)) : [];
        $monthlyPrice  = (float) $plan->price;
        $annualTotal   = $plan->annual_price ? (float) $plan->annual_price : null;
        $annualMonthly = $annualTotal ? round($annualTotal / 12, 2) : null;
        $savingsPct    = ($annualMonthly && $monthlyPrice > 0) ? round((1 - $annualMonthly / $monthlyPrice) * 100) : null;
    @endphp
    <div class="up-card {{ $isFeatured ? 'featured' : '' }} {{ $isCurrent ? 'current' : '' }}">
        @if($isCurrent)
        <span class="up-ribbon badge b-primary"><x-lx2-icon name="check" />Votre plan</span>
        @elseif($isFeatured)
        <span class="up-ribbon badge b-primary">Recommandé</span>
        @endif
        <div class="up-top" style="background:{{ $t['grad'] ?? $t['color'] }}"></div>

        <div class="up-body">
            <div class="up-name">{{ $plan->label }}</div>
            <div class="up-desc">{{ $plan->description ?? ' ' }}</div>

            @if($plan->is_enterprise)
                <div class="up-price-row"><span class="up-price" style="font-size:26px">Sur devis</span></div>
                <div class="up-subprice">{{ $plan->contact_cta ?? 'Pack multi-licences personnalisé' }}</div>
            @elseif(!$monthlyPrice)
                <div class="up-price-row"><span class="up-price" style="font-size:26px">Gratuit</span></div>
                <div class="up-subprice">Pour toujours, sans engagement</div>
            @else
                <div class="price-monthly-block">
                    <div class="up-price-row"><span class="up-price">{{ currency_format($monthlyPrice) }}</span><span class="up-period">/ mois</span></div>
                    @if($annualTotal)<div class="up-subprice">ou {{ currency_format($annualTotal) }}/an</div>@endif
                </div>
                @if($annualTotal)
                <div class="price-annual-block" style="display:none">
                    <div class="up-price-row">
                        <span class="up-price">{{ currency_format($annualMonthly) }}</span><span class="up-period">/ mois</span>
                        @if($savingsPct > 0)<span class="badge b-ok" style="margin-left:4px;height:20px;font-size:10.5px">-{{ $savingsPct }}%</span>@endif
                    </div>
                    <div class="up-subprice">{{ currency_format($annualTotal) }} facturé annuellement</div>
                </div>
                @endif
            @endif

            <div class="up-sep"></div>

            <div class="up-features">
                @forelse($planFeatures as $featText)
                <div class="up-feat">
                    <span class="up-feat-ico" style="background:{{ $t['soft'] }};color:{{ $t['color'] }}"><x-lx2-icon name="check" /></span>
                    {{ $featText }}
                </div>
                @empty
                <p class="up-subprice" style="font-style:italic">Fonctionnalités à configurer.</p>
                @endforelse
            </div>

            <div class="up-cta-wrap">
                @if($isCurrent)
                    <div class="btn btn-outline btn-block" style="pointer-events:none;opacity:.7"><x-lx2-icon name="check" />Plan actuel</div>
                @elseif($plan->is_enterprise && $pendingEnterpriseQuote)
                    @php
                        $quoteStatusLabels = [
                            'pending'   => 'En attente de traitement',
                            'contacted' => 'Notre équipe vous a contacté',
                            'proposed'  => 'Proposition envoyée — consultez vos emails',
                        ];
                    @endphp
                    <div class="btn btn-block" style="background:var(--warm-soft);color:var(--warm-fg);pointer-events:none"><x-lx2-icon name="clock" />Demande déjà envoyée</div>
                    <p class="up-cta-note">{{ $quoteStatusLabels[$pendingEnterpriseQuote->status] ?? 'Notre équipe va vous recontacter sous 24 h.' }}</p>
                @elseif($plan->is_enterprise)
                    <button type="button" class="btn btn-primary btn-block" onclick="toggleEnterpriseForm()" style="background:{{ $t['grad'] }}">
                        <x-lx2-icon name="phone" />Demander un devis
                    </button>
                @elseif(!$monthlyPrice)
                    <div class="btn btn-outline btn-block" style="pointer-events:none;opacity:.6">Plan gratuit</div>
                @elseif($plan->stripe_price_id)
                    <form method="POST" action="{{ route('checkout', $plan) }}" class="checkout-form" data-plan-id="{{ $plan->id }}" data-has-annual="{{ $plan->stripe_annual_price_id ? '1' : '0' }}">
                        @csrf
                        <input type="hidden" name="billing_period" value="monthly" class="billing-period-input">
                        <button type="submit" class="btn btn-primary btn-block" style="background:{{ $t['grad'] }}">
                            <x-lx2-icon name="arrow-up-right" /><span class="cta-label">Passer au plan {{ $plan->label }}</span>
                        </button>
                    </form>
                    <p class="up-cta-note"><x-lx2-icon name="lock" />Paiement sécurisé via Stripe</p>
                @else
                    <a href="#contact" class="btn btn-primary btn-block" style="background:{{ $t['grad'] }}" onclick="showUpgradeContact('{{ $plan->label }}')">
                        <x-lx2-icon name="mail" />Nous contacter
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Formulaire devis Entreprise (masqué par défaut) --}}
<div id="enterprise-form" class="card" style="max-width:520px;margin:28px auto 0;overflow:hidden;display:none">
    <div style="padding:20px 24px;background:linear-gradient(135deg,var(--purple-soft),#F1EAFE);display:flex;align-items:center;gap:14px">
        <span class="av-fb" style="width:44px;height:44px;border-radius:12px;background:#fff;color:var(--purple)"><x-lx2-icon name="gem" /></span>
        <div>
            <h2 style="margin:0;font-size:15.5px;font-weight:600">Demande de devis Pack Entreprise</h2>
            <p style="margin:2px 0 0;font-size:12.5px;color:var(--fg-2)">Notre équipe vous contactera sous 24 h.</p>
        </div>
    </div>

    @if(session('enterprise_quote_sent'))
    <div style="padding:36px 24px;text-align:center">
        <div class="av-fb" style="width:56px;height:56px;border-radius:999px;background:var(--ok-soft);color:var(--ok-fg);margin:0 auto 14px"><x-lx2-icon name="check" /></div>
        <h3 style="margin:0 0 4px;font-size:15px;font-weight:600">Demande envoyée !</h3>
        <p style="margin:0;font-size:13px;color:var(--muted-fg)">Notre équipe va étudier votre demande et vous recontacter rapidement.</p>
    </div>
    @else
    <form method="POST" action="{{ route('enterprise.request-quote') }}" class="up-qform" style="padding:22px 24px">
        @csrf
        @if($errors->any())
        <div class="lx2-flash b-hot" role="alert" style="margin-bottom:14px"><span>{{ $errors->first() }}</span></div>
        @endif

        <div class="field">
            <label for="eq_company">Nom de l'entreprise *</label>
            <input type="text" id="eq_company" class="input" name="company_name" required maxlength="100" value="{{ old('company_name') }}" placeholder="Acme SAS">
        </div>

        <div class="field">
            <label for="eq_seats">Nombre d'utilisateurs souhaités *</label>
            <div style="display:flex;align-items:center;gap:10px">
                <input type="number" id="eq_seats" class="input" style="width:100px;text-align:center;font-weight:600" name="seats_needed" required min="2" max="500" value="{{ old('seats_needed', 10) }}">
                <span class="up-cta-note" style="font-size:12px">licences (vous inclus — minimum 2)</span>
            </div>
        </div>

        <div class="field">
            <label for="eq_phone">Téléphone</label>
            <input type="tel" id="eq_phone" class="input" name="phone" maxlength="30" value="{{ old('phone', auth()->user()->phone) }}" placeholder="+33 6 00 00 00 00">
        </div>

        <div class="field">
            <label for="eq_message">Message (optionnel)</label>
            <textarea id="eq_message" class="textarea" name="message" maxlength="1000" rows="3" placeholder="Décrivez votre besoin, secteur d'activité, délai souhaité…">{{ old('message') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="background:linear-gradient(135deg,#B394F8,#9E6EF5)">Envoyer ma demande<x-lx2-icon name="arrow-up-right" /></button>
        <p class="up-cta-note" style="margin-top:12px">Votre demande sera traitée par notre équipe sous 24 h ouvrées.</p>
    </form>
    @endif
</div>

{{-- Contact générique --}}
<div id="contact" class="card card-pad" style="max-width:460px;margin:28px auto 0;text-align:center">
    <div class="av-fb" style="width:52px;height:52px;border-radius:14px;margin:0 auto 14px"><x-lx2-icon name="mail" /></div>
    <h2 id="contactTitle" style="margin:0 0 6px;font-size:15px;font-weight:600">Mettre à niveau votre plan</h2>
    <p style="margin:0 0 18px;font-size:13px;color:var(--muted-fg);line-height:1.6">En cas de difficulté pour upgrader votre abonnement, contactez notre équipe LeadXchange.</p>
    <a href="mailto:support@lxchange.org?subject=Demande%20d%27upgrade%20de%20plan" class="btn btn-primary">
        <x-lx2-icon name="mail" />support@lxchange.org
    </a>
</div>

@endsection

@push('scripts')
<script>
var currentBilling = 'monthly';

function setBilling(period) {
    currentBilling = period;
    document.getElementById('btn-monthly').classList.toggle('on', period === 'monthly');
    document.getElementById('btn-annual').classList.toggle('on', period === 'annual');

    document.querySelectorAll('.price-monthly-block').forEach(function (el) {
        el.style.display = period === 'monthly' ? '' : 'none';
    });
    document.querySelectorAll('.price-annual-block').forEach(function (el) {
        el.style.display = period === 'annual' ? '' : 'none';
    });

    document.querySelectorAll('.billing-period-input').forEach(function (input) {
        var form = input.closest('.checkout-form');
        var hasAnnual = form.dataset.hasAnnual === '1';
        input.value = (period === 'annual' && hasAnnual) ? 'annual' : 'monthly';
    });
}

function showUpgradeContact(planName) {
    document.getElementById('contactTitle').textContent = 'Passer au plan ' + planName;
    setTimeout(function () {
        document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
    }, 100);
}

function toggleEnterpriseForm() {
    var el = document.getElementById('enterprise-form');
    var contact = document.getElementById('contact');
    if (el.style.display === 'none') {
        el.style.display = 'block';
        contact.style.display = 'none';
        setTimeout(function () { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 50);
    } else {
        el.style.display = 'none';
        contact.style.display = 'block';
    }
}

@if(session('enterprise_quote_sent'))
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('enterprise-form').style.display = 'block';
    document.getElementById('contact').style.display = 'none';
});
@endif
@if($errors->any() && old('company_name'))
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('enterprise-form').style.display = 'block';
    document.getElementById('contact').style.display = 'none';
});
@endif
</script>
@endpush
