@extends('admin.layouts.admin')
@section('title', 'Analytics — Système')
@section('page-title', 'Analytics · Système')

@section('content')
@include('admin.super_admin.analytics._nav')

{{-- ── System Health ────────────────────────────────────────────────────── --}}
<div class="mb-5">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h2 class="text-sm font-bold text-slate-800">État du système</h2>
            <p class="text-xs text-slate-400">Vérification en temps réel · {{ now()->format('H:i:s') }}</p>
        </div>
        @php
        $allOk     = collect($health)->every(fn($s) => $s['status'] === 'ok');
        $hasError  = collect($health)->contains(fn($s) => $s['status'] === 'error');
        $hasWarn   = collect($health)->contains(fn($s) => $s['status'] === 'warning');
        @endphp
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold
            {{ $hasError ? 'bg-red-50 text-red-600' : ($hasWarn ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">
            <span class="w-2 h-2 rounded-full {{ $hasError ? 'bg-red-500' : ($hasWarn ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
            {{ $hasError ? 'Problèmes détectés' : ($hasWarn ? 'Avertissements' : 'Tous les services opérationnels') }}
        </div>
    </div>

    <div class="grid grid-cols-4 gap-3">
        @foreach($health as $key => $check)
        @php
        $statusConfig = [
            'ok'      => ['bg' => '#F0FDF4', 'border' => '#BBF7D0', 'dot' => '#22C55E', 'label' => 'Opérationnel', 'text' => '#15803D'],
            'warning' => ['bg' => '#FFFBEB', 'border' => '#FDE68A', 'dot' => '#F59E0B', 'label' => 'Avertissement', 'text' => '#92400E'],
            'error'   => ['bg' => '#FEF2F2', 'border' => '#FECACA', 'dot' => '#EF4444', 'label' => 'Erreur',        'text' => '#991B1B'],
        ];
        $sc = $statusConfig[$check['status']] ?? $statusConfig['warning'];
        @endphp
        <div class="rounded-2xl p-4 border" style="background:{{ $sc['bg'] }}; border-color:{{ $sc['border'] }};">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-600">{{ $check['label'] }}</span>
                <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-lg" style="background:white;">
                    <span class="w-2 h-2 rounded-full" style="background:{{ $sc['dot'] }}; box-shadow:0 0 0 3px {{ $sc['dot'] }}30;"></span>
                    <span class="text-[10px] font-bold uppercase tracking-widest" style="color:{{ $sc['text'] }};">{{ $sc['label'] }}</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">{{ $check['detail'] }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Platform Activity Today ──────────────────────────────────────────── --}}
<div class="grid grid-cols-2 gap-4 mb-5">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h3 class="text-sm font-bold text-slate-800 mb-4">Activité aujourd'hui</h3>
        <div class="space-y-3">
            @php
            $activities = [
                ['label' => 'Nouvelles inscriptions', 'value' => $todayActivity['new_users'],       'color' => '#14B8A6'],
                ['label' => 'Leads créés',            'value' => $todayActivity['new_leads'],       'color' => '#3B82F6'],
                ['label' => 'Connexions',             'value' => $todayActivity['new_connections'], 'color' => '#22C55E'],
                ['label' => 'Événements créés',       'value' => $todayActivity['new_events'],      'color' => '#F97316'],
                ['label' => 'Groupes créés',          'value' => $todayActivity['new_groups'],      'color' => '#8B5CF6'],
            ];
            $maxVal = max(1, max(array_column($activities, 'value')));
            @endphp
            @foreach($activities as $a)
            <div class="flex items-center gap-3">
                <span class="text-xs font-medium text-slate-500 w-40 flex-shrink-0">{{ $a['label'] }}</span>
                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="background:{{ $a['color'] }}; width:{{ round($a['value']/$maxVal*100) }}%; min-width:{{ $a['value'] > 0 ? '6px' : '0' }};"></div>
                </div>
                <span class="text-sm font-bold text-slate-700 w-8 text-right">{{ $a['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <h3 class="text-sm font-bold text-slate-800 mb-4">Notifications</h3>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="rounded-xl p-3" style="background:#F0FDF4;">
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($notifications['total']) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Total envoyées</p>
            </div>
            <div class="rounded-xl p-3" style="background:#EFF6FF;">
                <p class="text-2xl font-bold text-blue-600">{{ number_format($notifications['read']) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Lues</p>
            </div>
            <div class="rounded-xl p-3" style="background:#FFF7ED;">
                <p class="text-2xl font-bold text-orange-500">{{ number_format($notifications['unread']) }}</p>
                <p class="text-xs text-slate-500 mt-0.5">Non lues</p>
            </div>
            <div class="rounded-xl p-3" style="background:#FFFBEB;">
                <p class="text-2xl font-bold text-amber-600">{{ $notifications['open_rate'] }}%</p>
                <p class="text-xs text-slate-500 mt-0.5">Taux d'ouverture</p>
            </div>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full bg-blue-400 transition-all" style="width:{{ $notifications['open_rate'] }}%;"></div>
        </div>
        <p class="text-xs text-slate-400 mt-1">{{ $notifications['open_rate'] }}% des notifications ont été lues</p>
    </div>
</div>

{{-- ── Profile Completion Summary ───────────────────────────────────────── --}}
<div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Qualité des profils</h3>
            <p class="text-xs text-slate-400">Taux de remplissage moyen — {{ number_format($profileCompletion['total']) }} membres</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 rounded-xl" style="background:{{ $profileCompletion['average'] >= 70 ? '#F0FDF4' : ($profileCompletion['average'] >= 40 ? '#FFFBEB' : '#FEF2F2') }};">
            <span class="text-xl font-bold {{ $profileCompletion['average'] >= 70 ? 'text-emerald-600' : ($profileCompletion['average'] >= 40 ? 'text-amber-600' : 'text-red-600') }}">{{ $profileCompletion['average'] }}%</span>
            <span class="text-xs text-slate-500">complétion moyenne</span>
        </div>
    </div>
    <div class="grid grid-cols-4 gap-4">
        @foreach($profileCompletion['details'] as $d)
        <div class="rounded-2xl p-4 border border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-slate-600">{{ $d['label'] }}</p>
                <span class="text-sm font-bold {{ $d['pct'] >= 70 ? 'text-emerald-600' : ($d['pct'] >= 40 ? 'text-amber-600' : 'text-red-500') }}">{{ $d['pct'] }}%</span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-2">
                <div class="h-full rounded-full" style="width:{{ $d['pct'] }}%; background:{{ $d['pct'] >= 70 ? '#22C55E' : ($d['pct'] >= 40 ? '#F59E0B' : '#EF4444') }};"></div>
            </div>
            <div class="flex justify-between text-[11px] text-slate-400">
                <span>{{ $d['with'] }} remplis</span>
                <span class="text-red-400">{{ $d['missing'] }} manquants</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
