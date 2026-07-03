@extends('layouts.app')
@section('title', 'Exercer mes droits RGPD — LeadXchange')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Exercer mes droits RGPD</h1>
        <p class="text-sm text-gray-500 mt-1">Conformément au RGPD (Art. 15 à 21), vous pouvez exercer vos droits à tout moment. Réponse sous <strong>1 mois</strong>.</p>
    </div>

    @if(session('rgpd_success'))
    <div class="mb-6 flex items-start gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="m9 11 3 3L22 4"/></svg>
        <div>
            <p class="font-semibold">Demande envoyée</p>
            <p class="mt-0.5">{{ session('rgpd_success') }}</p>
        </div>
    </div>
    @endif

    {{-- Droits disponibles --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        @foreach([
            ['access',        '🔍', 'Droit d\'accès',        'Obtenir une copie de toutes vos données (Art. 15)'],
            ['rectification', '✏️', 'Rectification',         'Corriger des données inexactes (Art. 16)'],
            ['erasure',       '🗑️', 'Droit à l\'effacement', 'Supprimer votre compte et vos données (Art. 17)'],
            ['portability',   '📦', 'Portabilité',           'Recevoir vos données en format CSV (Art. 20)'],
            ['opposition',    '🚫', 'Droit d\'opposition',   'S\'opposer à certains traitements (Art. 21)'],
            ['limitation',    '⏸️', 'Limitation',            'Suspendre temporairement un traitement (Art. 18)'],
        ] as [$key, $emoji, $title, $desc])
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 cursor-pointer hover:border-teal-300 hover:bg-teal-50/30 transition"
             onclick="document.getElementById('right_type').value='{{ $key }}'; document.getElementById('selected-{{ $key }}').classList.add('ring-2','ring-teal-400'); document.querySelectorAll('[id^=selected]').forEach(e=>{ if(e.id!=='selected-{{ $key }}') e.classList.remove('ring-2','ring-teal-400'); });">
            <div id="selected-{{ $key }}" class="flex items-start gap-3 rounded-xl transition p-1">
                <span class="text-xl leading-none flex-shrink-0">{{ $emoji }}</span>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 leading-snug">{{ $desc }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('rgpd.submit') }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
        @csrf
        <input type="hidden" name="right_type" id="right_type" value="access">

        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Droit sélectionné</label>
            <select name="right_type" id="right_type_select" onchange="document.getElementById('right_type').value=this.value"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 bg-white">
                <option value="access">🔍 Droit d'accès (Art. 15)</option>
                <option value="rectification">✏️ Droit de rectification (Art. 16)</option>
                <option value="erasure">🗑️ Droit à l'effacement (Art. 17)</option>
                <option value="portability">📦 Droit à la portabilité (Art. 20)</option>
                <option value="opposition">🚫 Droit d'opposition (Art. 21)</option>
                <option value="limitation">⏸️ Droit à la limitation (Art. 18)</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Détails de votre demande <span class="text-gray-300">(optionnel)</span></label>
            <textarea name="details" rows="4" maxlength="1000"
                      class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition resize-none"
                      placeholder="Précisez votre demande si nécessaire…"></textarea>
        </div>

        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-500 leading-relaxed">
            <strong class="text-gray-700">Vos informations :</strong>
            {{ auth()->user()->first_name }} {{ auth()->user()->last_name }} — {{ auth()->user()->email }}<br>
            Ces informations seront jointes à votre demande pour vérification d'identité.
        </div>

        <button type="submit"
                class="w-full py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                style="background:linear-gradient(135deg,#2DD4B0,#14A98C);">
            Envoyer ma demande RGPD
        </button>
    </form>

    <p class="text-center text-xs text-gray-400 mt-4">
        Vous pouvez aussi écrire directement à
        <a href="mailto:contact@leadxchange.com" class="text-teal-600 underline">contact@leadxchange.com</a>
    </p>
</div>
@endsection
