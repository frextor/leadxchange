@extends('admin.layouts.admin')

@section('title', 'Groupes')
@section('page-title', 'Groupes')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Groupes</h1>
        <p class="text-sm text-gray-400 mt-1">Tous les groupes créés sur la plateforme.</p>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5L20 7"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#EEF2FF;">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($counts['total']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Total groupes</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4l3 3"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($counts['public']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Publics</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="1.8">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($counts['private']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Privés</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#2F44E0" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($counts['members']) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Membres total</p>
        </div>
    </div>

</div>

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<form method="GET"
      class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-52">
        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Recherche</label>
        <div class="relative">
            <svg width="13" height="13" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Nom du groupe…"
                   class="w-full h-9 pl-8 pr-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
        </div>
    </div>
    <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Secteur</label>
        <select name="sector_id"
                class="h-9 px-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition bg-white">
            <option value="">Tous les secteurs</option>
            @foreach($sectors as $s)
            <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Visibilité</label>
        <select name="visibility"
                class="h-9 px-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition bg-white">
            <option value="">Toutes</option>
            <option value="public"  {{ request('visibility') === 'public'  ? 'selected' : '' }}>Public</option>
            <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Privé</option>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit"
                class="h-9 px-4 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            Filtrer
        </button>
        @if(request()->hasAny(['search','sector_id','visibility']))
        <a href="{{ route('admin.groups.index') }}"
           class="h-9 px-4 flex items-center rounded-xl text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50 transition">
            Réinitialiser
        </a>
        @endif
    </div>
</form>

{{-- ── Table ────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Result count --}}
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400 font-medium">
            <span class="font-bold text-gray-700">{{ $groups->total() }}</span> groupe{{ $groups->total() > 1 ? 's' : '' }}
            @if(request()->hasAny(['search','sector_id','visibility']))
            <span class="ml-1 text-indigo-400">· Filtrés</span>
            @endif
        </p>
        <p class="text-xs text-gray-400">Page {{ $groups->currentPage() }}/{{ $groups->lastPage() }}</p>
    </div>

    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Groupe</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Créateur</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Secteur</th>
                <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Membres</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Visibilité</th>
                <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Créé le</th>
                <th class="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($groups as $group)
            @php
                $color = $group->cover_color ?? '#6366F1';
            @endphp
            <tr class="hover:bg-gray-50/60 transition group/row">

                {{-- Groupe --}}
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm"
                             style="background:{{ $color }};">
                            {{ strtoupper(substr($group->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $group->name }}</p>
                            @if($group->description)
                            <p class="text-[11px] text-gray-400 mt-0.5 max-w-[240px] leading-snug line-clamp-1">{{ $group->description }}</p>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Créateur --}}
                <td class="px-4 py-3.5">
                    @if($group->creator)
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-[9px] font-bold flex-shrink-0"
                             style="background:linear-gradient(135deg,#7181ED,#2F44E0);">
                            {{ strtoupper(substr($group->creator->first_name, 0, 1)) }}
                        </div>
                        <span class="text-xs text-gray-600 font-medium">{{ $group->creator->first_name }} {{ $group->creator->last_name }}</span>
                    </div>
                    @else
                    <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>

                {{-- Secteur --}}
                <td class="px-4 py-3.5">
                    @if($group->sector)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-600">
                        {{ $group->sector->name }}
                    </span>
                    @else
                    <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>

                {{-- Membres --}}
                <td class="px-4 py-3.5 text-center">
                    <div class="inline-flex items-center gap-1.5">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span class="text-sm font-bold text-gray-700">{{ number_format($group->members_count) }}</span>
                    </div>
                </td>

                {{-- Visibilité --}}
                <td class="px-4 py-3.5">
                    @if($group->is_public)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        Public
                    </span>
                    @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Privé
                    </span>
                    @endif
                </td>

                {{-- Date --}}
                <td class="px-4 py-3.5">
                    <p class="text-xs font-medium text-gray-600">{{ $group->created_at->format('d/m/Y') }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $group->created_at->diffForHumans() }}</p>
                </td>

                {{-- Actions --}}
                <td class="px-4 py-3.5 text-right">
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('admin.groups.show', $group) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Membres
                        </a>
                        <form method="POST" action="{{ route('admin.groups.destroy', $group) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="button"
                                    data-name="{{ $group->name }}"
                                    onclick="swalDelete(this, this.dataset.name)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold border border-red-100 text-red-500 hover:bg-red-50 transition opacity-0 group-hover/row:opacity-100">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                                Supprimer
                            </button>
                        </form>
                    </div>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center">
                    <div class="w-12 h-12 rounded-2xl mx-auto mb-3 flex items-center justify-center bg-gray-50">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-400">Aucun groupe trouvé</p>
                    @if(request()->hasAny(['search','sector_id','visibility']))
                    <a href="{{ route('admin.groups.index') }}" class="text-xs text-indigo-400 hover:underline mt-1 inline-block">Réinitialiser les filtres</a>
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($groups->hasPages())
    <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50/30">
        {{ $groups->links() }}
    </div>
    @endif

</div>
@endsection
