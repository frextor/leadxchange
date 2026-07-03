@extends('admin.layouts.admin')
@section('title', 'Libellés des permissions')
@section('page-title', 'Plans')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Plans</p>
        <h1 class="text-2xl font-bold text-gray-900">Libellés des permissions</h1>
        <p class="text-sm text-gray-400 mt-1">
            Renommez l'affichage de chaque permission sans changer la clé technique.
        </p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.super.plans.permissions') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
            ← Permissions
        </a>
        <button form="labels-form" type="submit"
                class="px-5 py-2 rounded-xl text-sm font-bold text-white hover:opacity-90 transition"
                style="background:#4338CA;">
            Enregistrer
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Info banner --}}
<div class="mb-6 flex items-start gap-3 bg-indigo-50 border border-indigo-100 text-indigo-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
    <p class="text-indigo-700">
        La <span class="font-mono text-xs bg-indigo-100 px-1 rounded font-semibold">clé technique</span> ne change jamais —
        c'est elle qui contrôle l'accès dans le code. Seul le libellé visible est modifiable.
    </p>
</div>

<form id="labels-form" method="POST" action="{{ route('admin.super.plans.permission-labels.update') }}">
@csrf
@method('PUT')

<div class="space-y-8">
@foreach($grouped as $category => $defs)

{{-- Category section --}}
<div>
    {{-- Section title --}}
    <div class="flex items-center gap-3 mb-3">
        <div class="w-1 h-5 rounded-full" style="background:#4338CA;"></div>
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">{{ $category }}</h2>
        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
            {{ count($defs) }} permission{{ count($defs) > 1 ? 's' : '' }}
        </span>
    </div>

    {{-- Permission cards grid --}}
    <div class="grid grid-cols-1 gap-3">
    @foreach($defs as $def)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex gap-6 items-start hover:border-indigo-100 transition">

        {{-- Left: key chip + type badge --}}
        <div class="flex-shrink-0 w-64">
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Clé technique</p>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 font-mono text-[11px] text-gray-600 bg-gray-100 px-2.5 py-1.5 rounded-lg border border-gray-200 leading-none">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    {{ $def->key }}
                </span>
            </div>
            <div class="mt-2">
                @if($def->type === 'bool')
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Oui / Non</span>
                @else
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Nombre</span>
                @endif
            </div>
        </div>

        {{-- Divider --}}
        <div class="w-px self-stretch bg-gray-100 flex-shrink-0"></div>

        {{-- Right: editable fields --}}
        <div class="flex-1 grid grid-cols-2 gap-4">

            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 block">
                    Libellé affiché
                </label>
                <input type="text"
                       name="label_{{ $def->id }}"
                       value="{{ old("label_{$def->id}", $def->label) }}"
                       required
                       maxlength="200"
                       placeholder="Nom affiché aux utilisateurs"
                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-800 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
            </div>

            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 block">
                    Catégorie
                </label>
                <input type="text"
                       name="category_{{ $def->id }}"
                       value="{{ old("category_{$def->id}", $def->category) }}"
                       maxlength="100"
                       placeholder="Nom de la catégorie"
                       class="w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-600 placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                <p class="text-[10px] text-gray-400 mt-1">Modifiez pour réorganiser les groupes</p>
            </div>

        </div>

    </div>
    @endforeach
    </div>

</div>
@endforeach
</div>

<div class="mt-8 flex items-center justify-between py-4 border-t border-gray-100">
    <p class="text-xs text-gray-400">{{ $grouped->flatten()->count() }} permissions au total</p>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.super.plans.permissions') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
            Annuler
        </a>
        <button type="submit"
                class="px-6 py-2.5 rounded-xl text-sm font-bold text-white hover:opacity-90 transition"
                style="background:#4338CA;">
            Enregistrer les libellés
        </button>
    </div>
</div>

</form>

@endsection
