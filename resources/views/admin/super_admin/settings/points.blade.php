@extends('admin.layouts.admin')
@section('title', 'Achat de points')
@section('page-title', 'Achat de points')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Achat de points</h1>
        <p class="text-sm text-gray-400 mt-1">Configurez le prix unitaire des points que les utilisateurs peuvent racheter.</p>
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

<div class="grid grid-cols-3 gap-6">

    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.super.settings.points.update') }}">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Prix par point</p>
                    <p class="text-xs text-gray-400 mb-5">Montant facturé à l'utilisateur pour racheter 1 point négatif via Stripe.</p>

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
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-xs text-amber-700">
                    <p class="font-semibold mb-1">Exemple de calcul</p>
                    <p>Si un utilisateur a un solde de <strong>-3 points</strong> et que le prix est <strong>{{ number_format($pricePerUnit, 2) }} €</strong>, il paiera <strong>{{ number_format(3 * $pricePerUnit, 2) }} €</strong> pour revenir à 0.</p>
                </div>

                <div class="flex justify-end pt-2">
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
                <li>Quand un utilisateur est en solde négatif depuis plus de 2 mois, un popup s'affiche sur son tableau de bord.</li>
                <li>En cliquant sur "Acheter des points", il est redirigé vers Stripe pour payer exactement le montant nécessaire pour revenir à 0.</li>
                <li>Le paiement est en mode <strong>one-time</strong> (pas un abonnement).</li>
            </ul>
        </div>
    </div>

</div>

@endsection
