@props(['feature', 'title' => null, 'description' => null, 'fullPage' => false])

@php
    $user = auth()->user();
    $can  = $user && $user->canFeature($feature);

    if (!$can) {
        // Find the cheapest plan that has this feature enabled
        $upgradePlan = \App\Models\Plan::where('is_active', true)
            ->orderBy('price')
            ->get()
            ->first(function ($p) use ($feature) {
                $features = is_array($p->features) ? $p->features : [];
                $val = $features[$feature] ?? false;
                if (is_bool($val)) return $val === true;
                if (is_int($val)) return $val > 0;
                return $val === null; // null = unlimited
            });

        $featureLabels = \App\Http\Controllers\Admin\SuperAdmin\PlanController::FEATURES;
        $featureLabel  = $featureLabels[$feature]['label'] ?? ucfirst(str_replace('_', ' ', $feature));
    }
@endphp

@if($can)
    {{ $slot }}
@elseif($fullPage)
{{-- ── Full-page upgrade wall ──────────────────────────────────────────── --}}
<div class="flex flex-col items-center justify-center min-h-[420px] px-4 py-16 text-center">
    {{-- Lock icon --}}
    <div class="w-20 h-20 rounded-3xl flex items-center justify-center mb-6 shadow-sm"
         style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF);">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
    </div>

    {{-- Text --}}
    <h2 class="text-xl font-bold text-gray-900 mb-2">
        {{ $title ?? 'Fonctionnalité non disponible' }}
    </h2>
    <p class="text-sm text-gray-500 max-w-sm leading-relaxed mb-2">
        {{ $description ?? "La fonctionnalité « {$featureLabel} » n'est pas incluse dans votre plan actuel." }}
    </p>
    @if($upgradePlan)
    <p class="text-xs text-indigo-500 font-semibold mb-6">
        Disponible à partir du plan
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100 ml-1">
            {{ $upgradePlan->label }}
            @if($upgradePlan->price > 0)
            · {{ currency_format($upgradePlan->price) }}/mois
            @else
            · Gratuit
            @endif
        </span>
    </p>
    @else
    <div class="mb-6"></div>
    @endif

    {{-- CTA --}}
    <a href="{{ route('upgrade') }}"
       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl text-sm font-semibold text-white shadow-sm transition hover:opacity-90 active:scale-[.98]"
       style="background:linear-gradient(135deg,#6366F1,#4338CA);">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        Mettre à niveau mon plan
    </a>
    <a href="{{ route('dashboard') }}"
       class="mt-3 text-xs text-gray-400 hover:text-gray-600 transition underline underline-offset-2">
        Retour au tableau de bord
    </a>
</div>

@else
{{-- ── Inline overlay (wraps the slot with blur + badge) ─────────────────── --}}
<div class="relative rounded-2xl overflow-hidden">
    {{-- Blurred slot content --}}
    <div class="pointer-events-none select-none" style="filter:blur(3px);opacity:.45;">
        {{ $slot }}
    </div>

    {{-- Overlay --}}
    <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 rounded-2xl"
         style="background:rgba(255,255,255,.72);backdrop-filter:saturate(0) blur(0px);">

        <div class="w-10 h-10 rounded-2xl flex items-center justify-center"
             style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>

        <div class="text-center px-4">
            <p class="text-sm font-bold text-gray-900">
                {{ $title ?? 'Upgrade requis' }}
            </p>
            <p class="text-xs text-gray-500 mt-0.5 leading-snug">
                {{ $description ?? "« {$featureLabel} » n'est pas inclus dans votre plan." }}
            </p>
        </div>

        @if($upgradePlan)
        <div class="text-center">
            <p class="text-[10px] text-gray-400 mb-1.5">
                Disponible avec le plan
                <span class="font-semibold text-indigo-500">{{ $upgradePlan->label }}</span>
            </p>
        </div>
        @endif

        <a href="{{ route('upgrade') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white shadow-sm transition hover:opacity-90"
           style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Mettre à niveau
        </a>
    </div>
</div>
@endif
