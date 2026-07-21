@extends('layouts.app')

@section('title', 'Mettre à niveau — LeadXchange')

@section('content')
@php
    $themes = [
        'basic'       => ['top' => '#64748B', 'bg' => '#F8FAFC', 'accent' => '#475569'],
        'premium'     => ['top' => '#6366F1', 'bg' => '#EEF2FF', 'accent' => '#4338CA'],
        'consul'      => ['top' => '#0D9488', 'bg' => '#F0FDFA', 'accent' => '#0F766E'],
        'ambassadeur' => ['top' => '#D97706', 'bg' => '#FFFBEB', 'accent' => '#92400E'],
        'enterprise'  => ['top' => '#1D4ED8', 'bg' => '#EFF6FF', 'accent' => '#1E40AF'],
    ];
@endphp

<div class="max-w-5xl mx-auto px-4 py-10">

    {{-- Raison du redirect (limite plan atteinte) --}}
    @if(session('upgrade_reason'))
    <div class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <div>
            <p class="font-semibold">Limite de votre plan atteinte</p>
            <p class="text-amber-700 mt-0.5">{{ session('upgrade_reason') }}</p>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold mb-4"
             style="background:#EEF2FF;color:#4338CA;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Plans d'abonnement
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 mb-3">Choisissez votre plan</h1>
        <p class="text-sm text-gray-500 max-w-md mx-auto">
            Débloquez toutes les fonctionnalités de LeadXchange et développez votre réseau professionnel.
        </p>

        {{-- Billing period toggle --}}
        @php $hasAnnualPlans = $plans->filter(fn($p) => $p->annual_price > 0)->isNotEmpty(); @endphp
        @if($hasAnnualPlans)
        <div class="mt-6 inline-flex items-center gap-1 p-1 rounded-2xl border border-gray-200 bg-gray-50">
            <button id="btn-monthly" onclick="setBilling('monthly')"
                    class="px-5 py-2 rounded-xl text-sm font-semibold transition billing-btn billing-btn--active">
                Mensuel
            </button>
            <button id="btn-annual" onclick="setBilling('annual')"
                    class="px-5 py-2 rounded-xl text-sm font-semibold transition billing-btn relative">
                Annuel
                <span class="absolute -top-2 -right-2 text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-500 text-white leading-none">-2 mois</span>
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">Économisez jusqu'à 2 mois avec la facturation annuelle</p>
        @endif
    </div>

    {{-- Current plan banner --}}
    @if($currentPlan)
    <div class="mb-7 flex items-center justify-center gap-2 text-sm">
        <span class="text-gray-500">Plan actuel :</span>
        <span class="font-bold text-gray-900">{{ $currentPlan->label }}</span>
        @if($currentPlan->is_enterprise)
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Entreprise</span>
        @elseif(!$currentPlan->price)
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Basic</span>
        @else
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600">{{ currency_format($currentPlan->price) }}/mois</span>
        @endif
    </div>
    @endif

    {{-- Plans grid --}}
    <div class="grid grid-cols-1 md:grid-cols-{{ min($plans->count(), 3) }} gap-5 mb-10">
        @foreach($plans as $plan)
        @php
            $t = $themes[$plan->name] ?? $themes['basic'];
            $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
        @endphp
        <div class="bg-white rounded-2xl border-2 overflow-hidden flex flex-col transition-shadow hover:shadow-lg relative
                    {{ $isCurrent ? 'border-indigo-300 shadow-md' : 'border-gray-100' }}"
             style="{{ $isCurrent ? '' : "border-top:3px solid {$t['top']};" }}">

            @if($isCurrent)
            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500 text-white">
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    Votre plan
                </span>
            </div>
            @endif

            {{-- Plan header --}}
            <div class="px-5 pt-5 pb-4">
                <h3 class="text-base font-bold text-gray-900 mb-0.5">{{ $plan->label }}</h3>
                @if($plan->description)
                <p class="text-xs text-gray-400 leading-relaxed">{{ $plan->description }}</p>
                @endif
            </div>

            {{-- Price --}}
            <div class="px-5 py-4 border-y border-gray-100" style="background:{{ $t['bg'] }};">
                @if($plan->is_enterprise)
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-900">Sur devis</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">{{ $plan->contact_cta ?? 'Pack multi-licences personnalisé' }}</p>
                @elseif(!$plan->price)
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-900">Basic</span>
                    <span class="text-xs text-gray-400">gratuit pour toujours</span>
                </div>
                @else
                @php
                    $monthlyPrice  = (float) $plan->price;
                    $annualTotal   = $plan->annual_price ? (float) $plan->annual_price : null;
                    $annualMonthly = $annualTotal ? round($annualTotal / 12, 2) : null;
                    $savingsPct    = ($annualMonthly && $monthlyPrice > 0)
                        ? round((1 - $annualMonthly / $monthlyPrice) * 100)
                        : null;
                @endphp
                {{-- Monthly price (shown by default) --}}
                <div class="price-monthly-block">
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-extrabold text-gray-900">{{ currency_format($monthlyPrice) }}</span>
                        <span class="text-xs text-gray-400">/ mois</span>
                    </div>
                    @if($annualTotal)
                    <p class="text-xs text-gray-400 mt-1">ou {{ currency_format($annualTotal) }}/an</p>
                    @endif
                </div>
                {{-- Annual price (hidden by default) --}}
                @if($annualTotal)
                <div class="price-annual-block" style="display:none;">
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-extrabold text-gray-900">{{ currency_format($annualMonthly) }}</span>
                        <span class="text-xs text-gray-400">/ mois</span>
                        @if($savingsPct && $savingsPct > 0)
                        <span class="ml-1 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-700">-{{ $savingsPct }}%</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ currency_format($annualTotal) }} facturé annuellement</p>
                </div>
                @endif
                @endif
            </div>

            {{-- Features list --}}
            <div class="px-5 py-4 flex-1">
                @php $planFeatures = is_array($plan->features) ? $plan->features : []; @endphp
                @if(count($planFeatures) > 0)
                <ul class="space-y-2">
                    @foreach($planFeatures as $feat)
                    @php $featText = is_array($feat) ? ($feat['name'] ?? $feat['label'] ?? implode(', ', array_filter((array)$feat, 'is_string'))) : (string)$feat; @endphp
                    <li class="flex items-center gap-2.5 text-xs text-gray-700">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0"
                              style="background:{{ $t['bg'] }};">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="{{ $t['top'] }}" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                        </span>
                        {{ $featText }}
                    </li>
                    @endforeach
                </ul>
                @else
                <p class="text-xs text-gray-400 italic">Fonctionnalités à configurer.</p>
                @endif
            </div>

            {{-- CTA --}}
            <div class="px-5 pb-5 pt-3">
                @if($isCurrent)
                <div class="w-full py-2.5 rounded-xl text-xs font-semibold text-center border-2 border-indigo-200 text-indigo-400 bg-indigo-50">
                    ✓ Plan actuel
                </div>
                @elseif($plan->is_enterprise)
                <button type="button"
                        onclick="toggleEnterpriseForm()"
                        class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 active:scale-[.98]"
                        style="background:linear-gradient(135deg,{{ $t['top'] }},{{ $t['accent'] }});">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.39 18a19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91A16 16 0 0 0 14 14.91l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Demander un devis
                </button>
                @elseif(!$plan->price)
                <div class="w-full py-2.5 rounded-xl text-xs font-semibold text-center bg-gray-50 text-gray-400 border border-gray-200">
                    Plan gratuit
                </div>
                @elseif($plan->stripe_price_id)
                <form method="POST" action="{{ route('checkout', $plan) }}" class="checkout-form" data-plan-id="{{ $plan->id }}" data-has-annual="{{ $plan->stripe_annual_price_id ? '1' : '0' }}">
                    @csrf
                    <input type="hidden" name="billing_period" value="monthly" class="billing-period-input">
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 active:scale-[.98]"
                            style="background:linear-gradient(135deg,{{ $t['top'] }},{{ $t['accent'] }});">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span class="cta-label">Passer au plan {{ $plan->label }}</span>
                    </button>
                </form>
                <p class="text-center text-[10px] text-gray-400 mt-2 flex items-center justify-center gap-1">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Paiement sécurisé via Stripe
                </p>
                @else
                <a href="#contact"
                   class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 active:scale-[.98]"
                   style="background:linear-gradient(135deg,{{ $t['top'] }},{{ $t['accent'] }});"
                   onclick="showUpgradeContact('{{ $plan->label }}')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Nous contacter
                </a>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    {{-- Enterprise quote form (hidden by default, toggled by the CTA button) --}}
    <div id="enterprise-form" class="max-w-lg mx-auto" style="display:none;">
        <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-sm overflow-hidden">
            <div class="px-6 pt-5 pb-4" style="background:linear-gradient(135deg,#EFF6FF,#E0F2FE);">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background:rgba(37,99,235,.12);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1D4ED8" stroke-width="1.8"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Demande de devis Pack Entreprise</h2>
                        <p class="text-xs text-gray-500">Notre équipe vous contactera sous 24 h.</p>
                    </div>
                </div>
            </div>

            @if(session('enterprise_quote_sent'))
            <div class="px-6 py-8 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4 bg-emerald-50">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Demande envoyée !</h3>
                <p class="text-sm text-gray-500">Notre équipe va étudier votre demande et vous recontacter rapidement.</p>
            </div>
            @else
            <form method="POST" action="{{ route('enterprise.request-quote') }}" class="px-6 py-5 space-y-4">
                @csrf

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom de l'entreprise <span class="text-red-400">*</span></label>
                    <input type="text" name="company_name" required maxlength="100"
                           value="{{ old('company_name') }}"
                           placeholder="Acme SAS"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nombre d'utilisateurs souhaités <span class="text-red-400">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="seats_needed" required min="2" max="500"
                               value="{{ old('seats_needed', 10) }}"
                               class="w-28 rounded-xl border border-gray-200 px-3 py-2.5 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <span class="text-xs text-gray-400">licences (vous inclus — minimum 2)</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Téléphone</label>
                    <input type="tel" name="phone" maxlength="30"
                           value="{{ old('phone') }}"
                           placeholder="+33 6 00 00 00 00"
                           class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Message (optionnel)</label>
                    <textarea name="message" maxlength="1000" rows="3"
                              placeholder="Décrivez votre besoin, secteur d'activité, délai souhaité…"
                              class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none">{{ old('message') }}</textarea>
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                        style="background:linear-gradient(135deg,#1D4ED8,#1E40AF);">
                    Envoyer ma demande →
                </button>

                <p class="text-center text-xs text-gray-400">
                    Votre demande sera traitée par notre équipe sous 24 h ouvrées.
                </p>
            </form>
            @endif
        </div>
    </div>

    {{-- Generic contact section (non-enterprise plans) --}}
    <div id="contact" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center max-w-lg mx-auto">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"
             style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h2 class="text-base font-bold text-gray-900 mb-2" id="contactTitle">Mettre à niveau votre plan</h2>
        <p class="text-sm text-gray-500 mb-5 leading-relaxed">
            Pour upgrader votre abonnement, contactez notre équipe ou utilisez l'application mobile LeadXchange.
        </p>
        <a href="mailto:support@leadxchange.ma?subject=Demande%20d%27upgrade%20de%20plan"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
           style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Contacter le support
        </a>
        <p class="mt-3 text-xs text-gray-400">ou utilisez l'application mobile pour gérer votre abonnement</p>
    </div>

