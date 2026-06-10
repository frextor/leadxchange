@extends('admin.layouts.admin')
@section('title', 'Plans d\'abonnement')
@section('page-title', 'Plans')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Plans d'abonnement</h1>
        <p class="text-sm text-gray-400 mt-1">Gérez les offres, tarifs et fonctionnalités de la plateforme.</p>
    </div>
    <a href="{{ route('admin.super.plans.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90 active:scale-[.98]"
       style="background:linear-gradient(135deg,#6366F1,#4338CA);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M12 5v14M5 12h14"/>
        </svg>
        Nouveau plan
    </a>
</div>

{{-- ── Stats ────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-4 gap-4 mb-7">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#EEF2FF;">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8">
                <rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Plans total</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="1.8">
                <path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['active_plans'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Plans actifs</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['subscribers'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">Abonnés actifs</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="1.8">
                <path d="M12 1v22"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($stats['mrr'], 0) }}</p>
            <p class="text-xs text-gray-400 mt-0.5">MRR (MAD)</p>
        </div>
    </div>

</div>

{{-- ── Plans Grid ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

@foreach($plans as $plan)
@php
    $themes = [
        'basic' => [
            'top'        => '#64748B',
            'light_bg'   => '#F8FAFC',
            'badge_bg'   => '#F1F5F9',
            'badge_text' => '#475569',
            'check'      => '#64748B',
            'pill_bg'    => '#E2E8F0',
            'pill_text'  => '#475569',
            'icon_path'  => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>',
        ],
        'vip' => [
            'top'        => '#6366F1',
            'light_bg'   => '#EEF2FF',
            'badge_bg'   => '#E0E7FF',
            'badge_text' => '#4338CA',
            'check'      => '#6366F1',
            'pill_bg'    => '#C7D2FE',
            'pill_text'  => '#3730A3',
            'icon_path'  => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        ],
        'ambassador' => [
            'top'        => '#D97706',
            'light_bg'   => '#FFFBEB',
            'badge_bg'   => '#FEF3C7',
            'badge_text' => '#92400E',
            'check'      => '#D97706',
            'pill_bg'    => '#FDE68A',
            'pill_text'  => '#78350F',
            'icon_path'  => '<path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>',
        ],
    ];
    $t = $themes[$plan->name] ?? $themes['basic'];

    $annualMonthly = $plan->annual_price ? ($plan->annual_price / 12) : null;
    $savings = ($annualMonthly && $plan->price > 0) ? round((1 - $annualMonthly / $plan->price) * 100) : null;

    $featureLabels = [
        'max_connections_per_month' => 'Connexions / mois',
        'view_profile_info'         => 'Voir les infos profil',
        'send_leads'                => 'Envoyer des leads',
        'join_groups'               => 'Rejoindre des groupes',
        'create_events'             => 'Créer des événements',
        'ambassador_badge'          => 'Badge ambassadeur',
        'requires_approval'         => 'Sur approbation',
        'receive_leads'             => 'Recevoir des leads',
        'chat'                      => 'Messagerie',
        'profile_video'             => 'Vidéo de présentation',
        'priority_support'          => 'Support prioritaire',
        'analytics'                 => 'Statistiques avancées',
        'api_access'                => 'Accès API',
        'lead_transfer'             => 'Transfert de leads',
        'advanced_search'           => 'Recherche avancée',
        'polls'                     => 'Sondages',
    ];
@endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col transition-shadow hover:shadow-md"
     style="border-top: 3px solid {{ $t['top'] }}; {{ !$plan->is_active ? 'opacity:.55;' : '' }}">

    {{-- ── Plan header ─────────────────────────────────────────── --}}
    <div class="px-5 pt-5 pb-4">

        <div class="flex items-start justify-between gap-3">

            {{-- Icon + name --}}
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:{{ $t['light_bg'] }};">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $t['top'] }}" stroke-width="1.8">
                        {!! $t['icon_path'] !!}
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 leading-tight">{{ $plan->label }}</h3>
                    <span class="inline-block text-[9px] font-mono font-bold px-1.5 py-0.5 rounded-md mt-0.5"
                          style="background:{{ $t['badge_bg'] }};color:{{ $t['badge_text'] }};">{{ $plan->name }}</span>
                </div>
            </div>

            {{-- Status + subscribers --}}
            <div class="text-right flex-shrink-0">
                @if($plan->is_active)
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Actif
                </span>
                @else
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full border border-gray-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>Inactif
                </span>
                @endif
                <div class="mt-1.5 text-right">
                    <span class="text-lg font-bold text-gray-800">{{ $plan->active_subscriptions_count }}</span>
                    <span class="text-xs text-gray-400"> abonné{{ $plan->active_subscriptions_count > 1 ? 's' : '' }}</span>
                </div>
            </div>
        </div>

        @if($plan->description)
        <p class="text-xs text-gray-500 leading-relaxed mt-3 border-l-2 border-gray-100 pl-3">{{ $plan->description }}</p>
        @endif
    </div>

    {{-- ── Pricing ──────────────────────────────────────────────── --}}
    <div class="px-5 py-4 border-y border-gray-100" style="background:{{ $t['light_bg'] }};">
        @if((float)$plan->price === 0.0)
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-gray-900">Gratuit</span>
                <span class="text-xs text-gray-400">pour toujours</span>
            </div>
        @else
            <div class="flex items-baseline gap-1.5">
                <span class="text-3xl font-extrabold text-gray-900">{{ number_format($plan->price, 0) }}</span>
                <span class="text-sm text-gray-500 font-medium">MAD</span>
                <span class="text-xs text-gray-400">/ mois</span>
            </div>
            @if($plan->annual_price)
            <div class="flex items-center gap-2 mt-2">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <span class="text-sm text-gray-600 font-semibold">{{ number_format($plan->annual_price, 0) }} MAD<span class="font-normal text-gray-400">/an</span></span>
                @if($savings && $savings > 0)
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-700">-{{ $savings }}%</span>
                @endif
            </div>
            @endif
        @endif
    </div>

    {{-- ── Limites ──────────────────────────────────────────────── --}}
    @if($plan->max_leads !== null || $plan->max_groups !== null || $plan->initial_points !== null)
    <div class="px-5 py-3 border-b border-gray-50">
        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2">Limites & quotas</p>
        <div class="grid grid-cols-3 gap-2">
            @if($plan->max_leads !== null)
            <div class="rounded-xl p-2 text-center" style="background:{{ $t['light_bg'] }};">
                <p class="text-base font-bold text-gray-800">{{ $plan->max_leads == 0 ? '∞' : $plan->max_leads }}</p>
                <p class="text-[9px] text-gray-400 mt-0.5">leads/mois</p>
            </div>
            @endif
            @if($plan->max_groups !== null)
            <div class="rounded-xl p-2 text-center" style="background:{{ $t['light_bg'] }};">
                <p class="text-base font-bold text-gray-800">{{ $plan->max_groups == 0 ? '∞' : $plan->max_groups }}</p>
                <p class="text-[9px] text-gray-400 mt-0.5">groupes</p>
            </div>
            @endif
            @if($plan->initial_points !== null)
            <div class="rounded-xl p-2 text-center" style="background:{{ $t['light_bg'] }};">
                <p class="text-base font-bold text-gray-800">{{ $plan->initial_points }}</p>
                <p class="text-[9px] text-gray-400 mt-0.5">pts initiaux</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Features ─────────────────────────────────────────────── --}}
    @if(is_array($plan->features) && count($plan->features) > 0)
    <div class="px-5 py-3 flex-1">
        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-2.5">Fonctionnalités</p>
        <ul class="space-y-1.5">
            @foreach($plan->features as $key => $val)
            @php
                $label      = $featureLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                // null  = illimité (positif), true/integer = activé, false = désactivé
                $isDisabled = $val === false;
                $isUnlimited = $val === null;
                $isNumeric  = is_int($val) && $val >= 0 && !is_bool($val);
                $isSpecial  = $key === 'requires_approval';
            @endphp

            @if($isSpecial && !$isDisabled)
            {{-- Badge "sur approbation" --}}
            <li class="flex items-center gap-2 text-xs">
                <span class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0 bg-amber-50">
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="3">
                        <path d="M12 8v4M12 16h.01"/>
                    </svg>
                </span>
                <span class="italic text-amber-700">{{ $label }}</span>
            </li>
            @else
            <li class="flex items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 min-w-0">
                    @if(!$isDisabled)
                    <span class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0"
                          style="background:{{ $t['light_bg'] }};">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="{{ $t['check'] }}" stroke-width="3">
                            <path d="m5 12 5 5L20 7"/>
                        </svg>
                    </span>
                    <span class="text-gray-700 truncate">{{ $label }}</span>
                    @else
                    <span class="w-4 h-4 rounded-full bg-gray-50 flex items-center justify-center flex-shrink-0">
                        <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="3">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </span>
                    <span class="text-gray-300 line-through truncate">{{ $label }}</span>
                    @endif
                </div>

                {{-- Valeur à droite --}}
                @if(!$isDisabled)
                    @if($isUnlimited)
                    <span class="flex-shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                          style="background:{{ $t['pill_bg'] }};color:{{ $t['pill_text'] }};">∞</span>
                    @elseif($isNumeric)
                    <span class="flex-shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded-md tabular-nums"
                          style="background:{{ $t['pill_bg'] }};color:{{ $t['pill_text'] }};">{{ $val }}</span>
                    @endif
                @endif
            </li>
            @endif
            @endforeach
        </ul>
    </div>
    @else
    <div class="px-5 py-4 flex-1 flex items-center justify-center">
        <p class="text-xs text-gray-300 italic">Aucune fonctionnalité définie</p>
    </div>
    @endif

    {{-- ── Actions ──────────────────────────────────────────────── --}}
    <div class="px-5 py-3.5 border-t border-gray-100 flex items-center gap-2 mt-auto">
        <a href="{{ route('admin.super.plans.edit', $plan) }}"
           class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Modifier
        </a>
        <form method="POST" action="{{ route('admin.super.plans.toggle', $plan) }}">
            @csrf
            <button type="submit"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold transition
                           {{ $plan->is_active
                               ? 'bg-red-50 text-red-600 hover:bg-red-100 border border-red-100'
                               : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-100' }}">
                {{ $plan->is_active ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
    </div>

</div>
@endforeach

</div>
@endsection
