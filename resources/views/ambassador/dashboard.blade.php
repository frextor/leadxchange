@extends('ambassador.layouts.ambassador')
@section('title', 'Dashboard Ambassadeur')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Bienvenue, {{ auth()->user()->first_name }} · ' . $regionName)

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Leads chart
    const lCtx = document.getElementById('leadsChart');
    if (lCtx) {
        new Chart(lCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($leadsChart['labels']) !!},
                datasets: [
                    {
                        label: 'Générés',
                        data: {!! json_encode($leadsChart['generated']) !!},
                        borderColor: '#14B8A6', backgroundColor: 'rgba(20,184,166,.1)',
                        borderWidth: 2.5, fill: true, tension: .4, pointRadius: 4,
                    },
                    {
                        label: 'Acceptés',
                        data: {!! json_encode($leadsChart['accepted']) !!},
                        borderColor: '#6366F1', backgroundColor: 'transparent',
                        borderWidth: 2, tension: .4, pointRadius: 3, borderDash: [4,3],
                    },
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: '#F1F5F9' }, beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    }
});
</script>
@endpush

@section('content')

{{-- KPI Row 1 — Region members + Events ──────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

    {{-- My Members --}}
    <div class="amb-kpi-card" style="background:linear-gradient(135deg,#0F766E,#14B8A6);border-color:#0F766E;">
        <p class="text-[11px] font-bold uppercase tracking-widest text-teal-100">Mes Membres</p>
        <p class="text-3xl font-bold text-white mt-2">{{ number_format($kpis['members_total']) }}</p>
        <p class="text-xs text-teal-200 mt-1">
            <span class="font-bold text-white">+{{ $kpis['members_this_month'] }}</span> ce mois
        </p>
    </div>

    {{-- Events --}}
    <div class="amb-kpi-card" style="background:linear-gradient(135deg,#4F46E5,#6366F1);border-color:#4338CA;">
        <p class="text-[11px] font-bold uppercase tracking-widest text-indigo-200">Événements</p>
        <p class="text-3xl font-bold text-white mt-2">{{ number_format($kpis['events_total']) }}</p>
        <p class="text-xs text-indigo-200 mt-1">
            <span class="font-bold text-white">{{ $kpis['events_upcoming'] }}</span> à venir
        </p>
    </div>

    {{-- Leads --}}
    <div class="amb-kpi-card">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Leads Générés</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($kpis['leads_generated']) }}</p>
        <p class="text-xs text-slate-400 mt-1">
            <span class="text-slate-600 font-semibold">{{ $kpis['leads_received'] }}</span> reçus
        </p>
    </div>

    {{-- Score --}}
    <div class="amb-kpi-card" style="background:linear-gradient(135deg,#F59E0B,#D97706);border-color:#B45309;">
        <p class="text-[11px] font-bold uppercase tracking-widest text-amber-100">Score Ambassadeur</p>
        <p class="text-3xl font-bold text-white mt-2">{{ number_format($kpis['score']) }}</p>
        <p class="text-xs text-amber-200 mt-1">
            Rang national <span class="font-bold text-white">#{{ $kpis['national_rank'] }}</span>
        </p>
    </div>
</div>

