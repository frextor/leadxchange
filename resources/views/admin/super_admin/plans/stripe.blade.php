@extends('admin.layouts.admin')
@section('title', 'Stripe — Plans')
@section('page-title', 'Plans')

@section('content')

<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Plans</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            Gestion Stripe
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $isConfigured ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $isConfigured ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                {{ $isConfigured ? 'API connectée' : 'Clé manquante' }}
            </span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Créez et synchronisez les produits/prix Stripe depuis l'admin.</p>
    </div>

    @if($isConfigured)
    <form method="POST" action="{{ route('admin.super.plans.stripe.sync-all') }}">
        @csrf
        <button type="submit"
                onclick="return confirm('Synchroniser tous les plans payants avec Stripe ?')"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                style="background:#4338CA;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            Tout synchroniser
        </button>
    </form>
    @endif
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-2xl px-5 py-3 text-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
    {{ session('error') }}
</div>
@endif

@if(! $isConfigured)
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mb-6">
    <div class="flex items-start gap-3">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" class="flex-shrink-0 mt-0.5"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <div>
            <p class="font-semibold text-amber-800 mb-1">Clés Stripe non configurées</p>
            <p class="text-sm text-amber-700">Ajoutez dans votre <code class="bg-amber-100 px-1 rounded">.env</code> :</p>
            <pre class="mt-2 bg-amber-100 rounded-xl px-4 py-3 text-xs text-amber-900 font-mono">STRIPE_SECRET_KEY=sk_live_...
STRIPE_PUBLISHABLE_KEY=pk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_CURRENCY=eur</pre>
        </div>
    </div>
</div>
@endif

