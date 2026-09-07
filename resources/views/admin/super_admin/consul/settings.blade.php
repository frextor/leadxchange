@extends('admin.layouts.admin')
@section('title', 'Paramètres Consul')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Consuls</p>
        <h1 class="text-2xl font-bold text-gray-900">Paramètres Consul</h1>
        <p class="text-sm text-gray-400 mt-1">Configuration des règles de gestion des demandes Consul.</p>
    </div>
    <a href="{{ route('admin.super.consuls.manage') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Retour aux Consuls
    </a>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.super.consuls.settings.update') }}">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 max-w-xl">

        <h2 class="text-sm font-bold text-gray-700 mb-4 flex items-center gap-2">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2F44E0" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Délai avant re-soumission après refus
        </h2>

        <p class="text-sm text-gray-500 mb-5 leading-relaxed">
            Quand une demande Consul est refusée, l'utilisateur doit attendre ce nombre de jours avant de pouvoir soumettre une nouvelle demande.
        </p>

        <div class="flex items-center gap-4">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Délai (en jours)</label>
                <input type="number" name="consul_rejection_cooldown_days"
                       value="{{ old('consul_rejection_cooldown_days', $cooldownDays) }}"
                       min="1" max="365"
                       class="w-full h-10 px-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-teal-400 transition @error('consul_rejection_cooldown_days') border-red-400 @enderror">
                @error('consul_rejection_cooldown_days')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2 mt-5">
                @foreach([7, 14, 30, 60, 90] as $preset)
                <button type="button"
                        onclick="document.querySelector('[name=consul_rejection_cooldown_days]').value = {{ $preset }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition
                               {{ $cooldownDays == $preset ? 'border-teal-400 bg-teal-50 text-teal-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                    {{ $preset }}j
                </button>
                @endforeach
            </div>
        </div>

        <div class="mt-6 pt-5 border-t border-gray-100 flex justify-end">
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                    style="background:#2F44E0;">
                Enregistrer
            </button>
        </div>

    </div>
</form>

@endsection