{{-- KPI Row 2 — Invitations + Progress ───────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="amb-kpi-card">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Invitations envoyées</p>
        <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($kpis['invitations_sent']) }}</p>
        <p class="text-xs text-slate-400 mt-1">
            <span class="text-emerald-600 font-semibold">{{ $kpis['invitations_accepted'] }}</span> acceptées
        </p>
    </div>

    {{-- Progress: members --}}
    <div class="amb-kpi-card col-span-2 lg:col-span-1">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-3">Objectif Membres</p>
        <div class="flex items-end justify-between mb-1.5">
            <span class="text-2xl font-bold text-slate-800">{{ $progress['members']['current'] }}</span>
            <span class="text-xs text-slate-400">/ {{ $progress['members']['target'] }}</span>
        </div>
        <div class="amb-progress-bar">
            <div class="amb-progress-fill" style="width:{{ min(100, round($progress['members']['current'] / max(1, $progress['members']['target']) * 100)) }}%;"></div>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">Ce mois-ci</p>
    </div>

    <div class="amb-kpi-card col-span-2 lg:col-span-1">
        <p class="text-[11px] font-bold uppercase tracking-widest text-slate-400 mb-3">Objectif Leads</p>
        <div class="flex items-end justify-between mb-1.5">
            <span class="text-2xl font-bold text-slate-800">{{ $progress['leads']['current'] }}</span>
            <span class="text-xs text-slate-400">/ {{ $progress['leads']['target'] }}</span>
        </div>
        <div class="amb-progress-bar">
            <div class="amb-progress-fill" style="width:{{ min(100, round($progress['leads']['current'] / max(1, $progress['leads']['target']) * 100)) }}%;background:linear-gradient(90deg,#6366F1,#818CF8);"></div>
        </div>
        <p class="text-[11px] text-slate-400 mt-1">Ce mois-ci</p>
    </div>
</div>

{{-- Charts + Tables ──────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Leads chart --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Activité Leads</h3>
                <p class="text-xs text-slate-400">6 derniers mois</p>
            </div>
            <a href="{{ route('ambassador.leads.index') }}" class="text-[11px] font-semibold text-teal-600 hover:underline">Voir tout →</a>
        </div>
        <div style="height:220px;"><canvas id="leadsChart"></canvas></div>
    </div>

    {{-- Upcoming events --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-800">Prochains événements</h3>
            <a href="{{ route('ambassador.events.index') }}" class="text-[11px] font-semibold text-teal-600 hover:underline">Voir tout →</a>
        </div>
        @forelse($upcomingEvents as $event)
        <div class="flex items-start gap-3 py-3 border-b border-gray-50 last:border-0">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0"
                 style="background:{{ $event->cover_color ?? 'linear-gradient(135deg,#14B8A6,#0F766E)' }};">
                {{ $event->starts_at?->format('d') }}
                <br>{{ $event->starts_at?->format('M') }}
            </div>
            <div class="min-w-0">
                <p class="text-[13px] font-semibold text-slate-800 truncate">{{ $event->title }}</p>
                <p class="text-[11px] text-slate-400">{{ $event->starts_at?->format('d/m H:i') }} · {{ $event->city?->name ?? $event->location }}</p>
                <p class="text-[11px] text-teal-600 font-medium mt-0.5">{{ $event->attendees_count }} participant(s)</p>
            </div>
        </div>
        @empty
        <p class="text-sm text-slate-400 text-center py-8">Aucun événement à venir.</p>
        @endforelse

        <a href="{{ route('ambassador.events.create') }}"
           class="mt-3 flex items-center justify-center gap-2 w-full py-2.5 rounded-xl text-[12px] font-bold text-white transition hover:opacity-90"
           style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
            + Créer un événement
        </a>
    </div>
</div>

{{-- Recent Members ────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Nouveaux membres</h3>
            <p class="text-xs text-slate-400">Derniers arrivants dans votre région</p>
        </div>
        <a href="{{ route('ambassador.members.index') }}" class="text-[11px] font-semibold text-teal-600 hover:underline">Voir tous →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-50">
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Membre</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden sm:table-cell">Entreprise</th>
                    <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3 hidden md:table-cell">Plan</th>
                    <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Inscrit le</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentMembers as $member)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            @if($member->profile?->avatar)
                                <img src="{{ $member->profile->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                            @else
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[11px] font-bold flex-shrink-0"
                                     style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                                    {{ strtoupper(substr($member->first_name,0,1)) }}{{ strtoupper(substr($member->last_name,0,1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-[13px] font-semibold text-slate-800">{{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="text-[11px] text-slate-400">{{ $member->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-sm text-slate-600 hidden sm:table-cell">{{ $member->company?->name ?? '—' }}</td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        @if($member->subscription?->plan)
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-teal-50 text-teal-700">{{ $member->subscription->plan->label }}</span>
                        @else
                        <span class="text-[11px] text-slate-400">Basic</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-center text-xs text-slate-400">{{ $member->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">Aucun membre dans votre région.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
