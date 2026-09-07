@extends('admin.layouts.admin')
@section('title', 'Événements')
@section('page-title', 'Événements')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Administration</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            Événements
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">{{ $events->total() }}</span>
        </h1>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- ── KPI strip ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $counts['total'] }}</p>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-1.5">Total événements</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 uppercase tracking-wide">À venir</span>
        </div>
        <p class="text-3xl font-extrabold text-emerald-600 tracking-tight">{{ $counts['upcoming'] }}</p>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-1.5">Événements à venir</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-9 h-9 rounded-xl bg-gray-50 flex items-center justify-center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-gray-400 tracking-tight">{{ $counts['past'] }}</p>
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mt-1.5">Événements passés</p>
    </div>
</div>

{{-- ── Filters ──────────────────────────────────────────────────────────── --}}
<form method="GET" id="filterForm">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
    <div class="flex flex-wrap gap-2.5 items-center">

        <div class="relative flex-1 min-w-52">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Titre de l'événement…"
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-teal-400 focus:ring-2 focus:ring-teal-50 transition">
        </div>

        <div class="flex flex-col gap-0.5">
            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest px-1">Type</label>
            <select name="type" onchange="document.getElementById('filterForm').submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:border-teal-400 transition">
                <option value="">Tous</option>
                <option value="virtual"   {{ request('type') === 'virtual'   ? 'selected' : '' }}>Virtuel</option>
                <option value="in_person" {{ request('type') === 'in_person' ? 'selected' : '' }}>Présentiel</option>
                <option value="hybrid"    {{ request('type') === 'hybrid'    ? 'selected' : '' }}>Hybride</option>
            </select>
        </div>

        <div class="flex flex-col gap-0.5">
            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest px-1">Statut</label>
            <select name="status" onchange="document.getElementById('filterForm').submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:border-teal-400 transition">
                <option value="">Tous</option>
                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>À venir</option>
                <option value="past"     {{ request('status') === 'past'     ? 'selected' : '' }}>Passés</option>
            </select>
        </div>

        <div class="flex flex-col gap-0.5">
            <label class="text-[9px] font-bold text-gray-400 uppercase tracking-widest px-1">Secteur</label>
            <select name="sector_id" onchange="document.getElementById('filterForm').submit()"
                    class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:border-teal-400 transition min-w-36">
                <option value="">Tous</option>
                @foreach($sectors as $s)
                <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2 ml-auto">
            <button type="submit" class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90" style="background:#2F44E0;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                Filtrer
            </button>
            @if(request()->hasAny(['search','type','status','sector_id']))
            <a href="{{ route('admin.events.index') }}" class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50 transition">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Reset
            </a>
            @endif
        </div>
    </div>
</div>
</form>

{{-- ── Table ────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
        <span class="text-sm text-gray-500">{{ $events->total() }} résultat{{ $events->total() > 1 ? 's' : '' }}</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="px-5 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Événement</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Créateur</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">Participants</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wider">Statut</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($events as $event)
                @php
                    $typeMap = [
                        'virtual'   => ['Virtuel',    '#EEF2FF', '#4338CA'],
                        'in_person' => ['Présentiel', '#ECFDF5', '#065F46'],
                        'hybrid'    => ['Hybride',    '#FEF3C7', '#92400E'],
                    ];
                    [$tLabel, $tBg, $tTxt] = $typeMap[$event->type] ?? ['—', '#F9FAFB', '#6B7280'];
                    $now     = now();
                    $isPast  = $event->ends_at && $event->ends_at < $now;
                    $isNow   = $event->starts_at <= $now && (!$event->ends_at || $event->ends_at > $now);
                    $isFuture = $event->starts_at > $now;
                    $colorHex = $event->cover_color ?? '#2F44E0';
                @endphp
                <tr class="hover:bg-gray-50/50 transition {{ $isPast ? 'opacity-60' : '' }}">

                    {{-- Événement --}}
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex-shrink-0"
                                 style="background:{{ $colorHex }};"></div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 truncate max-w-xs">{{ $event->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 truncate">
                                    {{ $event->sector?->name ?? '' }}
                                    @if($event->city) · {{ $event->city->name }} @elseif($event->location) · {{ Str::limit($event->location, 30) }} @endif
                                    @if($event->price > 0) · {{ currency_format($event->price) }} @else · Gratuit @endif
                                </p>
                            </div>
                        </div>
                    </td>

                    {{-- Créateur --}}
                    <td class="px-4 py-3.5">
                        @if($event->creator)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0"
                                 style="background:linear-gradient(135deg,#7181ED,#2F44E0);">
                                {{ strtoupper(substr($event->creator->first_name, 0, 1)) }}
                            </div>
                            <span class="text-sm text-gray-700 font-medium">{{ $event->creator->first_name }} {{ $event->creator->last_name }}</span>
                        </div>
                        @else
                        <span class="text-xs text-gray-400 italic">Utilisateur supprimé</span>
                        @endif
                    </td>

                    {{-- Type --}}
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold"
                              style="background:{{ $tBg }};color:{{ $tTxt }};">
                            {{ $tLabel }}
                        </span>
                    </td>

                    {{-- Date --}}
                    <td class="px-4 py-3.5 text-sm text-gray-600 whitespace-nowrap">
                        <p class="font-medium">{{ \Carbon\Carbon::parse($event->starts_at)->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($event->starts_at)->format('H:i') }}</p>
                    </td>

                    {{-- Participants --}}
                    <td class="px-4 py-3.5 text-center">
                        <span class="text-sm font-semibold text-gray-900">{{ $event->attendees_count }}</span>
                        @if($event->max_attendees)
                        <span class="text-xs text-gray-400"> / {{ $event->max_attendees }}</span>
                        @php $pct = min(100, round($event->attendees_count / $event->max_attendees * 100)); @endphp
                        <div class="w-16 h-1 bg-gray-100 rounded-full mx-auto mt-1">
                            <div class="h-1 rounded-full {{ $pct >= 90 ? 'bg-red-400' : 'bg-teal-500' }}"
                                 style="width:{{ $pct }}%;"></div>
                        </div>
                        @endif
                    </td>

                    {{-- Statut --}}
                    <td class="px-4 py-3.5">
                        @if($isPast)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Terminé
                        </span>
                        @elseif($isNow)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> En cours
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> À venir
                        </span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3.5">
                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}">
                            @csrf @method('DELETE')
                            <button type="button"
                                    data-name="{{ $event->title }}"
                                    onclick="swalDelete(this, this.dataset.name)"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-16 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <p class="text-sm text-gray-400 font-medium">Aucun événement trouvé.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($events->hasPages())
    <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400">
            {{ $events->firstItem() }}–{{ $events->lastItem() }} sur {{ $events->total() }}
        </p>
        {{ $events->links() }}
    </div>
    @endif
</div>

@endsection
