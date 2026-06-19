@extends('layouts.app')

@section('title', 'Échanges — LeadXchange')

@push('styles')
<style>
    .lx-input { height: 40px; padding: 0 14px; border-radius: 10px; border: 1.5px solid #E5E7EB;
                background: white; font-size: 13px; color: #111827; outline: none; width: 100%;
                font-family: inherit; transition: border-color .15s, box-shadow .15s; }
    .lx-input:focus { border-color: #111827; box-shadow: 0 0 0 3px rgba(17,24,39,.08); }
    .lx-input.error { border-color: #EF4444; }
    .lx-textarea { padding: 10px 14px; border-radius: 10px; border: 1.5px solid #E5E7EB;
                   background: white; font-size: 13px; color: #111827; outline: none; width: 100%;
                   font-family: inherit; transition: border-color .15s; resize: none; }
    .lx-textarea:focus { border-color: #111827; box-shadow: 0 0 0 3px rgba(17,24,39,.08); }

    /* Tabs */
    .main-tab { padding-bottom: 12px; padding-right: 32px; font-size: 14px; font-weight: 600;
                color: #9CA3AF; border-bottom: 2px solid transparent; transition: all .15s; cursor: pointer; border: none; background: none; }
    .main-tab.active { color: #111827; border-bottom-color: #111827; }

    /* Filter pills */
    .filter-pill { padding: 5px 14px; border-radius: 100px; font-size: 12px; font-weight: 600;
                   border: 1.5px solid #E5E7EB; background: white; color: #6B7280;
                   cursor: pointer; transition: all .15s; white-space: nowrap; }
    .filter-pill:hover { border-color: #9CA3AF; color: #374151; }
    .filter-pill.active { background: #111827; border-color: #111827; color: white; }

    /* Lead card */
    .lead-card { background: white; border-radius: 16px; border: 1px solid #E5E7EB;
                 overflow: hidden; display: flex; transition: box-shadow .2s; }
    .lead-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.06); }

    /* Status dots */
    .sdot { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; }
    .sdot::before { content: ''; display: block; width: 7px; height: 7px; border-radius: 50%; }

    /* Qual badge */
    .qual-btn { flex: 1; padding: 10px 8px; border-radius: 12px; border: 2px solid #E5E7EB;
                cursor: pointer; text-align: center; transition: all .15s; background: white; }
    .qual-btn.selected-chaud  { border-color: #DC2626; background: #FEE2E2; }
    .qual-btn.selected-tiede  { border-color: #D97706; background: #FEF3C7; }
    .qual-btn.selected-froid  { border-color: #3B82F6; background: #EFF6FF; }
</style>
@endpush

@section('content')
@php
    $userPoints   = $currentUser->points_balance ?? 0;
    $badgeLevel   = $currentUser->badge_level ?? 'neutre';
    $badgeConfig  = [
        'neutre'    => ['label' => 'Neutre',    'icon' => '○',  'classes' => 'text-gray-400'],
        'bronze'    => ['label' => 'Bronze',    'icon' => '🏆', 'classes' => 'text-yellow-700'],
        'argent'    => ['label' => 'Argent',    'icon' => '🏆', 'classes' => 'text-gray-500'],
        'or'        => ['label' => 'Or',        'icon' => '🏆', 'classes' => 'text-amber-500'],
        'platinium' => ['label' => 'Platinium', 'icon' => '💎', 'classes' => 'text-indigo-600'],
    ];
    $badge = $badgeConfig[$badgeLevel] ?? $badgeConfig['neutre'];

    // Progress to next badge (seuils : 0/5/10/15/20)
    if ($badgeLevel === 'neutre') {
        $nextLevelLabel = 'Bronze'; $nextLevelPts = 5;
        $progressPct = min(100, (int) round($userPoints / 5 * 100));
    } elseif ($badgeLevel === 'bronze') {
        $nextLevelLabel = 'Argent'; $nextLevelPts = 10;
        $progressPct = min(100, (int) round(max(0, $userPoints - 5) / 5 * 100));
    } elseif ($badgeLevel === 'argent') {
        $nextLevelLabel = 'Or'; $nextLevelPts = 15;
        $progressPct = min(100, (int) round(max(0, $userPoints - 10) / 5 * 100));
    } elseif ($badgeLevel === 'or') {
        $nextLevelLabel = 'Platinium'; $nextLevelPts = 20;
        $progressPct = min(100, (int) round(max(0, $userPoints - 15) / 5 * 100));
    } else {
        $nextLevelLabel = null; $nextLevelPts = null; $progressPct = 100;
    }

    // Stats deltas (last 7 days)
    $sentCount     = $sent->count();
    $receivedCount = $received->count();
    $pendingCount  = $received->where('status', 'new')->count();
    $convertedCount= $sent->where('status', 'converted')->count();
    $cutoff        = now()->subDays(7);
    $deltaSent     = $sent->where('created_at', '>=', $cutoff)->count();
    $deltaReceived = $received->where('created_at', '>=', $cutoff)->count();
    $deltaPending  = $received->where('status', 'new')->where('created_at', '>=', $cutoff)->count();
    $deltaConverted= $sent->where('status', 'converted')->where('created_at', '>=', $cutoff)->count();

    // Filter counts per tab
    $rcvCounts = ['all' => $receivedCount, 'new' => $received->where('status','new')->count(),
                  'accepted' => $received->where('status','accepted')->count(),
                  'converted' => $received->where('status','converted')->count(),
                  'rejected' => $received->where('status','rejected')->count()];
    $sntCounts = ['all' => $sentCount, 'new' => $sent->where('status','new')->count(),
                  'accepted' => $sent->where('status','accepted')->count(),
                  'converted' => $sent->where('status','converted')->count(),
                  'rejected' => $sent->where('status','rejected')->count()];
@endphp

{{-- ════ HERO HEADER ════ --}}
<div class="bg-white border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-6 lg:px-8 py-10">
        <div class="flex items-start justify-between gap-8 flex-wrap">

            {{-- Left: Hero text --}}
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Échanges / Vue d'ensemble</p>
                <h1 class="playfair text-5xl font-normal text-gray-900 leading-tight">
                    Échangez des<br>
                    <em class="text-teal-600 not-italic font-normal" style="font-style:italic;">leads qualifiés</em><br>
                    avec votre réseau.
                </h1>
                <p class="mt-4 text-sm text-gray-500 max-w-sm leading-relaxed">
                    Une économie de points encourage la réciprocité : envoyez un lead, gagnez un point.
                    Notez ceux que vous recevez sous 15 jours pour préserver le vôtre.
                </p>
            </div>

            {{-- Right: Points widget + CTA --}}
            <div class="flex flex-col items-end gap-4 flex-shrink-0">
                {{-- Points + progress --}}
                <div class="text-right">
                    <div class="flex items-center gap-2 justify-end">
                        <span class="text-sm font-semibold {{ $badge['classes'] }}">{{ $badge['icon'] }} {{ $badge['label'] }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ $userPoints }} <span class="text-xs font-medium text-gray-400">PTS</span></span>
                    </div>
                    <div class="w-40 h-1.5 bg-gray-100 rounded-full mt-2 overflow-hidden">
                        <div class="h-1.5 bg-teal-500 rounded-full transition-all" style="width: {{ $progressPct }}%"></div>
                    </div>
                    @if($nextLevelLabel)
                    <p class="text-xs text-gray-400 mt-1.5">{{ $userPoints }} / {{ $nextLevelPts }} pts · Prochain : {{ $nextLevelLabel }}</p>
                    @else
                    <p class="text-xs text-amber-600 mt-1.5">Niveau maximum atteint 🥇</p>
                    @endif
                    <a href="#" class="text-xs text-teal-600 hover:underline mt-0.5 inline-block">Historique des points →</a>
                </div>
                {{-- CTA --}}
                @if(auth()->user()->canFeature('send_leads'))
                <button onclick="document.getElementById('sendLeadModal').classList.remove('hidden')"
                        class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 transition">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Envoyer un Lead
                </button>
                @else
                <a href="{{ route('upgrade') }}"
                   class="flex items-center gap-2 px-5 py-3 rounded-xl text-sm font-semibold border-2 border-dashed border-indigo-200 text-indigo-400 hover:border-indigo-400 hover:text-indigo-600 transition bg-indigo-50/50">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Envoyer un Lead
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-500 ml-1">Upgrade</span>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ════ STATS ════ --}}
<div class="max-w-6xl mx-auto px-6 lg:px-8">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-6">
        @php
        $statCards = [
            ['label' => 'ENVOYÉS',    'value' => $sentCount,      'delta' => $deltaSent,      'num' => 'text-indigo-500', 'bg' => 'bg-indigo-50', 'delta_bg' => 'bg-indigo-100 text-indigo-600'],
            ['label' => 'REÇUS',      'value' => $receivedCount,  'delta' => $deltaReceived,  'num' => 'text-teal-600',   'bg' => 'bg-teal-50',   'delta_bg' => 'bg-teal-100 text-teal-700'],
            ['label' => 'EN ATTENTE', 'value' => $pendingCount,   'delta' => $deltaPending,   'num' => 'text-amber-500',  'bg' => 'bg-amber-50',  'delta_bg' => 'bg-amber-100 text-amber-700'],
            ['label' => 'CONVERTIS',  'value' => $convertedCount, 'delta' => $deltaConverted, 'num' => 'text-emerald-500','bg' => 'bg-emerald-50','delta_bg' => 'bg-emerald-100 text-emerald-700'],
        ];
        @endphp
        @foreach($statCards as $s)
        <div class="rounded-2xl border border-transparent {{ $s['bg'] }} p-5">
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-light {{ $s['num'] }}">{{ $s['value'] }}</span>
                @if($s['delta'] > 0)
                <span class="text-xs font-bold px-1.5 py-0.5 rounded {{ $s['delta_bg'] }}">+{{ $s['delta'] }}</span>
                @else
                <span class="text-xs font-semibold text-gray-300">0</span>
                @endif
            </div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-1">{{ $s['label'] }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- ════ MAIN CONTENT ════ --}}
<div class="max-w-6xl mx-auto px-6 lg:px-8 pb-12">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium text-teal-700 bg-teal-50 border border-teal-100">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->has('error'))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium text-red-600 bg-red-50 border border-red-100">
        {{ $errors->first('error') }}
    </div>
    @endif

    {{-- Main tabs --}}
    <div class="flex border-b border-gray-200 mb-6 gap-0">
        <button onclick="switchTab('received')" id="tab-received" class="main-tab active">
            Reçus
            <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full" id="badge-received">{{ $receivedCount }}</span>
        </button>
        <button onclick="switchTab('sent')" id="tab-sent" class="main-tab">
            Envoyés
            <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full" id="badge-sent">{{ $sentCount }}</span>
        </button>
    </div>

    {{-- Filters + Search --}}
    <div class="flex items-center justify-between gap-4 mb-5 flex-wrap">
        {{-- Status filters --}}
        <div class="flex items-center gap-2 flex-wrap" id="filter-bar">
            <button class="filter-pill active" data-filter="all" onclick="selectFilter('all')">
                Tous <span id="fp-all">{{ $receivedCount }}</span>
            </button>
            <button class="filter-pill" data-filter="new" onclick="selectFilter('new')">
                En attente <span id="fp-new">{{ $rcvCounts['new'] }}</span>
            </button>
            <button class="filter-pill" data-filter="accepted" onclick="selectFilter('accepted')">
                Acceptés <span id="fp-accepted">{{ $rcvCounts['accepted'] }}</span>
            </button>
            <button class="filter-pill" data-filter="converted" onclick="selectFilter('converted')">
                Convertis <span id="fp-converted">{{ $rcvCounts['converted'] }}</span>
            </button>
            <button class="filter-pill" data-filter="rejected" onclick="selectFilter('rejected')">
                Refusés <span id="fp-rejected">{{ $rcvCounts['rejected'] }}</span>
            </button>
        </div>
        {{-- Search --}}
        <div class="relative flex-shrink-0">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="leadSearch" placeholder="Rechercher une entreprise, un contact…"
                   oninput="applyFilter()"
                   class="h-9 w-72 pl-9 pr-4 rounded-full border border-gray-200 text-sm bg-white focus:outline-none focus:border-gray-400 transition">
        </div>
    </div>

    {{-- ── Received panel ── --}}
    <div id="panel-received">
        @if($received->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-teal-50">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.5">
                    <path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-600">Aucun lead reçu</p>
            <p class="text-sm text-gray-400 mt-1">Les leads envoyés par votre réseau apparaîtront ici</p>
        </div>
        @else
        <div class="space-y-3" id="list-received">
            @foreach($received as $lead)
            @include('leads._card', ['lead' => $lead, 'mode' => 'received', 'currentUser' => $currentUser])
            @endforeach
        </div>
        <div id="empty-received" class="hidden text-center py-10 text-sm text-gray-400">Aucun lead ne correspond à ce filtre.</div>
        @endif
    </div>

    {{-- ── Sent panel ── --}}
    <div id="panel-sent" class="hidden">
        @if($sent->isEmpty())
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-indigo-50">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.5">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </div>
            <p class="font-semibold text-gray-600">Aucun lead envoyé</p>
            <p class="text-sm text-gray-400 mt-1">Partagez une opportunité commerciale avec votre réseau</p>
            @if(auth()->user()->canFeature('send_leads'))
            <button onclick="document.getElementById('sendLeadModal').classList.remove('hidden')"
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 transition">
                Envoyer votre premier lead
            </button>
            @else
            <a href="{{ route('upgrade') }}"
               class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-indigo-200 text-indigo-500 hover:bg-indigo-50 transition">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Upgrade pour envoyer des leads
            </a>
            @endif
        </div>
        @else
        <div class="space-y-3" id="list-sent">
            @foreach($sent as $lead)
            @include('leads._card', ['lead' => $lead, 'mode' => 'sent', 'currentUser' => $currentUser])
            @endforeach
        </div>
        <div id="empty-sent" class="hidden text-center py-10 text-sm text-gray-400">Aucun lead ne correspond à ce filtre.</div>
        @endif
    </div>

</div>

{{-- ════ SEND LEAD MODAL ════ --}}
@if(auth()->user()->canFeature('send_leads'))
<div id="sendLeadModal"
     class="{{ $errors->any() && !session('success') ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.5);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[94vh] overflow-y-auto">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <div>
                <h2 class="font-bold text-gray-900">Envoyer un Lead</h2>
                <p class="text-xs text-gray-400 mt-0.5">Partagez une opportunité commerciale qualifiée</p>
            </div>
            <button type="button" onclick="document.getElementById('sendLeadModal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('leads.store') }}" class="px-6 py-5 space-y-4">
            @csrf

            {{-- Recipient --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Envoyer à <span class="text-red-400">*</span>
                </label>
                <input type="hidden" name="receiver_id" id="receiverId" value="{{ old('receiver_id') }}" required>
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <input type="text" id="receiverSearch"
                           placeholder="Chercher une connexion…"
                           autocomplete="off"
                           value="{{ old('receiver_id') ? (($u2=$connections->firstWhere('id',old('receiver_id'))) ? $u2->first_name.' '.$u2->last_name : '') : '' }}"
                           class="lx-input pl-9 @error('receiver_id') error @enderror"
                           oninput="filterMembers(this.value)"
                           onfocus="showMemberDropdown()"
                           onblur="setTimeout(hideMemberDropdown, 150)">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                    <div id="memberDropdown"
                         class="hidden absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl overflow-hidden"
                         style="max-height:220px;overflow-y:auto;">
                        @if($connections->isEmpty())
                        <div class="px-4 py-5 text-center text-sm text-gray-400">Connectez-vous avec des membres pour leur envoyer des leads</div>
                        @else
                        @foreach($connections as $u)
                        <button type="button"
                                class="member-option w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50 transition {{ ($u->points_balance ?? 0) < 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                data-id="{{ $u->id }}"
                                data-name="{{ $u->first_name }} {{ $u->last_name }}"
                                data-balance="{{ $u->points_balance ?? 0 }}"
                                onclick="selectMember(this)">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:linear-gradient(135deg,#1E8F88,#34d4bf);">
                                {{ strtoupper(substr($u->first_name, 0, 1)) }}{{ strtoupper(substr($u->last_name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $u->first_name }} {{ $u->last_name }}</p>
                                @if(($u->points_balance ?? 0) < 1)
                                <p class="text-xs text-red-400">Solde insuffisant</p>
                                @else
                                <p class="text-xs text-gray-400">{{ $u->points_balance }} pts</p>
                                @endif
                            </div>
                        </button>
                        @endforeach
                        @endif
                        <div id="noMemberResult" class="hidden px-4 py-4 text-sm text-gray-400 text-center">Aucun résultat</div>
                    </div>
                </div>
                @error('receiver_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Contact info --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Contact à recommander</p>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Entreprise <span class="text-red-400">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}"
                               placeholder="Nom de l'entreprise" required maxlength="150"
                               class="lx-input @error('company_name') error @enderror">
                        @error('company_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Contact <span class="text-red-400">*</span></label>
                        <input type="text" name="contact_name" value="{{ old('contact_name') }}"
                               placeholder="Nom du contact" required maxlength="100"
                               class="lx-input @error('contact_name') error @enderror">
                        @error('contact_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                               placeholder="email@exemple.com" required maxlength="150"
                               class="lx-input @error('contact_email') error @enderror">
                        @error('contact_email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Téléphone <span class="text-red-400">*</span></label>
                        <input type="tel" name="contact_phone" value="{{ old('contact_phone') }}"
                               placeholder="+33 6 XX XX XX XX" required maxlength="30"
                               class="lx-input @error('contact_phone') error @enderror">
                        @error('contact_phone') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Poste / Fonction</label>
                    <input type="text" name="contact_position" value="{{ old('contact_position') }}"
                           placeholder="Ex: Directeur Commercial" maxlength="100"
                           class="lx-input">
                </div>
            </div>

            {{-- Deadline + Qualification --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Deadline <span class="text-red-400">*</span>
                    </label>
                    <input type="date" name="deadline" value="{{ old('deadline') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required
                           class="lx-input @error('deadline') error @enderror">
                    @error('deadline') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                        Qualification <span class="text-red-400">*</span>
                    </label>
                    <input type="hidden" name="qualification" id="qualInput" value="{{ old('qualification', '') }}" required>
                    <div class="flex gap-2">
                        @foreach(\App\Models\Lead::$qualificationConfig as $key => $qc)
                        <button type="button"
                                class="qual-btn {{ old('qualification') === $key ? 'selected-'.$key : '' }}"
                                data-qual="{{ $key }}"
                                onclick="selectQual('{{ $key }}')"
                                title="{{ $qc['label'] }}">
                            <div class="text-lg">{{ $qc['icon'] }}</div>
                            <div class="text-xs font-semibold mt-0.5 {{ $qc['textClass'] }}">{{ $qc['label'] }}</div>
                        </button>
                        @endforeach
                    </div>
                    @error('qualification') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Sector --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    Secteur <span class="text-red-400">*</span>
                </label>
                <select name="sector_id" required
                        class="lx-input @error('sector_id') error @enderror">
                    <option value="">— Choisir un secteur —</option>
                    @foreach($sectors as $sector)
                    <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>
                        {{ $sector->name }}
                    </option>
                    @endforeach
                </select>
                @error('sector_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Notes</label>
                <textarea name="description" rows="3" maxlength="2000"
                          placeholder="Contexte, besoins spécifiques, historique de la relation…"
                          class="lx-textarea">{{ old('description') }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="document.getElementById('sendLeadModal').classList.add('hidden')"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white bg-gray-900 hover:bg-gray-800 transition flex items-center justify-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                    Envoyer le Lead
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    /* ── Tab switching ── */
    const rcvCounts = @json($rcvCounts);
    const sntCounts = @json($sntCounts);
    let currentTab    = 'received';
    let currentFilter = 'all';

    function switchTab(tab) {
        currentTab    = tab;
        currentFilter = 'all';
        ['received', 'sent'].forEach(t => {
            document.getElementById('panel-' + t).classList.toggle('hidden', t !== tab);
            const btn = document.getElementById('tab-' + t);
            btn.classList.toggle('active', t === tab);
        });
        updateFilterBar();
        applyFilter();
    }

    function updateFilterBar() {
        const counts = currentTab === 'received' ? rcvCounts : sntCounts;
        document.getElementById('fp-all').textContent      = counts.all;
        document.getElementById('fp-new').textContent      = counts.new;
        document.getElementById('fp-accepted').textContent = counts.accepted;
        document.getElementById('fp-converted').textContent= counts.converted;
        document.getElementById('fp-rejected').textContent = counts.rejected;
    }

    function selectFilter(status) {
        currentFilter = status;
        document.querySelectorAll('.filter-pill').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.filter === status);
        });
        applyFilter();
    }

    function applyFilter() {
        const searchQ  = (document.getElementById('leadSearch').value || '').toLowerCase().trim();
        const listId   = 'list-' + currentTab;
        const emptyId  = 'empty-' + currentTab;
        const list     = document.getElementById(listId);
        const emptyMsg = document.getElementById(emptyId);
        if (!list) return;

        let visible = 0;
        list.querySelectorAll('.lead-card-wrapper').forEach(wrapper => {
            const statusMatch = currentFilter === 'all' || wrapper.dataset.status === currentFilter;
            const searchMatch = !searchQ || (wrapper.dataset.search || '').toLowerCase().includes(searchQ);
            const show = statusMatch && searchMatch;
            wrapper.classList.toggle('hidden', !show);
            if (show) visible++;
        });
        if (emptyMsg) emptyMsg.classList.toggle('hidden', visible > 0);
    }

    /* ── Member autocomplete ── */
    function showMemberDropdown() { document.getElementById('memberDropdown').classList.remove('hidden'); }
    function hideMemberDropdown() { document.getElementById('memberDropdown').classList.add('hidden'); }
    function filterMembers(q) {
        const term    = q.toLowerCase().trim();
        const options = document.querySelectorAll('.member-option');
        const noRes   = document.getElementById('noMemberResult');
        let found = 0;
        options.forEach(opt => {
            const visible = !term || opt.dataset.name.toLowerCase().includes(term);
            opt.classList.toggle('hidden', !visible);
            if (visible) found++;
        });
        noRes.classList.toggle('hidden', found > 0);
        showMemberDropdown();
        if (!term) document.getElementById('receiverId').value = '';
    }
    function selectMember(btn) {
        const balance = parseInt(btn.dataset.balance, 10);
        if (balance < 1) { alert('Ce membre ne peut pas recevoir de leads pour le moment.'); return; }
        document.getElementById('receiverId').value     = btn.dataset.id;
        document.getElementById('receiverSearch').value = btn.dataset.name;
        hideMemberDropdown();
    }
    document.getElementById('sendLeadModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    /* ── Qualification selector ── */
    function selectQual(key) {
        document.getElementById('qualInput').value = key;
        document.querySelectorAll('.qual-btn').forEach(btn => {
            btn.className = 'qual-btn';
            if (btn.dataset.qual === key) btn.classList.add('selected-' + key);
        });
    }
    const savedQual = document.getElementById('qualInput').value;
    if (savedQual) selectQual(savedQual);

    /* ── Rating submit ── */
    function submitRating(leadId) {
        const form = document.getElementById('rateForm-' + leadId);
        const fields = ['quality', 'relevance', 'reactivity'];
        for (const f of fields) {
            if (!document.getElementById('val-' + leadId + '-' + f).value) {
                alert('Veuillez noter les 3 critères avant de valider.');
                return;
            }
        }
        form.submit();
    }

    /* Init badge counts */
    @if($receivedCount > 0)
    document.getElementById('badge-received').classList.add('bg-gray-900', 'text-white');
    @else
    document.getElementById('badge-received').classList.add('bg-gray-100', 'text-gray-500');
    @endif
    document.getElementById('badge-sent').classList.add('bg-gray-100', 'text-gray-500');
    updateFilterBar();
</script>
@endpush
