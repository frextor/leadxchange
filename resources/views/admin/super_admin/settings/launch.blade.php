@extends('admin.layouts.admin')
@section('title', 'Lancement de la plateforme')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Paramètres</p>
        <h1 class="text-2xl font-bold text-gray-900">Lancement de la plateforme</h1>
        <p class="text-sm text-gray-400 mt-1">Avant l'ouverture officielle, seule la page d'inscription est accessible aux visiteurs — les fonctionnalités du site restent verrouillées jusqu'à validation ci-dessous.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Statut & bascule --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:{{ $launched ? '#ECFDF5' : '#FFFBEB' }};">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $launched ? '#059669' : '#D97706' }}" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"/><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Statut de la plateforme</p>
                <p class="text-xs text-gray-400">{{ $pendingCount }} compte{{ $pendingCount > 1 ? 's' : '' }} inscrit{{ $pendingCount > 1 ? 's' : '' }} au total</p>
            </div>
        </div>

        <div class="p-4 rounded-xl border mb-5 {{ $launched ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $launched ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' }}"></span>
                <span class="text-sm font-semibold {{ $launched ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $launched ? 'Plateforme lancée — accès complet actif' : 'Mode pré-lancement — inscription uniquement' }}
                </span>
            </div>
            @if(!$launched)
            <p class="text-xs text-amber-600 mt-1.5">Les utilisateurs peuvent créer un compte mais voient une page d'attente à la place du site.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.super.settings.launch.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Confirmer le lancement officiel</p>
                    <p class="text-xs text-gray-400 mt-0.5">Active l'accès complet à la plateforme pour tous les inscrits</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="platform_launched" value="0">
                    <input type="checkbox" name="platform_launched" value="1"
                           class="sr-only peer" {{ $launched ? 'checked' : '' }}>
                    <div class="w-10 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Message affiché sur la page d'attente</label>
                <textarea name="prelaunch_message" rows="3"
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition resize-none"
                          placeholder="Votre inscription est confirmée. Nous vous préviendrons par email dès l'ouverture officielle.">{{ $message }}</textarea>
            </div>

            <button type="submit"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                    style="background:linear-gradient(135deg,#059669,#047857);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
                Enregistrer
            </button>
        </form>
    </div>

    {{-- Explication --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <p class="text-sm font-bold text-gray-900 mb-4">Comment ça fonctionne</p>
        <ul class="space-y-3 text-xs text-gray-500">
            <li class="flex gap-2.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Tant que le lancement n'est pas confirmé, l'inscription (<code class="text-[11px] bg-gray-100 px-1 rounded">/register</code>) reste ouverte à tous — c'est la liste d'attente.</span>
            </li>
            <li class="flex gap-2.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Toute autre page (dashboard, profil, leads…) affiche une page d'attente à ces utilisateurs — ils ne peuvent pas encore utiliser la plateforme.</span>
            </li>
            <li class="flex gap-2.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Les comptes admin / super admin ne sont jamais bloqués — utile pour tester le site avant l'ouverture.</span>
            </li>
            <li class="flex gap-2.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span>Une fois prêt en production, active la bascule ci-contre : <strong>tous les comptes déjà inscrits</strong> ont immédiatement accès complet, sans rien refaire.</span>
            </li>
        </ul>
    </div>

</div>

{{-- Aperçu page d'attente --}}
<div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm font-bold text-gray-900">Aperçu — Page d'attente vue par les inscrits</p>
    </div>
    <div class="rounded-xl overflow-hidden border border-gray-200" style="height:340px;">
        <iframe srcdoc="{{ view('prelaunch', ['message' => $message])->render() }}"
                class="w-full h-full border-0" title="Aperçu page d'attente"></iframe>
    </div>
</div>

@endsection
