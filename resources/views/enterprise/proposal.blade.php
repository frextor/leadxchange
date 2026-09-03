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

            {{-- État : virement demandé (status = contacted) --}}
            @if($quote->status === 'contacted' && !$quote->proposal_accepted_at)
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-6 py-5 text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <h3 class="text-base font-bold text-emerald-800 mb-1">Virement demandé — merci !</h3>
                <p class="text-sm text-emerald-700 mb-4">Notre équipe activera votre Pack Entreprise dès réception du virement.</p>
                @if(!empty($bankTransferDetails))
                <div class="bg-white rounded-xl border border-emerald-200 px-5 py-4 text-left">
                    <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Coordonnées bancaires</p>
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap font-mono leading-relaxed">{{ $bankTransferDetails }}</pre>
                </div>
                @endif
            </div>

            {{-- Session flash : virement vient d'être soumis --}}
            @elseif(session('wire_transfer_requested'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-6 py-5 text-center">
                <p class="text-base font-bold text-emerald-800 mb-1">✅ Votre demande de virement a été enregistrée.</p>
                <p class="text-sm text-emerald-700">Nous activerons votre licence dès réception du paiement.</p>
            </div>

            {{-- Boutons de paiement normaux --}}
            @else
            <div class="space-y-3">
                {{-- Stripe --}}
                @if($quote->stripe_payment_link)
                <div class="text-center">
                    <a href="{{ $quote->stripe_payment_link }}"
                       class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-2xl text-base font-bold text-white shadow-lg transition hover:opacity-90 hover:shadow-xl w-full justify-center"
                       style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        Accepter et payer — {{ number_format((float)$quote->proposed_price, 2, ',', ' ') }} €
                    </a>
                    <p class="text-xs text-gray-400 mt-2">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline-block mr-1"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Paiement sécurisé via Stripe — vos données bancaires ne transitent pas par nos serveurs.
                    </p>
                </div>
                @endif

                {{-- Virement bancaire --}}
                <div class="relative flex items-center gap-3 my-2">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs text-gray-400 font-medium">ou</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>

                <div id="wire-section">
                    <button type="button" onclick="document.getElementById('wire-details').classList.toggle('hidden')"
                            class="inline-flex items-center gap-2.5 px-8 py-3.5 rounded-2xl text-base font-bold text-emerald-700 border-2 border-emerald-300 bg-emerald-50 hover:bg-emerald-100 transition w-full justify-center">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                        Accepter le pack — paiement par virement
                    </button>

                    {{-- Détails + confirmation --}}
                    <div id="wire-details" class="hidden mt-4 rounded-2xl border border-emerald-200 bg-white overflow-hidden">
                        <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-100">
                            <p class="text-sm font-bold text-emerald-800">Coordonnées bancaires</p>
                            <p class="text-xs text-emerald-600 mt-0.5">Effectuez le virement, puis confirmez ci-dessous. Votre licence sera activée après réception.</p>
                        </div>
                        @if(!empty($bankTransferDetails))
                        <div class="px-6 py-4 border-b border-gray-100">
                            <pre class="text-sm text-gray-700 whitespace-pre-wrap font-mono leading-relaxed">{{ $bankTransferDetails }}</pre>
                        </div>
                        @endif
                        <div class="px-6 py-4">
                            <form method="POST" action="{{ route('enterprise.proposal.accept-wire', $quote->proposal_token) }}">
                                @csrf
                                <label class="flex items-start gap-3 mb-4 cursor-pointer">
                                    <input type="checkbox" id="wire-confirm" class="mt-0.5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-400" required>
                                    <span class="text-sm text-gray-600">J'ai effectué le virement bancaire du montant de <strong>{{ number_format((float)$quote->proposed_price, 2, ',', ' ') }} €</strong> et je confirme ma demande d'activation du Pack Entreprise.</span>
                                </label>
                                <button type="submit"
                                        class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
                                    Confirmer mon virement — Activer le pack
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                @if(!$quote->stripe_payment_link)
                <div class="text-center py-4 text-sm text-gray-500">
                    Pas de paiement en ligne disponible ? Utilisez le virement ci-dessus ou contactez-nous :
                    <a href="mailto:support@lxchange.org" class="text-indigo-600 underline">support@lxchange.org</a>
                </div>
                @endif
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
