@extends('admin.layouts.admin')
@section('title', 'Paiements')
@section('page-title', 'Paiements')

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Stripe</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            Paiements
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600">
                {{ $subscriptions->total() + $eventPayments->total() }}
            </span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Abonnements et paiements événements traités via Stripe.</p>
    </div>
    <a href="{{ route('admin.super.plans.stripe') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
        Gestion Stripe
    </a>
</div>

{{-- Stats KPI --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Abonnements actifs</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['active_subs']) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">MRR estimé</p>
        <p class="text-2xl font-bold text-emerald-600">{{ number_format($stats['mrr'], 2) }} €</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Revenus événements</p>
        <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['event_revenue'], 2) }} €</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Paiements événements</p>
        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['evt_count']) }}</p>
    </div>
</div>

{{-- Tabs --}}
<div class="flex items-center gap-1 mb-4 bg-gray-100 rounded-xl p-1 w-fit">
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'subscriptions']) }}"
       class="px-4 py-1.5 rounded-lg text-sm font-semibold transition {{ $tab === 'subscriptions' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
        Abonnements
        <span class="ml-1.5 text-xs font-bold px-1.5 py-0.5 rounded-full {{ $tab === 'subscriptions' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-500' }}">
            {{ $subscriptions->total() }}
        </span>
    </a>
    <a href="{{ request()->fullUrlWithQuery(['tab' => 'events']) }}"
       class="px-4 py-1.5 rounded-lg text-sm font-semibold transition {{ $tab === 'events' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
        Événements
        <span class="ml-1.5 text-xs font-bold px-1.5 py-0.5 rounded-full {{ $tab === 'events' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-200 text-gray-500' }}">
            {{ $eventPayments->total() }}
        </span>
    </a>
</div>

