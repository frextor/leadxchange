@extends('ambassador.layouts.ambassador')
@section('title', 'Événements')
@section('page-title', 'Événements')
@section('page-subtitle', $regionName)

@section('content')

{{-- Stats row ────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="amb-kpi-card text-center">
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Total événements</p>
    </div>
    <div class="amb-kpi-card text-center">
        <p class="text-2xl font-bold text-teal-600">{{ $stats['upcoming'] }}</p>
        <p class="text-xs text-slate-400 mt-1">À venir</p>
    </div>
    <div class="amb-kpi-card text-center">
        <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['attendees']) }}</p>
        <p class="text-xs text-slate-400 mt-1">Participants total</p>
    </div>
</div>

{{-- Tabs + Create button ─────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-4">
    <div class="flex gap-2">
        <a href="?tab=upcoming"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $tab !== 'past' ? 'text-white' : 'bg-white text-slate-600 border border-gray-200' }}"
           style="{{ $tab !== 'past' ? 'background:linear-gradient(135deg,#14B8A6,#0F766E);' : '' }}">
            À venir ({{ $upcoming->total() }})
        </a>
        <a href="?tab=past"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $tab === 'past' ? 'text-white' : 'bg-white text-slate-600 border border-gray-200' }}"
           style="{{ $tab === 'past' ? 'background:linear-gradient(135deg,#64748B,#475569);' : '' }}">
            Passés ({{ $past->total() }})
        </a>
    </div>
    <a href="{{ route('ambassador.events.create') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white"
       style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Créer un événement
    </a>
</div>

{{-- Event cards ──────────────────────────────────────────────────────────── --}}
@php $events = $tab === 'past' ? $past : $upcoming; @endphp

@if($events->count())
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-5">
    @foreach($events as $event)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition">
        {{-- Color header --}}
        <div class="h-20 flex items-center justify-between px-5"
             style="background:{{ $event->cover_color ?? 'linear-gradient(135deg,#14B8A6,#0F766E)' }};">
            <div>
                <span class="text-[10px] font-bold uppercase text-white/80 tracking-wider">{{ ucfirst($event->type) }}</span>
                @if(!$event->is_public)
                <span class="ml-2 text-[10px] font-bold text-red-200 bg-red-500/30 px-1.5 py-0.5 rounded">Annulé</span>
                @endif
            </div>
            <span class="text-white/90 text-[11px] font-semibold">{{ $event->starts_at?->format('d M Y') }}</span>
        </div>

        <div class="p-4">
            <h3 class="font-bold text-slate-800 mb-1 line-clamp-2">{{ $event->title }}</h3>
            <p class="text-xs text-slate-400 flex items-center gap-1.5 mb-3">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $event->city?->name ?? $event->location ?? 'En ligne' }}
            </p>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <span class="text-xs font-semibold text-teal-600">{{ $event->attendees_count }} participant(s)</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('ambassador.events.show', $event) }}"
                       class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg bg-gray-100 text-slate-600 hover:bg-gray-200 transition">
                        Voir
                    </a>
                    <a href="{{ route('ambassador.events.edit', $event) }}"
                       class="text-[11px] font-semibold px-2.5 py-1.5 rounded-lg text-white transition"
                       style="background:#14B8A6;">
                        Éditer
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div>{{ $events->appends(['tab' => $tab])->links() }}</div>

@else
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-16 text-center">
    <div class="text-5xl mb-4">📅</div>
    <p class="text-slate-700 font-semibold mb-1">Aucun événement</p>
    <p class="text-slate-400 text-sm mb-5">Créez votre premier événement régional.</p>
    <a href="{{ route('ambassador.events.create') }}"
       class="px-5 py-2.5 rounded-xl text-sm font-bold text-white"
       style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
        + Créer un événement
    </a>
</div>
@endif

@endsection
