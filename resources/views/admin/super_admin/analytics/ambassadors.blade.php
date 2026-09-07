@extends('admin.layouts.admin')
@section('title', 'Analytics — Ambassadeurs')
@section('page-title', 'Analytics · Ambassadeurs')

@section('content')
@include('admin.super_admin.analytics._nav')

{{-- Stats --}}
<div class="grid grid-cols-4 gap-3 mb-5">
    @php
    $cards = [
        ['label' => 'Ambassadeurs actifs',   'value' => $stats['approved'], 'color' => '#F59E0B', 'bg' => '#FFFBEB',
         'icon' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
        ['label' => 'Consuls',               'value' => $stats['consuls'],  'color' => '#3C55FD', 'bg' => '#F0FDFA',
         'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
        ['label' => 'Demandes en attente',   'value' => $stats['pending'],  'color' => '#F97316', 'bg' => '#FFF7ED',
         'icon' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
        ['label' => 'Demandes rejetées',     'value' => $stats['rejected'], 'color' => '#EF4444', 'bg' => '#FEF2F2',
         'icon' => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'],
    ];
    @endphp
    @foreach($cards as $c)
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:{{ $c['bg'] }};">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $c['color'] }}" stroke-width="1.8">{!! $c['icon'] !!}</svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-slate-900">{{ $c['value'] }}</p>
        <p class="text-xs font-semibold text-slate-500 mt-1">{{ $c['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-5 gap-4">

    {{-- Pending Requests --}}
    <div class="col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Demandes en attente</h3>
                <p class="text-xs text-slate-400">Consuls souhaitant devenir ambassadeur</p>
            </div>
            @if($stats['pending'] > 0)
            <a href="{{ route('admin.super.consul.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">Traiter tout →</a>
            @endif
        </div>
        @forelse($pendingRequests as $req)
        <div class="px-5 py-3 border-b border-gray-50 flex items-center gap-3 hover:bg-gray-50 transition">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:linear-gradient(135deg,#F59E0B,#D97706);">
                {{ strtoupper(substr($req->user->first_name ?? '?', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ $req->user->first_name ?? '—' }} {{ $req->user->last_name ?? '' }}</p>
                <p class="text-xs text-slate-400 truncate">{{ $req->user->city->name ?? 'Région inconnue' }}</p>
            </div>
            <a href="{{ route('admin.super.consul.index') }}" class="flex-shrink-0 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
                Traiter
            </a>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-sm text-slate-400">
            <svg class="w-10 h-10 mx-auto text-slate-200 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Aucune demande en attente
        </div>
        @endforelse
        @if($stats['pending'] > 5)
        <div class="px-5 py-3 text-center">
            <a href="{{ route('admin.super.consul.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">+{{ $stats['pending'] - 5 }} autres demandes →</a>
        </div>
        @endif
    </div>

    {{-- Top Ambassadors --}}
    <div class="col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-slate-800">Top Ambassadeurs</h3>
            <p class="text-xs text-slate-400">Classés par points / activité</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-50">
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">#</th>
                        <th class="text-left text-[11px] font-bold text-slate-400 uppercase px-5 py-3">Ambassadeur</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-4 py-3">Points</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-4 py-3">Badge</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-4 py-3">Leads</th>
                        <th class="text-center text-[11px] font-bold text-slate-400 uppercase px-4 py-3">Évén.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topAmbassadors as $i => $amb)
                    @php $amb = (object)$amb; @endphp
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                            <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $i < 3 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' }}">{{ $i+1 }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.users.show', $amb->id) }}" class="font-semibold text-sm text-slate-800 hover:text-teal-600">{{ $amb->name }}</a>
                            <p class="text-xs text-slate-400">{{ $amb->city_name ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-sm font-bold text-violet-600">{{ $amb->points_balance }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                            $badgeColors = ['neutre'=>'bg-gray-100 text-gray-500','bronze'=>'bg-amber-100 text-amber-700','argent'=>'bg-slate-100 text-slate-600','or'=>'bg-yellow-100 text-yellow-700','platinium'=>'bg-indigo-100 text-indigo-700'];
                            @endphp
                            <span class="text-[11px] px-2 py-0.5 rounded-lg font-semibold {{ $badgeColors[$amb->badge_level] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ ucfirst($amb->badge_level) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-600">{{ $amb->leads_sent }}</td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-600">{{ $amb->events_created }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-slate-400">Aucun ambassadeur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
