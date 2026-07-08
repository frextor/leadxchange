@extends('admin.layouts.admin')
@section('title', 'Catégories d\'événements')
@section('page-title', 'Catégories d\'événements')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Référentiel</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Catégories d'événements</h1>
        <p class="text-sm text-gray-400 mt-1">Gérez les catégories disponibles lors de la création d'un événement.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

<div class="mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4 w-48">
        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#EA580C" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $categories->total() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Catégories</p>
        </div>
    </div>
</div>

<div class="grid gap-5 lg:grid-cols-[1fr_300px] items-start">

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Label</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Clé</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Ordre</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($categories as $cat)
                    <tr class="hover:bg-gray-50/50 transition">

                        <td class="px-5 py-3">
                            <div id="cat-display-{{ $cat->id }}" class="font-semibold text-gray-900">{{ $cat->label }}</div>
                            <form id="cat-form-{{ $cat->id }}" method="POST"
                                  action="{{ route('admin.super.event-categories.update', $cat) }}"
                                  class="hidden items-center gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="label" value="{{ $cat->label }}" required maxlength="100"
                                       class="w-44 h-8 px-2 text-sm border-2 border-orange-300 rounded-lg focus:outline-none focus:border-orange-500 bg-orange-50">
                                <input type="number" name="sort_order" value="{{ $cat->sort_order }}" min="0" max="999"
                                       class="w-16 h-8 px-2 text-sm border-2 border-orange-300 rounded-lg focus:outline-none focus:border-orange-500 bg-orange-50">
                            </form>
                        </td>

                        <td class="px-4 py-3">
                            <code class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg">{{ $cat->key }}</code>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span id="cat-order-{{ $cat->id }}" class="text-xs text-gray-500">{{ $cat->sort_order }}</span>
                        </td>

                        <td class="px-4 py-3">
                            <div id="cat-actions-{{ $cat->id }}" class="flex items-center justify-end gap-2">
                                <button onclick="startCatEdit({{ $cat->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Modifier
                                </button>
                                <form method="POST" action="{{ route('admin.super.event-categories.destroy', $cat) }}">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                            data-name="{{ $cat->label }}"
                                            onclick="swalDelete(this, this.dataset.name)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 transition">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                            <div id="cat-edit-actions-{{ $cat->id }}" class="hidden items-center justify-end gap-2">
                                <button onclick="document.getElementById('cat-form-{{ $cat->id }}').submit()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-teal-500 hover:bg-teal-600 transition">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
                                    Enregistrer
                                </button>
                                <button onclick="cancelCatEdit({{ $cat->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Annuler
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-14 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </div>
                            <p class="text-sm text-gray-400">Aucune catégorie.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $categories->links() }}</div>
        @endif
    </div>

    {{-- Add form --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-4">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#EA580C" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Ajouter une catégorie</p>
                <p class="text-xs text-gray-400">Visible lors de la création d'un événement</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.super.event-categories.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Label <span class="text-red-400">*</span></label>
                <input type="text" name="label" value="{{ old('label') }}" required maxlength="100"
                       placeholder="ex : Hackathon"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-50 transition">
                @error('label')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Clé <span class="text-red-400">*</span> <span class="text-gray-300 font-normal normal-case">(unique, snake_case)</span></label>
                <input type="text" name="key" value="{{ old('key') }}" required maxlength="60"
                       placeholder="ex : hackathon"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-50 transition">
                @error('key')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Ordre d'affichage</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="999"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-50 transition">
            </div>

            <button type="submit"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 flex items-center justify-center gap-2"
                    style="background:linear-gradient(135deg,#F97316,#EA580C);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Créer la catégorie
            </button>
        </form>
    </div>

</div>

@push('scripts')
<script>
function startCatEdit(id) {
    document.getElementById('cat-display-' + id).classList.add('hidden');
    document.getElementById('cat-order-' + id).classList.add('hidden');
    document.getElementById('cat-actions-' + id).classList.add('hidden');
    const form = document.getElementById('cat-form-' + id);
    form.classList.remove('hidden');
    form.classList.add('flex');
    const editActions = document.getElementById('cat-edit-actions-' + id);
    editActions.classList.remove('hidden');
    editActions.classList.add('flex');
}
function cancelCatEdit(id) {
    document.getElementById('cat-display-' + id).classList.remove('hidden');
    document.getElementById('cat-order-' + id).classList.remove('hidden');
    document.getElementById('cat-actions-' + id).classList.remove('hidden');
    const form = document.getElementById('cat-form-' + id);
    form.classList.add('hidden');
    form.classList.remove('flex');
    const editActions = document.getElementById('cat-edit-actions-' + id);
    editActions.classList.add('hidden');
    editActions.classList.remove('flex');
}
</script>
@endpush
@endsection
