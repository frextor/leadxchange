@extends('admin.layouts.admin')

@section('title', 'Régions')
@section('page-title', 'Régions')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Configuration</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            Régions
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full bg-teal-50 text-teal-600">{{ $stats['total'] }}</span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Villes et régions utilisées pour localiser les membres.</p>
    </div>
</div>

{{-- ── Stats cards ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="1.8">
                <circle cx="12" cy="10" r="3"/>
                <path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Régions au total</p>
        </div>
        @if(request()->hasAny(['search','country_id']))
        <div class="ml-auto text-right">
            <p class="text-sm font-bold text-teal-600">{{ $cities->total() }}</p>
            <p class="text-[10px] text-gray-400">résultat{{ $cities->total() > 1 ? 's' : '' }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/>
                <path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['countries'] }}</p>
            <p class="text-xs text-gray-400 font-medium mt-0.5">Pays représentés</p>
        </div>
    </div>
</div>

{{-- ── Layout ───────────────────────────────────────────────────────────── --}}
<div class="grid gap-5 lg:grid-cols-[1fr_300px] items-start">

    {{-- ── Table card ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('admin.super.cities.index') }}"
              class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"
                     width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher une région..."
                       autocomplete="off"
                       class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-teal-300 focus:ring-2 focus:ring-teal-50 transition placeholder-gray-300">
            </div>
            <select name="country_id"
                    class="h-9 pl-3 pr-8 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-teal-300 focus:ring-2 focus:ring-teal-50 transition text-gray-600 bg-white">
                <option value="">Tous les pays</option>
                @foreach($countries as $c)
                <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>
                    {{ $c->flag ? $c->flag . ' ' : '' }}{{ $c->name }}
                </option>
                @endforeach
            </select>
            <button type="submit"
                    class="h-9 px-4 rounded-xl text-xs font-semibold text-white flex items-center gap-1.5 transition hover:opacity-90"
                    style="background:#0D9488;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                </svg>
                Filtrer
            </button>
            @if(request()->hasAny(['search','country_id']))
            <a href="{{ route('admin.super.cities.index') }}"
               class="h-9 px-3 rounded-xl text-xs font-medium text-gray-500 border border-gray-200 flex items-center gap-1 hover:bg-gray-50 transition">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Reset
            </a>
            @endif
        </form>

        {{-- Active filter chips --}}
        @if(request()->hasAny(['search','country_id']))
        <div class="px-5 py-2 border-b border-gray-50 flex items-center gap-2">
            <span class="text-[10px] text-gray-400 font-medium">Filtre actif :</span>
            @if(request('search'))
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 text-[11px] font-medium border border-teal-100">
                "{{ request('search') }}"
            </span>
            @endif
            @if(request('country_id'))
            @php $activeCountry = $countries->firstWhere('id', request('country_id')); @endphp
            @if($activeCountry)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-medium border border-indigo-100">
                {{ $activeCountry->flag ? $activeCountry->flag . ' ' : '' }}{{ $activeCountry->name }}
            </span>
            @endif
            @endif
            <span class="text-[10px] text-gray-400 ml-1">— {{ $cities->total() }} résultat{{ $cities->total() > 1 ? 's' : '' }}</span>
        </div>
        @endif

        {{-- Table sub-header --}}
        <div class="px-5 py-2.5 border-b border-gray-50 flex items-center justify-between">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Région</p>
            <div class="flex items-center gap-8">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Statut</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pays</p>
            </div>
        </div>

        {{-- Rows --}}
        <div id="cityTable">
        @php $prevLetter = ''; @endphp
        @forelse($cities as $city)
        @php
            $letter = strtoupper(mb_substr($city->name, 0, 1));
            $palettes = [
                'bg-teal-100 text-teal-700',
                'bg-indigo-100 text-indigo-600',
                'bg-violet-100 text-violet-600',
                'bg-cyan-100 text-cyan-700',
                'bg-emerald-100 text-emerald-700',
                'bg-blue-100 text-blue-600',
                'bg-pink-100 text-pink-600',
                'bg-amber-100 text-amber-700',
                'bg-rose-100 text-rose-600',
                'bg-orange-100 text-orange-600',
            ];
            $avatarColor = $palettes[ord($letter) % count($palettes)];
        @endphp

        @if($letter !== $prevLetter)
        <div class="px-5 py-1.5 flex items-center gap-2 border-b border-gray-50" style="background:#FAFBFC;">
            <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-extrabold text-gray-400" style="background:#F1F5F9;">{{ $letter }}</span>
        </div>
        @php $prevLetter = $letter; @endphp
        @endif

        <div class="city-row border-b border-gray-50 last:border-b-0 hover:bg-slate-50/60 transition-colors group"
             id="city-row-{{ $city->id }}"
             data-id="{{ $city->id }}">
            <div class="flex items-center px-5 py-2.5 gap-3">

                {{-- Letter avatar --}}
                <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-[11px] flex-shrink-0 {{ $avatarColor }}">
                    {{ $letter }}
                </div>

                {{-- Name display / edit --}}
                <div class="flex-1 min-w-0">
                    <span id="city-name-{{ $city->id }}" class="font-semibold text-gray-900 text-sm">{{ $city->name }}</span>
                    <form id="city-form-{{ $city->id }}" method="POST"
                          action="{{ route('admin.super.cities.update', $city) }}"
                          class="hidden items-center gap-2">
                        @csrf @method('PUT')
                        <input type="text" name="name" value="{{ $city->name }}" required maxlength="100"
                               onkeydown="if(event.key==='Escape'){cancelCityEdit(this.closest('.city-row').dataset.id)}"
                               class="border border-teal-300 rounded-xl px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-50 w-40 transition">
                        <select name="country_id"
                                class="border border-teal-300 rounded-xl px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-50 transition">
                            @foreach($countries as $c)
                            <option value="{{ $c->id }}" {{ $city->country_id == $c->id ? 'selected' : '' }}>
                                {{ $c->flag ? $c->flag . ' ' : '' }}{{ $c->name }}
                            </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                {{-- Status + Country + Actions --}}
                <div class="flex items-center gap-4 flex-shrink-0">

                    {{-- Toggle actif/inactif --}}
                    <button type="button"
                            id="toggle-btn-{{ $city->id }}"
                            data-id="{{ $city->id }}"
                            data-name="{{ addslashes($city->name) }}"
                            data-active="{{ $city->is_active ? '1' : '0' }}"
                            data-url="{{ route('admin.super.cities.toggle', $city) }}"
                            onclick="toggleCity(this)"
                            title="{{ $city->is_active ? 'Désactiver' : 'Activer' }}"
                            class="relative inline-flex items-center h-5 w-9 rounded-full transition-colors focus:outline-none {{ $city->is_active ? 'bg-teal-500' : 'bg-gray-200' }}">
                        <span id="toggle-dot-{{ $city->id }}" class="inline-block w-3.5 h-3.5 rounded-full bg-white shadow transform transition-transform {{ $city->is_active ? 'translate-x-4' : 'translate-x-0.5' }}"></span>
                    </button>

                    <span id="city-country-{{ $city->id }}"
                          class="text-xs text-gray-500 font-medium min-w-[80px]">
                        @if($city->country)
                            {{ $city->country->flag ? $city->country->flag . ' ' : '' }}{{ $city->country->name }}
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </span>

                    {{-- Actions --}}
                    <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="startCityEdit(this.closest('.city-row').dataset.id)"
                                id="city-edit-{{ $city->id }}"
                                title="Modifier"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                        </button>
                        <button onclick="submitCityForm(this.closest('.city-row').dataset.id)"
                                id="city-save-{{ $city->id }}"
                                title="Enregistrer"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-teal-600 hover:bg-teal-50 transition hidden">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="m5 12 5 5L20 7"/>
                            </svg>
                        </button>
                        <button onclick="cancelCityEdit(this.closest('.city-row').dataset.id)"
                                id="city-cancel-{{ $city->id }}"
                                title="Annuler"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 transition hidden">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('admin.super.cities.destroy', $city) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="button"
                                    data-name="{{ $city->name }}"
                                    onclick="swalDelete(this, this.dataset.name)"
                                    title="Supprimer"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                    <path d="M10 11v6M14 11v6"/>
                                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        @empty
        <div class="px-5 py-16 text-center">
            <div class="w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center mx-auto mb-3">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="1.5">
                    <circle cx="12" cy="10" r="3"/>
                    <path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/>
                </svg>
            </div>
            @if(request()->hasAny(['search','country_id']))
            <p class="text-sm font-semibold text-gray-400">Aucun résultat</p>
            <p class="text-xs text-gray-300 mt-1">Modifiez les filtres de recherche.</p>
            @else
            <p class="text-sm font-semibold text-gray-400">Aucune région pour l'instant</p>
            <p class="text-xs text-gray-300 mt-1">Créez votre première région via le formulaire.</p>
            @endif
        </div>
        @endforelse
        </div>

        {{-- Pagination --}}
        @if($cities->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-400">
                Page <span class="font-semibold text-gray-700">{{ $cities->currentPage() }}</span> sur {{ $cities->lastPage() }}
                <span class="text-gray-300 ml-1">— {{ $cities->total() }} régions</span>
            </p>
            <div class="flex items-center gap-1">
                @if($cities->onFirstPage())
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-200 cursor-not-allowed border border-gray-100">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </span>
                @else
                <a href="{{ $cities->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-teal-50 hover:text-teal-600 border border-gray-100 hover:border-teal-200 transition">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </a>
                @endif

                @foreach($cities->getUrlRange(max(1,$cities->currentPage()-2), min($cities->lastPage(),$cities->currentPage()+2)) as $page => $url)
                @if($page == $cities->currentPage())
                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white bg-teal-500">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-medium text-gray-500 hover:bg-gray-100 border border-gray-100 transition">{{ $page }}</a>
                @endif
                @endforeach

                @if($cities->hasMorePages())
                <a href="{{ $cities->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-teal-50 hover:text-teal-600 border border-gray-100 hover:border-teal-200 transition">
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
        <div class="px-5 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#F0FDFA,#CCFBF1);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white shadow-sm flex items-center justify-center">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-teal-900">Nouvelle région</p>
                    <p class="text-[10px] text-teal-500 font-medium">Ajouter une ville ou région</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.super.cities.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Nom de la région</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       placeholder="ex: Casablanca"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition placeholder-gray-300">
                @error('name')
                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            <div>
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Pays</label>
                <select name="country_id" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition bg-white text-gray-700">
                    <option value="">— Sélectionner —</option>
                    @foreach($countries as $c)
                    <option value="{{ $c->id }}" {{ old('country_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->flag ? $c->flag . ' ' : '' }}{{ $c->name }}
                    </option>
                    @endforeach
                </select>
                @error('country_id')
                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white flex items-center justify-center gap-2 transition hover:opacity-90 active:scale-[.98]"
                    style="background:linear-gradient(135deg,#0D9488,#0F766E);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Créer la région
            </button>
        </form>

        {{-- Quick stats --}}
        <div class="px-5 pb-5">
            <div class="rounded-xl border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-3.5 py-2 border-b border-gray-50">
                    <span class="text-[10px] text-gray-400">Total régions</span>
                    <span class="text-xs font-bold text-gray-700">{{ $stats['total'] }}</span>
                </div>
                <div class="flex items-center justify-between px-3.5 py-2 border-b border-gray-50">
                    <span class="text-[10px] text-gray-400">Actives</span>
                    <span class="text-xs font-bold text-teal-600">{{ $stats['active'] }}</span>
                </div>
                <div class="flex items-center justify-between px-3.5 py-2 border-b border-gray-50">
                    <span class="text-[10px] text-gray-400">Inactives</span>
                    <span class="text-xs font-bold text-gray-400">{{ $stats['inactive'] }}</span>
                </div>
                <div class="flex items-center justify-between px-3.5 py-2">
                    <span class="text-[10px] text-gray-400">Pays couverts</span>
                    <span class="text-xs font-bold text-indigo-600">{{ $stats['countries'] }}</span>
                </div>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function startCityEdit(id) {
    document.getElementById('city-name-' + id).classList.add('hidden');
    document.getElementById('city-country-' + id).classList.add('hidden');
    const form = document.getElementById('city-form-' + id);
    form.classList.remove('hidden');
    form.classList.add('flex');
    document.getElementById('city-edit-' + id).classList.add('hidden');
    document.getElementById('city-save-' + id).classList.remove('hidden');
    document.getElementById('city-cancel-' + id).classList.remove('hidden');
    form.querySelector('input').focus();
}

function cancelCityEdit(id) {
    document.getElementById('city-name-' + id).classList.remove('hidden');
    document.getElementById('city-country-' + id).classList.remove('hidden');
    const form = document.getElementById('city-form-' + id);
    form.classList.add('hidden');
    form.classList.remove('flex');
    document.getElementById('city-edit-' + id).classList.remove('hidden');
    document.getElementById('city-save-' + id).classList.add('hidden');
    document.getElementById('city-cancel-' + id).classList.add('hidden');
}

function submitCityForm(id) {
    document.getElementById('city-form-' + id).submit();
}

function toggleCity(btn) {
    const id      = btn.dataset.id;
    const name    = btn.dataset.name;
    const url     = btn.dataset.url;
    const dot     = document.getElementById('toggle-dot-' + id);
    const isActive = btn.dataset.active === '1';

    btn.disabled = true;

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-HTTP-Method-Override': 'PATCH',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: '_method=PATCH',
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        if (!data.success) throw new Error();

        btn.dataset.active = data.is_active ? '1' : '0';
        btn.title = data.is_active ? 'Désactiver' : 'Activer';

        if (data.is_active) {
            btn.classList.replace('bg-gray-200', 'bg-teal-500');
            dot.classList.replace('translate-x-0.5', 'translate-x-4');
        } else {
            btn.classList.replace('bg-teal-500', 'bg-gray-200');
            dot.classList.replace('translate-x-4', 'translate-x-0.5');
        }

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: data.is_active ? 'success' : 'info',
            title: data.message,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            customClass: { popup: 'swal-lx-popup' },
        });
    })
    .catch(() => {
        btn.disabled = false;
        Swal.fire({ icon: 'error', title: 'Erreur', text: 'Impossible de modifier le statut de la région.' });
    });
}
</script>
@endpush
@endsection
