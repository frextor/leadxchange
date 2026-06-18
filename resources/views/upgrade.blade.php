@extends('layouts.app')

@section('title', 'Mettre à niveau — LeadXchange')

@section('content')
@php
    $featureDefs = \App\Http\Controllers\Admin\SuperAdmin\PlanController::FEATURES;

    $themes = [
        'basic'       => ['top' => '#64748B', 'bg' => '#F8FAFC', 'accent' => '#475569'],
        'ambassadeur' => ['top' => '#D97706', 'bg' => '#FFFBEB', 'accent' => '#92400E'],
        'premium_gold'=> ['top' => '#6366F1', 'bg' => '#EEF2FF', 'accent' => '#4338CA'],
        'enterprise'  => ['top' => '#0D9488', 'bg' => '#F0FDFA', 'accent' => '#0F766E'],
    ];
@endphp

<div class="max-w-5xl mx-auto px-4 py-10">

    {{-- Header --}}
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold mb-4"
             style="background:#EEF2FF;color:#4338CA;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Plans d'abonnement
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 mb-3">Choisissez votre plan</h1>
        <p class="text-sm text-gray-500 max-w-md mx-auto">
            Débloquez toutes les fonctionnalités de LeadXchange et développez votre réseau professionnel.
        </p>
    </div>

    {{-- Current plan banner --}}
    @if($currentPlan)
    <div class="mb-7 flex items-center justify-center gap-2 text-sm">
        <span class="text-gray-500">Plan actuel :</span>
        <span class="font-bold text-gray-900">{{ $currentPlan->label }}</span>
        @if($currentPlan->price == 0)
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">Gratuit</span>
        @else
        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600">{{ currency_format($currentPlan->price) }}/mois</span>
        @endif
    </div>
    @endif

    {{-- Plans grid --}}
    <div class="grid grid-cols-1 md:grid-cols-{{ min($plans->count(), 3) }} gap-5 mb-10">
        @foreach($plans as $plan)
        @php
            $t = $themes[$plan->name] ?? $themes['basic'];
            $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
            $planFeatures = is_array($plan->features) ? $plan->features : [];
        @endphp
        <div class="bg-white rounded-2xl border-2 overflow-hidden flex flex-col transition-shadow hover:shadow-lg relative
                    {{ $isCurrent ? 'border-indigo-300 shadow-md' : 'border-gray-100' }}"
             style="{{ $isCurrent ? '' : "border-top:3px solid {$t['top']};" }}">

            @if($isCurrent)
            <div class="absolute top-3 right-3">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500 text-white">
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                    Votre plan
                </span>
            </div>
            @endif

            {{-- Plan header --}}
            <div class="px-5 pt-5 pb-4">
                <h3 class="text-base font-bold text-gray-900 mb-0.5">{{ $plan->label }}</h3>
                @if($plan->description)
                <p class="text-xs text-gray-400 leading-relaxed">{{ $plan->description }}</p>
                @endif
            </div>

            {{-- Price --}}
            <div class="px-5 py-4 border-y border-gray-100" style="background:{{ $t['bg'] }};">
                @if((float)$plan->price === 0.0)
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-gray-900">Gratuit</span>
                    <span class="text-xs text-gray-400">pour toujours</span>
                </div>
                @else
                <div class="flex items-baseline gap-1">
                    <span class="text-3xl font-extrabold text-gray-900">{{ currency_format($plan->price) }}</span>
                    <span class="text-xs text-gray-400">/ mois</span>
                </div>
                @if($plan->annual_price)
                <p class="text-xs text-gray-400 mt-1">ou {{ currency_format($plan->annual_price) }}/an</p>
                @endif
                @endif
            </div>

            {{-- Features list --}}
            <div class="px-5 py-4 flex-1">
                <ul class="space-y-2">
                    @foreach($featureDefs as $fKey => $fDef)
                    @php
                        $val = $planFeatures[$fKey] ?? ($fDef['type'] === 'number' ? null : false);
                        $enabled = is_bool($val) ? $val : ($val === null || (is_int($val) && $val > 0));
                    @endphp
                    <li class="flex items-center gap-2.5">
                        @if($enabled)
                        <span class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0"
                              style="background:{{ $t['bg'] }};">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="{{ $t['top'] }}" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                        </span>
                        <span class="text-xs text-gray-700">{{ $fDef['label'] }}</span>
                        @if($fDef['type'] === 'number' && is_int($val) && $val > 0)
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-md tabular-nums"
                              style="background:{{ $t['bg'] }};color:{{ $t['accent'] }};">{{ $val }}</span>
                        @elseif($fDef['type'] === 'number' && $val === null)
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-md"
                              style="background:{{ $t['bg'] }};color:{{ $t['accent'] }};">∞</span>
                        @endif
                        @else
                        <span class="w-4 h-4 rounded-full bg-gray-50 flex items-center justify-center flex-shrink-0">
                            <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </span>
                        <span class="text-xs text-gray-300 line-through">{{ $fDef['label'] }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>

            {{-- CTA --}}
            <div class="px-5 pb-5 pt-3">
                @if($isCurrent)
                <div class="w-full py-2.5 rounded-xl text-xs font-semibold text-center border-2 border-indigo-200 text-indigo-400 bg-indigo-50">
                    Plan actuel
                </div>
                @else
                <a href="#contact"
                   class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 active:scale-[.98]"
                   style="background:linear-gradient(135deg,{{ $t['top'] }},{{ $t['accent'] }});"
                   onclick="showUpgradeContact('{{ $plan->label }}')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Passer au plan {{ $plan->label }}
                </a>
                @endif
            </div>

        </div>
        @endforeach
    </div>

    {{-- Contact section --}}
    <div id="contact" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center max-w-lg mx-auto">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"
             style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h2 class="text-base font-bold text-gray-900 mb-2" id="contactTitle">Mettre à niveau votre plan</h2>
        <p class="text-sm text-gray-500 mb-5 leading-relaxed">
            Pour upgrader votre abonnement, contactez notre équipe ou utilisez l'application mobile LeadXchange.
        </p>
        <a href="mailto:support@leadxchange.ma?subject=Demande%20d%27upgrade%20de%20plan"
           class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
           style="background:linear-gradient(135deg,#6366F1,#4338CA);">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Contacter le support
        </a>
        <p class="mt-3 text-xs text-gray-400">ou utilisez l'application mobile pour gérer votre abonnement</p>
    </div>

</div>

@push('scripts')
<script>
function showUpgradeContact(planName) {
    document.getElementById('contactTitle').textContent = 'Passer au plan ' + planName;
    setTimeout(function() {
        document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
    }, 100);
}
</script>
@endpush

@endsection
