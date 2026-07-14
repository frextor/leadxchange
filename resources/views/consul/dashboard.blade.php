@extends('consul.layouts.consul')
@section('title', 'Espace Consul')
@section('page-title', 'Vue d\'ensemble')

@section('content')

{{-- KPI --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="consul-kpi-card">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Groupes</p>
        <p class="text-3xl font-bold text-teal-500">{{ $totalGroups }}</p>
        <p class="text-xs text-gray-400 mt-1">groupes gérés</p>
    </div>
    <div class="consul-kpi-card">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Abonnés</p>
        <p class="text-3xl font-bold text-teal-500">{{ $totalMembers }}</p>
        <p class="text-xs text-gray-400 mt-1">membres au total</p>
    </div>
    <div class="consul-kpi-card">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Événements</p>
        <p class="text-3xl font-bold text-teal-500">{{ $upcomingEvents->count() }}</p>
        <p class="text-xs text-gray-400 mt-1">à venir</p>
    </div>
    <div class="consul-kpi-card">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Ville</p>
        <p class="text-sm font-bold text-gray-800 mt-1">{{ $consul->city?->name ?? '—' }}</p>
        <p class="text-xs text-gray-400 mt-1">zone consul</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Liste des groupes --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Mes Groupes
            </h2>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full" style="background:#CCFBF1;color:#0F766E;">{{ $totalGroups }}</span>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($groups as $group)
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm text-white flex-shrink-0 shadow-sm"
                     style="background:linear-gradient(135deg,#14B8A6,#0D9488);">
                    {{ strtoupper(substr($group->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $group->name }}</p>
                    <p class="text-xs text-gray-400">{{ $group->members_count }} membre{{ $group->members_count > 1 ? 's' : '' }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('consul.group.members', $group) }}"
                       class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition"
                       style="border-color:#99F6E4;color:#0F766E;"
                       onmouseover="this.style.background='#F0FDFA'" onmouseout="this.style.background='transparent'">
                        Membres
                    </a>
                    <a href="{{ route('consul.group.events', $group) }}"
                       class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition"
                       style="border-color:#99F6E4;color:#0F766E;"
                       onmouseover="this.style.background='#F0FDFA'" onmouseout="this.style.background='transparent'">
                        Événements
                    </a>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center">
                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl flex items-center justify-center" style="background:#CCFBF1;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-500">Aucun groupe</p>
                <p class="text-xs text-gray-400 mt-1">Créez un groupe depuis l'espace membre</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Événements à venir --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#14B8A6" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Événements à venir
            </h2>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($upcomingEvents as $event)
            <a href="{{ route('events.show', $event) }}" class="px-5 py-3 flex items-center gap-3 hover:bg-teal-50 transition block">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-xs flex-shrink-0"
                     style="background:{{ $event->cover_color ?? '#14B8A6' }};">
                    {{ $event->starts_at->format('d') }}<br><span class="text-[9px] uppercase">{{ $event->starts_at->translatedFormat('M') }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $event->title }}</p>
                    <p class="text-xs text-gray-400">{{ $event->starts_at->format('d/m/Y à H:i') }} · {{ $event->city?->name ?? 'En ligne' }}</p>
                </div>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            @empty
            <div class="px-5 py-10 text-center">
                <p class="text-sm text-gray-400">Aucun événement à venir</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection
