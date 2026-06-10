@extends('admin.layouts.admin')

@section('title', 'Centres d\'intérêt')
@section('page-title', 'Centres d\'intérêt')
@section('page-subtitle', $interests->total() . ' intérêts')

@section('content')
<div class="grid gap-5 lg:grid-cols-[1fr_320px] items-start">

    {{-- List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    <th class="px-5 py-3 text-left">Intérêt</th>
                    <th class="px-4 py-3 text-left">Utilisateurs</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($interests as $interest)
                <tr class="hover:bg-gray-50 transition" id="int-row-{{ $interest->id }}">
                    <td class="px-5 py-3">
                        <div id="int-display-{{ $interest->id }}" class="flex items-center gap-2">
                            <span class="text-xl">{{ $interest->icon }}</span>
                            <span class="font-medium text-gray-900">{{ $interest->name }}</span>
                        </div>
                        <form id="int-form-{{ $interest->id }}" method="POST"
                              action="{{ route('admin.super.interests.update', $interest) }}" class="hidden flex gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="icon" value="{{ $interest->icon }}" maxlength="10" placeholder="🎯" class="border border-teal-400 rounded-lg px-2 py-1 text-sm w-14 focus:outline-none">
                            <input type="text" name="name" value="{{ $interest->name }}" required class="border border-teal-400 rounded-lg px-2 py-1 text-sm w-44 focus:outline-none">
                        </form>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $interest->users_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button onclick="startIntEdit({{ $interest->id }})" id="int-edit-{{ $interest->id }}" class="text-xs font-medium text-indigo-600 hover:underline">Modifier</button>
                            <button onclick="document.getElementById('int-form-{{ $interest->id }}').submit()" id="int-save-{{ $interest->id }}" class="text-xs font-medium text-teal-600 hover:underline hidden">Enregistrer</button>
                            <button onclick="cancelIntEdit({{ $interest->id }})" id="int-cancel-{{ $interest->id }}" class="text-xs font-medium text-gray-400 hover:underline hidden">Annuler</button>
                            <form method="POST" action="{{ route('admin.super.interests.destroy', $interest) }}">
                                @csrf @method('DELETE')
                                <button type="button"
                                        data-name="{{ $interest->name }}"
                                        onclick="swalDelete(this, this.dataset.name)"
                                        class="text-xs font-medium text-red-500 hover:underline">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-5 py-10 text-center text-sm text-gray-400">Aucun intérêt.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($interests->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $interests->links() }}</div>
        @endif
    </div>

    {{-- Add form --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-4">
        <h3 class="text-sm font-bold text-gray-900 mb-4">Ajouter un intérêt</h3>
        <form method="POST" action="{{ route('admin.super.interests.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Icône (emoji)</label>
                <input type="text" name="icon" value="{{ old('icon') }}" maxlength="10"
                       placeholder="ex: 🎯"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       placeholder="ex: Intelligence artificielle"
                       class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-teal-400">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold text-white" style="background:#1E8F88;">Créer l'intérêt</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function startIntEdit(id) {
    document.getElementById('int-display-' + id).classList.add('hidden');
    document.getElementById('int-form-' + id).classList.remove('hidden');
    document.getElementById('int-form-' + id).classList.add('flex');
    document.getElementById('int-edit-' + id).classList.add('hidden');
    document.getElementById('int-save-' + id).classList.remove('hidden');
    document.getElementById('int-cancel-' + id).classList.remove('hidden');
}
function cancelIntEdit(id) {
    document.getElementById('int-display-' + id).classList.remove('hidden');
    document.getElementById('int-form-' + id).classList.add('hidden');
    document.getElementById('int-form-' + id).classList.remove('flex');
    document.getElementById('int-edit-' + id).classList.remove('hidden');
    document.getElementById('int-save-' + id).classList.add('hidden');
    document.getElementById('int-cancel-' + id).classList.add('hidden');
}
</script>
@endpush
@endsection
