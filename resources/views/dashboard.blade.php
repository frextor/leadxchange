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
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ member_name($prospect) }}</p>
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
                    $userPlan = auth()->user()->subscription?->plan;
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
                <span class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white/90 border border-white/20" style="background:rgba(255,255,255,0.12);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    Plan {{ $userPlan?->label ?? 'Basic' }}
                </span>

                {{-- Role badges & consul request --}}
                @php $authUser = auth()->user(); @endphp

                {{-- Badge Ambassadeur (toujours visible si ambassadeur) --}}
                @if($authUser->isAmbassador())
                <span class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold border" style="background:rgba(255,215,0,0.2); border-color:rgba(255,215,0,0.35); color:#FFD700;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#FFD700" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Ambassadeur ✓
                </span>
                @endif

                {{-- Badge Consul (toujours visible si consul) --}}
                @if($authUser->isConsul())
                <span class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white/90 border border-white/20" style="background:rgba(255,255,255,0.12);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Consul ✓
                </span>

                {{-- Bouton demande consul (ambassadeur OU plan payant, pas encore consul) --}}
                @elseif($authUser->hasPendingConsulRequest())
                <span class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-amber-300 border border-amber-300/30" style="background:rgba(245,158,11,0.15);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                    Demande Consul en attente…
                </span>
                @elseif($authUser->isAmbassador())
                <form method="POST" action="{{ route('consul.request') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white border border-white/20 hover:bg-white/10 transition backdrop-blur-sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Demander le rôle Consul
                    </button>
                </form>
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
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ member_name($prospect) }}</p>
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
                    @if(auth()->user()->canFeature('can_send_invitations'))
                    <button onclick="sendConnect({{ $prospect->id }}, this)"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                        style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                        Connect
                    </button>
                    @else
                    <button type="button" onclick="openUpgradeModal('can_send_invitations')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-indigo-200 text-indigo-500 hover:bg-indigo-50 transition flex items-center gap-1 cursor-pointer"
                            style="background:transparent;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Upgrade
                    </button>
                    @endif
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


    {{-- ── PLANS & FONCTIONNALITÉS ── --}}
    @if($plans->isNotEmpty())
    @php
        $currentPlanId = $user->subscription?->plan_id;
        $currentPlan   = $user->subscription?->plan;

        $planThemes = [
            'basic'       => ['color'=>'#64748B','light'=>'#F8FAFC','border'=>'#CBD5E1','icon'=>'<path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>'],
            'premium'     => ['color'=>'#6366F1','light'=>'#EEF2FF','border'=>'#818CF8','icon'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
            'consul'      => ['color'=>'#0D9488','light'=>'#F0FDFA','border'=>'#2DD4BF','icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
            'ambassadeur' => ['color'=>'#D97706','light'=>'#FFFBEB','border'=>'#FBBF24','icon'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
            'enterprise'  => ['color'=>'#2563EB','light'=>'#EFF6FF','border'=>'#60A5FA','icon'=>'<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>'],
        ];

        $keyPerms = [
            'can_view_member_name'   => 'Voir le nom complet des membres',
            'can_send_invitations'   => 'Envoyer des invitations de connexion',
            'can_send_mail'          => 'Messagerie illimitée',
            'max_leads_per_month'    => 'Leads par mois',
            'can_send_sql'           => 'Leads SQL & SP',
            'can_join_pole'          => 'Rejoindre des groupes',
            'can_create_pole'        => 'Créer des groupes',
            'can_create_events'      => 'Créer des événements',
            'can_organize_regional_events' => 'Événements régionaux',
        ];

        $sortedPlans = $plans->sortBy('sort_order')->values();
    @endphp

    <div>
        {{-- Header --}}
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Nos offres d'abonnement</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Vous êtes sur le plan
                    <strong class="text-gray-800">{{ $currentPlan?->label ?? 'Basic (gratuit)' }}</strong>.
                    Passez à l'offre suivante pour débloquer plus de fonctionnalités.
                </p>
            </div>
            <a href="{{ route('billing.index') }}"
               class="text-xs font-semibold text-gray-400 hover:text-gray-600 transition flex items-center gap-1 whitespace-nowrap">
                Gérer mon abonnement
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

        {{-- Plans grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-{{ min($sortedPlans->count(), 3) }} gap-4">
            @foreach($sortedPlans as $planIdx => $plan)
            @php
                $t         = $planThemes[$plan->name] ?? $planThemes['basic'];
                $isCurrent = $plan->id === $currentPlanId;
                $perms     = is_array($plan->permissions) ? $plan->permissions : [];
                $feats     = is_array($plan->features) ? $plan->features : [];
                $isHighlighted = !$isCurrent && $planIdx === 1;
            @endphp

            <div class="relative flex flex-col rounded-2xl border-2 overflow-hidden transition hover:shadow-lg"
                 style="border-color: {{ $isCurrent ? $t['color'] : ($isHighlighted ? $t['border'] : '#E5E7EB') }};
                        box-shadow: {{ $isHighlighted ? '0 8px 30px rgba(0,0,0,.10)' : 'none' }};">

                {{-- Top color bar --}}
                <div class="h-1.5" style="background: {{ $t['color'] }};"></div>

                {{-- Badge --}}
                @if($isCurrent)
                <div class="absolute top-3 right-3">
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border"
                          style="color:{{ $t['color'] }};border-color:{{ $t['color'] }};background:{{ $t['light'] }};">
                        ✓ Plan actuel
                    </span>
                </div>
                @elseif($isHighlighted)
                <div class="absolute top-3 right-3">
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full text-white" style="background:#111827;">
                        Recommandé
                    </span>
                </div>
                @endif

                <div class="p-5 flex-1 flex flex-col" style="background:{{ $isCurrent ? $t['light'] : '#fff' }};">

                    {{-- Icon + Nom + Prix --}}
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background:{{ $t['light'] }};">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="{{ $t['color'] }}" stroke-width="1.8">
                                {!! $t['icon'] !!}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 leading-tight">{{ $plan->label }}</p>
                            @if($plan->description)
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-snug truncate">{{ $plan->description }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Prix --}}
                    <div class="flex items-baseline gap-1 mb-5">
                        @if((float)$plan->price === 0.0)
                        <span class="text-3xl font-extrabold text-gray-900 tracking-tight">Gratuit</span>
                        <span class="text-xs text-gray-400">pour toujours</span>
                        @else
                        <span class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ currency_format($plan->price) }}</span>
                        <span class="text-xs text-gray-400 mb-0.5">/mois</span>
                        @endif
                    </div>

                    {{-- Fonctionnalités clés (permissions) --}}
                    <ul class="space-y-2 flex-1 mb-5">
                        @foreach($keyPerms as $permKey => $permLabel)
                        @php
                            $val     = $perms[$permKey] ?? false;
                            $enabled = is_bool($val) ? $val : ($val === null ? true : ($val > 0));
                            $isLimit = is_int($val) && $val > 0;
                            $isNull  = $val === null;
                        @endphp
                        <li class="flex items-center gap-2 text-xs {{ $enabled ? 'text-gray-700' : 'text-gray-300' }}">
                            @if($enabled)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                 stroke="{{ $t['color'] }}" stroke-width="2.5" class="flex-shrink-0">
                                <path d="m5 12 5 5L20 7"/>
                            </svg>
                            @else
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                 stroke="#D1D5DB" stroke-width="2.5" class="flex-shrink-0">
                                <path d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                            @endif
                            <span class="{{ $enabled ? '' : 'line-through' }}">{{ $permLabel }}</span>
                            @if($isLimit)
                            <span class="ml-auto text-[10px] font-bold px-1.5 rounded flex-shrink-0"
                                  style="background:{{ $t['light'] }};color:{{ $t['color'] }};">{{ $val }}</span>
                            @elseif($isNull && $enabled)
                            <span class="ml-auto text-[10px] font-bold px-1.5 rounded flex-shrink-0"
                                  style="background:{{ $t['light'] }};color:{{ $t['color'] }};">∞</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>

                    {{-- CTA --}}
                    @if($isCurrent)
                    <div class="w-full py-2.5 rounded-xl text-xs font-bold text-center border"
                         style="color:{{ $t['color'] }};border-color:{{ $t['color'] }};background:{{ $t['light'] }};">
                        ✓ Votre plan actuel
                    </div>
                    @elseif($plan->stripe_price_id)
                    <form method="POST" action="{{ route('checkout', $plan) }}">
                        @csrf
                        <button type="submit"
                                class="w-full py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90"
                                style="background:{{ $t['color'] }};">
                            Passer à {{ $plan->label }} →
                        </button>
                    </form>
                    @else
                    <a href="{{ route('upgrade') }}"
                       class="w-full py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 flex items-center justify-center"
                       style="background:{{ $t['color'] }};">
                        Découvrir {{ $plan->label }} →
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Note légale §12.4 --}}
        <p class="text-center text-[11px] text-gray-400 mt-5">
            Paiement sécurisé via Stripe · Résiliation possible à tout moment · Sans engagement · <a href="{{ url('/legal/cgu') }}" target="_blank" class="underline hover:text-gray-600">CGU</a>
        </p>
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
