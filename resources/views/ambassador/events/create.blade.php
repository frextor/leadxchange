@extends('ambassador.layouts.ambassador')
@section('title', 'Créer un événement')
@section('page-title', 'Nouvel événement')
@section('page-subtitle', 'Créer un événement pour votre région')

@section('content')

<div class="max-w-3xl">
<form method="POST" action="{{ route('ambassador.events.store') }}" class="space-y-5">
    @csrf

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-sm font-bold text-slate-800 mb-5">Informations générales</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Titre *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Type *</label>
                    <select name="type" required class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                        <option value="in_person" @selected(old('type')=='in_person')>Présentiel</option>
                        <option value="virtual" @selected(old('type')=='virtual')>Virtuel</option>
                        <option value="hybrid" @selected(old('type')=='hybrid')>Hybride</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Secteur</label>
                    <select name="sector_id" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                        <option value="">— Tous secteurs —</option>
                        @foreach($sectors as $sector)
                        <option value="{{ $sector->id }}" @selected(old('sector_id')==$sector->id)>{{ $sector->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Description</label>
                <textarea name="description" rows="4"
                          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300 resize-none">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-sm font-bold text-slate-800 mb-5">Lieu & Dates</h2>

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Date de début *</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Date de fin *</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Lieu / Adresse</label>
                <input type="text" name="location" value="{{ old('location') }}"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Lien de réunion (visio)</label>
                <input type="url" name="meeting_link" value="{{ old('meeting_link') }}"
                       placeholder="https://meet.google.com/..."
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Ville</label>
                    <select name="city_id" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                        <option value="">— Sélectionner —</option>
                        @foreach($cities as $city)
                        <option value="{{ $city->id }}" @selected(old('city_id')==$city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Max. participants</label>
                    <input type="number" name="max_attendees" value="{{ old('max_attendees') }}" min="1"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Prix (€) — laisser vide si gratuit</label>
                <input type="number" name="price" value="{{ old('price') }}" min="0" step="0.01"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-300">
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('ambassador.events.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Retour</a>
        <button type="submit"
                class="px-6 py-2.5 rounded-xl text-sm font-bold text-white"
                style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
            Créer l'événement
        </button>
    </div>
</form>
</div>

@endsection
