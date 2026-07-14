@extends('consul.layouts.consul')
@section('title', 'Événements — ' . $group->name)
@section('page-title', $group->name . ' · Événements')

@section('content')

<div class="mb-5 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <a href="{{ route('consul.dashboard') }}" class="text-sm text-gray-400 hover:text-gray-600 transition flex items-center gap-1">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
            Mes groupes
        </a>
        <h1 class="text-xl font-bold text-gray-900 mt-0.5">{{ $group->name }} · Événements</h1>
    </div>
    <a href="{{ route('consul.group.members', $group) }}"
       class="flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-xl border transition"
       style="border-color:#99F6E4;color:#0F766E;"
       onmouseover="this.style.background='#F0FDFA'" onmouseout="this.style.background='transparent'">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Membres du groupe
    </a>
</div>

<div class="space-y-4">
    @forelse($events as $event)
    @php
        $isPast     = $event->starts_at->isPast();
        $typeColors = ['virtual' => '#0EA5E9', 'in_person' => '#10B981', 'hybrid' => '#F59E0B'];
        $color      = $typeColors[$event->type] ?? $event->cover_color ?? '#14B8A6';
        $groupParticipants = $event->attendees->filter(fn($u) => in_array($u->id, $memberIds));
    @endphp
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-stretch gap-0">

            {{-- Color bar --}}
            <div class="w-1.5 flex-shrink-0" style="background:{{ $color }};"></div>

            {{-- Content --}}
            <div class="flex-1 px-5 py-4">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="text-sm font-bold text-gray-900">{{ $event->title }}</h3>
                            @if($isPast)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-gray-100 text-gray-500">Terminé</span>
                            @else
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold" style="background:#CCFBF1;color:#0F766E;">À venir</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                {{ $event->starts_at->format('d/m/Y à H:i') }}
                            </span>
                            @if($event->city)
                            <span class="flex items-center gap-1">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $event->city->name }}
                            </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                {{ $event->attendees_count }} participant{{ $event->attendees_count > 1 ? 's' : '' }} au total
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('events.show', $event) }}" target="_blank"
                       class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg border flex-shrink-0 transition"
                       style="border-color:#99F6E4;color:#0F766E;"
                       onmouseover="this.style.background='#F0FDFA'" onmouseout="this.style.background='transparent'">
                        Voir l'événement
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                </div>

                {{-- Group participants --}}
                @if($groupParticipants->isNotEmpty())
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 mb-2">
                        {{ $groupParticipants->count() }} membre{{ $groupParticipants->count() > 1 ? 's' : '' }} du groupe participant{{ $groupParticipants->count() > 1 ? 's' : '' }}
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($groupParticipants->take(12) as $p)
                        <div class="flex items-center gap-1.5 rounded-lg px-2 py-1" style="background:#F0FDFA;">
                            @if($p->profile?->avatar)
                                <img src="{{ $p->profile->avatar_url }}" class="w-5 h-5 rounded-full object-cover flex-shrink-0">
                            @else
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-white font-bold text-[9px] flex-shrink-0"
                                     style="background:linear-gradient(135deg,#14B8A6,#0D9488);">
                                    {{ strtoupper(substr($p->first_name,0,1)) }}
                                </div>
                            @endif
                            <span class="text-xs font-medium text-gray-700">{{ $p->first_name }} {{ $p->last_name }}</span>
                        </div>
                        @endforeach
                        @if($groupParticipants->count() > 12)
                        <div class="flex items-center px-2 py-1 rounded-lg bg-gray-100">
                            <span class="text-xs text-gray-500 font-semibold">+{{ $groupParticipants->count() - 12 }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-400">Aucun membre du groupe inscrit à cet événement.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-5 py-12 text-center">
        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl flex items-center justify-center" style="background:#CCFBF1;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <p class="text-sm font-semibold text-gray-500">Aucun événement lié à ce groupe</p>
        <p class="text-xs text-gray-400 mt-1">Les événements portant le nom du groupe ou auxquels participent vos membres apparaissent ici.</p>
    </div>
    @endforelse
</div>

@endsection
