@extends('admin.layouts.admin')

@section('title', 'Événements')
@section('page-title', 'Événements')
@section('page-subtitle', $events->total() . ' événements')

@section('content')

{{-- KPI --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4"><p class="text-xs text-gray-400 font-medium">Total</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ $counts['total'] }}</p></div>
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4"><p class="text-xs font-medium" style="color:#059669;">À venir</p><p class="text-2xl font-bold mt-1" style="color:#059669;">{{ $counts['upcoming'] }}</p></div>
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4"><p class="text-xs text-gray-400 font-medium">Passés</p><p class="text-2xl font-bold text-gray-400 mt-1">{{ $counts['past'] }}</p></div>
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Recherche</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Titre…"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Type</label>
        <select name="type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            <option value="">Tous</option>
            @foreach(['virtual'=>'Virtuel','in_person'=>'Présentiel','hybrid'=>'Hybride'] as $v=>$l)
            <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Catégorie</label>
        <select name="category" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            <option value="">Toutes</option>
            @foreach(['networking'=>'Networking','workshop'=>'Workshop','conference'=>'Conférence','pitch'=>'Pitch','after_work'=>'After Work','webinar'=>'Webinar','community'=>'Communauté'] as $v=>$l)
            <option value="{{ $v }}" {{ request('category') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
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
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#1E8F88;">Filtrer</button>
        @if(request()->hasAny(['search','type','category','sector_id']))
        <a href="{{ route('admin.events.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50">Reset</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                <th class="px-5 py-3 text-left">Événement</th>
                <th class="px-4 py-3 text-left">Créateur</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Participants</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($events as $event)
            @php
                $typeMap  = ['virtual'=>['Virtuel','#EEF2FF','#4338CA'],'in_person'=>['Présentiel','#ECFDF5','#065F46'],'hybrid'=>['Hybride','#FEF3C7','#92400E']];
                [$tLabel,$tBg,$tTxt] = $typeMap[$event->type] ?? ['—','#F9FAFB','#6B7280'];
                $isPast = $event->ends_at && $event->ends_at < now();
            @endphp
            <tr class="hover:bg-gray-50 transition {{ $isPast ? 'opacity-60' : '' }}">
                <td class="px-5 py-3">
                    <div class="font-medium text-gray-900">{{ $event->title }}</div>
                    <div class="text-xs text-gray-400">{{ $event->sector?->name ?? '—' }} · {{ $event->city?->name ?? ($event->location ?: '—') }}</div>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ $event->creator?->first_name }} {{ $event->creator?->last_name }}</td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:{{ $tBg }};color:{{ $tTxt }};">{{ $tLabel }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    {{ \Carbon\Carbon::parse($event->starts_at)->format('d/m/Y') }}
                    @if($isPast)<span class="ml-1 text-[10px] text-gray-400">(terminé)</span>@endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    {{ $event->attendees_count }}
                    @if($event->max_attendees)<span class="text-gray-400">/ {{ $event->max_attendees }}</span>@endif
                </td>
                <td class="px-4 py-3">
                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}">
                        @csrf @method('DELETE')
                        <button type="button"
                                data-name="{{ $event->title }}"
                                onclick="swalDelete(this, this.dataset.name)"
                                class="text-xs font-medium text-red-500 hover:underline">Supprimer</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">Aucun événement trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($events->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $events->links() }}</div>
    @endif
</div>
@endsection
