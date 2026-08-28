@extends('admin.layouts.admin')
@section('title', 'Générer une proposition — ' . $quote->company_name)

@section('content')
<div class="p-6 max-w-3xl mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.super.enterprise.quotes') }}"
           class="text-gray-400 hover:text-gray-600 transition">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Proposition Pack Entreprise</h1>
            <p class="text-sm text-gray-400 mt-0.5">Demande de {{ $quote->user->first_name }} {{ $quote->user->last_name }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-5 py-3 text-sm flex items-center gap-2">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Infos client (lecture seule) ──────────────────────────────── --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Client</h2>

                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                         style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        {{ strtoupper(substr($quote->user->first_name,0,1).substr($quote->user->last_name,0,1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $quote->user->first_name }} {{ $quote->user->last_name }}</p>
                        <p class="text-xs text-gray-400">{{ $quote->user->email }}</p>
                    </div>
                </div>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Société</dt>
                        <dd class="font-semibold text-gray-900">{{ $quote->company_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Licences demandées</dt>
                        <dd class="font-semibold text-gray-900">{{ $quote->seats_needed }}</dd>
                    </div>
                    @if($quote->phone)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Téléphone</dt>
                        <dd class="font-semibold text-gray-900">{{ $quote->phone }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Demande le</dt>
                        <dd class="text-gray-600">{{ $quote->created_at->format('d/m/Y') }}</dd>
                    </div>
                </dl>

                @if($quote->message)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 mb-1">Message du client</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $quote->message }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Formulaire de proposition ───────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <form method="POST" action="{{ route('admin.super.enterprise.quotes.proposal.send', $quote) }}"
                  class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">
                @csrf

                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Votre proposition</h2>

                <div class="grid grid-cols-2 gap-5">
                    {{-- Nombre de licences --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Licences proposées</label>
                        <input type="number" name="proposed_seats" required min="1" max="500"
                               value="{{ old('proposed_seats', $quote->proposed_seats ?? $quote->seats_needed) }}"
                               class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('proposed_seats')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Durée --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Durée (mois)</label>
                        <input type="number" name="proposed_duration_months" required min="1" max="60"
                               value="{{ old('proposed_duration_months', $quote->proposed_duration_months ?? 12) }}"
                               class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('proposed_duration_months')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Plan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Plan inclus</label>
                    <div class="grid grid-cols-2 sm:grid-cols-{{ $plans->count() }} gap-3">
                        @foreach($plans as $plan)
                        <label class="relative flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 cursor-pointer transition
                                      {{ (old('plan_id', $quote->plan_id ?? 5) == $plan->id) ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }}"
                               style="min-width:0;">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                   {{ (old('plan_id', $quote->plan_id ?? 5) == $plan->id) ? 'checked' : '' }}
                                   class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l=>l.classList.remove('border-indigo-500','bg-indigo-50'));this.closest('label').classList.add('border-indigo-500','bg-indigo-50')">
                            <span class="text-xs font-bold text-gray-700">{{ $plan->label }}</span>
                            @if($plan->price > 0)
                            <span class="text-[11px] text-gray-400">{{ number_format($plan->price, 0, ',', ' ') }} €/mois</span>
                            @else
                            <span class="text-[11px] text-gray-400">Gratuit</span>
                            @endif
                        </label>
                        @endforeach
                    </div>
                    @error('plan_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Prix total proposé --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Prix total (€ TTC)
                        <span class="text-gray-400 font-normal ml-1">— ce montant sera débité via Stripe</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="proposed_price" required min="0" step="0.01"
                               value="{{ old('proposed_price', $quote->proposed_price ?? '') }}"
                               placeholder="ex : 1500.00"
                               class="w-full rounded-xl border border-gray-300 pl-4 pr-12 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-sm">€</span>
                    </div>
                    @error('proposed_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Message d'accompagnement --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Message d'accompagnement
                        <span class="text-gray-400 font-normal">(optionnel)</span>
                    </label>
                    <textarea name="proposal_message" rows="4" maxlength="2000"
                              class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                              placeholder="Détails sur les fonctionnalités incluses, conditions particulières…">{{ old('proposal_message', $quote->proposal_message) }}</textarea>
                    @error('proposal_message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Info Stripe --}}
                <div class="flex items-start gap-3 rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2" class="mt-0.5 flex-shrink-0">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p class="text-xs text-indigo-700">
                        Un <strong>lien de paiement Stripe</strong> sera généré automatiquement avec le montant saisi.
                        Le client n'aura qu'à cliquer et payer — la licence s'active immédiatement après.
                    </p>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('admin.super.enterprise.quotes') }}"
                       class="text-sm text-gray-500 hover:text-gray-700 transition">Annuler</a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        Envoyer la proposition
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
