@extends('layouts.app')

@section('title', 'Dashboard — LeadXchange')

@push('styles')
<style>
    .stat-card { background:white; border-radius:16px; border:1px solid #E5E7EB; padding:20px 24px; display:flex; align-items:center; gap:16px; transition:box-shadow .2s; }
    .stat-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); }
    .stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .prospect-row { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #F3F4F6; }
    .prospect-row:last-child { border-bottom:none; }
    .circle-progress { transform:rotate(-90deg); }
    @keyframes slideUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

{{-- ── WELCOME MODAL (first login only) ── --}}
<div id="welcomeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" style="animation:slideUp .3s ease;">

        {{-- Header --}}
        <div class="relative px-7 pt-8 pb-5 text-center" style="background:linear-gradient(135deg,#0f2027,#1a3a4a,#1E8F88);">
            <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 80% 20%,white 1px,transparent 1px);background-size:22px 22px;"></div>
            <div class="relative z-10">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:rgba(255,255,255,0.15);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h2 class="text-xl font-bold text-white">Welcome to LeadXchange, {{ auth()->user()->first_name }}!</h2>
                <p class="text-white/60 text-sm mt-1">Here are a few people you might want to connect with</p>
            </div>
        </div>

        {{-- Recommendations --}}
        <div class="px-7 py-5 space-y-3">
            @foreach($prospects->take(3) as $prospect)
            @php $hue = ($prospect->id * 47) % 360; $hue2 = ($hue + 40) % 360; @endphp
            <div class="flex items-center gap-4 p-3.5 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full flex-shrink-0 overflow-hidden">
                    @if($prospect->profile?->avatar)
                        <img src="{{ $prospect->profile->avatar_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white font-semibold text-sm"
                             style="background:linear-gradient(135deg,hsl({{ $hue }} 60% 55%),hsl({{ $hue2 }} 55% 45%));">
                            {{ strtoupper(substr($prospect->first_name,0,1).substr($prospect->last_name,0,1)) }}
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $prospect->first_name }} {{ $prospect->last_name }}</p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ $prospect->profile?->job_title ?? 'LeadXchange member' }}
                        @if($prospect->company) · {{ $prospect->company->name }} @endif
                    </p>
                    @if($prospect->city)
                    <p class="text-[11px] text-teal-600 mt-0.5 flex items-center gap-1">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $prospect->city->name }}
                        @if($prospect->city_id === auth()->user()->city_id)
                        <span class="px-1.5 py-px rounded-full font-semibold text-[9px] tracking-wide" style="background:#E6F7F4;color:#1E8F88;">Near you</span>
                        @endif
                    </p>
                    @endif
                </div>

                {{-- Connect --}}
                <button onclick="welcomeConnect({{ $prospect->id }}, this)"
                    class="flex-shrink-0 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white transition"
                    style="background:#1E8F88;"
                    onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                    Connect
                </button>
            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="px-7 pb-7 flex gap-3">
            <button onclick="closeWelcomeModal()"
                class="flex-1 py-3 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                Maybe later
            </button>
            <a href="{{ route('connections.index') }}"
               onclick="closeWelcomeModal()"
               class="flex-1 py-3 rounded-xl text-sm font-bold text-white text-center transition"
               style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                Explore network
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-7 space-y-6">

    {{-- ── HERO BANNER ── --}}
    <div class="relative overflow-hidden rounded-2xl p-7" style="background: linear-gradient(135deg, #0f2027, #1a3a4a, #1E8F88);">
        {{-- decorative circles --}}
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full opacity-10" style="background:#34d4bf;"></div>
        <div class="absolute right-32 bottom-0 w-32 h-32 rounded-full opacity-10" style="background:#34d4bf;"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                {{-- Badges --}}
                <div class="flex items-center gap-2 mb-3">
                    @php $planName = auth()->user()->subscription?->plan?->name; @endphp
                    @if($planName && $planName !== 'basic')
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide"
                          style="background:rgba(255,215,0,0.2); color:#FFD700; border:1px solid rgba(255,215,0,0.3);">
                        ★ {{ strtoupper($planName) }}
                    </span>
                    @endif
                    <span class="text-xs text-white/50">{{ now()->isoFormat('dddd, D MMMM') }}</span>
                </div>

                {{-- Greeting --}}
                <h1 class="text-3xl font-bold text-white mb-1">
                    Hi {{ auth()->user()->first_name }} 👋
                </h1>

                {{-- Profile completion as stars --}}
                @php
                    $stars = round($completion / 20);
                    $jobTag = auth()->user()->profile?->job_title ?? auth()->user()->subscription?->plan?->name;
                @endphp
                <div class="flex items-center gap-2 mt-1">
                    <div class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $i <= $stars ? '#FFD700' : 'none' }}" stroke="#FFD700" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-white/60 text-sm">{{ $completion }}% profile completed</span>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('profile.me') }}"
                   class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white border border-white/20 hover:bg-white/10 transition backdrop-blur-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                    Complete Profile
                </a>
                @if($jobTag)
                <span class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-medium text-white/80 border border-white/10" style="background:rgba(255,255,255,0.08);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    {{ $jobTag }}
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── STATS ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#E6F7F4;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $connectionCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Connections</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $pendingCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Pending requests</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#EDE9FE;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8B5CF6" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $completion }}%</p>
                <p class="text-xs text-gray-500 mt-0.5">Profile score</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#FCE7F3;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#EC4899" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $groupCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Groups joined</p>
            </div>
        </div>
    </div>

    {{-- ── BOTTOM TWO COLUMNS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- New prospects --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-gray-900">New prospects for you</h2>
                <a href="{{ route('connections.index') }}"
                   class="text-sm font-semibold transition-colors" style="color:#1E8F88;">See all</a>
            </div>

            @forelse($prospects as $prospect)
            <div class="prospect-row">
                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
                     style="background: linear-gradient(135deg, hsl({{ ($prospect->id * 47) % 360 }} 60% 55%), hsl({{ ($prospect->id * 47 + 40) % 360 }} 55% 45%));">
                    {{ strtoupper(substr($prospect->first_name, 0, 1)) }}{{ strtoupper(substr($prospect->last_name, 0, 1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $prospect->first_name }} {{ $prospect->last_name }}</p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ $prospect->profile?->job_title ?? 'LeadXchange member' }}
                        @if($prospect->company) · {{ $prospect->company->name }} @endif
                    </p>
                    @if($prospect->city)
                    <p class="text-[11px] text-teal-600 mt-0.5 flex items-center gap-1">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $prospect->city->name }}
                        @if($prospect->city_id === auth()->user()->city_id)
                        <span class="px-1.5 py-px rounded-full font-semibold text-[9px] tracking-wide" style="background:#E6F7F4;color:#1E8F88;">Near you</span>
                        @endif
                    </p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('profile.show', $prospect->id) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                        View
                    </a>
                    <button onclick="sendConnect({{ $prospect->id }}, this)"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                        style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                        Connect
                    </button>
                </div>
            </div>
            @empty
            <div class="py-10 text-center">
                <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:#E6F7F4;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <p class="text-sm text-gray-500">No prospects yet — <a href="{{ route('connections.index') }}" style="color:#1E8F88;" class="font-semibold">explore the network</a></p>
            </div>
            @endforelse
        </div>

        {{-- Sharpen profile --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-1">Sharpen your profile</h2>
            <p class="text-xs text-gray-400 mb-5">Stronger profiles get 3× more inbound leads.</p>

            {{-- Circular progress --}}
            <div class="flex justify-center mb-5">
                <div class="relative w-28 h-28">
                    <svg class="w-full h-full" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#F3F4F6" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#1E8F88" stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="{{ round(2 * 3.14159 * 50) }}"
                            stroke-dashoffset="{{ round(2 * 3.14159 * 50 * (1 - $completion / 100)) }}"
                            style="transform:rotate(-90deg);transform-origin:center;transition:stroke-dashoffset 1s ease;"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-bold text-gray-900">{{ $completion }}%</span>
                        <span class="text-xs text-gray-400">complete</span>
                    </div>
                </div>
            </div>

            @if(count($missing) > 0)
            <p class="text-xs font-semibold text-gray-500 mb-3">{{ count($missing) }} field{{ count($missing) > 1 ? 's' : '' }} left</p>
            <div class="space-y-2">
                @foreach(array_slice($missing, 0, 4) as $field)
                <div class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full border-2 border-gray-200 flex-shrink-0"></div>
                    <span class="text-sm text-gray-600">{{ $field['label'] }}</span>
                    <svg class="ml-auto text-gray-300 flex-shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-2">
                <p class="text-sm font-semibold text-green-600">🎉 Profile complete!</p>
            </div>
            @endif

            <a href="{{ route('profile.me') }}"
               class="mt-5 block w-full py-3 rounded-xl text-sm font-bold text-white text-center transition"
               style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                COMPLETE PROFILE
            </a>
        </div>
    </div>

    {{-- ── GROUPS ── --}}
    @if($featuredGroups->isNotEmpty())
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Popular groups</h2>
                <p class="text-xs text-gray-400 mt-0.5">Communities matching your interests</p>
            </div>
            <a href="{{ route('groups.index') }}" class="text-sm font-semibold transition-colors" style="color:#1E8F88;">See all</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($featuredGroups as $group)
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden transition"
                 onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)'"
                 onmouseout="this.style.boxShadow='';this.style.transform=''">
                {{-- Cover --}}
                <div class="h-20 relative" style="background: linear-gradient(135deg, {{ $group->cover_color }}, {{ $group->cover_color }}cc);">
                    <div class="absolute inset-0 flex items-center px-5">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                    </div>
                    @if($group->sector)
                    <span class="absolute top-2.5 right-3 px-2 py-0.5 rounded-full text-xs font-medium text-white bg-black/20">
                        {{ $group->sector->name }}
                    </span>
                    @endif
                </div>
                {{-- Body --}}
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-1 truncate">{{ $group->name }}</h3>
                    @if($group->description)
                    <p class="text-xs text-gray-400 line-clamp-1 mb-3">{{ $group->description }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ number_format($group->members_count) }} members
                        </div>
                        @if(in_array($group->id, $memberGroupIds))
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold border"
                              style="border-color:#1E8F88;color:#1E8F88;">Joined</span>
                        @else
                        <form method="POST" action="{{ route('groups.join', $group->id) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                                    style="background:#1E8F88;"
                                    onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                                Join
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── EVENTS FOR YOU ── --}}
    @if($upcomingEvents->isNotEmpty())
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Events for you</h2>
                <p class="text-xs text-gray-400 mt-0.5">Upcoming events on LeadXchange</p>
            </div>
            <a href="{{ route('events.index') }}" class="text-sm font-semibold transition-colors" style="color:#1E8F88;">Browse all</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($upcomingEvents as $event)
            @php
                $typeColors = ['virtual' => '#6366F1', 'in_person' => '#1E8F88', 'hybrid' => '#F59E0B'];
                $typeLabels = ['virtual' => 'Virtual', 'in_person' => 'In-person', 'hybrid' => 'Hybrid'];
                $typeColor  = $typeColors[$event->type] ?? $event->cover_color;
                $typeLabel  = $typeLabels[$event->type] ?? $event->type;
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden" style="transition:box-shadow .2s,transform .2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
                {{-- Cover --}}
                <div class="h-24 relative" style="background: linear-gradient(135deg, {{ $event->cover_color }}, {{ $event->cover_color }}cc);">
                    <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 70% 30%,white 1px,transparent 1px);background-size:18px 18px;"></div>
                    <div class="absolute inset-0 flex items-center px-5">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                    </div>
                    <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-xs font-semibold text-white" style="background:rgba(0,0,0,0.25);">{{ $typeLabel }}</span>
                </div>
                {{-- Body --}}
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-900 leading-snug mb-2 line-clamp-2">{{ $event->title }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-2">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $event->starts_at->isoFormat('ddd, D MMM · H:mm') }}
                    </div>
                    @if($event->sector)
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium mb-2" style="background:#E6F7F4;color:#1E8F88;">{{ $event->sector->name }}</span>
                    @endif
                    <div class="flex items-center justify-between mt-1">
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ number_format($event->attendees_count) }} attending
                        </div>
                        @if(in_array($event->id, $attendingEventIds))
                        <form method="POST" action="{{ route('events.leave', $event->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition" style="border-color:#1E8F88;color:#1E8F88;" onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">Registered ✓</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('events.join', $event->id) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition" style="background:{{ $typeColor }};" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">Register</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    {{-- ── UPGRADE YOUR REACH ── --}}
    @if($plans->isNotEmpty())
    @php
        $currentPlanId   = $user->subscription?->plan_id;
        $currentPlanName = $user->subscription?->plan?->name ?? 'basic';

        // Meta par slug réel de la DB
        $planMeta = [
            'basic' => [
                'label'    => 'Basic',
                'subtitle' => 'Get started',
                'accent'   => '#1E8F88',
                'iconBg'   => '#F3F4F6',
                'iconColor'=> '#6B7280',
                'iconSvg'  => '<path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
                'cta'      => 'GET STARTED',
            ],
            'vip' => [
                'label'    => 'VIP',
                'subtitle' => 'For active sellers',
                'accent'   => '#F59E0B',
                'iconBg'   => '#FEF3C7',
                'iconColor'=> '#D97706',
                'iconSvg'  => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                'cta'      => 'UPGRADE NOW',
            ],
            'enterprise' => [
                'label'    => 'Entreprise',
                'subtitle' => 'For teams',
                'accent'   => '#1E8F88',
                'iconBg'   => '#E6F7F4',
                'iconColor'=> '#1E8F88',
                'iconSvg'  => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
                'cta'      => 'GO ENTERPRISE',
            ],
        ];

        $sortedPlans  = $plans->sortBy('price')->values();
        $popularIndex = $sortedPlans->count() >= 2 ? 1 : 0;
    @endphp
    <div>
        <div class="mb-5">
            <h2 class="text-base font-semibold text-gray-900">Upgrade your reach</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                You're on <strong class="text-gray-800">{{ ucfirst($currentPlanName) }}</strong>. Unlock more leads, intros, and visibility.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 items-stretch">
            @foreach($sortedPlans as $planIdx => $plan)
            @php
                $slug      = strtolower($plan->name);
                $meta      = $planMeta[$slug] ?? ['label'=>ucfirst($plan->name),'subtitle'=>'','accent'=>'#1E8F88','iconBg'=>'#E6F7F4','iconColor'=>'#1E8F88','iconSvg'=>'<circle cx="12" cy="12" r="10"/>','cta'=>'UPGRADE'];
                $isCurrent = $plan->id === $currentPlanId;
                $isPopular = !$isCurrent && $planIdx === $popularIndex;
            @endphp

            <div class="relative bg-white rounded-2xl flex flex-col border transition"
                 style="border-color:{{ $isCurrent ? $meta['accent'] : '#E5E7EB' }};
                        box-shadow:{{ $isPopular ? '0 4px 24px rgba(0,0,0,0.10)' : '0 1px 4px rgba(0,0,0,0.04)' }};">

                {{-- Badge --}}
                @if($isCurrent)
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-[3px] rounded-full text-[10px] font-bold uppercase tracking-widest border"
                          style="color:{{ $meta['accent'] }};border-color:{{ $meta['accent'] }};background:{{ $meta['iconBg'] }};">CURRENT</span>
                </div>
                @elseif($isPopular)
                <div class="absolute top-4 left-4">
                    <span class="px-2.5 py-[3px] rounded-full text-[10px] font-bold uppercase tracking-widest text-white" style="background:#111827;">POPULAR</span>
                </div>
                @endif

                <div class="p-6 flex-1 flex flex-col {{ ($isCurrent || $isPopular) ? 'pt-10' : '' }}">

                    {{-- Icon + Name --}}
                    <div class="flex items-center gap-3.5 mb-6">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background:{{ $meta['iconBg'] }};">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                 stroke="{{ $meta['iconColor'] }}" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round">
                                {!! $meta['iconSvg'] !!}
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 leading-tight">{{ $meta['label'] }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $meta['subtitle'] }}</p>
                        </div>
                    </div>

                    {{-- Price --}}
                    <div class="flex items-end gap-1.5 mb-5">
                        @if($plan->price == 0)
                            <span class="text-4xl font-extrabold text-gray-900 leading-none tracking-tight">Free</span>
                        @else
                            <span class="text-4xl font-extrabold text-gray-900 leading-none tracking-tight">${{ number_format($plan->price, 0) }}</span>
                            <span class="text-sm text-gray-400 pb-1">/ mo</span>
                        @endif
                    </div>

                    {{-- Features --}}
                    <ul class="space-y-2.5 flex-1 mb-6">
                        @forelse($plan->features ?? [] as $feat)
                        @php $featText = is_array($feat) ? ($feat['name'] ?? $feat['label'] ?? implode(', ', array_filter((array)$feat, 'is_string'))) : (string)$feat; @endphp
                        <li class="flex items-center gap-2.5 text-sm text-gray-600">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                 stroke="{{ $meta['accent'] }}" stroke-width="2.5"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            {{ $featText }}
                        </li>
                        @empty
                        <li class="text-sm text-gray-400 italic">No features listed</li>
                        @endforelse
                    </ul>

                    {{-- CTA --}}
                    @if($isCurrent)
                    <div class="w-full py-3 rounded-xl text-xs font-bold text-center tracking-widest"
                         style="background:#F9FAFB;color:#9CA3AF;border:1px solid #E5E7EB;">
                        YOUR CURRENT PLAN
                    </div>
                    @else
                    <a href="{{ route('upgrade') }}"
                       class="w-full py-3 rounded-xl text-xs font-bold tracking-widest text-white transition flex items-center justify-center"
                       style="background:{{ $meta['accent'] }};"
                       onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                        {{ $meta['cta'] }}
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // ── Welcome modal ──
    const WELCOME_KEY = 'lx_welcome_shown_{{ auth()->id() }}';

    if (!localStorage.getItem(WELCOME_KEY)) {
        document.getElementById('welcomeModal').classList.remove('hidden');
        localStorage.setItem(WELCOME_KEY, '1');
    }

    function closeWelcomeModal() {
        const modal = document.getElementById('welcomeModal');
        modal.style.opacity = '0';
        modal.style.transition = 'opacity .2s';
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    document.getElementById('welcomeModal').addEventListener('click', function(e) {
        if (e.target === this) closeWelcomeModal();
    });

    async function welcomeConnect(userId, btn) {
        btn.disabled = true;
        btn.textContent = '…';
        try {
            const res = await fetch('/api/connections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Authorization': 'Bearer ' + window.API_TOKEN },
                credentials: 'same-origin',
                body: JSON.stringify({ receiver_id: userId })
            });
            if (!res.ok) throw new Error();
            btn.textContent = 'Sent ✓';
            btn.style.background = '#6B7280';
            btn.onmouseover = btn.onmouseout = null;
        } catch {
            btn.disabled = false;
            btn.textContent = 'Connect';
        }
    }

    async function sendConnect(userId, btn) {
        btn.disabled = true;
        btn.textContent = '...';
        try {
            const res = await fetch('/api/connections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Authorization': 'Bearer ' + window.API_TOKEN },
                credentials: 'same-origin',
                body: JSON.stringify({ receiver_id: userId })
            });
            if (!res.ok) throw new Error();
            btn.textContent = 'Sent';
            btn.style.background = '#6B7280';
            btn.onmouseover = btn.onmouseout = null;
        } catch {
            btn.disabled = false;
            btn.textContent = 'Connect';
        }
    }
</script>
@endpush
