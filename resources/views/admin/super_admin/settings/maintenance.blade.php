@extends('admin.layouts.admin')
@section('title', 'Maintenance')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Paramètres</p>
        <h1 class="text-2xl font-bold text-gray-900">Maintenance & Disponibilité</h1>
        <p class="text-sm text-gray-400 mt-1">CGU §5.3 — Service maintenu 24h/24, 7j/7. Annoncez les maintenances 48h à l'avance.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Bandeau d'annonce --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Bandeau d'annonce</p>
                <p class="text-xs text-gray-400">Affiché en haut de toutes les pages utilisateurs</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.super.settings.maintenance.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Activer le bandeau</p>
                    <p class="text-xs text-gray-400 mt-0.5">Visible immédiatement par tous les utilisateurs connectés</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="maintenance_banner_enabled" value="0">
                    <input type="checkbox" name="maintenance_banner_enabled" value="1"
                           class="sr-only peer"
                           {{ $settings->get('maintenance_banner_enabled')?->value == '1' ? 'checked' : '' }}>
                    <div class="w-10 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Message du bandeau</label>
                <textarea name="maintenance_banner_message" rows="3"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-50 transition resize-none"
                          placeholder="Maintenance programmée le [DATE] de [H1] à [H2]. Le service sera brièvement indisponible.">{{ $settings->get('maintenance_banner_message')?->value ?? '' }}</textarea>
                <p class="text-xs text-gray-400 mt-1.5">Ce message s'affiche en bandeau sombre en haut de chaque page.</p>
            </div>

            <button type="submit"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                    style="background:#D97706;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
                Enregistrer
            </button>
        </form>
    </div>

    {{-- Mode maintenance Laravel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Mode maintenance complet</p>
                <p class="text-xs text-gray-400">Affiche la page 503 à tous les visiteurs</p>
            </div>
        </div>

        @php $isDown = app()->isDownForMaintenance(); @endphp

        <div class="p-4 rounded-xl border mb-4 {{ $isDown ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-100' }}">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $isDown ? 'bg-red-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                <span class="text-sm font-semibold {{ $isDown ? 'text-red-700' : 'text-emerald-700' }}">
                    {{ $isDown ? 'Application en maintenance' : 'Application en ligne' }}
                </span>
            </div>
            @if($isDown)
            <p class="text-xs text-red-600 mt-1.5">La page 503 s'affiche actuellement à tous les visiteurs.</p>
            @endif
        </div>

        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.super.settings.maintenance.down') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold border border-red-200 text-red-600 hover:bg-red-50 transition {{ $isDown ? 'opacity-40 cursor-not-allowed' : '' }}"
                        {{ $isDown ? 'disabled' : '' }}>
                    Activer la maintenance
                </button>
            </form>
            <form method="POST" action="{{ route('admin.super.settings.maintenance.up') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-xl text-sm font-semibold border border-emerald-200 text-emerald-700 hover:bg-emerald-50 transition {{ !$isDown ? 'opacity-40 cursor-not-allowed' : '' }}"
                        {{ !$isDown ? 'disabled' : '' }}>
                    Remettre en ligne
                </button>
            </form>
        </div>

        <p class="text-xs text-gray-400 mt-3">⚠ Le mode maintenance coupe l'accès à <strong>tous</strong> les utilisateurs sauf les IPs autorisées.</p>
    </div>

</div>

{{-- Aperçu page 503 --}}
<div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm font-bold text-gray-900">Aperçu — Page de maintenance (503)</p>
        <a href="{{ route('admin.super.settings.maintenance.preview') }}" target="_blank"
           class="flex items-center gap-1.5 text-xs font-semibold text-indigo-500 hover:text-indigo-700 transition">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Ouvrir en plein écran
        </a>
    </div>
    <div class="rounded-xl overflow-hidden border border-gray-200" style="height:300px;">
        <iframe src="{{ route('admin.super.settings.maintenance.preview') }}"
                class="w-full h-full border-0" title="Aperçu page 503"></iframe>
    </div>
</div>

@endsection
