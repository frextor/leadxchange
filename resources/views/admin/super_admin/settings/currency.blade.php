@extends('admin.layouts.admin')
@section('title', 'Paramètres — Devise')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Paramètres de devise</h1>
        <p class="text-sm text-gray-400 mt-1">Configurez l'affichage des prix sur toute la plateforme.</p>
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
    $val = fn(string $k, string $d) => old($k, $settings->get($k)?->value ?? $d);
@endphp

<div class="grid grid-cols-3 gap-6">

    {{-- ── Form ── --}}
    <div class="col-span-2 space-y-5">

        <form method="POST" action="{{ route('admin.super.settings.currency.update') }}" id="currency-form">
            @csrf @method('PUT')

            {{-- Presets --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Devises courantes</p>
                <div class="flex flex-wrap gap-2">
                    @foreach([
                        ['label'=>'Euro',          'symbol'=>'€',   'pos'=>'after',  'dec'=>0, 'thou'=>' ', 'dsep'=>'.'],
                        ['label'=>'Dollar US',     'symbol'=>'$',   'pos'=>'before', 'dec'=>2, 'thou'=>',', 'dsep'=>'.'],
                        ['label'=>'Dirham (MAD)',  'symbol'=>'MAD', 'pos'=>'after',  'dec'=>2, 'thou'=>' ', 'dsep'=>'.'],
                        ['label'=>'Dirham (DH)',   'symbol'=>'DH',  'pos'=>'after',  'dec'=>2, 'thou'=>' ', 'dsep'=>'.'],
                        ['label'=>'Livre sterling','symbol'=>'£',   'pos'=>'before', 'dec'=>2, 'thou'=>',', 'dsep'=>'.'],
                        ['label'=>'Franc CFA',     'symbol'=>'CFA', 'pos'=>'after',  'dec'=>0, 'thou'=>' ', 'dsep'=>'.'],
                        ['label'=>'CHF',           'symbol'=>'CHF', 'pos'=>'before', 'dec'=>2, 'thou'=> "'", 'dsep'=>'.'],
                        ['label'=>'Dinar (TND)',   'symbol'=>'TND', 'pos'=>'after',  'dec'=>3, 'thou'=>' ', 'dsep'=>'.'],
                    ] as $p)
                    <button type="button"
                            onclick="applyPreset('{{ $p['symbol'] }}','{{ $p['pos'] }}','{{ $p['dec'] }}','{{ addslashes($p['thou']) }}','{{ $p['dsep'] }}')"
                            class="px-3 py-1.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50 transition">
                        {{ $p['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Custom settings --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-5">Personnaliser</p>

                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Symbole / code devise</label>
                        <input type="text" name="currency_symbol" id="f-symbol"
                               value="{{ $val('currency_symbol', '€') }}"
                               maxlength="10"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                               placeholder="€" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Position</label>
                        <select name="currency_position" id="f-position"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                            <option value="after"  {{ $val('currency_position','after')  === 'after'  ? 'selected' : '' }}>Après le montant (100 €)</option>
                            <option value="before" {{ $val('currency_position','after')  === 'before' ? 'selected' : '' }}>Avant le montant (€ 100)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Décimales</label>
                        <select name="currency_decimals" id="f-decimals"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                            <option value="0" {{ (int)$val('currency_decimals','0') === 0 ? 'selected' : '' }}>0 — Sans décimales (100)</option>
                            <option value="2" {{ (int)$val('currency_decimals','0') === 2 ? 'selected' : '' }}>2 — Deux décimales (100.00)</option>
                            <option value="3" {{ (int)$val('currency_decimals','0') === 3 ? 'selected' : '' }}>3 — Trois décimales (100.000)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Séparateur de milliers</label>
                        <select name="currency_thousands_sep" id="f-thou"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                            @foreach([' '=>'Espace (1 000)', ','=>'Virgule (1,000)', '.'=>'Point (1.000)', ''=>'Aucun (1000)'] as $sep => $lbl)
                            <option value="{{ $sep }}" {{ $val('currency_thousands_sep',' ') === $sep ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Séparateur décimal</label>
                        <select name="currency_decimal_sep" id="f-dsep"
                                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                            <option value="." {{ $val('currency_decimal_sep','.') === '.' ? 'selected' : '' }}>Point (100.00)</option>
                            <option value="," {{ $val('currency_decimal_sep','.') === ',' ? 'selected' : '' }}>Virgule (100,00)</option>
                        </select>
                    </div>

                </div>
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

        </form>
    </div>

    {{-- ── Live preview ── --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Prévisualisation</p>

            <div class="space-y-3">
                @foreach([0, 1250, 9990, 149.99] as $amount)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-xs text-gray-400">{{ $amount }}</span>
                    <span id="preview-{{ $loop->index }}" class="text-sm font-bold text-gray-900">
                        {{ currency_format($amount) }}
                    </span>
                </div>
                @endforeach
            </div>

            <p class="text-[10px] text-gray-400 mt-3">La prévisualisation se met à jour en temps réel.</p>
        </div>

        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 text-xs text-indigo-700 space-y-2">
            <p class="font-bold">Utilisation dans les vues</p>
            <p>Utilisez le helper global :</p>
            <code class="block bg-white rounded-lg px-3 py-2 font-mono text-indigo-600">currency_format($price)</code>
            <p>Ou avec décimales spécifiques :</p>
            <code class="block bg-white rounded-lg px-3 py-2 font-mono text-indigo-600">currency_format($price, 2)</code>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const previewAmounts = [0, 1250, 9990, 149.99];

function getFields() {
    return {
        symbol:   document.getElementById('f-symbol').value || '€',
        position: document.getElementById('f-position').value,
        decimals: parseInt(document.getElementById('f-decimals').value),
        thou:     document.getElementById('f-thou').value,
        dsep:     document.getElementById('f-dsep').value,
    };
}

function formatAmount(amount, f) {
    // Format number
    const parts = amount.toFixed(f.decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, f.thou);
    const formatted = f.decimals > 0 ? parts.join(f.dsep) : parts[0];

    return f.position === 'before'
        ? f.symbol + ' ' + formatted
        : formatted + ' ' + f.symbol;
}

function updatePreviews() {
    const f = getFields();
    previewAmounts.forEach((amount, i) => {
        const el = document.getElementById('preview-' + i);
        if (el) el.textContent = formatAmount(amount, f);
    });
}

// Listen to all field changes
['f-symbol','f-position','f-decimals','f-thou','f-dsep'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', updatePreviews);
});

function applyPreset(symbol, position, decimals, thou, dsep) {
    document.getElementById('f-symbol').value   = symbol;
    document.getElementById('f-position').value = position;
    document.getElementById('f-decimals').value = decimals;
    document.getElementById('f-thou').value     = thou;
    document.getElementById('f-dsep').value     = dsep;
    updatePreviews();
}

// Initial render
updatePreviews();
</script>
@endpush
