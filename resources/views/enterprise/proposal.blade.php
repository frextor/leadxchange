@extends('layouts.app')
@section('title', 'Proposition Pack Entreprise — ' . $quote->company_name)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-14">

    {{-- En-tête --}}
    <div class="flex items-center gap-3 mb-6">
        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-indigo-100 text-indigo-700">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Proposition reçue
        </span>
        <span class="text-xs text-gray-400">{{ $quote->proposal_sent_at->format('d/m/Y') }}</span>
    </div>

    {{-- Carte principale --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">

        {{-- Header dégradé --}}
        <div class="px-8 py-6" style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF); border-bottom:1px solid #C7D2FE;">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $quote->company_name }}</h1>
            <p class="text-sm text-indigo-700">
                LeadXchange vous propose un Pack Entreprise personnalisé.
                Examinez les détails ci-dessous et procédez au paiement pour activer votre licence.
            </p>
        </div>

        {{-- Détails de la proposition --}}
        <div class="px-8 py-6">
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-5">Détails de l'offre</h2>

            <dl class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs text-gray-500 mb-1">Plan inclus</dt>
                    <dd class="text-base font-bold text-gray-900">{{ $quote->plan?->label ?? '—' }}</dd>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs text-gray-500 mb-1">Licences utilisateurs</dt>
                    <dd class="text-base font-bold text-gray-900">{{ $quote->proposed_seats }} sièges</dd>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <dt class="text-xs text-gray-500 mb-1">Durée</dt>
                    <dd class="text-base font-bold text-gray-900">{{ $quote->proposed_duration_months }} mois</dd>
                </div>
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                    <dt class="text-xs text-indigo-600 mb-1">Prix total TTC</dt>
                    <dd class="text-2xl font-extrabold text-indigo-700">
                        {{ number_format((float)$quote->proposed_price, 2, ',', ' ') }} €
                    </dd>
                </div>
            </dl>

            {{-- Fonctionnalités du plan --}}
            @if($quote->plan)
            @php
                $perms = collect($quote->plan->permissions ?? [])
                    ->filter(fn($v) => $v === true || (is_numeric($v) && $v > 0))
                    ->keys()
                    ->map(fn($k) => str_replace(['can_', '_'], ['', ' '], $k))
                    ->map(fn($k) => ucfirst($k))
                    ->take(8);
            @endphp
            @if($perms->isNotEmpty())
            <div class="mb-6">
                <p class="text-xs font-semibold text-gray-500 mb-3">Fonctionnalités incluses</p>
                <ul class="grid grid-cols-2 gap-2">
                    @foreach($perms as $perm)
                    <li class="flex items-center gap-2 text-sm text-gray-700">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        {{ $perm }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @endif

            {{-- Message d'accompagnement --}}
            @if($quote->proposal_message)
            <div class="rounded-xl bg-amber-50 border border-amber-100 px-5 py-4 mb-6">
                <p class="text-xs font-semibold text-amber-700 mb-1.5">Message de l'équipe LeadXchange</p>
                <p class="text-sm text-amber-900 leading-relaxed">{{ $quote->proposal_message }}</p>
            </div>
            @endif

            {{-- Bouton payer --}}
            @if($quote->stripe_payment_link)
            <div class="text-center">
                <a href="{{ $quote->stripe_payment_link }}"
                   class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-2xl text-base font-bold text-white shadow-lg transition hover:opacity-90 hover:shadow-xl"
                   style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                    Accepter et payer — {{ number_format((float)$quote->proposed_price, 2, ',', ' ') }} €
                </a>
                <p class="text-xs text-gray-400 mt-3">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline-block mr-1"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Paiement sécurisé via Stripe — vos données bancaires ne transitent pas par nos serveurs.
                </p>
            </div>
            @else
            <div class="text-center py-4 text-sm text-gray-500">
                Le lien de paiement sera disponible très prochainement. Contactez-nous :
                <a href="mailto:contact@leadxchange.com" class="text-indigo-600 underline">contact@leadxchange.com</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Contact --}}
    <p class="text-center text-sm text-gray-400">
        Des questions sur cette proposition ?
        <a href="mailto:contact@leadxchange.com" class="text-indigo-600 hover:underline font-medium">Contactez-nous →</a>
    </p>

</div>
@endsection
