@php
    use Illuminate\Support\Facades\Storage;

    $typeColors  = ['virtual' => '#6366F1', 'in_person' => '#1E8F88', 'hybrid' => '#F59E0B'];
    $typeLabels  = ['virtual' => 'Virtuel', 'in_person' => 'Présentiel', 'hybrid' => 'Hybride'];
    $typeColor   = $typeColors[$event->type] ?? $event->cover_color;
    $typeLabel   = $typeLabels[$event->type] ?? $event->type;
    $isPast      = $event->starts_at->isPast();
    $catLabels   = App\Models\Event::categoryLabels();
    $catLabel    = $catLabels[$event->category ?? ''] ?? null;
    $capacity    = $event->max_attendees;
    $pct         = $capacity ? min(100, round($event->attendees_count / $capacity * 100)) : null;
    $isAttending = in_array($event->id, $attendingIds);
    $isOrganizer = $event->created_by === auth()->id();
@endphp

<div class="ev-card cursor-pointer" onclick="window.location='{{ route('events.show', $event->id) }}'" style="position:relative;">
    {{-- Cover --}}
    <div class="relative h-[120px] overflow-hidden">
        @if($event->cover_image)
        <img src="{{ Storage::disk('public')->url($event->cover_image) }}" alt="{{ $event->title }}"
             class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0" style="background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,.35));"></div>
        @else
        <div class="absolute inset-0" style="background:linear-gradient(135deg,{{ $event->cover_color }},{{ $event->cover_color }}99);"></div>
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 70% 30%,white 1px,transparent 1px);background-size:18px 18px;"></div>
        @endif

        {{-- Date badge --}}
        <div class="absolute top-3 left-3 rounded-xl overflow-hidden shadow-md text-center min-w-[44px]">
            <div class="px-2 py-0.5 text-white text-[10px] font-bold uppercase tracking-wide"
                 style="background:{{ $typeColor }};">
                {{ $event->starts_at->isoFormat('MMM') }}
            </div>
            <div class="px-2 py-1 bg-white text-gray-900 text-lg font-extrabold leading-none">
                {{ $event->starts_at->format('d') }}
            </div>
        </div>

        {{-- Type badge --}}
        <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-white"
              style="background:{{ $typeColor }};">
            {{ $typeLabel }}
        </span>

        {{-- Organizer badge --}}
        @if($isOrganizer)
        <span class="absolute bottom-3 left-3 px-2 py-0.5 rounded-full text-[11px] font-semibold"
              style="background:#FEF3C7;color:#92400E;">
            Organizer
        </span>
        @endif

        {{-- Price badge --}}
        @if(!$isPast && !$isOrganizer)
        <span class="absolute bottom-3 right-3 px-2 py-0.5 rounded-full text-[11px] font-semibold"
              style="{{ $event->is_free ? 'background:#10B981;color:white;' : 'background:rgba(0,0,0,.45);color:white;' }}">
            {{ $event->is_free ? 'Gratuit' : currency_format($event->price) }}
        </span>
        @elseif(!$isPast && $isOrganizer)
        <span class="absolute bottom-3 right-3 px-2 py-0.5 rounded-full text-[11px] font-semibold"
              style="{{ $event->is_free ? 'background:#10B981;color:white;' : 'background:rgba(0,0,0,.45);color:white;' }}">
            {{ $event->is_free ? 'Gratuit' : currency_format($event->price) }}
        </span>
        @endif
    </div>

    {{-- Body --}}
    <div class="p-4">
        {{-- Title --}}
        <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-2 line-clamp-2">{{ $event->title }}</h3>

        {{-- Date + time --}}
        <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1.5">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            {{ $event->starts_at->isoFormat('ddd, D MMM · H:mm') }}
            @if($event->ends_at)
            <span class="text-gray-300">–</span>
            <span>{{ $event->ends_at->isoFormat('H:mm') }}</span>
            @endif
        </div>

        {{-- Location / virtual --}}
        @if($event->type !== 'virtual' && $event->location)
        <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1.5">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            <span class="truncate max-w-[160px]">{{ $event->location }}</span>
        </div>
        @elseif($event->type !== 'in_person' && $event->meeting_link)
        <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1.5">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 7h3a5 5 0 0 1 5 5 5 5 0 0 1-5 5h-3m-6 0H6a5 5 0 0 1-5-5 5 5 0 0 1 5-5h3"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            En ligne
        </div>
        @endif

        {{-- Tags row --}}
        <div class="flex flex-wrap gap-1.5 mb-3">
            @if($catLabel)
            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium" style="background:#E6F7F4;color:#1E8F88;">
                {{ $catLabel }}
            </span>
            @endif
            @if($event->sector)
            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-500">
                {{ $event->sector->name }}
            </span>
            @endif
        </div>

        {{-- Progress bar (only when capacity set) --}}
        @if($capacity)
        <div class="mb-3">
            <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1">
                <span>{{ number_format($event->attendees_count) }} / {{ number_format($capacity) }} places</span>
                <span class="{{ $pct >= 90 ? 'text-red-500' : ($pct >= 60 ? 'text-amber-500' : 'text-gray-400') }}">
                    {{ $pct }}%
                </span>
            </div>
            <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full rounded-full transition-all"
                     style="width:{{ $pct }}%;background:{{ $pct >= 90 ? '#EF4444' : ($pct >= 60 ? '#F59E0B' : $typeColor) }};"></div>
            </div>
        </div>
        @else
        <div class="flex items-center gap-1 text-xs text-gray-400 mb-3">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            {{ number_format($event->attendees_count) }} participant(s)
        </div>
        @endif

        {{-- Action button --}}
        @if($isPast)
        <button disabled class="w-full py-2 rounded-xl text-xs font-semibold bg-gray-100 text-gray-400 cursor-default">
            Événement terminé
        </button>
        @elseif($isOrganizer)
        <div class="flex gap-2" onclick="event.stopPropagation()">
            <a href="{{ route('events.show', $event->id) }}"
               class="flex-1 py-2 rounded-xl text-xs font-semibold text-center border transition"
               style="border-color:#1E8F88;color:#1E8F88;"
               onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">
                Manage
            </a>
            <button type="button"
                    onclick="openInviteModal({{ $event->id }}, '{{ addslashes($event->title) }}')"
                    class="px-3 py-2 rounded-xl text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="22" y1="11" x2="16" y2="11"/>
                </svg>
                Invite
            </button>
        </div>
        @elseif($isAttending)
        <form method="POST" action="{{ route('events.leave', $event->id) }}" onclick="event.stopPropagation()">
            @csrf @method('DELETE')
            <button type="submit" class="w-full py-2 rounded-xl text-xs font-semibold border transition"
                    style="border-color:#1E8F88;color:#1E8F88;"
                    onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">
                Inscrit ✓ &nbsp;· Annuler
            </button>
        </form>
        @else
        @if(auth()->user()->canFeature('can_participate_events'))
        <form method="POST" action="{{ route('events.join', $event->id) }}" onclick="event.stopPropagation()">
            @csrf
            <button type="submit" class="w-full py-2 rounded-xl text-xs font-semibold text-white transition"
                    style="background:{{ $typeColor }};"
                    onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                Register
            </button>
        </form>
        @else
        <button type="button" onclick="event.stopPropagation();openUpgradeModal('can_participate_events')"
                class="w-full py-2 rounded-xl text-xs font-semibold text-center border border-dashed transition cursor-pointer"
                style="border-color:#6366F1;color:#6366F1;background:transparent;">
            <svg class="inline mr-1" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Upgrade pour participer
        </button>
        @endif
        @endif
    </div>
</div>
