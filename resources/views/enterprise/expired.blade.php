@extends('layouts.app')
@section('title', 'Licence expirée — ' . $license->company_name)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-14">

    {{-- ─── En-tête avec badge plan ──────────────────────────────────────── --}}
    <div class="flex items-center gap-3 mb-6">
        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-full bg-red-100 text-red-700">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Licence expirée
        </span>
        @if($license->plan)
        <span class="text-xs text-gray-400">Pack {{ ucfirst($license->plan->name) }}</span>
        @endif
    </div>

    {{-- ─── Carte principale ───────────────────────────────────────────────── --}}
    <div class="bg-white border border-red-200 rounded-2xl shadow-sm overflow-hidden mb-8">

        {{-- Bandeau rouge --}}
        <div class="px-8 py-6" style="background:linear-gradient(135deg,#FEF2F2 0%,#FEE2E2 100%); border-bottom:1px solid #FECACA;">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $license->company_name }}</h1>
                    <p class="text-sm text-red-700 font-medium">
                        Votre licence a expiré le
                        <strong>{{ \Carbon\Carbon::parse($license->expires_at)->translatedFormat('d F Y') }}</strong>
                        — soit il y a {{ now()->diffInDays($license->expires_at) }} jour{{ now()->diffInDays($license->expires_at) > 1 ? 's' : '' }}.
                    </p>
                </div>
                {{-- Icône --}}
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center" style="background:#FEE2E2;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="1.8">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Récapitulatif licence --}}
        <div class="px-8 py-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Récapitulatif</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <dt class="text-xs text-gray-500 mb-1">Sièges achetés</dt>
                    <dd class="text-3xl font-extrabold text-gray-900">{{ $license->seats_total }}</dd>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 text-center">
                    <dt class="text-xs text-gray-500 mb-1">Utilisateurs actifs</dt>
                    <dd class="text-3xl font-extrabold text-gray-900">{{ $license->seats_used }}</dd>
                </div>
                <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
                    <dt class="text-xs text-red-600 mb-1">Statut</dt>
                    <dd class="text-sm font-bold text-red-700 mt-1">Accès suspendu</dd>
                    <p class="text-xs text-red-400 mt-0.5">depuis le {{ \Carbon\Carbon::parse($license->expires_at)->format('d/m/Y') }}</p>
                </div>
            </dl>

            {{-- Ce qui est désactivé --}}
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4">
                <p class="text-sm font-semibold text-amber-800 mb-2">Fonctionnalités désactivées :</p>
                <ul class="text-sm text-amber-700 space-y-1 list-none">
                    <li class="flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Accès des membres de l'équipe ({{ $license->seats_used }} compte{{ $license->seats_used > 1 ? 's' : '' }} impacté{{ $license->seats_used > 1 ? 's' : '' }})
                    </li>
                    <li class="flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Gestion des licences (invitation / révocation)
                    </li>
                    <li class="flex items-center gap-2">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Fonctionnalités Premium liées au pack
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- ─── Formulaire de renouvellement ──────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-8 pt-7 pb-2">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Renouveler votre licence</h2>
            <p class="text-sm text-gray-500">
                Remplissez ce formulaire et notre équipe vous recontacte sous 24 h avec un devis personnalisé.
            </p>
        </div>

        @if(session('enterprise_quote_sent'))
        <div class="mx-8 my-4 flex items-start gap-3 rounded-xl bg-green-50 border border-green-200 px-5 py-4">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2" class="mt-0.5 flex-shrink-0">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-green-800">Demande envoyée !</p>
                <p class="text-sm text-green-700 mt-0.5">Notre équipe reviendra vers vous dans les plus brefs délais.</p>
            </div>
        </div>
        @else
        <form method="POST" action="{{ route('enterprise.request-quote') }}" class="px-8 py-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Nom de la société --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nom de la société</label>
                    <input type="text" name="company_name" required maxlength="100"
                           value="{{ old('company_name', $license->company_name) }}"
                           class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Acme Corp">
                    @error('company_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Nombre de sièges --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre de licences souhaitées</label>
                    <input type="number" name="seats_needed" required min="2" max="500"
                           value="{{ old('seats_needed', $license->seats_total) }}"
                           class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('seats_needed')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Téléphone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Téléphone <span class="text-gray-400 font-normal">(optionnel)</span></label>
                    <input type="text" name="phone" maxlength="30"
                           value="{{ old('phone') }}"
                           class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="+212 6 XX XX XX XX">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Message --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Message <span class="text-gray-400 font-normal">(optionnel)</span></label>
                    <textarea name="message" rows="3" maxlength="1000"
                              class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                              placeholder="Durée souhaitée, besoins spécifiques…">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Récapitulatif contact --}}
            <div class="flex items-center gap-3 rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2" class="flex-shrink-0">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <p class="text-xs text-indigo-700">
                    Demande soumise en tant que <strong>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong>
                    ({{ auth()->user()->email }})
                </p>
            </div>

            <div class="flex items-center justify-between gap-4 pt-2 flex-wrap">
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
                    ← Retour au tableau de bord
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                        style="background:#6366F1;" onmouseover="this.style.background='#4F46E5'" onmouseout="this.style.background='#6366F1'">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Envoyer la demande de renouvellement
                </button>
            </div>
        </form>
        @endif
    </div>

    {{-- Contact direct --}}
    <p class="text-center text-sm text-gray-400 mt-6">
        Besoin d'une réponse urgente ?
        <a href="mailto:contact@leadxchange.com" class="text-indigo-600 hover:underline font-medium">contact@leadxchange.com</a>
    </p>

</div>
@endsection
