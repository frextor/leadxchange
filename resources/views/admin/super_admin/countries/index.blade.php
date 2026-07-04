@extends('admin.layouts.admin')
@section('title', 'Pays')
@section('page-title', 'Pays')

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Référentiel</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pays</h1>
        <p class="text-sm text-gray-400 mt-1">Gérez les pays et leurs codes ISO disponibles sur la plateforme.</p>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- KPI strip --}}
@php
    $totalCities = $countries->getCollection()->sum('cities_count');
@endphp
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#14A98C" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $countries->total() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Pays enregistrés</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4338CA" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $totalCities }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Villes associées</p>
        </div>
    </div>
</div>

<div class="grid gap-5 lg:grid-cols-[1fr_300px] items-start">

    {{-- Left: search + table --}}
    <div class="space-y-4">

        {{-- Search --}}
        <form method="GET" class="flex gap-3 items-center">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher un pays…"
                       class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition bg-white">
            </div>
            <button type="submit"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 flex-shrink-0"
                    style="background:#4338CA;">
                Filtrer
            </button>
            @if(request('search'))
            <a href="{{ route('admin.super.countries.index') }}"
               class="px-4 py-2.5 rounded-xl text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50 transition flex-shrink-0">
                Reset
            </a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pays</th>
                            <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Code ISO</th>
                            <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Villes</th>
                            <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($countries as $country)
                        <tr class="hover:bg-gray-50/50 transition group" id="country-row-{{ $country->id }}">

                            {{-- Name cell --}}
                            <td class="px-5 py-3">
                                {{-- Display mode --}}
                                <div id="country-display-{{ $country->id }}" class="flex items-center gap-2.5">
                                    <span class="text-xl leading-none">{{ $country->flag }}</span>
                                    <span class="font-semibold text-gray-900">{{ $country->name }}</span>
                                </div>
                                {{-- Edit mode --}}
                                <form id="country-form-{{ $country->id }}" method="POST"
                                      action="{{ route('admin.super.countries.update', $country) }}"
                                      class="hidden items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="flag" value="{{ $country->flag }}" maxlength="10"
                                           placeholder="🇫🇷"
                                           class="w-14 h-8 text-center text-sm border-2 border-teal-300 rounded-lg focus:outline-none focus:border-teal-500 bg-teal-50">
                                    <input type="text" name="name" value="{{ $country->name }}" required
                                           class="w-40 h-8 px-2 text-sm border-2 border-teal-300 rounded-lg focus:outline-none focus:border-teal-500 bg-teal-50">
                                    <input type="text" name="code" value="{{ $country->code }}" required maxlength="2"
                                           placeholder="FR"
                                           class="w-14 h-8 px-2 text-sm font-mono uppercase border-2 border-teal-300 rounded-lg focus:outline-none focus:border-teal-500 bg-teal-50">
                                </form>
                            </td>

                            {{-- Code --}}
                            <td class="px-4 py-3">
                                <span id="country-code-{{ $country->id }}"
                                      class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-mono font-bold bg-gray-100 text-gray-600">
                                    {{ strtoupper($country->code) }}
                                </span>
                            </td>

                            {{-- Cities count --}}
                            <td class="px-4 py-3 text-center">
                                @if($country->cities_count > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                                    {{ $country->cities_count }}
                                </span>
                                @else
                                <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                {{-- View mode --}}
                                <div id="country-actions-{{ $country->id }}" class="flex items-center justify-end gap-2">
                                    <button onclick="startCountryEdit({{ $country->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Modifier
                                    </button>
                                    @if($country->cities_count === 0)
                                    <form method="POST" action="{{ route('admin.super.countries.destroy', $country) }}">
                                        @csrf @method('DELETE')
                                        <button type="button"
                                                data-name="{{ $country->name }}"
                                                onclick="swalDelete(this, this.dataset.name)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 transition">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                            Supprimer
                                        </button>
                                    </form>
                                    @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 bg-gray-50 cursor-not-allowed"
                                          title="{{ $country->cities_count }} ville(s) liée(s)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        Supprimer
                                    </span>
                                    @endif
                                </div>
                                {{-- Edit mode --}}
                                <div id="country-edit-actions-{{ $country->id }}" class="hidden items-center justify-end gap-2">
                                    <button onclick="document.getElementById('country-form-{{ $country->id }}').submit()"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-teal-500 hover:bg-teal-600 transition">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
                                        Enregistrer
                                    </button>
                                    <button onclick="cancelCountryEdit({{ $country->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        Annuler
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-14 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                </div>
                                <p class="text-sm text-gray-400">Aucun pays trouvé.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($countries->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $countries->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Right: Add form --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-4">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#14A98C" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Ajouter un pays</p>
                <p class="text-xs text-gray-400">Code ISO 3166-1 alpha-2</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.super.countries.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Drapeau <span class="text-gray-300 font-normal normal-case">(emoji)</span></label>
                <input type="text" name="flag" value="{{ old('flag') }}" maxlength="10"
                       placeholder="🇫🇷"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xl text-center focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nom du pays</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       placeholder="ex : France"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Code ISO <span class="text-gray-300 font-normal normal-case">(2 lettres)</span></label>
                <input type="text" name="code" value="{{ old('code') }}" required maxlength="2"
                       placeholder="FR"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono uppercase tracking-widest text-center focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
                @error('code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 flex items-center justify-center gap-2"
                    style="background:linear-gradient(135deg,#2DD4B0,#14A98C);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Créer le pays
            </button>
        </form>
    </div>

</div>

@push('scripts')
<script>
function startCountryEdit(id) {
    document.getElementById('country-display-' + id).classList.add('hidden');
    document.getElementById('country-code-' + id).classList.add('hidden');
    document.getElementById('country-actions-' + id).classList.add('hidden');
    const form = document.getElementById('country-form-' + id);
    form.classList.remove('hidden');
    form.classList.add('flex');
    const editActions = document.getElementById('country-edit-actions-' + id);
    editActions.classList.remove('hidden');
    editActions.classList.add('flex');
}
function cancelCountryEdit(id) {
    document.getElementById('country-display-' + id).classList.remove('hidden');
    document.getElementById('country-code-' + id).classList.remove('hidden');
    document.getElementById('country-actions-' + id).classList.remove('hidden');
    const form = document.getElementById('country-form-' + id);
    form.classList.add('hidden');
    form.classList.remove('flex');
    const editActions = document.getElementById('country-edit-actions-' + id);
    editActions.classList.add('hidden');
    editActions.classList.remove('flex');
}
</script>
@endpush
@endsection
