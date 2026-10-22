@extends('admin.layouts.admin')
@section('title', "Paramètres — Textes de l'interface")
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Textes de l'interface</h1>
        <p class="text-sm text-gray-400 mt-1">Personnalisez les boutons et messages affichés aux membres. Laissez un champ vide pour revenir au texte par défaut.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.super.settings.ui-texts.update') }}">
        @csrf @method('PUT')

        @php $oldTexts = old('texts', []); @endphp
        @foreach ($registry as $section => $items)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">{{ $section }}</p>
            <div class="space-y-4">
                @foreach ($items as $key => $def)
                @php
                    $current = $oldTexts[$key] ?? $saved->get('ui_text.' . $key)?->value;
                    $isCustom = $current !== null;
                @endphp
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5" for="ui_{{ $loop->parent->index }}_{{ $loop->index }}">
                        {{ $def['label'] }}
                    </label>
                    <input type="text" name="texts[{{ $key }}]" id="ui_{{ $loop->parent->index }}_{{ $loop->index }}" maxlength="300"
                           value="{{ $current ?? '' }}"
                           placeholder="{{ $def['default'] }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error($key) border-red-300 @else border-gray-200 @enderror">
                    <p class="text-[11px] mt-1 {{ $isCustom ? 'text-indigo-500' : 'text-gray-400' }}">
                        {{ $isCustom ? 'Personnalisé — vide = retour au texte par défaut ci-dessus.' : 'Par défaut : "' . $def['default'] . '"' }}
                    </p>
                    @error($key)<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end mt-2 mb-8">
            <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                Enregistrer
            </button>
        </div>
    </form>
</div>

@endsection
