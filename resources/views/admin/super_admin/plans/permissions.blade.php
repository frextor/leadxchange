@extends('admin.layouts.admin')
@section('title', 'Permissions des plans')
@section('page-title', 'Plans')

@section('content')

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin &rsaquo; Plans</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Permissions des plans</h1>
        <p class="text-sm text-gray-400 mt-1">Configurez les fonctionnalités accessibles pour chaque plan d'abonnement.</p>
    </div>
    <a href="{{ route('admin.super.plans.index') }}"
       class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M5 12l7 7M5 12l7-7"/></svg>
        Retour aux plans
    </a>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-4 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-medium">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5L20 7"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.super.plans.permissions.update') }}">
@csrf

{{-- ── Legend ──────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-4 mb-5 text-[10px] font-semibold text-gray-400 uppercase tracking-widest">
    <div class="flex items-center gap-1.5">
        <div class="w-5 h-5 rounded-md bg-emerald-500 flex items-center justify-center">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
        </div>
        Inclus
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-5 h-5 rounded-md bg-gray-200 flex items-center justify-center">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="3"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </div>
        Non inclus
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-16 h-5 rounded-md bg-indigo-100 text-indigo-600 flex items-center justify-center text-[9px] font-bold">
            ∞ Illimité
        </div>
        Illimité (vide)
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-16 h-5 rounded-md bg-indigo-100 text-indigo-600 flex items-center justify-center text-[9px] font-bold">
            50
        </div>
        Valeur limite
    </div>
</div>

{{-- ── Matrix ───────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    {{-- Plan header row --}}
    <div class="grid border-b border-gray-100" style="grid-template-columns: 260px repeat({{ count($plans) }}, 1fr);">
        <div class="px-5 py-4 border-r border-gray-100">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Fonctionnalité</span>
        </div>
        @foreach($plans as $plan)
        @php
            $themes = [
                'basic'       => ['top' => '#6366F1', 'light' => '#EEF2FF', 'text' => '#4338CA'],
                'vip'         => ['top' => '#F59E0B', 'light' => '#FFFBEB', 'text' => '#92400E'],
                'enterprise'  => ['top' => '#0D9488', 'light' => '#F0FDFA', 'text' => '#0F766E'],
            ];
            $t = $themes[$plan->name] ?? ['top' => '#6B7280', 'light' => '#F9FAFB', 'text' => '#374151'];
        @endphp
        <div class="px-4 py-4 text-center" style="background:{{ $t['light'] }}; border-bottom: 3px solid {{ $t['top'] }};">
            <p class="text-sm font-bold" style="color:{{ $t['text'] }};">{{ $plan->label }}</p>
            <p class="text-[10px] text-gray-400 font-medium mt-0.5">
                @if($plan->price == 0) Gratuit @else {{ currency_format($plan->price) }}/mois @endif
            </p>
        </div>
        @endforeach
    </div>

    {{-- Feature rows --}}
    @foreach($features as $key => $def)
    @php $isNumber = $def['type'] === 'number'; @endphp
    <div class="grid border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition"
         style="grid-template-columns: 260px repeat({{ count($plans) }}, 1fr);">

        {{-- Feature label --}}
        <div class="px-5 py-4 border-r border-gray-100 flex flex-col justify-center">
            <span class="text-sm font-semibold text-gray-800">{{ $def['label'] }}</span>
            @if($isNumber)
            <span class="text-[10px] text-gray-400 font-medium mt-0.5">Nombre ou vide = illimité</span>
            @endif
        </div>

        {{-- Plan cells --}}
        @foreach($plans as $plan)
        @php
            $val = $plan->features[$key] ?? false;
            $fieldKey = "features_{$plan->id}_{$key}";
        @endphp
        <div class="px-4 py-4 flex items-center justify-center" id="cell-{{ $plan->id }}-{{ $key }}">
            @if($isNumber)
            {{-- Number input --}}
            <input type="number" name="{{ $fieldKey }}" min="0"
                   value="{{ $val !== null ? $val : '' }}"
                   placeholder="∞"
                   class="w-20 text-center border border-gray-200 rounded-xl px-2 py-1.5 text-sm font-medium focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition placeholder-indigo-300">
            @else
            {{-- Toggle checkbox --}}
            <label class="cursor-pointer">
                <input type="hidden" name="{{ $fieldKey }}" value="0">
                <input type="checkbox" name="{{ $fieldKey }}" value="1"
                       {{ $val ? 'checked' : '' }}
                       class="peer sr-only toggle-bool"
                       data-plan="{{ $plan->id }}" data-key="{{ $key }}">
                <div class="w-10 h-6 rounded-full transition relative peer-checked:bg-emerald-400 bg-gray-200">
                    <div class="absolute top-1 left-1 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4 peer-not-checked:translate-x-0"
                         style="transform:{{ $val ? 'translateX(16px)' : 'translateX(0)' }}"></div>
                </div>
            </label>
            @endif
        </div>
        @endforeach
    </div>
    @endforeach
</div>

{{-- ── Submit ────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3 mt-6">
    <button type="submit"
            class="flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
        Enregistrer les permissions
    </button>
    <p class="text-xs text-gray-400">Les modifications prennent effet immédiatement pour tous les utilisateurs abonnés.</p>
</div>

</form>

<script>
document.querySelectorAll('.toggle-bool').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var thumb = this.closest('label').querySelector('div > div');
        if (thumb) thumb.style.transform = this.checked ? 'translateX(16px)' : 'translateX(0)';
    });
});
</script>

@endsection
