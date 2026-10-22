@extends('admin.layouts.admin')
@section('title', 'Paramètres — Titres du dashboard')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Titres du dashboard</h1>
        <p class="text-sm text-gray-400 mt-1">Personnalisez les titres des blocs affichés sur la page d'accueil des membres.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

@php
    $t = fn(string $k) => old($k, $settings->get($k)?->value ?? $defaults[$k]);
    $fields = [
        'dashboard_title_reseau'       => 'Réseau à développer',
        'dashboard_title_activite'     => 'Mon activité',
        'dashboard_title_opportunites' => 'Opportunités disponibles',
        'dashboard_title_todo'         => 'À faire',
        'dashboard_title_events'       => 'Événements à venir',
        'dashboard_title_groups'       => 'Groupes populaires',
    ];
@endphp

<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.super.settings.dashboard-titles.update') }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            @foreach ($fields as $key => $hint)
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5" for="{{ $key }}">
                    {{ $hint }} <span class="text-red-400">*</span>
                </label>
                <input type="text" name="{{ $key }}" id="{{ $key }}" required maxlength="60"
                       value="{{ $t($key) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error($key) border-red-300 @else border-gray-200 @enderror">
                @error($key)<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            @endforeach
        </div>

        <div class="flex justify-end mt-5">
            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                Enregistrer
            </button>
        </div>
    </form>
</div>

@endsection
