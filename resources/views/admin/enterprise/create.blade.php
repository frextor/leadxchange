@extends('admin.layouts.admin')
@section('title', 'Nouveau pack Entreprise')

@section('content')
<div class="p-6 max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('admin.super.enterprise.index') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">← Retour aux licences</a>
        <h1 class="text-xl font-bold text-gray-900 mt-2">Créer un pack Entreprise</h1>
        <p class="text-sm text-gray-500 mt-0.5">Le titulaire reçoit le plan <strong>Premium</strong>. Les licences sont pré-générées automatiquement et distribuées depuis « Mon équipe ».</p>
    </div>

    @if($errors->any())
    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3 text-sm">{{ $errors->first() }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.super.enterprise.store') }}" class="space-y-5">
            @csrf

            {{-- Company name --}}
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">
                    Nom de l'entreprise <span class="text-red-500">*</span>
                </label>
                <input type="text" name="company_name" required
                       value="{{ old('company_name') }}"
                       placeholder="ex : TechCorp SAS"
                       class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                       style="--tw-ring-color:#6366F1;">
                <p class="text-xs text-gray-400 mt-1">Affiché comme « Premium — TechCorp SAS » sur le profil des membres.</p>
            </div>

            {{-- Holder --}}
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
                <p class="text-xs text-gray-400 mt-1">Ce compte peut inviter les autres membres depuis « Mon équipe ».</p>
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
                    <p class="text-xs text-gray-400 mt-1">1 pour le titulaire + N-1 membres.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Expiration</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                           class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2"
                           style="--tw-ring-color:#6366F1;">
                    <p class="text-xs text-gray-400 mt-1">Vide = sans expiration.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1.5">Notes internes</label>
                <textarea name="notes" rows="2" placeholder="Contrat, conditions, remarques…"
                          class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 resize-none"
                          style="--tw-ring-color:#6366F1;">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('admin.super.enterprise.index') }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Créer et générer les licences
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
