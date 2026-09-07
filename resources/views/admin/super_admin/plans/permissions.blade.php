@extends('admin.layouts.admin')
@section('title', 'Permissions des plans')
@section('page-title', 'Plans')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Plans</p>
        <h1 class="text-2xl font-bold text-gray-900">Permissions par plan</h1>
        <p class="text-sm text-gray-400 mt-1">Cochez pour autoriser, décochez pour interdire.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.super.plans.index') }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
            ← Retour
        </a>
        <button form="perms-form" type="submit"
                class="px-5 py-2 rounded-xl text-sm font-bold text-white hover:opacity-90 transition"
                style="background:#4338CA;">
            Enregistrer
        </button>
    </div>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm font-medium">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

@php
$planThemes = [
    'basic'       => ['color' => '#64748B', 'light' => '#F1F5F9'],
    'premium'     => ['color' => '#6366F1', 'light' => '#EEF2FF'],
    'consul'      => ['color' => '#2F44E0', 'light' => '#F0FDFA'],
    'ambassadeur' => ['color' => '#D97706', 'light' => '#FFFBEB'],
    'enterprise'  => ['color' => '#2563EB', 'light' => '#EFF6FF'],
];
@endphp

<form id="perms-form" method="POST" action="{{ route('admin.super.plans.permissions.update') }}">
@csrf

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full border-collapse">

    {{-- Plan header --}}
    <thead>
        <tr>
            <th class="text-left px-5 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100 w-64">
                Permission
            </th>
            @foreach($plans as $plan)
            @php $t = $planThemes[$plan->name] ?? $planThemes['basic']; @endphp
            <th class="px-4 py-4 border-b border-gray-100 text-center min-w-[120px]"
                style="background:{{ $t['light'] }};">
                <span class="block text-sm font-extrabold" style="color:{{ $t['color'] }};">{{ $plan->label }}</span>
                <span class="block text-[11px] font-medium text-gray-400 mt-0.5">
                    {{ $plan->price > 0 ? currency_format($plan->price).'/mois' : 'Basic' }}
                </span>
            </th>
            @endforeach
        </tr>
    </thead>

    <tbody>
    @foreach($permissions as $group => $perms)

        {{-- Group row --}}
        <tr>
            <td colspan="{{ $plans->count() + 1 }}"
                class="px-5 py-2.5 bg-gray-50 border-y border-gray-100">
                <span class="text-[10px] font-extrabold text-gray-500 uppercase tracking-widest">
                    {{ $group }}
                </span>
            </td>
        </tr>

        {{-- Permission rows --}}
        @foreach($perms as $key => $def)
        <tr class="border-b border-gray-50 hover:bg-blue-50/20 transition-colors">

            {{-- Permission label --}}
            <td class="px-5 py-3">
                <p class="text-sm font-medium text-gray-800">{{ $def['label'] }}</p>
                @if($def['type'] === 'number')
                <p class="text-[10px] text-gray-400 mt-0.5">Laisser vide = illimité</p>
                @endif
            </td>

            {{-- Value per plan --}}
            @foreach($plans as $plan)
            @php
                $permsArr = is_array($plan->permissions) ? $plan->permissions : [];
                $val = array_key_exists($key, $permsArr) ? $permsArr[$key] : null;
                $t = $planThemes[$plan->name] ?? $planThemes['basic'];
                $fieldName = "perm_{$plan->id}_{$key}";
            @endphp
            <td class="px-4 py-3 text-center">

                @if($def['type'] === 'bool')
                {{-- Checkbox --}}
                <label class="inline-flex items-center justify-center cursor-pointer">
                    <input type="checkbox"
                           name="{{ $fieldName }}"
                           value="1"
                           {{ $val ? 'checked' : '' }}
                           class="w-5 h-5 rounded cursor-pointer"
                           style="accent-color:{{ $t['color'] }};">
                </label>

                @elseif($def['type'] === 'number')
                {{-- Number input --}}
                <input type="number"
                       name="{{ $fieldName }}"
                       value="{{ $val !== null ? $val : '' }}"
                       min="0"
                       placeholder="∞"
                       class="w-20 border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-center focus:outline-none focus:ring-2 focus:border-transparent transition"
                       style="focus-ring-color:{{ $t['color'] }};">
                @endif

            </td>
            @endforeach
        </tr>
        @endforeach

    @endforeach
    </tbody>

</table>
</div>
</div>

{{-- Legend --}}
<div class="mt-4 flex items-center gap-6 text-xs text-gray-400 px-1">
    <div class="flex items-center gap-1.5">
        <input type="checkbox" checked disabled class="w-4 h-4 rounded" style="accent-color:#6366F1;">
        <span>Autorisé</span>
    </div>
    <div class="flex items-center gap-1.5">
        <input type="checkbox" disabled class="w-4 h-4 rounded">
        <span>Interdit</span>
    </div>
    <div class="flex items-center gap-1.5">
        <input type="text" value="∞" disabled class="w-12 border border-gray-200 rounded px-1 text-center text-xs text-gray-300">
        <span>Vide = illimité</span>
    </div>
    <div class="flex items-center gap-1.5">
        <input type="number" value="10" disabled class="w-12 border border-gray-200 rounded px-1 text-center text-xs">
        <span>Nombre = limite</span>
    </div>
</div>

</form>

@endsection
