@extends('ambassador.layouts.ambassador')
@section('title', 'Classement')
@section('page-title', 'Classement')
@section('page-subtitle', 'Votre position parmi ' . $totalAmbassadors . ' ambassadeur(s)')

@section('content')

{{-- My rank cards ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-4 mb-5">
    <div class="amb-kpi-card text-center" style="background:linear-gradient(135deg,#F59E0B,#D97706);border-color:#B45309;">
        <p class="text-[11px] font-bold uppercase tracking-widest text-amber-100 mb-2">Rang National</p>
        <p class="text-5xl font-black text-white">#{{ $nationalRank }}</p>
        <p class="text-amber-200 text-xs mt-2">sur {{ $totalAmbassadors }} ambassadeur(s)</p>
    </div>
    <div class="amb-kpi-card text-center" style="background:linear-gradient(135deg,#14B8A6,#0F766E);border-color:#0F766E;">
        <p class="text-[11px] font-bold uppercase tracking-widest text-teal-100 mb-2">Score Ambassadeur</p>
        <p class="text-5xl font-black text-white">{{ number_format($myScore) }}</p>
        <p class="text-teal-200 text-xs mt-2">pts</p>
    </div>
</div>

{{-- Top Ambassadors ──────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-5">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-slate-800">🏆 Top Ambassadeurs Nationaux</h3>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($topAmbassadors as $i => $item)
        @php
        $user = $item['user'];
        $isMe = $user->id === $ambassador->id;
        $medals = ['🥇','🥈','🥉'];
        $medal  = $medals[$i] ?? '#' . ($i+1);
        @endphp
        <div class="px-5 py-4 flex items-center gap-4 {{ $isMe ? 'bg-teal-50' : '' }}">
            <span class="text-2xl w-8 text-center flex-shrink-0">{{ $medal }}</span>
            <div class="flex items-center gap-3 flex-1 min-w-0">
                @if($user->profile?->avatar)
                    <img src="{{ $user->profile->avatar_url }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0"
                         style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                        {{ strtoupper(substr($user->first_name,0,1)) }}{{ strtoupper(substr($user->last_name,0,1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-[13px] font-bold text-slate-800 truncate">
                        {{ $user->first_name }} {{ $user->last_name }}
                        @if($isMe)<span class="text-[10px] text-teal-600 font-semibold ml-1">(moi)</span>@endif
                    </p>
                    <p class="text-[11px] text-slate-400">{{ $user->region?->name ?? $user->city?->name ?? '—' }}</p>
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-[15px] font-black {{ $isMe ? 'text-teal-700' : 'text-slate-800' }}">{{ number_format($item['score']) }}</p>
                <p class="text-[10px] text-slate-400">pts</p>
            </div>
        </div>
        @endforeach

        @if(!collect($topAmbassadors)->pluck('user.id')->contains($ambassador->id))
        <div class="px-5 py-4 flex items-center gap-4 bg-teal-50 border-t-2 border-teal-100">
            <span class="text-2xl w-8 text-center flex-shrink-0">#{{ $nationalRank }}</span>
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                    {{ strtoupper(substr($ambassador->first_name,0,1)) }}{{ strtoupper(substr($ambassador->last_name,0,1)) }}
                </div>
                <div>
                    <p class="text-[13px] font-bold text-teal-700">{{ $ambassador->first_name }} {{ $ambassador->last_name }} <span class="text-[10px]">(moi)</span></p>
                    <p class="text-[11px] text-slate-400">{{ $ambassador->region?->name ?? $ambassador->city?->name ?? '—' }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[15px] font-black text-teal-700">{{ number_format($myScore) }}</p>
                <p class="text-[10px] text-slate-400">pts</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Regional Ambassadors ─────────────────────────────────────────────────── --}}
@if(count($regionalAmbassadors) > 1)
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-slate-800">📍 Classement Régional</h3>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($regionalAmbassadors as $i => $item)
        @php $isMe = $item['user']->id === $ambassador->id; @endphp
        <div class="px-5 py-3 flex items-center gap-4 {{ $isMe ? 'bg-teal-50' : '' }}">
            <span class="text-sm font-bold text-slate-400 w-6 text-center">#{{ $i+1 }}</span>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-slate-800">
                    {{ $item['user']->first_name }} {{ $item['user']->last_name }}
                    @if($isMe)<span class="text-teal-600 text-[10px]">(moi)</span>@endif
                </p>
            </div>
            <span class="text-sm font-bold {{ $isMe ? 'text-teal-700' : 'text-slate-600' }}">{{ number_format($item['score']) }} pts</span>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
