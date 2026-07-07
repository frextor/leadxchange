@extends('ambassador.layouts.ambassador')
@section('title', 'Invitations')
@section('page-title', 'Invitations')
@section('page-subtitle', 'Gestion des invitations à vos événements')

@section('content')

{{-- KPIs ─────────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="amb-kpi-card text-center">
        <p class="text-3xl font-bold text-slate-800">{{ $kpis['sent'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Envoyées</p>
    </div>
    <div class="amb-kpi-card text-center">
        <p class="text-3xl font-bold text-amber-500">{{ $kpis['pending'] }}</p>
        <p class="text-xs text-slate-400 mt-1">En attente</p>
    </div>
    <div class="amb-kpi-card text-center">
        <p class="text-3xl font-bold text-emerald-600">{{ $kpis['accepted'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Acceptées</p>
    </div>
    <div class="amb-kpi-card text-center">
        <p class="text-3xl font-bold text-red-500">{{ $kpis['declined'] }}</p>
        <p class="text-xs text-slate-400 mt-1">Refusées</p>
    </div>
</div>

{{-- Quick send invitation ─────────────────────────────────────────────────── --}}
@if($events->count() && $eligibleMembers->count())
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
    <h3 class="text-sm font-bold text-slate-800 mb-4">Envoyer une invitation rapide</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @foreach($eligibleMembers->take(6) as $member)
        <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:border-teal-200 transition">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                    {{ strtoupper(substr($member->first_name,0,1)) }}{{ strtoupper(substr($member->last_name,0,1)) }}
                </div>
                <p class="text-[12px] font-semibold text-slate-800 truncate max-w-[100px]">{{ $member->first_name }} {{ $member->last_name }}</p>
            </div>
            @if($events->count())
            <form method="POST" action="{{ route('ambassador.invitations.send', $member) }}">
                @csrf
                <input type="hidden" name="event_id" value="{{ $events->first()->id }}">
                <button type="submit" class="text-[10px] font-bold px-2 py-1 rounded-lg text-white" style="background:#14B8A6;">
                    Inviter
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Invitations table ────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Historique</h3>
        <form method="GET" class="flex gap-2">
            <select name="status" class="rounded-xl border border-gray-200 px-3 py-1.5 text-xs focus:outline-none" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="pending" @selected(request('status')=='pending')>En attente</option>
                <option value="accepted" @selected(request('status')=='accepted')>Acceptées</option>
                <option value="declined" @selected(request('status')=='declined')>Refusées</option>
            </select>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Invité</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden md:table-cell">Événement</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Statut</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invitations as $inv)
                @php
                $colors = ['pending'=>'bg-amber-50 text-amber-600','accepted'=>'bg-emerald-50 text-emerald-700','declined'=>'bg-red-50 text-red-600'];
                $labels = ['pending'=>'En attente','accepted'=>'Acceptée','declined'=>'Refusée'];
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0"
                                 style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                                {{ strtoupper(substr($inv->user?->first_name,0,1)) }}{{ strtoupper(substr($inv->user?->last_name,0,1)) }}
                            </div>
                            <div>
                                <p class="text-[13px] font-semibold text-slate-800">{{ $inv->user?->first_name }} {{ $inv->user?->last_name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $inv->user?->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-sm text-slate-600 hidden md:table-cell max-w-[180px] truncate">
                        {{ $inv->event?->title ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $colors[$inv->status] ?? '' }}">
                            {{ $labels[$inv->status] ?? $inv->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-center text-xs text-slate-400">{{ $inv->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">Aucune invitation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invitations->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $invitations->links() }}</div>
    @endif
</div>

@endsection