{{-- Plans grid --}}
<div class="space-y-3">
    @foreach($plans as $plan)
    @php
        $sp = $stripeProducts[$plan->id] ?? null;
        $hasStripe = $plan->stripe_price_id && $plan->stripe_product_id;
        $isFree = (float) $plan->price === 0.0;
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5" id="plan-card-{{ $plan->id }}">
        <div class="flex items-start gap-4">

            {{-- Plan info --}}
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                 style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                {{ strtoupper(substr($plan->name, 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <p class="font-bold text-gray-900">{{ $plan->label }}</p>
                    <span class="text-sm font-semibold text-gray-500">{{ $plan->price > 0 ? currency_format($plan->price).'/mois' : 'Gratuit' }}</span>
                    @if($isFree)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 font-semibold">Pas de paiement</span>
                    @elseif($hasStripe)
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold">✓ Stripe configuré</span>
                    @else
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-semibold">⚠ Non synchronisé</span>
                    @endif
                </div>

                {{-- Stripe IDs --}}
                @if(! $isFree)
                <div class="grid grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Stripe Product ID</label>
                        <div class="flex items-center gap-2">
                            <code class="text-xs font-mono text-gray-600 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100 truncate flex-1">
                                {{ $plan->stripe_product_id ?? '—' }}
                            </code>
                            @if($sp && $sp['product'])
                            <a href="https://dashboard.stripe.com/products/{{ $plan->stripe_product_id }}"
                               target="_blank"
                               class="text-[10px] text-indigo-500 hover:text-indigo-700 flex-shrink-0">↗</a>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Stripe Price ID</label>
                        <div class="flex items-center gap-2">
                            <code class="text-xs font-mono text-gray-600 bg-gray-50 px-2 py-1 rounded-lg border border-gray-100 truncate flex-1">
                                {{ $plan->stripe_price_id ?? '—' }}
                            </code>
                            @if($sp && $sp['price'])
                            <a href="https://dashboard.stripe.com/prices/{{ $plan->stripe_price_id }}"
                               target="_blank"
                               class="text-[10px] text-indigo-500 hover:text-indigo-700 flex-shrink-0">↗</a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Price details from Stripe --}}
                @if($sp && $sp['price'])
                <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                    <span>💳 {{ number_format($sp['price']->unit_amount / 100, 2) }} {{ strtoupper($sp['price']->currency) }}/{{ $sp['price']->recurring?->interval ?? '—' }}</span>
                    <span class="px-1.5 py-0.5 rounded-md {{ $sp['price']->active ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                        {{ $sp['price']->active ? 'Actif' : 'Archivé' }}
                    </span>
                </div>
                @endif
                @endif
            </div>

            {{-- Sync button --}}
            @if(! $isFree && $isConfigured)
            <button onclick="syncPlan({{ $plan->id }}, '{{ $plan->label }}')"
                    id="sync-btn-{{ $plan->id }}"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition flex-shrink-0
                           {{ $hasStripe ? 'border-gray-200 text-gray-600 hover:bg-gray-50' : 'border-indigo-200 text-indigo-600 hover:bg-indigo-50' }}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                {{ $hasStripe ? 'Resynchroniser' : 'Créer sur Stripe' }}
            </button>
            @endif

        </div>

        {{-- Result message zone --}}
        <div id="sync-result-{{ $plan->id }}" class="hidden mt-3 px-4 py-2 rounded-xl text-xs font-medium"></div>
    </div>
    @endforeach
</div>

{{-- Webhook info --}}
<div class="mt-6 bg-gray-50 rounded-2xl border border-gray-100 p-5">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Configuration Webhook Stripe</p>
    <p class="text-sm text-gray-600 mb-2">Ajoutez ce webhook dans votre <a href="https://dashboard.stripe.com/webhooks" target="_blank" class="text-indigo-500 hover:underline">Stripe Dashboard</a> :</p>
    <code class="block bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-mono text-gray-800">
        {{ config('app.url') }}/stripe/webhook
    </code>
    <p class="text-xs text-gray-400 mt-2">Événements à activer : <code>customer.subscription.updated</code>, <code>customer.subscription.deleted</code>, <code>checkout.session.completed</code></p>
</div>

@endsection

@push('scripts')
<script>
const syncUrl = '{{ url("/admin/super/plans/stripe") }}';
const csrf    = document.querySelector('meta[name="csrf-token"]')?.content
             || '{{ csrf_token() }}';

async function syncPlan(planId, planLabel) {
    const btn    = document.getElementById('sync-btn-' + planId);
    const result = document.getElementById('sync-result-' + planId);

    btn.disabled = true;
    btn.innerHTML = `<svg class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Synchronisation…`;

    try {
        const res  = await fetch(`${syncUrl}/${planId}/sync`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        });
        const data = await res.json();

        if (data.success) {
            result.className = 'mt-3 px-4 py-2 rounded-xl text-xs font-medium bg-emerald-50 text-emerald-700';
            result.innerHTML = `✓ <strong>${planLabel}</strong> synchronisé — Product: <code>${data.stripe_product_id}</code> · Price: <code>${data.stripe_price_id}</code> · ${data.amount}`;
            result.classList.remove('hidden');
            btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 11 3 3L22 4"/></svg> Synchronisé ✓`;
            btn.className = btn.className.replace('border-indigo-200 text-indigo-600 hover:bg-indigo-50', 'border-emerald-200 text-emerald-600 hover:bg-emerald-50');
            // Update IDs displayed
            setTimeout(() => location.reload(), 1500);
        } else {
            result.className = 'mt-3 px-4 py-2 rounded-xl text-xs font-medium bg-red-50 text-red-700';
            result.innerHTML = `✗ Erreur : ${data.error}`;
            result.classList.remove('hidden');
            btn.disabled = false;
            btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg> Réessayer`;
        }
    } catch (e) {
        result.className = 'mt-3 px-4 py-2 rounded-xl text-xs font-medium bg-red-50 text-red-700';
        result.innerHTML = '✗ Erreur réseau. Vérifiez la console.';
        result.classList.remove('hidden');
        btn.disabled = false;
    }
}
</script>
@endpush
