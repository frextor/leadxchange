@extends('admin.layouts.admin')

@section('title', 'Groupes')
@section('page-title', 'Groupes')
@section('page-subtitle', $groups->total() . ' groupes')

@section('content')

{{-- KPI --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4"><p class="text-xs text-gray-400 font-medium">Total</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $counts['total'] }}</p></div>
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4"><p class="text-xs font-medium" style="color:#059669;">Publics</p><p class="text-2xl font-bold mt-1" style="color:#059669;">{{ $counts['public'] }}</p></div>
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4"><p class="text-xs text-gray-400 font-medium">Privés</p><p class="text-2xl font-bold text-gray-400 mt-1">{{ $counts['private'] }}</p></div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Recherche</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom du groupe…"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Secteur</label>
        <select name="sector_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            <option value="">Tous</option>
            @foreach($sectors as $s)
            <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Visibilité</label>
        <select name="visibility" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            <option value="">Toutes</option>
            <option value="public"  {{ request('visibility') === 'public'  ? 'selected' : '' }}>Public</option>
            <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Privé</option>
        </select>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#1E8F88;">Filtrer</button>
        @if(request()->hasAny(['search','sector_id','visibility']))
        <a href="{{ route('admin.groups.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50">Reset</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                <th class="px-5 py-3 text-left">Groupe</th>
                <th class="px-4 py-3 text-left">Créateur</th>
                <th class="px-4 py-3 text-left">Secteur</th>
                <th class="px-4 py-3 text-left">Membres</th>
                <th class="px-4 py-3 text-left">Visibilité</th>
                <th class="px-4 py-3 text-left">Créé le</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($groups as $group)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                             style="background:{{ $group->cover_color ?? '#1E8F88' }};">
                            {{ strtoupper(substr($group->name,0,1)) }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">{{ $group->name }}</div>
                            @if($group->description)
                            <div class="text-xs text-gray-400 truncate max-w-48">{{ Str::limit($group->description, 60) }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $group->creator?->first_name }} {{ $group->creator?->last_name }}</td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $group->sector?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm font-semibold text-gray-700">{{ $group->members_count ?? 0 }}</td>
                <td class="px-4 py-3">
                    @if($group->is_public)
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#ECFDF5;color:#065F46;">Public</span>
                    @else
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:#F9FAFB;color:#6B7280;">Privé</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $group->created_at->format('d/m/Y') }}</td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.groups.destroy', $group) }}">
                        @csrf @method('DELETE')
                        <button type="button"
                                data-name="{{ $group->name }}"
                                onclick="swalDelete(this, this.dataset.name)"
                                class="text-xs font-medium text-red-500 hover:underline">Supprimer</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-400">Aucun groupe trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($groups->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $groups->links() }}</div>
    @endif
</div>
@endsection
