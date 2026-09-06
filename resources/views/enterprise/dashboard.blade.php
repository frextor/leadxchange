@extends('enterprise.layouts.enterprise')
@section('title', 'Tableau de bord — ' . $license->company_name)
@section('page-title', 'Tableau de bord')

@section('content')

<div class="mb-6">
    <h1 class="text-xl font-bold text-slate-900">Bonjour {{ auth()->user()->first_name }} 👋</h1>
    <p class="text-sm text-slate-500 mt-0.5">Voici l'activité de <strong>{{ $license->company_name }}</strong> sur LeadXchange.</p>
</div>

{{-- ── Seats + expiry ───────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Licences</p>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-slate-900 mt-2">{{ $license->seats_used }}<span class="text-base font-medium text-slate-300"> / {{ $license->seats_total }}</span></p>
        <div class="h-1.5 rounded-full bg-slate-100 mt-2.5 overflow-hidden">
            <div class="h-full rounded-full" style="width:{{ $license->seats_total ? min(100, round($license->seats_used/$license->seats_total*100)) : 0 }}%; background:linear-gradient(90deg,#6366F1,#4338CA);"></div>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Licences libres</p>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-emerald-600 mt-2">{{ $license->seatsAvailable() }}</p>
        <p class="text-xs text-slate-400 mt-1">disponibles pour inviter</p>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Expiration</p>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
        @if($analytics['days_left'] !== null)
        <p class="text-2xl font-extrabold {{ $analytics['days_left'] <= 14 ? 'text-amber-600' : 'text-slate-900' }} mt-2">{{ $analytics['days_left'] }} <span class="text-base font-medium text-slate-300">jours</span></p>
        <p class="text-xs text-slate-400 mt-1">jusqu'au {{ $license->expires_at->format('d/m/Y') }}</p>
        @else
        <p class="text-2xl font-extrabold text-slate-900 mt-2">—</p>
        <p class="text-xs text-slate-400 mt-1">sans date d'expiration</p>
        @endif
    </div>
</div>

{{-- ── Analytics ─────────────────────────────────────────────────────────── --}}
<div class="mb-6">
    <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/></svg>
        Activité de l'équipe
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
            <p class="text-2xl font-extrabold text-slate-900">{{ $analytics['leads_sent_total'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Leads envoyés</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
            <p class="text-2xl font-extrabold text-teal-600">{{ $analytics['leads_converted'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Convertis</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
            <p class="text-2xl font-extrabold text-slate-900">{{ $analytics['leads_recv_total'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Leads reçus</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
            <p class="text-2xl font-extrabold text-indigo-600">{{ $analytics['connections_total'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Connexions</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-4 py-3.5">
            <p class="text-2xl font-extrabold text-amber-600">⭐ {{ $analytics['points_total'] }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Points cumulés</p>
        </div>
    </div>
</div>

{{-- ── Leaderboard preview ──────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-bold text-slate-800">Classement de l'équipe</h2>
        <a href="{{ route('enterprise.team') }}" class="text-xs font-semibold text-indigo-600 hover:underline">Gérer les membres →</a>
    </div>
    @if($leaderboard->isEmpty())
    <div class="px-5 py-8 text-center text-sm text-slate-400">Aucune donnée pour le moment.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-50">
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Membre</th>
                    <th class="text-center px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Envoyés</th>
                    <th class="text-center px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Convertis</th>
                    <th class="text-center px-3 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Connexions</th>
                    <th class="text-right px-5 py-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Points</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($leaderboard->take(5) as $i => $row)
                <tr>
                    <td class="px-5 py-2.5">
                        <div class="flex items-center gap-2.5">
                            @if($i === 0)<span title="Meilleur contributeur">🥇</span>@endif
                            <span class="text-sm font-medium text-slate-700">{{ $row['user']->first_name }} {{ $row['user']->last_name }}</span>
                            @if($row['user']->id === $license->holder_user_id)
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-500">TITULAIRE</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-3 py-2.5 text-center font-semibold text-slate-700">{{ $row['leads_sent'] }}</td>
                    <td class="px-3 py-2.5 text-center font-semibold text-teal-600">{{ $row['converted'] }}</td>
                    <td class="px-3 py-2.5 text-center font-semibold text-indigo-600">{{ $row['connections'] }}</td>
                    <td class="px-5 py-2.5 text-right font-semibold text-amber-600">{{ $row['points'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ── Quick actions ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="{{ route('enterprise.team') }}"
       class="flex items-center gap-4 bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4 hover:border-indigo-200 hover:shadow transition">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#EEF2FF;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4338CA" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-800">Gérer les membres</p>
            <p class="text-xs text-slate-400 mt-0.5">Inviter, révoquer, envoyer un message</p>
        </div>
    </a>
    <a href="mailto:contact@leadxchange.com"
       class="flex items-center gap-4 bg-white rounded-2xl border border-slate-200 shadow-sm px-5 py-4 hover:border-indigo-200 hover:shadow transition">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#F0FDFA;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-800">Modifier mon quota</p>
            <p class="text-xs text-slate-400 mt-0.5">Contacter l'équipe LeadXchange</p>
        </div>
    </a>
</div>

@endsection
