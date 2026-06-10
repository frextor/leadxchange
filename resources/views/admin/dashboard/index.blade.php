@extends('admin.layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- ── Header greeting ────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
            Bonjour, <span style="color:#0D9488;">{{ auth()->user()->first_name }}</span> 👋
        </h1>
        <p class="text-sm text-gray-400 mt-0.5">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
    </div>
</div>

{{-- ── KPI cards ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-5">

    <a href="{{ route('admin.users.index') }}"
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-teal-50 group-hover:bg-teal-100 transition-colors mb-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-teal-600"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total_users']) }}</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mt-1">Utilisateurs</p>
        <p class="text-[10px] text-gray-300 mt-0.5">{{ $stats['unverified_users'] }} non vérifiés</p>
    </a>

    <a href="{{ route('admin.leads.index') }}"
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-indigo-50 group-hover:bg-indigo-100 transition-colors mb-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-indigo-500"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total_leads']) }}</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mt-1">Leads</p>
        <p class="text-[10px] text-gray-300 mt-0.5">{{ $stats['fraud_leads'] }} fraudes signalées</p>
    </a>

    <a href="{{ route('admin.events.index') }}"
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-amber-50 group-hover:bg-amber-100 transition-colors mb-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-500"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total_events']) }}</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mt-1">Événements</p>
        <p class="text-[10px] text-gray-300 mt-0.5">événements créés</p>
    </a>

    <a href="{{ route('admin.groups.index') }}"
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-pink-50 group-hover:bg-pink-100 transition-colors mb-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-pink-500"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total_groups']) }}</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mt-1">Groupes</p>
        <p class="text-[10px] text-gray-300 mt-0.5">groupes actifs</p>
    </a>

    <a href="{{ route('admin.users.index') }}"
       class="group bg-white rounded-2xl p-4 border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-blue-50 group-hover:bg-blue-100 transition-colors mb-3">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-500"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3.87-3.87"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ number_format($stats['total_connections']) }}</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mt-1">Connexions</p>
        <p class="text-[10px] text-gray-300 mt-0.5">connexions acceptées</p>
    </a>

    <a href="{{ route('admin.super.subscribers.index') }}"
       class="group rounded-2xl p-4 border shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all block"
       style="background:linear-gradient(135deg,#0D9488,#0F766E); border-color:#0D9488;">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-3" style="background:rgba(255,255,255,.15);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        </div>
        <p class="text-2xl font-extrabold text-white tracking-tight">{{ number_format($stats['active_subs']) }}</p>
        <p class="text-[10px] font-bold uppercase tracking-wide mt-1" style="color:rgba(255,255,255,.6);">Abonnements</p>
        <p class="text-[10px] mt-0.5" style="color:rgba(255,255,255,.4);">abonnements actifs</p>
    </a>

</div>

{{-- ── Alerts ──────────────────────────────────────────────────────────── --}}
@php
    $alerts = [];
    if ($stats['pending_videos'] > 0)   $alerts[] = ['msg' => $stats['pending_videos'].' vidéo(s) en attente',         'route' => 'admin.videos.index',  'cls' => 'bg-amber-50 border-amber-200 text-amber-700'];
    if ($stats['fraud_leads'] > 0)      $alerts[] = ['msg' => $stats['fraud_leads'].' lead(s) signalé(s) pour fraude', 'route' => 'admin.leads.index',   'cls' => 'bg-red-50 border-red-200 text-red-600'];
    if ($stats['unverified_users'] > 0) $alerts[] = ['msg' => $stats['unverified_users'].' utilisateur(s) non vérifiés','route' => 'admin.users.index',   'cls' => 'bg-blue-50 border-blue-200 text-blue-700'];
    if (auth()->user()->role === 'super_admin') {
        $pendingAmb = \App\Models\User::where('ambassador_status', 'pending')->count();
        if ($pendingAmb > 0) $alerts[] = ['msg' => $pendingAmb.' demande(s) ambassadeur', 'route' => 'admin.super.ambassadors.index', 'cls' => 'bg-violet-50 border-violet-200 text-violet-700'];
    }
@endphp
@if(count($alerts))
<div class="flex flex-wrap gap-2 mb-5">
    @foreach($alerts as $alert)
    <a href="{{ route($alert['route']) }}"
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border text-xs font-semibold transition hover:shadow-sm {{ $alert['cls'] }}">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        {{ $alert['msg'] }}
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
    </a>
    @endforeach
</div>
@endif

{{-- ── Main panels ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Recent users (2/3) --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Derniers inscrits</h2>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-teal-600 hover:text-teal-700 flex items-center gap-1">
                Voir tous <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentUsers as $u)
            <div class="flex items-center gap-3 px-5 py-2.5 hover:bg-gray-50/60 transition">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                    {{ strtoupper(substr($u->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $u->first_name }} {{ $u->last_name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $u->email }}</p>
                </div>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    @if($u->subscription?->plan)
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold bg-teal-50 text-teal-700">{{ $u->subscription->plan->label }}</span>
                    @endif
                    @if(!$u->email_verified_at)
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-semibold bg-amber-50 text-amber-700">non vérifié</span>
                    @endif
                    <span class="text-[10px] text-gray-300 whitespace-nowrap">{{ $u->created_at->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <p class="px-5 py-8 text-sm text-gray-400 text-center">Aucun utilisateur.</p>
            @endforelse
        </div>
    </div>

    {{-- Quick access (1/3) --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Accès rapide</h2>
        </div>
        <div class="divide-y divide-gray-50">
            @php
            $modules = [
                ['Utilisateurs', route('admin.users.index'),  $stats['total_users'].' membres',       'text-teal-600',   'bg-teal-50',   '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
                ['Leads',        route('admin.leads.index'),  $stats['total_leads'].' leads',         'text-indigo-500', 'bg-indigo-50', '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
                ['Événements',   route('admin.events.index'), $stats['total_events'].' créés',        'text-amber-500',  'bg-amber-50',  '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
                ['Groupes',      route('admin.groups.index'), $stats['total_groups'].' actifs',       'text-pink-500',   'bg-pink-50',   '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
                ['Vidéos',       route('admin.videos.index'), $stats['pending_videos'].' en attente', 'text-blue-500',   'bg-blue-50',   '<polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/>'],
            ];
            if (auth()->user()->role === 'super_admin') {
                $modules[] = ['Abonnés',      route('admin.super.subscribers.index'),  $stats['active_subs'].' actifs', 'text-emerald-600', 'bg-emerald-50', '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>'];
                $modules[] = ['Ambassadeurs', route('admin.super.ambassadors.index'), '', 'text-violet-600', 'bg-violet-50', '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'];
            }
            @endphp
            @foreach($modules as [$name, $url, $sub, $cls, $bg, $icon])
            <a href="{{ $url }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition group">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ $bg }} flex-shrink-0 group-hover:scale-105 transition-transform">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ $cls }}">{!! $icon !!}</svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">{{ $name }}</p>
                    @if($sub)<p class="text-[10px] text-gray-400">{{ $sub }}</p>@endif
                </div>
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            @endforeach
        </div>
    </div>

</div>

@endsection
