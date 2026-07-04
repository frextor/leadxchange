@extends('admin.layouts.admin')
@section('title', 'Icônes & Visuels')
@section('page-title', 'Icônes & Visuels')

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Notation & Badges</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Icônes & Visuels</h1>
        <p class="text-sm text-gray-400 mt-1">Chargez les images affichées sur les profils, le dashboard et les pages leads.</p>
    </div>
    <a href="{{ route('admin.notation.index') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 transition">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
        Retour
    </a>
</div>

@php
    $planMeta = [
        'basic'       => ['label' => 'Basic',       'color' => 'gray'],
        'premium'     => ['label' => 'Premium',      'color' => 'indigo'],
        'consul'      => ['label' => 'Consul',       'color' => 'teal'],
        'ambassadeur' => ['label' => 'Ambassadeur',  'color' => 'amber'],
    ];
    $badgeMeta = [
        'neutre'    => ['label' => 'Neutre',    'color' => 'gray'],
        'bronze'    => ['label' => 'Bronze',    'color' => 'amber'],
        'argent'    => ['label' => 'Argent',    'color' => 'slate'],
        'or'        => ['label' => 'Or',        'color' => 'yellow'],
        'platinium' => ['label' => 'Platinium', 'color' => 'indigo'],
    ];
@endphp

{{-- ── PLANS ── --}}
<div class="mb-8">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold text-gray-900">Icônes des Plans</p>
            <p class="text-xs text-gray-400">Affichées sur le dashboard et le profil de chaque membre</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($plans as $key)
        @php
            $meta = $planMeta[$key];
            $imgPath = public_path('images/plans/' . $key . '.jpg');
            $hasImg = file_exists($imgPath);
            $successKey = 'success_plan_' . $key;
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Preview --}}
            <div class="bg-gray-50 h-36 flex items-center justify-center relative">
                @if($hasImg)
                <img src="{{ asset('images/plans/' . $key . '.jpg') }}?v={{ filemtime($imgPath) }}"
                     alt="{{ $meta['label'] }}"
                     class="max-h-32 max-w-full object-contain p-2">
                @else
                <div class="text-center">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5" class="mx-auto mb-2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <p class="text-xs text-gray-300">Aucune image</p>
                </div>
                @endif
                @if(session($successKey))
                <div class="absolute top-2 right-2 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                </div>
                @endif
            </div>
            {{-- Info + Upload --}}
            <div class="p-4">
                <p class="text-sm font-bold text-gray-900 mb-3">{{ $meta['label'] }}</p>
                <form method="POST"
                      action="{{ route('admin.notation.icons.plan', $key) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <label class="flex flex-col items-center gap-2 w-full cursor-pointer group">
                        <div class="w-full border-2 border-dashed border-gray-200 group-hover:border-indigo-300 rounded-xl p-3 text-center transition">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" class="mx-auto mb-1 group-hover:stroke-indigo-400 transition"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p class="text-[11px] text-gray-400 group-hover:text-indigo-500 transition">
                                {{ $hasImg ? 'Remplacer' : 'Choisir une image' }}
                            </p>
                            <p class="text-[10px] text-gray-300 mt-0.5">JPG, PNG, WebP · max 4 Mo</p>
                        </div>
                        <input type="file" name="icon" accept="image/jpeg,image/png,image/webp"
                               class="hidden" onchange="this.closest('form').submit()">
                    </label>
                    @error('icon')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ── BADGES SCORE ── --}}
<div>
    <div class="flex items-center gap-3 mb-4">
        <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold text-gray-900">Icônes des Badges Score</p>
            <p class="text-xs text-gray-400">Affichées sur le profil et la page Leads selon le niveau de points</p>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach($badges as $key)
        @php
            $meta = $badgeMeta[$key];
            $imgPath = public_path('images/badges/' . $key . '.jpg');
            $hasImg = file_exists($imgPath);
            $successKey = 'success_badge_' . $key;
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Preview --}}
            <div class="bg-gray-50 h-32 flex items-center justify-center relative">
                @if($hasImg)
                <img src="{{ asset('images/badges/' . $key . '.jpg') }}?v={{ filemtime($imgPath) }}"
                     alt="{{ $meta['label'] }}"
                     class="max-h-28 max-w-full object-contain p-2">
                @else
                <div class="text-center">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5" class="mx-auto mb-1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <p class="text-[11px] text-gray-300">Aucune image</p>
                </div>
                @endif
                @if(session($successKey))
                <div class="absolute top-2 right-2 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="m5 12 5 5L20 7"/></svg>
                </div>
                @endif
            </div>
            {{-- Info + Upload --}}
            <div class="p-3">
                <p class="text-xs font-bold text-gray-900 mb-2">{{ $meta['label'] }}</p>
                <form method="POST"
                      action="{{ route('admin.notation.icons.badge', $key) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <label class="flex flex-col items-center gap-1.5 w-full cursor-pointer group">
                        <div class="w-full border-2 border-dashed border-gray-200 group-hover:border-amber-300 rounded-xl p-2 text-center transition">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" class="mx-auto mb-0.5 group-hover:stroke-amber-400 transition"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p class="text-[10px] text-gray-400 group-hover:text-amber-500 transition">
                                {{ $hasImg ? 'Remplacer' : 'Choisir' }}
                            </p>
                        </div>
                        <input type="file" name="icon" accept="image/jpeg,image/png,image/webp"
                               class="hidden" onchange="this.closest('form').submit()">
                    </label>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
