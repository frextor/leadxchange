@extends('admin.layouts.admin')

@section('title', 'Pays')
@section('page-title', 'Pays')
@section('page-subtitle', $countries->total() . ' pays')

@section('content')
<div class="grid gap-5 lg:grid-cols-[1fr_320px] items-start">

    {{-- List --}}
    <div class="space-y-4">
        <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex gap-3 items-end">
            <div class="flex-1">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom du pays…"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#1E8F88;">Filtrer</button>
                @if(request('search'))
                <a href="{{ route('admin.super.countries.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50">Reset</a>
                @endif
            </div>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Pays</th>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Villes</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($countries as $country)
                    <tr class="hover:bg-gray-50 transition" id="country-row-{{ $country->id }}">
                        <td class="px-5 py-3">
                            <div id="country-display-{{ $country->id }}" class="flex items-center gap-2">
                                <span class="text-xl">{{ $country->flag }}</span>
                                <span class="font-medium text-gray-900">{{ $country->name }}</span>
                            </div>
                            <form id="country-form-{{ $country->id }}" method="POST"
                                  action="{{ route('admin.super.countries.update', $country) }}" class="hidden flex gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="flag" value="{{ $country->flag }}" maxlength="10" placeholder="🇫🇷" class="border border-teal-400 rounded-lg px-2 py-1 text-sm w-16 focus:outline-none">
                                <input type="text" name="name" value="{{ $country->name }}" required class="border border-teal-400 rounded-lg px-2 py-1 text-sm w-36 focus:outline-none">
                                <input type="text" name="code" value="{{ $country->code }}" required maxlength="2" placeholder="FR" class="border border-teal-400 rounded-lg px-2 py-1 text-sm w-14 focus:outline-none uppercase">
                            </form>
                        </td>
                        <td class="px-4 py-3">
                            <span id="country-code-{{ $country->id }}" class="px-2 py-1 rounded text-xs font-mono font-bold bg-gray-100 text-gray-600">{{ strtoupper($country->code) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $country->cities_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button onclick="startCountryEdit({{ $country->id }})" id="country-edit-{{ $country->id }}" class="text-xs font-medium text-indigo-600 hover:underline">Modifier</button>
                                <button onclick="document.getElementById('country-form-{{ $country->id }}').submit()" id="country-save-{{ $country->id }}" class="text-xs font-medium text-teal-600 hover:underline hidden">Enregistrer</button>
                                <button onclick="cancelCountryEdit({{ $country->id }})" id="country-cancel-{{ $country->id }}" class="text-xs font-medium text-gray-400 hover:underline hidden">Annuler</button>
                                @if($country->cities_count === 0)
                                <form method="POST" action="{{ route('admin.super.countries.destroy', $country) }}">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                            data-name="{{ $country->name }}"
                                            onclick="swalDelete(this, this.dataset.name)"
                                            class="text-xs font-medium text-red-500 hover:underline">Supprimer</button>
                                </form>
                                @else
                                <span class="text-xs text-gray-300" title="{{ $country->cities_count }} villes liées">Supprimer</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400">Aucun pays trouvé.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($countries->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">{{ $countries->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Add form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-4">
        <h3 class="text-sm font-bold text-gray-900 mb-4">Ajouter un pays</h3>
        <form method="POST" action="{{ route('admin.super.countries.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Drapeau (emoji)</label>
                <input type="text" name="flag" value="{{ old('flag') }}" maxlength="10"
                       placeholder="ex: 🇫🇷"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom du pays</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       placeholder="ex: France"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Code ISO (2 lettres)</label>
                <input type="text" name="code" value="{{ old('code') }}" required maxlength="2"
                       placeholder="ex: FR" class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400 uppercase">
                @error('code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#1E8F88;">Créer le pays</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function startCountryEdit(id) {
    document.getElementById('country-display-' + id).classList.add('hidden');
    document.getElementById('country-code-' + id).classList.add('hidden');
    document.getElementById('country-form-' + id).classList.remove('hidden');
    document.getElementById('country-form-' + id).classList.add('flex');
    document.getElementById('country-edit-' + id).classList.add('hidden');
    document.getElementById('country-save-' + id).classList.remove('hidden');
    document.getElementById('country-cancel-' + id).classList.remove('hidden');
}
function cancelCountryEdit(id) {
    document.getElementById('country-display-' + id).classList.remove('hidden');
    document.getElementById('country-code-' + id).classList.remove('hidden');
    document.getElementById('country-form-' + id).classList.add('hidden');
    document.getElementById('country-form-' + id).classList.remove('flex');
    document.getElementById('country-edit-' + id).classList.remove('hidden');
    document.getElementById('country-save-' + id).classList.add('hidden');
    document.getElementById('country-cancel-' + id).classList.add('hidden');
}
</script>
@endpush
@endsection
