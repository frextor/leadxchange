@extends('admin.layouts.admin')
@section('title', 'Centres d\'intérêt')
@section('page-title', 'Centres d\'intérêt')

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Référentiel</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Centres d'intérêt</h1>
        <p class="text-sm text-gray-400 mt-1">Gérez les thématiques proposées aux membres lors de leur inscription.</p>
    </div>
</div>

{{-- Flash --}}
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

{{-- KPI strip --}}
@php $totalUsers = $interests->getCollection()->sum('users_count'); @endphp
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $interests->total() }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Centres d'intérêt</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#14A98C" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $totalUsers }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Membres concernés</p>
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
                        <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Centre d'intérêt</th>
                        <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Membres</th>
                        <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($interests as $interest)
                    <tr class="hover:bg-gray-50/50 transition">

                        {{-- Name cell --}}
                        <td class="px-5 py-3">
                            <div id="int-display-{{ $interest->id }}" class="flex items-center gap-2.5">
                                <span class="text-xl leading-none">{{ $interest->icon }}</span>
                                <span class="font-semibold text-gray-900">{{ $interest->name }}</span>
                            </div>
                            <form id="int-form-{{ $interest->id }}" method="POST"
                                  action="{{ route('admin.super.interests.update', $interest) }}"
                                  class="hidden items-center gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="icon" value="{{ $interest->icon }}" maxlength="10"
                                       placeholder="🎯"
                                       class="w-14 h-8 text-center text-xl border-2 border-violet-300 rounded-lg focus:outline-none focus:border-violet-500 bg-violet-50">
                                <input type="text" name="name" value="{{ $interest->name }}" required
                                       class="w-52 h-8 px-2 text-sm border-2 border-violet-300 rounded-lg focus:outline-none focus:border-violet-500 bg-violet-50">
                            </form>
                        </td>

                        {{-- Users count --}}
                        <td class="px-4 py-3 text-center">
                            @if($interest->users_count > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-teal-50 text-teal-700">
                                {{ $interest->users_count }}
                            </span>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-3">
                            {{-- View mode --}}
                            <div id="int-actions-{{ $interest->id }}" class="flex items-center justify-end gap-2">
                                <button onclick="startIntEdit({{ $interest->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Modifier
                                </button>
                                <form method="POST" action="{{ route('admin.super.interests.destroy', $interest) }}">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                            data-name="{{ $interest->name }}"
                                            onclick="swalDelete(this, this.dataset.name)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 transition">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                            {{-- Edit mode --}}
                            <div id="int-edit-actions-{{ $interest->id }}" class="hidden items-center justify-end gap-2">
                                <button onclick="document.getElementById('int-form-{{ $interest->id }}').submit()"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-teal-500 hover:bg-teal-600 transition">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg>
                                    Enregistrer
                                </button>
                                <button onclick="cancelIntEdit({{ $interest->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Annuler
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-14 text-center">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </div>
                            <p class="text-sm text-gray-400">Aucun centre d'intérêt.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($interests->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $interests->links() }}</div>
        @endif
    </div>

    {{-- Add form --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 sticky top-4">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Ajouter un intérêt</p>
                <p class="text-xs text-gray-400">Thématique visible à l'inscription</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.super.interests.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Icône <span class="text-gray-300 font-normal normal-case">(emoji)</span></label>
                <input type="text" name="icon" value="{{ old('icon') }}" maxlength="10"
                       placeholder="🎯"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-xl text-center focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-50 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nom</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                       placeholder="ex : Intelligence artificielle"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-50 transition">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 flex items-center justify-center gap-2"
                    style="background:linear-gradient(135deg,#8B5CF6,#7C3AED);">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Créer l'intérêt
            </button>
        </form>
    </div>

</div>

@push('scripts')
<script>
function startIntEdit(id) {
    document.getElementById('int-display-' + id).classList.add('hidden');
    document.getElementById('int-actions-' + id).classList.add('hidden');
    const form = document.getElementById('int-form-' + id);
    form.classList.remove('hidden');
    form.classList.add('flex');
    const editActions = document.getElementById('int-edit-actions-' + id);
    editActions.classList.remove('hidden');
    editActions.classList.add('flex');
}
function cancelIntEdit(id) {
    document.getElementById('int-display-' + id).classList.remove('hidden');
    document.getElementById('int-actions-' + id).classList.remove('hidden');
    const form = document.getElementById('int-form-' + id);
    form.classList.add('hidden');
    form.classList.remove('flex');
    const editActions = document.getElementById('int-edit-actions-' + id);
    editActions.classList.add('hidden');
    editActions.classList.remove('flex');
}
</script>
@endpush
@endsection
