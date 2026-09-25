@extends('admin.layouts.admin')
@section('title', 'Essai Full Access')
@section('page-title', 'Paramètres')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Paramètres</p>
        <h1 class="text-2xl font-bold text-gray-900">Essai « Full Access »</h1>
        <p class="text-sm text-gray-400 mt-1">Offre un accès complet (équivalent Entreprise) pendant une durée définie depuis la date d'inscription, avant retour automatique au plan Basic.</p>
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
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:{{ $enabled ? '#ECFDF5' : '#FFFBEB' }};">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $enabled ? '#059669' : '#D97706' }}" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Statut de la promotion</p>
                <p class="text-xs text-gray-400">
                    {{ $activeTrialCount }} membre{{ $activeTrialCount > 1 ? 's' : '' }} actuellement en essai ·
                    {{ $lapsedTrialCount }} déjà revenu{{ $lapsedTrialCount > 1 ? 's' : '' }} en Basic
                </p>
            </div>
        </div>

        <div class="p-4 rounded-xl border mb-5 {{ $enabled ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }}">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $enabled ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                <span class="text-sm font-semibold {{ $enabled ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $enabled ? "Essai actif — {$months} mois depuis l'inscription" : 'Essai désactivé' }}
                </span>
            </div>
            @if($enabled)
            <p class="text-xs text-emerald-600 mt-1.5">Calculé rétroactivement : un membre inscrit il y a plus de {{ $months }} mois est déjà repassé en Basic.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.super.settings.trial.update') }}" class="space-y-4">
            @csrf @method('PUT')

            <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Activer l'essai Full Access</p>
                    <p class="text-xs text-gray-400 mt-0.5">Accès Enterprise offert depuis la date d'inscription de chaque membre</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="trial_enabled" value="0">
                    <input type="checkbox" name="trial_enabled" value="1"
                           class="sr-only peer" {{ $enabled ? 'checked' : '' }}>
                    <div class="w-10 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Durée de l'essai (en mois)</label>
                <input type="number" name="trial_duration_months" min="1" max="24" value="{{ $months }}"
                       class="w-32 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
                <p class="text-xs text-gray-400 mt-1.5">Une modification recalcule immédiatement la date de fin d'essai de tous les membres, y compris ceux déjà inscrits.</p>
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
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <span>Pendant la durée choisie, un membre encore sur le plan Basic obtient automatiquement toutes les permissions du plan <strong>Enterprise</strong> — sans changer d'abonnement, ni recevoir de facture.</span>
            </li>
            <li class="flex gap-2.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="10"/></svg>
                <span>La durée se calcule depuis la <strong>date d'inscription réelle</strong> de chaque membre (pas depuis l'activation de cette page) — elle s'applique donc aussi aux comptes déjà créés.</span>
            </li>
            <li class="flex gap-2.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M20 7h-9m3-3-3 3 3 3"/><path d="M4 17h9m-3-3 3 3-3 3"/></svg>
                <span>Un membre qui a déjà souscrit un plan payant (Premium, Enterprise…) garde ce plan tel quel — l'essai ne s'applique que tant qu'il n'a rien acheté.</span>
            </li>
            <li class="flex gap-2.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span>Passé le délai, le retour au plan Basic est automatique — aucune tâche planifiée à surveiller, aucun abonnement à annuler.</span>
            </li>
        </ul>
    </div>

</div>

@endsection
