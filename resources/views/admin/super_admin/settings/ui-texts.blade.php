@extends('admin.layouts.admin')
@section('title', "Paramètres — Textes de l'interface")
@section('page-title', 'Paramètres')

@section('content')

@php
    // Icônes par thème de section (zone de l'app concernée).
    $sectionIcons = [
        'Dashboard'        => '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
        'Achat de points'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v10M9.5 9.5a2.5 2.5 0 0 1 2.5-1h.5a2 2 0 0 1 0 4h-1a2 2 0 0 0 0 4h.5a2.5 2.5 0 0 0 2.5-1"/>',
        'Leads'            => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9"/>',
        'Événements'       => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'Groupes'          => '<path d="M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'Réseau'           => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 10.5 15.4 6.5M8.6 13.5l6.8 4"/>',
    ];
    $iconFor = function (string $section) use ($sectionIcons) {
        foreach ($sectionIcons as $needle => $svg) {
            if (str_starts_with($section, $needle)) return $svg;
        }
        return '<circle cx="12" cy="12" r="9"/>';
    };

    $oldTexts = old('texts', []);
    $totalCustom = 0;
    $sectionMeta = [];
    foreach ($registry as $section => $items) {
        $count = 0;
        foreach ($items as $key => $def) {
            if (($oldTexts[$key] ?? $saved->get('ui_text.' . $key)?->value) !== null) $count++;
        }
        $sectionMeta[$section] = $count;
        $totalCustom += $count;
    }
@endphp

<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Textes de l'interface</h1>
        <p class="text-sm text-gray-400 mt-1 max-w-xl">Personnalisez les boutons et messages affichés aux membres. Laissez un champ vide pour revenir au texte par défaut.</p>
    </div>
    <div class="flex items-center gap-3 flex-shrink-0">
        @if($totalCustom > 0)
        <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-100">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
            {{ $totalCustom }} texte{{ $totalCustom > 1 ? 's' : '' }} personnalisé{{ $totalCustom > 1 ? 's' : '' }}
        </span>
        @endif
        <button form="ui-texts-form" type="submit"
                class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
            Enregistrer
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Barre de recherche + navigation rapide par section --}}
<div class="sticky top-0 z-10 -mx-1 px-1 pb-4 mb-2 bg-gray-50/95 backdrop-blur-sm">
    <div class="flex items-center gap-3 flex-wrap">
        <div class="relative flex-1 min-w-[220px] max-w-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" id="uiTextSearch" placeholder="Rechercher un texte ou un bouton…"
                   class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-gray-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>
        <div class="flex items-center gap-1.5 overflow-x-auto" id="uiTextNav">
            @foreach ($registry as $section => $items)
            <a href="#sec-{{ $loop->index }}" class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-500 bg-white border border-gray-200 hover:border-indigo-300 hover:text-indigo-600 transition whitespace-nowrap">
                {{ $section }}
                @if($sectionMeta[$section] > 0)<span class="text-indigo-500">({{ $sectionMeta[$section] }})</span>@endif
            </a>
            @endforeach
        </div>
    </div>
    <p id="uiTextNoMatch" class="hidden text-sm text-gray-400 mt-3 px-1">Aucun texte ne correspond à cette recherche.</p>
</div>

<form id="ui-texts-form" method="POST" action="{{ route('admin.super.settings.ui-texts.update') }}">
    @csrf @method('PUT')

    <div class="max-w-3xl space-y-5">
        @foreach ($registry as $section => $items)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden ui-section" id="sec-{{ $loop->index }}">
            <div class="flex items-center gap-2.5 px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $iconFor($section) !!}</svg>
                </span>
                <p class="text-xs font-bold text-gray-600 uppercase tracking-wider">{{ $section }}</p>
                @if($sectionMeta[$section] > 0)
                <span class="text-[11px] font-semibold text-indigo-500 bg-indigo-50 rounded-full px-2 py-0.5">{{ $sectionMeta[$section] }} personnalisé{{ $sectionMeta[$section] > 1 ? 's' : '' }}</span>
                @endif
            </div>
            <div class="p-6 space-y-4">
                @foreach ($items as $key => $def)
                @php
                    $current = $oldTexts[$key] ?? $saved->get('ui_text.' . $key)?->value;
                    $isCustom = $current !== null;
                    $fieldId = 'ui_' . $loop->parent->index . '_' . $loop->index;
                @endphp
                <div class="ui-field" data-search="{{ mb_strtolower($def['label'] . ' ' . $def['default']) }}">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <label class="text-xs font-semibold text-gray-500" for="{{ $fieldId }}">{{ $def['label'] }}</label>
                        @if($isCustom)
                        <button type="button" class="ui-reset text-[11px] font-semibold text-gray-400 hover:text-red-500 transition" data-target="{{ $fieldId }}">Réinitialiser</button>
                        @endif
                    </div>
                    <input type="text" name="texts[{{ $key }}]" id="{{ $fieldId }}" maxlength="300"
                           value="{{ $current ?? '' }}"
                           placeholder="{{ $def['default'] }}"
                           class="ui-input w-full px-3.5 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-200 transition @error($key) border-red-300 @else border-gray-200 @enderror">
                    <p class="text-[11px] mt-1 {{ $isCustom ? 'text-indigo-500 font-medium' : 'text-gray-400' }}">
                        {{ $isCustom ? 'Personnalisé — vide = retour au texte par défaut ci-dessous.' : 'Par défaut : "' . $def['default'] . '"' }}
                    </p>
                    @error($key)<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end pb-8">
            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
                Enregistrer
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
(function () {
    const search = document.getElementById('uiTextSearch');
    const noMatch = document.getElementById('uiTextNoMatch');

    search.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let anyVisible = false;

        document.querySelectorAll('.ui-section').forEach(function (section) {
            let sectionHasMatch = false;
            section.querySelectorAll('.ui-field').forEach(function (field) {
                const match = !q || field.dataset.search.includes(q);
                field.style.display = match ? '' : 'none';
                if (match) sectionHasMatch = true;
            });
            section.style.display = sectionHasMatch ? '' : 'none';
            if (sectionHasMatch) anyVisible = true;
        });

        noMatch.classList.toggle('hidden', anyVisible || !q);
    });

    document.querySelectorAll('.ui-reset').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);
            if (!input) return;
            input.value = '';
            input.focus();
        });
    });
})();
</script>
@endpush
