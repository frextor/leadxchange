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
    $priceCents        = (int) ($settings->get('payments.point_price_cents')?->value ?? 100);
    $testMode          = ($settings->get('payments.subscription_test_mode')?->value ?? '0') === '1';
    $testMonthMinutes  = (int) ($settings->get('payments.subscription_test_monthly_minutes')?->value ?? 5);
    $testAnnualMinutes = (int) ($settings->get('payments.subscription_test_annual_minutes')?->value ?? 10);
@endphp

<div class="grid grid-cols-3 gap-6">

    <div class="col-span-2">
        <form id="payments-form" method="POST" action="{{ route('admin.super.settings.payments.update') }}">
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

            {{-- Subscription test mode --}}
            <div class="border-t border-gray-100 pt-5 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Mode test abonnements</p>
                        <p class="text-xs text-gray-400 mt-0.5">Remplace la durée réelle par de courtes fenêtres pour tester le cycle de vie des abonnements.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="subscription_test_mode" value="1" class="sr-only peer" {{ $testMode ? 'checked' : '' }} id="test-mode-toggle">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ml-2 text-sm font-semibold {{ $testMode ? 'text-amber-600' : 'text-gray-400' }}" id="test-mode-label">{{ $testMode ? 'Actif' : 'Inactif' }}</span>
                    </label>
                </div>

                <div id="test-mode-fields" class="{{ $testMode ? '' : 'opacity-40 pointer-events-none' }} grid grid-cols-2 gap-4 transition-opacity duration-200">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Durée mensuelle <span class="text-gray-400 font-normal">(minutes)</span></label>
                        <input type="number" name="subscription_test_monthly_minutes"
                               value="{{ old('subscription_test_monthly_minutes', $testMonthMinutes) }}"
                               min="1" max="10080"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-200 focus:border-amber-400 transition">
                        <p class="mt-1 text-xs text-gray-400">Ex : 5 = l'abonnement mensuel dure 5 min</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Durée annuelle <span class="text-gray-400 font-normal">(minutes)</span></label>
                        <input type="number" name="subscription_test_annual_minutes"
                               value="{{ old('subscription_test_annual_minutes', $testAnnualMinutes) }}"
                               min="1" max="10080"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-200 focus:border-amber-400 transition">
                        <p class="mt-1 text-xs text-gray-400">Ex : 10 = l'abonnement annuel dure 10 min</p>
                    </div>
                </div>

                @if($testMode)
                <div class="flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-700">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Mode test actif — les abonnements expirent en {{ $testMonthMinutes }} min (mensuel) / {{ $testAnnualMinutes }} min (annuel). Désactivez après les tests.
                </div>
                @endif
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

        {{-- Virement bancaire --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#ECFDF5;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-800">Paiement par virement</p>
                    <p class="text-xs text-gray-400">Affiche un bouton virement sur la page de proposition Pack Entreprise</p>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="bank_transfer_toggle" name="bank_transfer_enabled" value="1"
                           form="payments-form"
                           {{ $bankTransferEnabled ? 'checked' : '' }}
                           class="sr-only">
                    <button type="button" onclick="document.getElementById('bank_transfer_toggle').click()"
                            id="bank_transfer_toggle_ui"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $bankTransferEnabled ? 'bg-emerald-500' : 'bg-gray-300' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $bankTransferEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                    <span id="bank_transfer_label" class="ml-2 text-sm font-semibold {{ $bankTransferEnabled ? 'text-emerald-600' : 'text-gray-400' }}">
                        {{ $bankTransferEnabled ? 'Activé' : 'Désactivé' }}
                    </span>
                </div>
            </div>
            <div id="bank_transfer_fields" class="px-5 py-4 {{ $bankTransferEnabled ? '' : 'opacity-40 pointer-events-none' }}">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Coordonnées bancaires affichées au client</label>
                <textarea name="bank_transfer_details" form="payments-form" rows="6"
                          placeholder="Banque : CIH Bank&#10;IBAN : MA64 0000 0000 0000 0000 0000&#10;BIC/SWIFT : CIHMMAMC&#10;Titulaire : LeadXchange SAS&#10;Référence : indiquer votre nom + « Pack Entreprise »"
                          class="w-full rounded-xl border border-gray-200 px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-emerald-400 resize-none">{{ $bankTransferDetails }}</textarea>
                <p class="text-xs text-gray-400 mt-1.5">Ce texte sera affiché au client quand il choisit "Payer par virement". Après confirmation du virement, l'admin active le pack manuellement.</p>
            </div>
        </div>

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

// Test mode toggle
const toggle = document.getElementById('test-mode-toggle');
const label  = document.getElementById('test-mode-label');
const fields = document.getElementById('test-mode-fields');

toggle.addEventListener('change', function () {
    const active = this.checked;
    fields.classList.toggle('opacity-40', !active);
    fields.classList.toggle('pointer-events-none', !active);
    label.textContent = active ? 'Actif' : 'Inactif';
    label.className = active
        ? 'ml-2 text-sm font-semibold text-amber-600'
        : 'ml-2 text-sm font-semibold text-gray-400';
});

// Bank transfer toggle
const btToggle = document.getElementById('bank_transfer_toggle');
const btToggleUi = document.getElementById('bank_transfer_toggle_ui');
const btFields  = document.getElementById('bank_transfer_fields');
const btLabel   = document.getElementById('bank_transfer_label');
if (btToggle) {
    btToggle.addEventListener('change', function () {
        const on = this.checked;
        btFields.classList.toggle('opacity-40', !on);
        btFields.classList.toggle('pointer-events-none', !on);
        btLabel.textContent = on ? 'Activé' : 'Désactivé';
        btLabel.className = on
            ? 'ml-2 text-sm font-semibold text-emerald-600'
            : 'ml-2 text-sm font-semibold text-gray-400';
        btToggleUi.classList.toggle('bg-emerald-500', on);
        btToggleUi.classList.toggle('bg-gray-300', !on);
        btToggleUi.querySelector('span').classList.toggle('translate-x-6', on);
        btToggleUi.querySelector('span').classList.toggle('translate-x-1', !on);
    });
}
</script>
@endpush