{{-- ── Tab: Abonnements ──────────────────────────────────────────────────────── --}}
@if($tab === 'subscriptions')

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.super.payments.index') }}"
          class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3 flex-wrap">
        <input type="hidden" name="tab" value="subscriptions">
        <div class="relative flex-1 min-w-[180px]">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email…"
                   class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50 transition placeholder-gray-300">
        </div>
        <select name="plan_id" class="h-9 pl-3 pr-8 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-300 text-gray-600 bg-white">
            <option value="">Tous les plans</option>
            @foreach($plans as $p)
            <option value="{{ $p->id }}" {{ request('plan_id') == $p->id ? 'selected' : '' }}>{{ $p->label }}</option>
            @endforeach
        </select>
        <select name="status" class="h-9 pl-3 pr-8 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-300 text-gray-600 bg-white">
            <option value="">Tous les statuts</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Actif</option>
            <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Annulé</option>
        </select>
        <select name="billing_period" class="h-9 pl-3 pr-8 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-300 text-gray-600 bg-white">
            <option value="">Mensuel & Annuel</option>
            <option value="monthly" {{ request('billing_period') === 'monthly' ? 'selected' : '' }}>Mensuel</option>
            <option value="annual"  {{ request('billing_period') === 'annual'  ? 'selected' : '' }}>Annuel</option>
        </select>
        <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer whitespace-nowrap">
            <input type="checkbox" name="renewing_soon" value="1" {{ request('renewing_soon') ? 'checked' : '' }}
                   class="rounded border-gray-300 text-indigo-600">
            Renouvellement ≤ 7j
        </label>
        <button type="submit" class="h-9 px-4 rounded-xl text-xs font-semibold text-white transition hover:opacity-90" style="background:#6366F1;">Filtrer</button>
        @if(request()->hasAny(['search','plan_id','status']))
        <a href="{{ route('admin.super.payments.index', ['tab' => 'subscriptions']) }}"
           class="h-9 px-3 rounded-xl text-xs font-medium text-gray-500 border border-gray-200 flex items-center gap-1 hover:bg-gray-50 transition">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg> Reset
        </a>
        @endif
    </form>

    {{-- Table header --}}
    <div class="grid grid-cols-[1fr_130px_110px_110px_120px_140px_48px] px-5 py-2 border-b border-gray-50 bg-gray-50/60">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Membre</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Plan</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Montant</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Statut</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Début</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Renouvellement</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"></p>
    </div>

    {{-- Rows --}}
    @forelse($subscriptions as $sub)
    <div class="grid grid-cols-[1fr_130px_110px_110px_120px_140px_48px] items-center px-5 py-3 border-b border-gray-50 last:border-b-0 hover:bg-slate-50/50 transition-colors">

        {{-- User --}}
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                {{ strtoupper(substr($sub->user?->first_name ?? '?', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate leading-none">
                    {{ $sub->user?->first_name }} {{ $sub->user?->last_name }}
                </p>
                <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ $sub->user?->email }}</p>
            </div>
        </div>

        {{-- Plan --}}
        <p class="text-sm font-medium text-gray-700 truncate">{{ $sub->plan?->label ?? '—' }}</p>

        {{-- Amount --}}
        <p class="text-sm font-semibold text-gray-900">
            @if($sub->plan && (float) $sub->plan->price > 0)
                {{ number_format($sub->plan->price, 2) }} €<span class="text-gray-400 font-normal text-xs">/mois</span>
            @else
                <span class="text-gray-400">—</span>
            @endif
        </p>

        {{-- Status --}}
        <div>
            @if($sub->status === 'active')
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Actif
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Annulé
                </span>
            @endif
            @if($sub->cancel_at_period_end)
            <p class="text-[10px] text-amber-500 font-medium mt-0.5">Fin le {{ $sub->current_period_end?->format('d/m/Y') }}</p>
            @endif
        </div>

        {{-- Début --}}
        <div>
            <p class="text-sm text-gray-700">{{ $sub->created_at->format('d/m/Y') }}</p>
            <p class="text-[11px] text-gray-400">
                {{ $sub->billing_period === 'annual' ? 'Annuel' : 'Mensuel' }}
            </p>
        </div>

        {{-- Renouvellement --}}
        <div>
            @if($sub->current_period_end)
                @php
                    $isExpired   = $sub->current_period_end->isPast();
                    $isSoon      = !$isExpired && $sub->current_period_end->diffInDays(now()) <= 7;
                    $willCancel  = $sub->cancel_at_period_end;
                @endphp
                <p class="text-sm font-medium
                    {{ $isExpired  ? 'text-red-600' : ($isSoon ? 'text-amber-600' : 'text-gray-700') }}">
                    {{ $sub->current_period_end->format('d/m/Y') }}
                </p>
                <p class="text-[10px] font-semibold mt-0.5
                    {{ $willCancel ? 'text-amber-500' : ($isExpired ? 'text-red-400' : 'text-gray-400') }}">
                    @if($willCancel)   ⚠ Annulation prévue
                    @elseif($isExpired) Expiré
                    @elseif($isSoon)   Dans {{ $sub->current_period_end->diffInDays(now()) }}j
                    @else              Auto-renouvellement
                    @endif
                </p>
            @else
                <span class="text-gray-400 text-sm">—</span>
            @endif
        </div>

        {{-- Stripe link --}}
        @if($sub->stripe_subscription_id)
        <a href="https://dashboard.stripe.com/subscriptions/{{ $sub->stripe_subscription_id }}"
           target="_blank"
           title="Voir sur Stripe"
           class="w-8 h-8 rounded-lg flex items-center justify-center text-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 transition mx-auto">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
        @else
        <span></span>
        @endif

    </div>
    @empty
    <div class="px-5 py-16 text-center">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-3">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
        </div>
        <p class="text-sm font-semibold text-gray-400">Aucun abonnement Stripe trouvé</p>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($subscriptions->hasPages())
    <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400">
            {{ $subscriptions->firstItem() }}–{{ $subscriptions->lastItem() }} sur {{ $subscriptions->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if($subscriptions->onFirstPage())
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-200 cursor-not-allowed border border-gray-100">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </span>
            @else
            <a href="{{ $subscriptions->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 border border-gray-100 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            @endif
            @if($subscriptions->hasMorePages())
            <a href="{{ $subscriptions->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 border border-gray-100 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            @else
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-200 cursor-not-allowed border border-gray-100">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ── Tab: Événements ───────────────────────────────────────────────────────── --}}
@else

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('admin.super.payments.index') }}"
          class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3 flex-wrap">
        <input type="hidden" name="tab" value="events">
        <div class="relative flex-1 min-w-[180px]">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email…"
                   class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-300 focus:ring-2 focus:ring-indigo-50 transition placeholder-gray-300">
        </div>
        <select name="evt_status" class="h-9 pl-3 pr-8 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-300 text-gray-600 bg-white">
            <option value="">Tous les statuts</option>
            <option value="succeeded"               {{ request('evt_status') === 'succeeded'               ? 'selected' : '' }}>Réussi</option>
            <option value="failed"                  {{ request('evt_status') === 'failed'                  ? 'selected' : '' }}>Échoué</option>
            <option value="canceled"                {{ request('evt_status') === 'canceled'                ? 'selected' : '' }}>Annulé</option>
            <option value="requires_payment_method" {{ request('evt_status') === 'requires_payment_method' ? 'selected' : '' }}>En attente</option>
        </select>
        <button type="submit" class="h-9 px-4 rounded-xl text-xs font-semibold text-white transition hover:opacity-90" style="background:#6366F1;">Filtrer</button>
        @if(request()->hasAny(['search','evt_status']))
        <a href="{{ route('admin.super.payments.index', ['tab' => 'events']) }}"
           class="h-9 px-3 rounded-xl text-xs font-medium text-gray-500 border border-gray-200 flex items-center gap-1 hover:bg-gray-50 transition">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg> Reset
        </a>
        @endif
    </form>

    {{-- Table header --}}
    <div class="grid grid-cols-[1fr_1fr_110px_110px_130px_48px] px-5 py-2 border-b border-gray-50 bg-gray-50/60">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Membre</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Événement</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Montant</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Statut</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Date</p>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest"></p>
    </div>

    {{-- Rows --}}
    @forelse($eventPayments as $pmt)
    <div class="grid grid-cols-[1fr_1fr_110px_110px_130px_48px] items-center px-5 py-3 border-b border-gray-50 last:border-b-0 hover:bg-slate-50/50 transition-colors">

        {{-- User --}}
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                {{ strtoupper(substr($pmt->user?->first_name ?? '?', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate leading-none">
                    {{ $pmt->user?->first_name }} {{ $pmt->user?->last_name }}
                </p>
                <p class="text-[11px] text-gray-400 truncate mt-0.5">{{ $pmt->user?->email }}</p>
            </div>
        </div>

        {{-- Event --}}
        <p class="text-sm font-medium text-gray-700 truncate pr-2">{{ $pmt->event?->title ?? '—' }}</p>

        {{-- Amount --}}
        <p class="text-sm font-semibold text-gray-900">
            {{ number_format($pmt->amount / 100, 2) }} {{ strtoupper($pmt->currency) }}
        </p>

        {{-- Status --}}
        <span @class([
            'inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold w-fit',
            'bg-emerald-50 text-emerald-700' => $pmt->status === 'succeeded',
            'bg-red-50 text-red-600'         => in_array($pmt->status, ['failed', 'canceled']),
            'bg-amber-50 text-amber-700'     => !in_array($pmt->status, ['succeeded', 'failed', 'canceled']),
        ])>
            <span @class([
                'w-1.5 h-1.5 rounded-full',
                'bg-emerald-500' => $pmt->status === 'succeeded',
                'bg-red-500'     => in_array($pmt->status, ['failed', 'canceled']),
                'bg-amber-500'   => !in_array($pmt->status, ['succeeded', 'failed', 'canceled']),
            ])></span>
            {{ match($pmt->status) {
                'succeeded' => 'Réussi',
                'failed'    => 'Échoué',
                'canceled'  => 'Annulé',
                default     => 'En attente',
            } }}
        </span>

        {{-- Date --}}
        <div>
            <p class="text-sm text-gray-700">{{ $pmt->created_at->format('d/m/Y') }}</p>
            <p class="text-[11px] text-gray-400">{{ $pmt->created_at->format('H:i') }}</p>
        </div>

        {{-- Stripe link --}}
        @if($pmt->stripe_payment_intent_id)
        <a href="https://dashboard.stripe.com/payments/{{ $pmt->stripe_payment_intent_id }}"
           target="_blank"
           title="Voir sur Stripe"
           class="w-8 h-8 rounded-lg flex items-center justify-center text-indigo-400 hover:bg-indigo-50 hover:text-indigo-600 transition mx-auto">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
        @else
        <span></span>
        @endif

    </div>
    @empty
    <div class="px-5 py-16 text-center">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-3">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <p class="text-sm font-semibold text-gray-400">Aucun paiement événement trouvé</p>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if($eventPayments->hasPages())
    <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between">
        <p class="text-xs text-gray-400">
            {{ $eventPayments->firstItem() }}–{{ $eventPayments->lastItem() }} sur {{ $eventPayments->total() }}
        </p>
        <div class="flex items-center gap-1">
            @if($eventPayments->onFirstPage())
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-200 cursor-not-allowed border border-gray-100">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </span>
            @else
            <a href="{{ $eventPayments->previousPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 border border-gray-100 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            @endif
            @if($eventPayments->hasMorePages())
            <a href="{{ $eventPayments->nextPageUrl() }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 border border-gray-100 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            @else
            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-200 cursor-not-allowed border border-gray-100">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
            </span>
            @endif
        </div>
    </div>
    @endif
</div>
@endif

@endsection
