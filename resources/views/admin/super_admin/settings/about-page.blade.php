@extends('admin.layouts.admin')
@section('title', 'Paramètres — Page À propos')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Page À propos</h1>
        <p class="text-sm text-gray-400 mt-1">Configurez le contenu de la page publique accessible à tous les visiteurs.</p>
    </div>
    <a href="{{ route('about') }}" target="_blank"
       class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        Voir la page
    </a>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

@php
    $s = fn(string $k, $d = null) => old($k, $settings->get($k)?->value ?? $d);
@endphp

<div class="grid grid-cols-3 gap-6">

    {{-- ── Form ── --}}
    <div class="col-span-2 space-y-5">
        <form method="POST" action="{{ route('admin.super.settings.about-page.update') }}" id="about-form">
            @csrf @method('PUT')

            {{-- Activation --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Activation</p>
                <label class="flex items-center justify-between gap-4 cursor-pointer">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Page À propos visible</p>
                        <p class="text-xs text-gray-400 mt-0.5">Lorsque désactivée, la page affiche un 404 pour les visiteurs.</p>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="hidden" name="about_enabled" value="0">
                        <input type="checkbox" name="about_enabled" value="1" id="toggle-about"
                               class="sr-only peer"
                               {{ $s('about_enabled', '1') === '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-indigo-600 rounded-full transition peer-focus:ring-2 peer-focus:ring-indigo-300 cursor-pointer"
                             onclick="document.getElementById('toggle-about').click()"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-5 pointer-events-none"
                             id="about-knob"></div>
                    </div>
                </label>
            </div>

            {{-- En-tête --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">En-tête de la page</p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">
                            Titre <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="about_title"
                               value="{{ $s('about_title', 'À propos de LeadXchange') }}"
                               placeholder="À propos de LeadXchange"
                               maxlength="100"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Accroche (tagline)</label>
                        <input type="text" name="about_tagline"
                               value="{{ $s('about_tagline', '') }}"
                               placeholder="La plateforme professionnelle qui connecte les talents du Maroc"
                               maxlength="200"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                    </div>
                </div>
            </div>

            {{-- Mission --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Notre mission</p>
                <textarea name="about_mission"
                          rows="3"
                          maxlength="600"
                          placeholder="Décrivez la mission de la plateforme en quelques phrases (affiché dans un bloc mis en valeur)."
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition resize-none">{{ $s('about_mission', '') }}</textarea>
                <p class="text-[10px] text-gray-400 mt-1">Texte affiché dans le bloc "Mission" en haut de page. Maximum 600 caractères.</p>
            </div>

            {{-- Contenu principal --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Contenu principal</p>
                <p class="text-xs text-gray-400 mb-4">HTML accepté : <code class="bg-gray-100 px-1 rounded">&lt;h2&gt;</code>, <code class="bg-gray-100 px-1 rounded">&lt;p&gt;</code>, <code class="bg-gray-100 px-1 rounded">&lt;ul&gt;</code>, <code class="bg-gray-100 px-1 rounded">&lt;strong&gt;</code>, <code class="bg-gray-100 px-1 rounded">&lt;em&gt;</code>.</p>
                <textarea name="about_content"
                          rows="12"
                          maxlength="10000"
                          placeholder="<h2>Notre histoire</h2>&#10;<p>LeadXchange est née de la conviction que...</p>&#10;&#10;<h2>Nos valeurs</h2>&#10;<ul>&#10;  <li><strong>Transparence</strong> : ...</li>&#10;</ul>"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition resize-y">{{ $s('about_content', '') }}</textarea>
            </div>

            {{-- Infos pratiques --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Informations pratiques</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Email de contact</label>
                        <input type="email" name="about_contact_email"
                               value="{{ $s('about_contact_email', '') }}"
                               placeholder="contact@leadxchange.com"
                               maxlength="150"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Année de création</label>
                        <input type="number" name="about_founded_year"
                               value="{{ $s('about_founded_year', date('Y')) }}"
                               min="2000" max="2030"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                    </div>
                </div>
            </div>

            {{-- Call to Action --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Bouton d'appel à l'action (CTA)</p>
                <p class="text-xs text-gray-400 mb-4">Affiché en bas de la page. Laissez vide pour ne pas afficher de bouton.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Libellé du bouton</label>
                        <input type="text" name="about_cta_label"
                               value="{{ $s('about_cta_label', '') }}"
                               placeholder="Rejoindre la plateforme"
                               maxlength="60"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">URL du bouton</label>
                        <input type="text" name="about_cta_url"
                               value="{{ $s('about_cta_url', '') }}"
                               placeholder="/register"
                               maxlength="255"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>

    {{-- ── Aperçu / Info ── --}}
    <div class="space-y-4">

        {{-- URL --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Accès public</p>
            <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2.5 border border-gray-200">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <span class="text-xs font-mono text-gray-700 flex-1 truncate">{{ url('/a-propos') }}</span>
                <a href="{{ route('about') }}" target="_blank"
                   class="text-[10px] font-bold text-indigo-600 hover:text-indigo-800 flex-shrink-0">↗</a>
            </div>
            <p class="text-xs text-gray-400 mt-2">Page accessible à tous sans connexion.</p>
        </div>

        {{-- Aperçu miniature --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Aperçu</p>
            <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                <div class="px-4 py-5 text-center" style="background:linear-gradient(135deg,#0f2027,#1a3a4a,#1E8F88);">
                    <div class="w-10 h-10 mx-auto mb-2 rounded-xl flex items-center justify-center bg-white/15 font-bold text-white text-sm">LX</div>
                    <p class="text-white text-xs font-bold leading-snug" id="preview-title">{{ $s('about_title', 'À propos de LeadXchange') }}</p>
                    <p class="text-white/60 text-[10px] mt-1 leading-snug" id="preview-tagline">{{ Str::limit($s('about_tagline', ''), 60) }}</p>
                </div>
                <div class="px-4 py-4 bg-white space-y-2">
                    <div class="h-2 bg-gray-100 rounded w-full"></div>
                    <div class="h-2 bg-gray-100 rounded w-4/5"></div>
                    <div class="h-2 bg-gray-100 rounded w-3/5"></div>
                    <div class="h-2 bg-gray-100 rounded w-full mt-3"></div>
                    <div class="h-2 bg-gray-100 rounded w-2/3"></div>
                </div>
                @if($s('about_cta_label'))
                <div class="px-4 py-3 bg-white border-t border-gray-100">
                    <div class="w-full py-2 rounded-lg text-[10px] font-bold text-center text-white" style="background:#1E8F88;">
                        {{ $s('about_cta_label') }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Conseils --}}
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 text-xs text-indigo-700 space-y-2.5">
            <p class="font-bold text-sm">Conseils de rédaction</p>
            <div class="space-y-2">
                <div class="flex items-start gap-2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 flex-shrink-0"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <p><strong>Mission</strong> : 2–3 phrases percutantes résumant la valeur de la plateforme.</p>
                </div>
                <div class="flex items-start gap-2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 flex-shrink-0"><path d="M9 12l2 2 4-4"/></svg>
                    <p><strong>Contenu</strong> : utilisez <code class="bg-indigo-100 px-1 rounded">&lt;h2&gt;</code> pour les sections et <code class="bg-indigo-100 px-1 rounded">&lt;ul&gt;</code> pour les listes.</p>
                </div>
                <div class="flex items-start gap-2">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="mt-0.5 flex-shrink-0"><path d="M15 10l-4 4-4-4"/></svg>
                    <p><strong>CTA</strong> : pointez vers <code class="bg-indigo-100 px-1 rounded">/register</code> pour inviter les visiteurs à s'inscrire.</p>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
const toggleAbout = document.getElementById('toggle-about');
const knobAbout   = document.getElementById('about-knob');
function updateToggleAbout() {
    knobAbout.style.transform = toggleAbout.checked ? 'translateX(20px)' : 'translateX(0)';
}
toggleAbout.addEventListener('change', updateToggleAbout);
updateToggleAbout();

// Live preview update
document.querySelector('[name=about_title]').addEventListener('input', e => {
    document.getElementById('preview-title').textContent = e.target.value || 'À propos de LeadXchange';
});
document.querySelector('[name=about_tagline]').addEventListener('input', e => {
    const v = e.target.value.substring(0, 60);
    document.getElementById('preview-tagline').textContent = v;
});
</script>
@endpush