</div>

@push('scripts')
<style>
.billing-btn { color:#6B7280; }
.billing-btn--active { background:#fff; color:#1E293B; box-shadow:0 1px 3px rgba(0,0,0,.1); }
</style>
<script>
var currentBilling = 'monthly';

function setBilling(period) {
    currentBilling = period;

    document.getElementById('btn-monthly').classList.toggle('billing-btn--active', period === 'monthly');
    document.getElementById('btn-annual').classList.toggle('billing-btn--active', period === 'annual');

    // Toggle price blocks
    document.querySelectorAll('.price-monthly-block').forEach(function(el) {
        el.style.display = period === 'monthly' ? '' : 'none';
    });
    document.querySelectorAll('.price-annual-block').forEach(function(el) {
        el.style.display = period === 'annual' ? '' : 'none';
    });

    // Update hidden billing_period inputs
    document.querySelectorAll('.billing-period-input').forEach(function(input) {
        var form = input.closest('.checkout-form');
        var hasAnnual = form.dataset.hasAnnual === '1';
        input.value = (period === 'annual' && hasAnnual) ? 'annual' : 'monthly';
    });
}

function showUpgradeContact(planName) {
    document.getElementById('contactTitle').textContent = 'Passer au plan ' + planName;
    setTimeout(function() {
        document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
    }, 100);
}
function toggleEnterpriseForm() {
    var el = document.getElementById('enterprise-form');
    var contact = document.getElementById('contact');
    if (el.style.display === 'none') {
        el.style.display = 'block';
        contact.style.display = 'none';
        setTimeout(function() { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 50);
    } else {
        el.style.display = 'none';
        contact.style.display = 'block';
    }
}
@if(session('enterprise_quote_sent'))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('enterprise-form').style.display = 'block';
    document.getElementById('contact').style.display = 'none';
});
@endif
@if($errors->any() && old('company_name'))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('enterprise-form').style.display = 'block';
    document.getElementById('contact').style.display = 'none';
});
@endif
</script>
@endpush

@endsection
