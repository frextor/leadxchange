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
    </div>

    {{-- Current plan banner --}}
    @if($currentPlan)
    <div class="mb-7 flex items-center justify-center gap-2 text-sm">
        <span class="text-gray-500">Plan actuel :</span>
        <span class="font-bold text-gray-900">{{ $currentPlan->label }}</span>
        @if($currentPlan->is_enterprise)
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">Entreprise</span>
        @elseif(!$currentPlan->price)
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Gratuit</span>
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
                    <span class="text-3xl font-extrabold text-gray-900">Gratuit</span>
                    <span class="text-xs text-gray-400">pour toujours</span>
                </div>
                @else
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-extrabold text-gray-900">{{ currency_format($plan->price) }}</span>
                    <span class="text-xs text-gray-400">/ mois</span>
                </div>
                @if($plan->annual_price)
                <p class="text-xs text-gray-400 mt-1">ou {{ currency_format($plan->annual_price) }}/an</p>
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
                <a href="#contact"
                   class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 active:scale-[.98]"
                   style="background:linear-gradient(135deg,{{ $t['top'] }},{{ $t['accent'] }});"
                   onclick="showUpgradeContact('{{ $plan->label }}')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.39 18a19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 3.18 2 2 0 0 1 4.11 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91A16 16 0 0 0 14 14.91l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Demander un devis
                </a>
                @elseif(!$plan->price)
                <div class="w-full py-2.5 rounded-xl text-xs font-semibold text-center bg-gray-50 text-gray-400 border border-gray-200">
                    Plan gratuit
                </div>
                @elseif($plan->stripe_price_id)
                <form method="POST" action="{{ route('checkout', $plan) }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 active:scale-[.98]"
                            style="background:linear-gradient(135deg,{{ $t['top'] }},{{ $t['accent'] }});">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Passer au plan {{ $plan->label }}
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

    {{-- Contact section --}}
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
<script>
function showUpgradeContact(planName) {
    document.getElementById('contactTitle').textContent = 'Passer au plan ' + planName;
    setTimeout(function() {
        document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
    }, 100);
}
</script>
@endpush

@endsection
