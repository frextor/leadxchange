@extends('admin.layouts.admin')
@section('title', 'Achat de points & Facturation')
@section('page-title', 'Achat de points & Facturation')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Achat de points & Facturation</h1>
        <p class="text-sm text-gray-400 mt-1">Prix des points, facturation mensuelle/annuelle.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0">
        <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div class="space-y-6">

    {{-- ── Section 1 : Facturation annuelle ── --}}
    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2">
            <form method="POST" action="{{ route('admin.super.settings.billing.annual.update') }}">
                @csrf @method('PUT')

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-0.5">Facturation annuelle</p>
                        <p class="text-xs text-gray-400">Contrôlez l'affichage du toggle mensuel/annuel sur la page d'offres.</p>
                    </div>

                    {{-- Toggle activé/désactivé --}}
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Proposer la facturation annuelle</p>
                            <p class="text-xs text-gray-400 mt-0.5">Affiche le toggle Mensuel / Annuel sur la page d'abonnement.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="billing_annual_enabled" value="1" class="sr-only peer"
                                   {{ $annualEnabled ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer
                                        peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5
                                        after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5
                                        after:transition-all peer-checked:bg-indigo-500"></div>
                        </label>
                    </div>

                    {{-- Remise annuelle --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Remise affichée pour la facturation annuelle
                        </label>
                        <p class="text-xs text-gray-400 mb-3">
                            Pourcentage indicatif affiché aux utilisateurs (ex : "Économisez 20%"). Ne modifie pas automatiquement les prix des plans — renseignez le prix annuel dans chaque plan.
                        </p>
                        <div class="flex items-center gap-3 max-w-xs">
                            <div class="relative flex-1">
                                <input type="number" name="billing_annual_discount_pct"
                                       value="{{ old('billing_annual_discount_pct', $annualDiscount) }}"
                                       min="0" max="80" step="1" placeholder="0"
                                       class="w-full h-10 pl-3 pr-10 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">%</span>
                            </div>
                            <div class="flex gap-2">
                                @foreach([0, 10, 15, 20, 25, 30] as $pct)
                                <button type="button"
                                        onclick="document.querySelector('[name=billing_annual_discount_pct]').value='{{ $pct }}'"
                                        class="px-2.5 py-1.5 rounded-lg border text-xs font-semibold transition
                                               {{ $annualDiscount == $pct ? 'border-indigo-400 bg-indigo-50 text-indigo-600' : 'border-gray-200 text-gray-500 hover:border-indigo-300 hover:text-indigo-600' }}">
                                    {{ $pct == 0 ? 'Aucune' : $pct.'%' }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @error('billing_annual_discount_pct')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Prévisualisation du badge --}}
                    <div class="bg-gray-50 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-500 mb-2">Aperçu du badge sur la page d'offres</p>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1.5 rounded-full bg-white border border-gray-200 text-xs font-semibold text-gray-600">Mensuel</span>
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold text-white" style="background:#111827;">
                                Annuel
                                @if($annualDiscount > 0)
                                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[9px] font-bold" style="background:#10B981;color:white;">-{{ $annualDiscount }}%</span>
                                @endif
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">Le badge "Annuel" est affiché si la facturation annuelle est activée.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                                style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 text-xs text-indigo-700 space-y-2">
                <p class="font-bold">Comment ça fonctionne ?</p>
                <ul class="space-y-1.5 list-disc list-inside">
                    <li>Activez ici le toggle mensuel/annuel globalement.</li>
                    <li>Pour chaque plan, renseignez le <strong>Prix annuel</strong> dans la fiche plan → il sera utilisé par Stripe.</li>
                    <li>La remise affichée est indicative — le vrai prix est celui du plan.</li>
                </ul>
                <a href="{{ route('admin.super.plans.index') }}"
                   class="inline-flex items-center gap-1 mt-2 font-semibold text-indigo-600 hover:underline">
                    Gérer les plans →
                </a>
            </div>
        </div>
    </div>

    <hr class="border-gray-100">

    {{-- ── Section 2 : Achat de points ── --}}
    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2">
            <form method="POST" action="{{ route('admin.super.settings.points.update') }}">
                @csrf @method('PUT')

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-0.5">Achat de points</p>
                        <p class="text-xs text-gray-400">Montant facturé à l'utilisateur pour racheter 1 point négatif via Stripe.</p>
                    </div>

                    <div class="max-w-xs">
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Prix par point (€)</label>
                        <div class="relative">
                            <input type="number" name="points_price_per_unit"
                                   value="{{ old('points_price_per_unit', $pricePerUnit) }}"
                                   min="0.01" step="0.01" required
                                   class="w-full h-10 pl-3 pr-10 rounded-xl border border-gray-200 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">€</span>
                        </div>
                        @error('points_price_per_unit')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-xs text-amber-700">
                        <p class="font-semibold mb-1">Exemple</p>
                        <p>Solde <strong>-3 pts</strong> × <strong>{{ number_format($pricePerUnit, 2) }} €</strong> = <strong>{{ number_format(3 * $pricePerUnit, 2) }} €</strong> facturés.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                                style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="space-y-4">
            <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5 text-xs text-amber-700 space-y-2">
                <p class="font-bold">Déclenchement</p>
                <ul class="space-y-1.5 list-disc list-inside">
                    <li>Solde négatif depuis plus de 2 mois → popup sur le dashboard.</li>
                    <li>L'utilisateur achète exactement le nombre de points pour revenir à 0.</li>
                    <li>Paiement one-time via Stripe (pas un abonnement).</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection
