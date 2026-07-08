@extends('admin.layouts.admin')
@section('title', 'Notation')
@section('page-title', 'Notation')

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Administration</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Notation des membres</h1>
        <p class="text-sm text-gray-400 mt-1">Scores calculés sur les 60 derniers jours glissants.</p>
    </div>
    <form method="POST" action="{{ route('admin.notation.recalculate-all') }}">
        @csrf
        <button type="submit"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                style="background:#4338CA;"
                onclick="return confirm('Recalculer les scores de tous les membres ?')">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            Recalculer tout
        </button>
    </form>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- ── Explication du système de points (CGU §6) ────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-5 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between cursor-pointer"
         onclick="this.nextElementSibling.classList.toggle('hidden')">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4338CA" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900">Fonctionnement du système de points (CGU §6)</p>
                <p class="text-xs text-gray-400">Cliquez pour afficher / masquer</p>
            </div>
        </div>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
    </div>

    <div class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 divide-y lg:divide-y-0 lg:divide-x divide-gray-100">

            {{-- Colonne 1 : Flux de points --}}
            <div class="p-5">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Flux de points</p>

                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                        <span class="text-lg font-extrabold text-emerald-600 leading-none mt-0.5">+2</span>
                        <div>
                            <p class="text-xs font-semibold text-emerald-800">Lead envoyé (accepté)</p>
                            <p class="text-[11px] text-emerald-700 mt-0.5">Crédité à l'émetteur dès acceptation</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-red-50 border border-red-100">
                        <span class="text-lg font-extrabold text-red-500 leading-none mt-0.5">−1</span>
                        <div>
                            <p class="text-xs font-semibold text-red-800">Lead reçu (accepté)</p>
                            <p class="text-[11px] text-red-700 mt-0.5">Débité au receveur dès acceptation</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100">
                        <span class="text-xs font-extrabold text-amber-700 leading-none mt-0.5 whitespace-nowrap">+N / −N</span>
                        <div>
                            <p class="text-xs font-semibold text-amber-800">Bonification post-RDV</p>
                            <p class="text-[11px] text-amber-700 mt-0.5">Receveur attribue des points → émetteur les reçoit, receveur les perd</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2">Limites</p>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Plafond maximum</span>
                            <span class="font-bold text-gray-800">30 pts</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Minimum pour recevoir</span>
                            <span class="font-bold text-gray-800">≥ 1 pt</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Suspension réception</span>
                            <span class="font-bold text-gray-800">Solde nul 2 mois</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Colonne 2 : Grille de bonification --}}
            <div class="p-5">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Grille de bonification (CGU §6.2.2)</p>

                <div class="space-y-2">
                    @foreach([
                        ['UQ', 'Unqualified Lead',         0, 'bg-gray-100 text-gray-500',     'Non qualifié'],
                        ['MQL','Marketing Qualified Lead', 1, 'bg-blue-100 text-blue-700',     'Lead basique / peu qualifié'],
                        ['SQL','Sale Qualified Lead',      3, 'bg-indigo-100 text-indigo-700', 'Lead très qualifié / RDV abouti'],
                        ['SP', 'Sale Process',             5, 'bg-violet-100 text-violet-700', 'Lead exceptionnel / affaire conclue'],
                    ] as [$code, $label, $pts, $cls, $desc])
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                        <span class="inline-flex items-center justify-center w-10 h-8 rounded-lg text-[10px] font-extrabold {{ $cls }} flex-shrink-0">{{ $code }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-800 truncate">{{ $label }}</p>
                            <p class="text-[10px] text-gray-400 truncate">{{ $desc }}</p>
                        </div>
                        <span class="text-sm font-extrabold {{ $pts > 0 ? 'text-emerald-600' : 'text-gray-400' }} flex-shrink-0">
                            {{ $pts > 0 ? '+' . $pts : '0' }} pt{{ $pts > 1 ? 's' : '' }}
                        </span>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 p-3 rounded-xl bg-orange-50 border border-orange-100">
                    <p class="text-[10px] font-bold text-orange-700 uppercase tracking-widest mb-1">Délai notation</p>
                    <p class="text-xs text-orange-700">Le receveur dispose de <strong>15 jours</strong> pour noter (prolongeable 15j une fois).</p>
                    <p class="text-xs text-orange-600 mt-1">Passé ce délai : lead = UQ, émetteur <strong>−2 pts</strong>, receveur <strong>+1 pt</strong></p>
                </div>
            </div>

            {{-- Colonne 3 : Exemple + Malus --}}
            <div class="p-5">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Exemple illustratif (CGU §6.4)</p>

                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-xs">
                        <div class="w-7 h-7 rounded-full bg-teal-100 flex items-center justify-center text-[10px] font-bold text-teal-700 flex-shrink-0">S</div>
                        <span class="text-gray-600">Sophie envoie un lead à Marc</span>
                    </div>
                    <div class="ml-9 space-y-1">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 text-[11px]">
                            <span class="text-gray-500">À la réception</span>
                            <div class="flex gap-3">
                                <span class="font-bold text-emerald-600">Sophie +2</span>
                                <span class="font-bold text-red-500">Marc −1</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 text-[11px]">
                            <span class="text-gray-500">Marc note SP (+5)</span>
                            <div class="flex gap-3">
                                <span class="font-bold text-emerald-600">Sophie +5</span>
                                <span class="font-bold text-red-500">Marc −5</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-indigo-50 border border-indigo-100 text-[11px]">
                            <span class="font-semibold text-indigo-800">Total final</span>
                            <div class="flex gap-3">
                                <span class="font-extrabold text-emerald-600">Sophie +7</span>
                                <span class="font-extrabold text-red-500">Marc −6</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-red-50 border border-red-100">
                    <p class="text-[10px] font-bold text-red-700 uppercase tracking-widest mb-1">Malus UQ (CGU §6.2.3)</p>
                    <p class="text-xs text-red-700">Si un émetteur cumule <strong>&gt; 3 leads UQ</strong> sur 6 mois glissants :</p>
                    <div class="mt-1.5 flex items-center gap-2">
                        <span class="text-sm font-extrabold text-red-600">−5 pts</span>
                        <span class="text-xs text-red-600">appliqués automatiquement</span>
                    </div>
                </div>

                <div class="mt-3 p-3 rounded-xl bg-blue-50 border border-blue-100">
                    <p class="text-[10px] font-bold text-blue-700 uppercase tracking-widest mb-1">Traitement automatique</p>
                    <p class="text-xs text-blue-700">La commande <code class="bg-blue-100 px-1 rounded text-[10px]">leads:process-expired-ratings</code> s'exécute chaque nuit à 04h00.</p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ── Configuration des ranges de badges ───────────────────────────────── --}}
@php
$badgeRanges = [
    'neutre'    => ['label'=>'Neutre',    'icon'=>'○',  'color'=>'gray',   'min'=>$ranges['neutre']['min'],    'max'=>$ranges['neutre']['max'],    'minKey'=>null,                  'maxKey'=>'badge_neutre_max',    'minFixed'=>true,  'maxFixed'=>false],
    'bronze'    => ['label'=>'Bronze',    'icon'=>'🏆', 'color'=>'amber',  'min'=>$ranges['bronze']['min'],    'max'=>$ranges['bronze']['max'],    'minKey'=>'badge_bronze_min',    'maxKey'=>'badge_bronze_max',    'minFixed'=>false, 'maxFixed'=>false],
    'argent'    => ['label'=>'Argent',    'icon'=>'🏆', 'color'=>'slate',  'min'=>$ranges['argent']['min'],    'max'=>$ranges['argent']['max'],    'minKey'=>'badge_argent_min',    'maxKey'=>'badge_argent_max',    'minFixed'=>false, 'maxFixed'=>false],
    'or'        => ['label'=>'Or',        'icon'=>'🏆', 'color'=>'yellow', 'min'=>$ranges['or']['min'],        'max'=>$ranges['or']['max'],        'minKey'=>'badge_or_min',        'maxKey'=>'badge_or_max',        'minFixed'=>false, 'maxFixed'=>false],
    'platinium' => ['label'=>'Platinium', 'icon'=>'💎', 'color'=>'indigo', 'min'=>$ranges['platinium']['min'], 'max'=>$ranges['platinium']['max'], 'minKey'=>'badge_platinium_min', 'maxKey'=>null,                  'minFixed'=>false, 'maxFixed'=>true],
];
$colorMap = [
    'gray'   => ['bg'=>'bg-gray-50',    'border'=>'border-gray-200',   'text'=>'text-gray-500',   'input'=>'border-gray-300'],
    'amber'  => ['bg'=>'bg-amber-50',   'border'=>'border-amber-200',  'text'=>'text-amber-700',  'input'=>'border-amber-300'],
    'slate'  => ['bg'=>'bg-slate-50',   'border'=>'border-slate-200',  'text'=>'text-slate-600',  'input'=>'border-slate-300'],
    'yellow' => ['bg'=>'bg-yellow-50',  'border'=>'border-yellow-200', 'text'=>'text-yellow-700', 'input'=>'border-yellow-300'],
    'indigo' => ['bg'=>'bg-indigo-50',  'border'=>'border-indigo-200', 'text'=>'text-indigo-700', 'input'=>'border-indigo-300'],
];
@endphp

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
    <div class="flex items-center justify-between mb-5">
        <div>
            <p class="text-sm font-bold text-gray-900">Plages de points par badge</p>
            <p class="text-xs text-gray-400 mt-0.5">Définissez librement les bornes <strong>De</strong> et <strong>À</strong> pour chaque niveau.</p>
        </div>
        <form method="POST" action="{{ route('admin.notation.thresholds') }}" id="thresholds-form" class="hidden">@csrf</form>
    </div>

    <div class="space-y-3">
        @foreach($badgeRanges as $bKey => $b)
        @php $c = $colorMap[$b['color']]; @endphp
        <div class="{{ $c['bg'] }} border {{ $c['border'] }} rounded-2xl px-5 py-3.5 flex items-center gap-4">

            {{-- Badge label --}}
            <div class="w-28 flex-shrink-0 flex items-center gap-2">
                <span class="text-base">{{ $b['icon'] }}</span>
                <span class="text-sm font-bold {{ $c['text'] }}">{{ $b['label'] }}</span>
            </div>

            {{-- Range --}}
            <div class="flex items-center gap-3 flex-1">
                {{-- Min --}}
                <div class="flex flex-col items-center gap-0.5">
                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">De</span>
                    @if($b['minFixed'])
                    <div class="w-20 h-9 flex items-center justify-center rounded-lg border bg-white border-gray-200 text-gray-400 text-sm font-bold">0</div>
                    @else
                    <input type="number"
                           name="{{ $b['minKey'] }}"
                           form="thresholds-form"
                           value="{{ $b['min'] }}"
                           min="1"
                           class="w-20 h-9 text-center text-sm font-bold rounded-lg border-2 {{ $c['input'] }} {{ $c['bg'] }} {{ $c['text'] }} focus:outline-none focus:ring-2 transition">
                    @endif
                </div>

                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" class="flex-shrink-0"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>

                {{-- Max --}}
                <div class="flex flex-col items-center gap-0.5">
                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">À</span>
                    @if($b['maxFixed'])
                    <div class="w-20 h-9 flex items-center justify-center rounded-lg border bg-white border-gray-200 text-sm font-bold {{ $c['text'] }}">∞</div>
                    @else
                    <input type="number"
                           name="{{ $b['maxKey'] }}"
                           form="thresholds-form"
                           value="{{ $b['max'] }}"
                           min="0"
                           class="w-20 h-9 text-center text-sm font-bold rounded-lg border-2 {{ $c['input'] }} {{ $c['bg'] }} {{ $c['text'] }} focus:outline-none focus:ring-2 transition">
                    @endif
                </div>

                {{-- pts label --}}
                <span class="text-xs font-semibold text-gray-400 flex-shrink-0">points</span>
            </div>

            {{-- Members count --}}
            <div class="text-right flex-shrink-0">
                @php
                    $memberCount = \App\Models\User::where('role','user')->where('badge_level', $bKey)->count();
                @endphp
                <p class="text-lg font-extrabold {{ $c['text'] }}">{{ $memberCount }}</p>
                <p class="text-[10px] text-gray-400">membre{{ $memberCount > 1 ? 's' : '' }}</p>
            </div>

        </div>
        @endforeach
    </div>

    <div class="mt-4 flex items-center gap-3">
        <button type="submit" form="thresholds-form"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                style="background:#4338CA;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
            Enregistrer et recalculer
        </button>
        <p class="text-xs text-gray-400">Les scores de tous les membres seront recalculés avec les nouveaux seuils.</p>
    </div>
</div>

{{-- Paramètres d'envoi --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-5 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <p class="text-sm font-bold text-gray-900">Paramètres d'envoi de leads</p>
        <p class="text-xs text-gray-400 mt-0.5">Règles appliquées lors de l'envoi d'un lead</p>
    </div>
    <div class="px-5 py-4">
        <form method="POST" action="{{ route('admin.notation.settings') }}" id="settings-form">
            @csrf
            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Bloquer l'envoi si solde négatif</p>
                    <p class="text-xs text-gray-400 mt-0.5">Empêche un utilisateur dont le solde est &lt; 0 d'envoyer de nouveaux leads</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0">
                    <input type="hidden" name="block_negative_sender" value="0">
                    <input type="checkbox" name="block_negative_sender" value="1"
                           {{ $blockNegativeSender ? 'checked' : '' }}
                           onchange="document.getElementById('settings-form').submit()"
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full
                                after:h-5 after:w-5 after:transition-all
                                peer-checked:bg-indigo-600"></div>
                </label>
            </div>
        </form>
    </div>
</div>

{{-- Légende formule --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap items-center gap-4 text-xs">
    <p class="font-bold text-gray-500 uppercase tracking-widest text-[10px]">Formule (60j)</p>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 text-teal-700 font-semibold">Lead envoyé <span class="bg-teal-200 rounded-lg px-1.5">+2</span></span>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 text-red-600 font-semibold">Lead reçu <span class="bg-red-200 rounded-lg px-1.5">-1</span></span>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 font-semibold">MQL <span class="bg-blue-200 rounded-lg px-1.5">+1</span></span>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 font-semibold">SQL <span class="bg-indigo-200 rounded-lg px-1.5">+3</span></span>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-violet-50 text-violet-700 font-semibold">SP <span class="bg-violet-200 rounded-lg px-1.5">+5</span></span>
    <div class="ml-auto flex items-center gap-2 text-[10px] font-semibold">
        @foreach($badges as $key => $b)
        <span class="px-2 py-0.5 rounded-full {{ $b['color'] }}">{{ $b['label'] }} ({{ $b['min'] }}{{ $b['max'] ? '–'.$b['max'] : '+' }})</span>
        @endforeach
    </div>
</div>

{{-- Filtres --}}
<form method="GET" class="flex items-center gap-3 mb-5">
    <div class="relative flex-1 max-w-xs">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email…"
               class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
    </div>
    <select name="badge" onchange="this.form.submit()"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white text-gray-700 focus:outline-none focus:border-indigo-400 transition">
        <option value="">Tous les badges</option>
        @foreach($badges as $key => $b)
        <option value="{{ $key }}" {{ request('badge') === $key ? 'selected' : '' }}>{{ $b['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#4338CA;">Filtrer</button>
    @if(request()->hasAny(['search','badge']))
    <a href="{{ route('admin.notation.index') }}" class="px-3 py-2 rounded-xl text-sm text-gray-500 border border-gray-200 hover:bg-gray-50 transition">Reset</a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Membre</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Badge</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Score</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center" colspan="3">Leads envoyés (60j)</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Reçus</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Actions</th>
                </tr>
                <tr class="border-b border-gray-50 bg-gray-50/50">
                    <th></th><th></th><th></th>
                    <th class="px-4 pb-2 text-[9px] font-bold text-blue-400 uppercase tracking-wider text-center">MQL</th>
                    <th class="px-4 pb-2 text-[9px] font-bold text-indigo-400 uppercase tracking-wider text-center">SQL</th>
                    <th class="px-4 pb-2 text-[9px] font-bold text-violet-400 uppercase tracking-wider text-center">SP</th>
                    <th></th><th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                @php
                    $d   = $details[$user->id] ?? null;
                    $rec = $received[$user->id] ?? 0;
                    $badge = $badges[$user->badge_level] ?? $badges['neutre'];
                @endphp
                <tr class="hover:bg-gray-50/50 transition group">
                    {{-- Membre --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                                {{ strtoupper(substr($user->first_name,0,1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Badge actuel --}}
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $badge['color'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </td>

                    {{-- Score --}}
                    <td class="px-4 py-3 text-center">
                        <span class="text-lg font-extrabold {{ $user->points_balance >= 20 ? 'text-indigo-600' : ($user->points_balance >= 10 ? 'text-teal-600' : 'text-gray-700') }}">
                            {{ $user->points_balance }}
                        </span>
                    </td>

                    {{-- MQL / SQL / SP --}}
                    <td class="px-4 py-3 text-center text-sm font-semibold text-blue-600">{{ $d?->mql ?? 0 }}</td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-indigo-600">{{ $d?->sql_count ?? 0 }}</td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-violet-600">{{ $d?->sp ?? 0 }}</td>

                    {{-- Reçus --}}
                    <td class="px-4 py-3 text-center text-sm font-semibold text-red-500">{{ $rec }}</td>

                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-end">
                            {{-- Recalculer --}}
                            <form method="POST" action="{{ route('admin.notation.recalculate', $user) }}">
                                @csrf
                                <button type="submit" title="Recalculer"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-teal-600 hover:bg-teal-50 transition">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                </button>
                            </form>

                            {{-- Modifier --}}
                            <button type="button" onclick="openEdit({{ $user->id }}, {{ $user->points_balance }}, '{{ $user->badge_level }}')"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400">Aucun membre trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $users->links() }}</div>
    @endif
</div>

{{-- Modal édition --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <h3 class="text-base font-bold text-gray-900 mb-5">Modifier la notation</h3>
        <form id="edit-form" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Score (points)</label>
                    <input type="number" name="points_balance" id="edit-points"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition font-mono text-center text-lg font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Badge</label>
                    <select name="badge_level" id="edit-badge"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 bg-white">
                        @foreach($badges as $key => $b)
                        <option value="{{ $key }}">{{ $b['label'] }} ({{ $b['min'] }}{{ $b['max'] ? '–'.$b['max'] : '+' }} pts)</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition"
                        style="background:#4338CA;">Enregistrer</button>
                <button type="button" onclick="closeEdit()"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">Annuler</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const editRouteBase = '{{ url("/admin/notation") }}';

function openEdit(userId, points, badge) {
    document.getElementById('edit-form').action = editRouteBase + '/' + userId;
    document.getElementById('edit-points').value = points;
    document.getElementById('edit-badge').value  = badge;
    const modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeEdit() {
    const modal = document.getElementById('edit-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEdit(); });
</script>
@endpush
