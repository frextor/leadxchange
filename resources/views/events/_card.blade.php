@php
    $typeColors = ['virtual' => '#6366F1', 'in_person' => '#1E8F88', 'hybrid' => '#F59E0B'];
    $typeLabels = ['virtual' => 'Virtual', 'in_person' => 'In-person', 'hybrid' => 'Hybrid'];
    $typeColor  = $typeColors[$event->type] ?? $event->cover_color;
    $typeLabel  = $typeLabels[$event->type] ?? $event->type;
    $isPast     = $event->starts_at->isPast();
@endphp
<div class="group-card {{ $isPast ? 'opacity-75' : '' }}">
    {{-- Cover --}}
    <div class="h-24 relative" style="background: linear-gradient(135deg, {{ $event->cover_color }}, {{ $event->cover_color }}cc);">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 70% 30%,white 1px,transparent 1px);background-size:18px 18px;"></div>
        <div class="absolute inset-0 flex items-center px-5">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
        </div>
        <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-xs font-semibold text-white"
              style="background:rgba(0,0,0,0.25);">{{ $typeLabel }}</span>
    </div>

    {{-- Body --}}
    <div class="p-4">
        <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-2 line-clamp-2">{{ $event->title }}</h3>

        <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1.5">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ $event->starts_at->isoFormat('ddd, D MMM · H:mm') }}
        </div>

        @if($event->type !== 'virtual' && $event->location)
        <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1.5">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            {{ $event->location }}
        </div>
        @elseif($event->type !== 'in_person' && $event->meeting_link)
        <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-1.5">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 7h3a5 5 0 0 1 5 5 5 5 0 0 1-5 5h-3m-6 0H6a5 5 0 0 1-5-5 5 5 0 0 1 5-5h3"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Virtual link
        </div>
        @endif

        @if($event->sector)
        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium mb-2"
              style="background:#E6F7F4;color:#1E8F88;">{{ $event->sector->name }}</span>
        @endif

        <div class="flex items-center justify-between mt-2">
            <div class="flex items-center gap-1 text-xs text-gray-400">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                {{ number_format($event->attendees_count) }} attending
            </div>

            @if($isPast)
            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-400 bg-gray-100">Ended</span>
            @elseif(in_array($event->id, $attendingEventIds))
            <form method="POST" action="{{ route('events.leave', $event->id) }}">
                @csrf @method('DELETE')
                <button type="submit"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition"
                    style="border-color:#1E8F88;color:#1E8F88;"
                    onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">
                    Registered ✓
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('events.join', $event->id) }}">
                @csrf
                <button type="submit"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                    style="background:{{ $typeColor }};"
                    onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                    Register
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
