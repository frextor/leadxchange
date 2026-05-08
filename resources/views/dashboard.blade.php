@extends('layouts.dashboard')

@section('title', 'Dashboard — LeadXchange')

@push('styles')
<style>
    .stat-card { background:white; border-radius:16px; border:1px solid #E5E7EB; padding:20px 24px; display:flex; align-items:center; gap:16px; transition:box-shadow .2s; }
    .stat-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); }
    .stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .prospect-row { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #F3F4F6; }
    .prospect-row:last-child { border-bottom:none; }
    .circle-progress { transform:rotate(-90deg); }
</style>
@endpush

@section('content')
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
</div>
@endsection

@push('scripts')
<script>
    async function sendConnect(userId, btn) {
        btn.disabled = true;
        btn.textContent = '...';
        try {
            const res = await fetch('/api/connections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
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
