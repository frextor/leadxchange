@extends('admin.layouts.admin')
@section('title', 'Paramètres — Paiements')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Paramètres de paiement</h1>
        <p class="text-sm text-gray-400 mt-1">Configurez le prix d'achat des points de solde.</p>
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

@php
    $priceCents = (int) ($settings->get('payments.point_price_cents')?->value ?? 100);
@endphp

<div class="grid grid-cols-3 gap-6">

    <div class="col-span-2">
        <form method="POST" action="{{ route('admin.super.settings.payments.update') }}">
            @csrf @method('PUT')

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Prix du point de solde</p>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                        Prix par point <span class="text-gray-400 font-normal">(en centimes)</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="number"
                               name="point_price_cents"
                               id="point_price_cents"
                               value="{{ old('point_price_cents', $priceCents) }}"
                               min="1"
                               max="100000"
                               class="w-40 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                               required>
                        <span class="text-sm text-gray-500">centimes</span>
                        <span class="text-sm text-gray-400">=</span>
                        <span id="price-euros" class="text-sm font-semibold text-gray-700">{{ number_format($priceCents / 100, 2) }} €</span>
                        <span class="text-sm text-gray-400">par point</span>
                    </div>
                    @error('point_price_cents')
                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-400">Valeur actuelle : <strong>{{ $priceCents }} centimes</strong> = {{ number_format($priceCents / 100, 2) }} € par point.</p>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 text-xs text-indigo-700 space-y-3">
            <p class="font-bold text-sm">Comment ça fonctionne</p>
            <p>L'utilisateur choisit un nombre de points à acheter. Le montant total est calculé automatiquement :</p>
            <div class="bg-white rounded-xl px-4 py-3 font-mono text-indigo-600 space-y-1">
                <p>10 pts × <span id="hint-price">{{ number_format($priceCents / 100, 2) }}</span> € = <span id="hint-total">{{ number_format($priceCents * 10 / 100, 2) }}</span> €</p>
            </div>
            <p class="text-indigo-500">Le paiement est traité via Stripe. Les points sont crédités automatiquement après confirmation.</p>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const input = document.getElementById('point_price_cents');
const euros = document.getElementById('price-euros');
const hintPrice = document.getElementById('hint-price');
const hintTotal = document.getElementById('hint-total');

function update() {
    const cents = parseInt(input.value) || 0;
    const e = (cents / 100).toFixed(2);
    euros.textContent = e + ' €';
    hintPrice.textContent = e;
    hintTotal.textContent = (cents * 10 / 100).toFixed(2);
}

input.addEventListener('input', update);
</script>
@endpush
