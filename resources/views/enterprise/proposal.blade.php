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

            {{-- État : virement envoyé, en attente de validation admin --}}
            @if($quote->status === 'contacted' && !$quote->proposal_accepted_at)
            <div class="rounded-2xl border-2 border-amber-200 bg-amber-50 px-6 py-6 text-center">
                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-3">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#B45309" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <h3 class="text-base font-bold text-amber-800 mb-1">Virement envoyé — en attente de validation</h3>
                <p class="text-sm text-amber-700">Notre équipe vérifie la réception de votre virement et activera votre Pack Entreprise sous peu.</p>
                @if(!empty($bankTransferDetails))
                <div class="bg-white rounded-xl border border-amber-200 px-5 py-4 mt-4 text-left">
                    <p class="text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Rappel des coordonnées bancaires</p>
                    <pre class="text-sm text-gray-700 whitespace-pre-wrap font-mono leading-relaxed">{{ $bankTransferDetails }}</pre>
                </div>
                @endif
            </div>

            @else
            {{-- Choisissez votre mode de paiement --}}
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Choisissez votre mode de paiement</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                {{-- Option 1 : Carte bancaire (Stripe) --}}
                <div class="rounded-2xl border-2 border-indigo-200 bg-indigo-50 overflow-hidden flex flex-col">
                    <div class="px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#EEF2FF;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-indigo-900">Carte bancaire</p>
                            <p class="text-xs text-indigo-500">Paiement immédiat via Stripe</p>
                        </div>
                    </div>
                    <div class="px-5 pb-5 mt-auto">
                        @if($quote->stripe_payment_link)
                        <a href="{{ $quote->stripe_payment_link }}"
                           class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                           style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Payer {{ number_format((float)$quote->proposed_price, 2, ',', ' ') }} €
                        </a>
                        <p class="text-xs text-indigo-400 text-center mt-2">Activation immédiate après paiement</p>
                        @else
                        <p class="text-xs text-indigo-400 text-center py-2">Lien de paiement non disponible.<br>Contactez-nous : <a href="mailto:support@lxchange.org" class="underline">support@lxchange.org</a></p>
                        @endif
                    </div>
                </div>

                {{-- Option 2 : Virement bancaire --}}
                <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50 overflow-hidden flex flex-col">
                    <div class="px-5 py-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#ECFDF5;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M12 14v-4M10 12h4"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-emerald-900">Virement bancaire</p>
                            <p class="text-xs text-emerald-600">Activation après vérification</p>
                        </div>
                    </div>
                    <div class="px-5 pb-5 mt-auto">
                        <button type="button" onclick="document.getElementById('wire-details').classList.toggle('hidden'); this.classList.add('hidden'); document.getElementById('wire-cancel').classList.remove('hidden')"
                                class="flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl text-sm font-bold text-emerald-700 border-2 border-emerald-300 bg-white hover:bg-emerald-100 transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                            Payer par virement
                        </button>
                        <button type="button" id="wire-cancel" onclick="document.getElementById('wire-details').classList.add('hidden'); this.classList.add('hidden'); document.querySelector('[onclick*=wire-details]').classList.remove('hidden')"
                                class="hidden flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 transition mt-2">
                            Annuler
                        </button>
                    </div>
                </div>
            </div>

            {{-- Détails virement (accordéon) --}}
            <div id="wire-details" class="hidden mt-4 rounded-2xl border border-emerald-200 bg-white overflow-hidden">
                <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-100">
                    <p class="text-sm font-bold text-emerald-800">Coordonnées bancaires</p>
                    <p class="text-xs text-emerald-600 mt-0.5">Effectuez le virement puis confirmez ci-dessous. Notre équipe activera votre licence après réception.</p>
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
                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-emerald-500 focus:ring-emerald-400" required>
                            <span class="text-sm text-gray-600">J'ai effectué le virement du montant de <strong>{{ number_format((float)$quote->proposed_price, 2, ',', ' ') }} €</strong> et je confirme ma demande d'activation du Pack Entreprise.</span>
                        </label>
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
                            Confirmer mon virement
                        </button>
                    </form>
                </div>
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
