@extends('admin.layouts.admin')
@section('title', 'Paramètres — Popup de bienvenue')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Popup de bienvenue</h1>
        <p class="text-sm text-gray-400 mt-1">Configurez les critères de sélection des profils affichés lors de la connexion.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
    {{ session('success') }}
</div>
@endif

@php
    $s = fn(string $k, $d = null) => old($k, $settings->get($k)?->value ?? $d);
@endphp

<div class="grid grid-cols-3 gap-6">

    {{-- ── Form ── --}}
    <div class="col-span-2 space-y-5">
        <form method="POST" action="{{ route('admin.super.settings.welcome-popup.update') }}" id="popup-form">
            @csrf @method('PUT')

            {{-- Activation --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Activation</p>
                <label class="flex items-center justify-between gap-4 cursor-pointer">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Afficher le popup</p>
                        <p class="text-xs text-gray-400 mt-0.5">Lorsque désactivé, aucun popup ne s'affiche pour les utilisateurs.</p>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="hidden" name="welcome_popup_enabled" value="0">
                        <input type="checkbox" name="welcome_popup_enabled" value="1" id="toggle-enabled"
                               class="sr-only peer"
                               {{ $s('welcome_popup_enabled', '1') === '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-indigo-600 rounded-full transition peer-focus:ring-2 peer-focus:ring-indigo-300 cursor-pointer"
                             onclick="document.getElementById('toggle-enabled').click()"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-5 pointer-events-none"
                             id="toggle-knob"></div>
                    </div>
                </label>
            </div>

            {{-- Critère de sélection --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Critère de sélection des profils</p>
                <p class="text-xs text-gray-400 mb-4">Définit comment les membres suggérés sont choisis parmi tous les utilisateurs.</p>

                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['none',          'Aléatoire',              'Les membres les plus récents, sans filtre.',                                           'M2 12h20M12 2v20'],
                        ['same_city',     'Même ville',             'Membres habitant la même ville que l\'utilisateur connecté.',                          'M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z'],
                        ['same_region',   'Même région',            'Membres de la même région que l\'utilisateur (basé sur le champ région du profil).',   'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11l2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3'],
                        ['same_interest', 'Même centre d\'intérêt', 'Membres ayant au moins un intérêt commun.',                                            'M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0Z'],
                        ['both',          'Ville + Intérêt',        'Membres de la même ville ET avec des intérêts communs. Si pas assez, complète par ville seule.', 'M9 12l2 2 4-4'],
                    ] as [$val, $label, $desc, $path])
                    <label class="criteria-card flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                                  {{ $s('welcome_popup_criteria', 'none') === $val ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200 hover:bg-gray-50' }}">
                        <input type="radio" name="welcome_popup_criteria" value="{{ $val }}"
                               class="mt-0.5 accent-indigo-600 flex-shrink-0"
                               {{ $s('welcome_popup_criteria', 'none') === $val ? 'checked' : '' }}
                               onchange="updateCriteriaCards()">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">{{ $desc }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Nombre de profils --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Nombre de profils affichés</p>
                <div class="flex gap-3">
                    @foreach([2, 3, 4] as $n)
                    <label class="count-card flex-1 flex flex-col items-center gap-1 py-4 rounded-xl border-2 cursor-pointer transition
                                  {{ (int)$s('welcome_popup_count', 3) === $n ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200' }}">
                        <input type="radio" name="welcome_popup_count" value="{{ $n }}"
                               class="sr-only"
                               {{ (int)$s('welcome_popup_count', 3) === $n ? 'checked' : '' }}
                               onchange="updateCountCards()">
                        <span class="text-2xl font-bold {{ (int)$s('welcome_popup_count', 3) === $n ? 'text-indigo-600' : 'text-gray-400' }}" id="count-num-{{ $n }}">{{ $n }}</span>
                        <span class="text-xs text-gray-400">profil{{ $n > 1 ? 's' : '' }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Fréquence --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Fréquence d'affichage</p>
                <div class="space-y-3">
                    @foreach([
                        ['once',    'Une seule fois',       'Le popup s\'affiche une seule fois par compte, puis ne reparaît plus jamais.'],
                        ['session', 'À chaque session',     'Le popup s\'affiche une fois par session de navigateur (se réinitialise à la fermeture du navigateur).'],
                        ['always',  'À chaque connexion',   'Le popup s\'affiche à chaque visite du tableau de bord.'],
                    ] as [$val, $label, $desc])
                    <label class="freq-card flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition
                                  {{ $s('welcome_popup_frequency', 'once') === $val ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-200 hover:bg-gray-50' }}">
                        <input type="radio" name="welcome_popup_frequency" value="{{ $val }}"
                               class="mt-0.5 accent-indigo-600 flex-shrink-0"
                               {{ $s('welcome_popup_frequency', 'once') === $val ? 'checked' : '' }}
                               onchange="updateFreqCards()">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Textes du popup --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Textes affichés</p>
                <p class="text-xs text-gray-400 mb-5">Laissez vide pour utiliser le texte par défaut. <code class="bg-gray-100 px-1 rounded">:prenom</code> sera remplacé par le prénom de l'utilisateur.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Titre</label>
                        <input type="text" name="welcome_popup_title"
                               value="{{ $s('welcome_popup_title', '') }}"
                               placeholder="Welcome to LeadXchange, :prenom !"
                               maxlength="120"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Sous-titre</label>
                        <input type="text" name="welcome_popup_subtitle"
                               value="{{ $s('welcome_popup_subtitle', '') }}"
                               placeholder="Here are a few people you might want to connect with"
                               maxlength="200"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Bouton "Plus tard"</label>
                            <input type="text" name="welcome_popup_btn_later"
                                   value="{{ $s('welcome_popup_btn_later', '') }}"
                                   placeholder="Maybe later"
                                   maxlength="60"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Bouton principal (CTA)</label>
                            <input type="text" name="welcome_popup_btn_cta"
                                   value="{{ $s('welcome_popup_btn_cta', '') }}"
                                   placeholder="Explore network"
                                   maxlength="60"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    {{-- ── Aperçu / Aide ── --}}
    <div class="space-y-4">

        {{-- Prévisualisation popup --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Aperçu du popup</p>
            <div class="rounded-xl overflow-hidden border border-gray-200 shadow-md">
                <div class="px-5 py-4 text-center" style="background:linear-gradient(135deg,#0f2027,#1a3a4a,#2F44E0);">
                    <div class="w-10 h-10 rounded-xl mx-auto mb-2 flex items-center justify-center" style="background:rgba(255,255,255,0.15);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <p class="text-white text-xs font-bold">Welcome to LeadXchange !</p>
                    <p class="text-white/60 text-[10px] mt-0.5">Here are people you might connect with</p>
                </div>
                <div class="px-4 py-3 space-y-2 bg-white">
                    @foreach([['Sophie', 'M', 'UX Designer'], ['Marc', 'D', 'CEO · Casablanca'], ['Imane', 'I', 'Marketing']] as [$name, $initial, $title])
                    <div class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-100">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0"
                             style="background:linear-gradient(135deg,#2F44E0,#3C55FD);">{{ $initial }}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-900">{{ $name }}</p>
                            <p class="text-[10px] text-gray-400 truncate">{{ $title }}</p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg text-white flex-shrink-0" style="background:#2F44E0;">+</span>
                    </div>
                    @endforeach
                </div>
                <div class="px-4 py-3 bg-white border-t border-gray-100 flex gap-2">
                    <div class="flex-1 py-1.5 rounded-lg text-[10px] font-semibold text-center border border-gray-200 text-gray-500">Later</div>
                    <div class="flex-1 py-1.5 rounded-lg text-[10px] font-semibold text-center text-white" style="background:#2F44E0;">Explore</div>
                </div>
            </div>
        </div>

        {{-- Aide --}}
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 text-xs text-indigo-700 space-y-2.5">
            <p class="font-bold text-sm">Comment ça fonctionne ?</p>
            <div class="space-y-2">
                <div class="flex items-start gap-2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 flex-shrink-0"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/></svg>
                    <p><strong>Même ville</strong> : filtre sur les membres partageant la même ville que l'utilisateur connecté.</p>
                </div>
                <div class="flex items-start gap-2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 flex-shrink-0"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    <p><strong>Même région</strong> : filtre sur les membres ayant la même région (champ région du profil utilisateur).</p>
                </div>
                <div class="flex items-start gap-2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 flex-shrink-0"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <p><strong>Même intérêt</strong> : filtre sur les membres ayant au moins un centre d'intérêt en commun.</p>
                </div>
                <div class="flex items-start gap-2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
                    <p><strong>Ville + Intérêt</strong> : cumule les deux critères. Si le nombre de profils est insuffisant, complète avec la ville seule.</p>
                </div>
                <div class="flex items-start gap-2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <p><strong>Fréquence "Une seule fois"</strong> : utilise le localStorage du navigateur — se réinitialise si l'utilisateur change de navigateur.</p>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Toggle switch visual
const toggle = document.getElementById('toggle-enabled');
const knob   = document.getElementById('toggle-knob');
function updateToggle() {
    knob.style.transform = toggle.checked ? 'translateX(20px)' : 'translateX(0)';
}
toggle.addEventListener('change', updateToggle);
updateToggle();

// Criteria cards
function updateCriteriaCards() {
    document.querySelectorAll('.criteria-card').forEach(card => {
        const radio = card.querySelector('input[type=radio]');
        card.classList.toggle('border-indigo-500', radio.checked);
        card.classList.toggle('bg-indigo-50',      radio.checked);
        card.classList.toggle('border-gray-200',  !radio.checked);
    });
}

// Count cards
function updateCountCards() {
    document.querySelectorAll('.count-card').forEach(card => {
        const radio = card.querySelector('input[type=radio]');
        const num   = card.querySelector('span:first-of-type');
        card.classList.toggle('border-indigo-500', radio.checked);
        card.classList.toggle('bg-indigo-50',      radio.checked);
        card.classList.toggle('border-gray-200',  !radio.checked);
        if (num) {
            num.classList.toggle('text-indigo-600', radio.checked);
            num.classList.toggle('text-gray-400',  !radio.checked);
        }
    });
}

// Frequency cards
function updateFreqCards() {
    document.querySelectorAll('.freq-card').forEach(card => {
        const radio = card.querySelector('input[type=radio]');
        card.classList.toggle('border-indigo-500', radio.checked);
        card.classList.toggle('bg-indigo-50',      radio.checked);
        card.classList.toggle('border-gray-200',  !radio.checked);
    });
}
</script>
@endpush
