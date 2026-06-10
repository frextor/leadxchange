@extends('admin.layouts.admin')

@section('title', 'Secteurs d\'activité')
@section('page-title', 'Secteurs')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Configuration</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            Secteurs d'activité
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full bg-violet-50 text-violet-500">{{ $stats['total'] }}</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Catégories utilisées pour les profils membres et entreprises.</p>
    </div>
</div>

{{-- ── Stats cards ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="1.8">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Secteurs au total</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="1.8">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['with_companies'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">En utilisation</p>
        </div>
        @if($stats['total'] > 0)
        <div class="ml-auto">
            <div class="text-right">
                <span class="text-xs font-bold text-teal-600">{{ round($stats['with_companies'] / $stats['total'] * 100) }}%</span>
            </div>
            <div class="w-16 h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden">
                <div class="h-full bg-teal-400 rounded-full" style="width:{{ round($stats['with_companies'] / $stats['total'] * 100) }}%"></div>
            </div>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['without_companies'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Sans entreprise</p>
        </div>
    </div>
</div>

{{-- ── Layout ───────────────────────────────────────────────────────────── --}}
<div class="grid gap-5 lg:grid-cols-[1fr_300px] items-start">

    {{-- ── Table ───────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Search bar --}}
        <div class="px-5 py-3.5 border-b border-gray-100">
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="searchSectors"
                       placeholder="Rechercher un secteur..."
                       autocomplete="off"
                       class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-violet-300 focus:ring-2 focus:ring-violet-50 transition placeholder-gray-300">
                <button id="clearSearch" onclick="clearSectorSearch()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 rounded-md flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition hidden">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Table sub-header --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                <span id="resultCount">{{ $sectors->total() }}</span> secteur{{ $sectors->total() > 1 ? 's' : '' }}
                @if($sectors->hasPages())
                <span class="text-gray-300 font-normal"> — page {{ $sectors->currentPage() }}/{{ $sectors->lastPage() }}</span>
                @endif
            </p>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Entreprises</p>
        </div>

        {{-- Rows --}}
        <div id="sectorTable">
        @php $prevLetter = ''; @endphp
        @forelse($sectors as $sector)
        @php
            $letter = strtoupper(mb_substr($sector->name, 0, 1));
            $palettes = [
                'bg-violet-100 text-violet-600',
                'bg-teal-100 text-teal-700',
                'bg-indigo-100 text-indigo-600',
                'bg-amber-100 text-amber-700',
                'bg-rose-100 text-rose-600',
                'bg-blue-100 text-blue-600',
                'bg-emerald-100 text-emerald-700',
                'bg-orange-100 text-orange-600',
                'bg-cyan-100 text-cyan-700',
                'bg-pink-100 text-pink-600',
            ];
            $avatarColor = $palettes[ord($letter) % count($palettes)];
        @endphp

        @if($letter !== $prevLetter)
        <div class="letter-sep px-5 py-1.5 flex items-center gap-2 border-b border-gray-50" style="background:#FAFBFC;" data-letter="{{ $letter }}">
            <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-extrabold text-gray-400" style="background:#F1F5F9;">{{ $letter }}</span>
        </div>
        @php $prevLetter = $letter; @endphp
        @endif

        <div class="sector-row border-b border-gray-50 last:border-b-0 hover:bg-slate-50/60 transition-colors group"
             id="row-{{ $sector->id }}"
             data-name="{{ strtolower($sector->name) }}"
             data-id="{{ $sector->id }}">
            <div class="flex items-center px-5 py-3 gap-3">

                {{-- Letter avatar --}}
                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0 {{ $avatarColor }}">
                    {{ strtoupper(mb_substr($sector->name, 0, 1)) }}
                </div>

                {{-- Name display / edit form --}}
                <div class="flex-1 min-w-0">
                    <span id="name-{{ $sector->id }}" class="font-semibold text-gray-900 text-sm">{{ $sector->name }}</span>
                    <form id="edit-form-{{ $sector->id }}" method="POST"
                          action="{{ route('admin.super.sectors.update', $sector) }}" class="hidden items-center gap-2">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $sector->name }}" required maxlength="100"
                               onkeydown="var id=this.closest('.sector-row').dataset.id;if(event.key==='Enter'){submitEdit(id)}if(event.key==='Escape'){cancelEdit(id)}"
                               class="border border-violet-300 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-100 w-60 transition">
                    </form>
                </div>

                {{-- Right side: companies badge + actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">

                    {{-- Companies badge --}}
                    @if($sector->companies_count > 0)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 text-xs font-semibold border border-teal-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                        {{ $sector->companies_count }}
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-gray-50 text-gray-300 text-xs border border-gray-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-200 flex-shrink-0"></span>
                        0
                    </span>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">

                        <button onclick="startEdit(this.closest('.sector-row').dataset.id)" id="edit-btn-{{ $sector->id }}"
                                title="Modifier"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>

                        <button onclick="submitEdit(this.closest('.sector-row').dataset.id)" id="save-btn-{{ $sector->id }}"
                                title="Enregistrer"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-teal-600 hover:bg-teal-50 transition hidden">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                        </button>

                        <button onclick="cancelEdit(this.closest('.sector-row').dataset.id)" id="cancel-btn-{{ $sector->id }}"
                                title="Annuler"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 transition hidden">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>

                        @if($sector->companies_count === 0)
                        <form method="POST" action="{{ route('admin.super.sectors.destroy', $sector) }}" id="del-sector-{{ $sector->id }}">
                            @csrf @method('DELETE')
                            <button type="button"
                                    data-name="{{ $sector->name }}"
                                    onclick="swalDelete(this, this.dataset.name)"
                                    title="Supprimer"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                        @else
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-200 cursor-not-allowed"
                             title="Impossible — des entreprises utilisent ce secteur">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="px-5 py-16 text-center">
            <div class="w-14 h-14 rounded-2xl bg-violet-50 flex items-center justify-center mx-auto mb-3">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-400">Aucun secteur pour l'instant</p>
            <p class="text-xs text-gray-300 mt-1">Créez votre premier secteur d'activité via le formulaire.</p>
        </div>
        @endforelse
        </div>

        {{-- No search results state --}}
        <div id="noResults" class="hidden px-5 py-14 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-400">Aucun résultat</p>
            <p class="text-xs text-gray-300 mt-1">Essayez un autre terme de recherche.</p>
        </div>

        {{-- Pagination --}}
        @if($sectors->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">Page <span class="font-semibold text-gray-700">{{ $sectors->currentPage() }}</span> sur {{ $sectors->lastPage() }}</p>
            <div class="flex items-center gap-1">
                @if($sectors->onFirstPage())
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-200 cursor-not-allowed border border-gray-100">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </span>
                @else
                <a href="{{ $sectors->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-violet-50 hover:text-violet-600 border border-gray-100 hover:border-violet-200 transition">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                @endif

                @foreach($sectors->getUrlRange(max(1,$sectors->currentPage()-2), min($sectors->lastPage(),$sectors->currentPage()+2)) as $page => $url)
                @if($page == $sectors->currentPage())
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white bg-violet-500">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-medium text-gray-500 hover:bg-gray-100 border border-gray-100 transition">{{ $page }}</a>
                @endif
                @endforeach

                @if($sectors->hasMorePages())
                <a href="{{ $sectors->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-violet-50 hover:text-violet-600 border border-gray-100 hover:border-violet-200 transition">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                @else
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-200 cursor-not-allowed border border-gray-100">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </span>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- ── Add form ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden sticky top-4">

        {{-- Card header --}}
        <div class="px-5 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#F5F3FF,#EDE9FE);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-white shadow-sm">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-violet-900">Nouveau secteur</p>
                    <p class="text-[10px] text-violet-500 font-medium">Ajouter une catégorie</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.super.sectors.store') }}" class="p-5">
            @csrf
            <div class="mb-4">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Nom du secteur</label>
                <div class="relative">
                    <input type="text" name="name" id="newSectorName" value="{{ old('name') }}" required maxlength="100"
                           placeholder="ex: Intelligence artificielle"
                           oninput="updateCharCount(this)"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-50 transition placeholder-gray-300">
                    <span id="charCount" class="absolute right-3 bottom-2.5 text-[10px] text-gray-300 pointer-events-none">0/100</span>
                </div>
                @error('name')
                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>
            <button type="submit"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white flex items-center justify-center gap-2 transition hover:opacity-90 active:scale-[.98]"
                    style="background:linear-gradient(135deg,#7C3AED,#6D28D9);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                Créer le secteur
            </button>
        </form>

        {{-- Hint --}}
        <div class="px-5 pb-5">
            <div class="flex items-start gap-2.5 px-3.5 py-3 rounded-xl bg-amber-50 border border-amber-100">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p class="text-[10px] text-amber-700 leading-relaxed">Un secteur ne peut être supprimé que s'il n'est lié à aucune entreprise.</p>
            </div>
        </div>

        {{-- Quick stats in form --}}
        <div class="px-5 pb-5">
            <div class="rounded-xl border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-3.5 py-2 border-b border-gray-50">
                    <span class="text-[10px] text-gray-400">Total</span>
                    <span class="text-xs font-bold text-gray-700">{{ $stats['total'] }}</span>
                </div>
                <div class="flex items-center justify-between px-3.5 py-2 border-b border-gray-50">
                    <span class="text-[10px] text-gray-400">En utilisation</span>
                    <span class="text-xs font-bold text-teal-600">{{ $stats['with_companies'] }}</span>
                </div>
                <div class="flex items-center justify-between px-3.5 py-2">
                    <span class="text-[10px] text-gray-400">Supprimables</span>
                    <span class="text-xs font-bold text-gray-500">{{ $stats['without_companies'] }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// ── Character counter ──────────────────────────────────────────────────
function updateCharCount(input) {
    const el = document.getElementById('charCount');
    const len = input.value.length;
    el.textContent = len + '/100';
    el.className = 'absolute right-3 bottom-2.5 text-[10px] pointer-events-none ' +
        (len > 85 ? (len >= 100 ? 'text-red-400' : 'text-amber-400') : 'text-gray-300');
}

// ── Live search ─────────────────────────────────────────────────────────
const searchInput  = document.getElementById('searchSectors');
const clearBtn     = document.getElementById('clearSearch');
const noResults    = document.getElementById('noResults');

searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    clearBtn.classList.toggle('hidden', !q);

    const rows = document.querySelectorAll('.sector-row');
    const seps = document.querySelectorAll('.letter-sep');
    const visibleLetters = new Set();

    rows.forEach(function (row) {
        const match = !q || row.dataset.name.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visibleLetters.add(row.dataset.name[0].toUpperCase());
    });

    seps.forEach(function (sep) {
        sep.style.display = visibleLetters.has(sep.dataset.letter) ? '' : 'none';
    });

    noResults.classList.toggle('hidden', visibleLetters.size > 0 || !q);
});

function clearSectorSearch() {
    searchInput.value = '';
    searchInput.dispatchEvent(new Event('input'));
    searchInput.focus();
}

// ── Inline edit ─────────────────────────────────────────────────────────
function startEdit(id) {
    document.getElementById('name-' + id).classList.add('hidden');
    const form = document.getElementById('edit-form-' + id);
    form.classList.remove('hidden');
    form.classList.add('flex');
    document.getElementById('edit-btn-' + id).classList.add('hidden');
    document.getElementById('save-btn-' + id).classList.remove('hidden');
    document.getElementById('cancel-btn-' + id).classList.remove('hidden');
    const delForm = document.getElementById('del-sector-' + id);
    if (delForm) delForm.classList.add('hidden');
    const input = form.querySelector('input');
    input.focus();
    input.select();
}

function submitEdit(id) {
    document.getElementById('edit-form-' + id).submit();
}

function cancelEdit(id) {
    document.getElementById('name-' + id).classList.remove('hidden');
    const form = document.getElementById('edit-form-' + id);
    form.classList.add('hidden');
    form.classList.remove('flex');
    document.getElementById('edit-btn-' + id).classList.remove('hidden');
    document.getElementById('save-btn-' + id).classList.add('hidden');
    document.getElementById('cancel-btn-' + id).classList.add('hidden');
    const delForm = document.getElementById('del-sector-' + id);
    if (delForm) delForm.classList.remove('hidden');
}
</script>
@endpush
@endsection
