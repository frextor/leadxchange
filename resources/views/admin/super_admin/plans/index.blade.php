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
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.super.plans.permissions') }}"
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Permissions
        </a>
        <a href="{{ route('admin.super.plans.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
           style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Nouveau plan
        </a>
    </div>
</div>

{{-- ── Stats ────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
        </div>
        <div><p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['total'] }}</p><p class="text-xs text-gray-400 mt-0.5">Plans total</p></div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="1.8"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div><p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['active_plans'] }}</p><p class="text-xs text-gray-400 mt-0.5">Plans actifs</p></div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#0D9488" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div><p class="text-2xl font-bold text-gray-900 leading-none">{{ $stats['subscribers'] }}</p><p class="text-xs text-gray-400 mt-0.5">Abonnés actifs</p></div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="1.8"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div><p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($stats['mrr'], 0) }}</p><p class="text-xs text-gray-400 mt-0.5">MRR ({{ currency_symbol() }})</p></div>
    </div>
</div>

{{-- ── Plans Grid ───────────────────────────────────────────────────────── --}}
@php
$planThemes = [
    'basic'       => ['color'=>'#64748B','light'=>'#F8FAFC','badge'=>'#E2E8F0','badgeTxt'=>'#475569','border'=>'#94A3B8','icon'=>'<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>'],
    'premium'     => ['color'=>'#6366F1','light'=>'#EEF2FF','badge'=>'#E0E7FF','badgeTxt'=>'#4338CA','border'=>'#818CF8','icon'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
    'consul'      => ['color'=>'#0D9488','light'=>'#F0FDFA','badge'=>'#CCFBF1','badgeTxt'=>'#0F766E','border'=>'#2DD4BF','icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
    'ambassadeur' => ['color'=>'#D97706','light'=>'#FFFBEB','badge'=>'#FDE68A','badgeTxt'=>'#92400E','border'=>'#FBBF24','icon'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
    'enterprise'  => ['color'=>'#2563EB','light'=>'#EFF6FF','badge'=>'#BFDBFE','badgeTxt'=>'#1E40AF','border'=>'#60A5FA','icon'=>'<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4M9 9h.01M9 13h.01M9 17h.01M15 13h.01M15 17h.01"/>'],
];

// Permissions groupées pour l'affichage
$permGroups = [
    'Profil'      => ['can_view_member_name'=>'Voir le nom','can_view_member_photo'=>'Photo','can_view_member_region'=>'Région','can_view_member_pitch'=>'Pitch','can_view_member_video'=>'Vidéo','can_view_member_contact'=>'Contact'],
    'Connexions'  => ['can_send_invitations'=>'Envoyer invitations','can_receive_invitations'=>'Recevoir invitations'],
    'Chat'        => ['can_send_mail'=>'Envoyer messages','can_receive_mail'=>'Recevoir messages','can_reply_mail'=>'Répondre','mail_reply_weekly_limit'=>'Limite/semaine'],
    'Leads'       => ['can_send_leads'=>'Envoyer leads','can_receive_leads'=>'Recevoir leads','max_leads_per_month'=>'Max/mois','can_send_mql'=>'MQL','can_send_sql'=>'SQL','can_send_sp'=>'SP'],
    'Groupes'     => ['can_join_pole'=>'Rejoindre groupe','max_groups_joined'=>'Max groupes','can_create_pole'=>'Créer groupe','can_invite_to_group'=>'Inviter','can_organize_group_events'=>'Organiser événements'],
    'Événements'  => ['can_participate_events'=>'Participer','can_receive_event_invitations'=>'Recevoir invitations','can_create_events'=>'Créer événements','can_organize_regional_events'=>'Événements régionaux'],
    'Spécial'     => ['can_nominate_consul'=>'Nommer consul','can_add_member'=>'Ajouter membre'],
];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5">
@foreach($plans as $plan)
@php
    $t    = $planThemes[$plan->name] ?? $planThemes['basic'];
    $perms = is_array($plan->permissions) ? $plan->permissions : [];
    $annualMonthly = $plan->annual_price ? round($plan->annual_price / 12, 0) : null;
    $savings = ($annualMonthly && $plan->price > 0) ? round((1 - $annualMonthly / $plan->price) * 100) : null;
@endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col"
     style="border-top: 3px solid {{ $t['color'] }}; {{ !$plan->is_active ? 'opacity:.6;' : '' }}">

    {{-- Header --}}
    <div class="px-5 pt-5 pb-4">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:{{ $t['light'] }};">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="{{ $t['color'] }}" stroke-width="1.8">{!! $t['icon'] !!}</svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">{{ $plan->label }}</h3>
                    <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded-md" style="background:{{ $t['badge'] }};color:{{ $t['badgeTxt'] }};">{{ strtoupper($plan->name) }}</span>
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full border {{ $plan->is_active ? 'text-emerald-600 bg-emerald-50 border-emerald-100' : 'text-gray-400 bg-gray-50 border-gray-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $plan->is_active ? 'bg-emerald-400 animate-pulse' : 'bg-gray-300' }}"></span>
                    {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                </span>
                <div class="mt-1 text-right">
                    <span class="text-lg font-bold text-gray-800">{{ $plan->active_subscriptions_count }}</span>
                    <span class="text-xs text-gray-400"> abonné{{ $plan->active_subscriptions_count != 1 ? 's' : '' }}</span>
                </div>
            </div>
        </div>
        @if($plan->description)
        <p class="text-xs text-gray-400 mt-2.5 leading-relaxed">{{ $plan->description }}</p>
        @endif
    </div>

    {{-- Prix --}}
    <div class="px-5 py-3.5 border-y border-gray-100" style="background:{{ $t['light'] }};">
        @if((float)$plan->price === 0.0)
        <span class="text-2xl font-extrabold text-gray-900">Basic</span>
        <span class="text-xs text-gray-400 ml-1">pour toujours</span>
        @else
        <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-extrabold text-gray-900">{{ currency_format($plan->price) }}</span>
            <span class="text-xs text-gray-400">/ mois</span>
            @if($savings && $savings > 0)
            <span class="ml-2 text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-700">-{{ $savings }}% annuel</span>
            @endif
        </div>
        @if($plan->annual_price)
        <p class="text-xs text-gray-400 mt-0.5">ou {{ currency_format($plan->annual_price) }}/an</p>
        @endif
        @endif
    </div>

    {{-- Fonctionnalités par groupe --}}
    <div class="flex-1 divide-y divide-gray-50">
        @foreach($permGroups as $groupName => $groupKeys)
        @php
            $hasAny = false;
            foreach(array_keys($groupKeys) as $k) {
                if(array_key_exists($k, $perms)) { $hasAny = true; break; }
            }
        @endphp
        @if($hasAny)
        <div class="px-5 py-3">
            <p class="text-[9px] font-bold uppercase tracking-widest mb-2" style="color:{{ $t['color'] }};">{{ $groupName }}</p>
            <div class="grid grid-cols-2 gap-x-3 gap-y-1.5">
                @foreach($groupKeys as $key => $label)
                @if(array_key_exists($key, $perms))
                @php
                    $val = $perms[$key];
                    $isBool = is_bool($val);
                    $isNull = $val === null;
                    $isInt  = is_int($val);
                    $enabled = $isBool ? $val : ($isNull ? true : ($isInt ? $val > 0 : (bool)$val));
                @endphp
                <div class="flex items-center gap-1.5 min-w-0">
                    @if($enabled)
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="{{ $t['color'] }}" stroke-width="3" class="flex-shrink-0"><path d="m5 12 5 5L20 7"/></svg>
                    <span class="text-[11px] text-gray-700 truncate">{{ $label }}</span>
                    @if($isNull)
                    <span class="text-[9px] font-bold px-1 rounded flex-shrink-0" style="background:{{ $t['badge'] }};color:{{ $t['badgeTxt'] }};">∞</span>
                    @elseif($isInt && $val > 0)
                    <span class="text-[9px] font-bold px-1 rounded flex-shrink-0 tabular-nums" style="background:{{ $t['badge'] }};color:{{ $t['badgeTxt'] }};">{{ $val }}</span>
                    @elseif($isInt && $val === 0)
                    <span class="text-[9px] font-bold px-1 rounded flex-shrink-0" style="background:{{ $t['badge'] }};color:{{ $t['badgeTxt'] }};">∞</span>
                    @endif
                    @else
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="3" class="flex-shrink-0"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    <span class="text-[11px] text-gray-300 truncate">{{ $label }}</span>
                    @endif
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>

    {{-- Actions --}}
    <div class="px-5 py-3.5 border-t border-gray-100 flex items-center gap-2 mt-auto bg-gray-50/50">
        <a href="{{ route('admin.super.plans.edit', $plan) }}"
           class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Modifier
        </a>
        <a href="{{ route('admin.super.plans.permissions') }}"
           class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Permissions
        </a>
        <form method="POST" action="{{ route('admin.super.plans.toggle-visible', $plan) }}" title="{{ $plan->is_visible ? 'Masquer de la page d\'accueil' : 'Afficher sur la page d\'accueil' }}">
            @csrf
            <button type="submit"
                    class="px-3 py-2 rounded-xl text-xs font-semibold transition border {{ $plan->is_visible ? 'bg-teal-50 text-teal-700 border-teal-100 hover:bg-teal-100' : 'bg-gray-50 text-gray-400 border-gray-200 hover:bg-gray-100' }}">
                @if($plan->is_visible)
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline -mt-0.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                @else
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline -mt-0.5"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                @endif
            </button>
        </form>
        <form method="POST" action="{{ route('admin.super.plans.toggle', $plan) }}">
            @csrf
            <button type="submit"
                    class="px-3 py-2 rounded-xl text-xs font-semibold transition border {{ $plan->is_active ? 'bg-red-50 text-red-600 border-red-100 hover:bg-red-100' : 'bg-emerald-50 text-emerald-700 border-emerald-100 hover:bg-emerald-100' }}">
                {{ $plan->is_active ? 'Désactiver' : 'Activer' }}
            </button>
        </form>
    </div>

</div>
@endforeach
</div>

@endsection
