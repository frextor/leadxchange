@extends('admin.layouts.admin')
@section('title', 'Nouvelle licence Entreprise')

@section('content')
<div class="p-6 max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('admin.super.enterprise.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">← Retour aux licences</a>
        <h1 class="text-xl font-bold text-gray-900 mt-2">Créer une licence Entreprise</h1>
        <p class="text-sm text-gray-500 mt-0.5">Attribuez un pack multi-licences à un compte titulaire.</p>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    @if(!$enterprisePlan)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 text-sm text-amber-800 mb-6">
        <strong>Aucun plan Entreprise actif.</strong> Activez d'abord le plan "enterprise" dans la gestion des plans.
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.super.enterprise.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                    Titulaire du pack <span class="text-red-500">*</span>
                </label>
                <select name="holder_user_id" required
                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                        style="--tw-ring-color:#6366F1;">
                    <option value="">— Sélectionner un utilisateur —</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('holder_user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Ce compte pourra inviter les autres membres.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                        Nombre de licences <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="seats_total" min="2" max="500"
                           value="{{ old('seats_total', 10) }}" required
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#6366F1;">
                    <p class="text-xs text-gray-400 mt-1">Min. 2 (titulaire + 1 membre).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                        Expiration
                    </label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#6366F1;">
                    <p class="text-xs text-gray-400 mt-1">Laisser vide = sans expiration.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Notes internes</label>
                <textarea name="notes" rows="3" placeholder="Nom de l'entreprise, conditions contractuelles…"
                          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 resize-none"
                          style="--tw-ring-color:#6366F1;">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('admin.super.enterprise.index') }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit" {{ !$enterprisePlan ? 'disabled' : '' }}
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 disabled:opacity-40"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    Créer et attribuer la licence
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
