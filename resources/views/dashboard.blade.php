@extends('layouts.app')

@section('title', 'Dashboard — LeadXchange')

@push('styles')
<style>
    .stat-card { background:white; border-radius:16px; border:1px solid #E5E7EB; padding:20px 24px; display:flex; align-items:center; gap:16px; transition:box-shadow .2s; }
    .stat-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); }
    .stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .prospect-row { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #F3F4F6; }
    .prospect-row:last-child { border-bottom:none; }
    .circle-progress { transform:rotate(-90deg); }
    @keyframes slideUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')

{{-- ── AMBASSADOR BANNER ── --}}
@if(auth()->user()->isAmbassador())
<div class="mx-auto max-w-7xl px-6 lg:px-8 pt-5">
    <a href="{{ route('ambassador.dashboard') }}"
       class="flex items-center justify-between gap-4 px-5 py-4 rounded-2xl text-white hover:opacity-95 transition"
       style="background:linear-gradient(135deg,#0F1629,#14B8A6);">
        <div class="flex items-center gap-3">
            <span class="text-2xl">🏅</span>
            <div>
                <p class="font-bold text-[14px]">Espace Ambassadeur disponible</p>
                <p class="text-teal-200 text-xs">Accédez à votre tableau de bord régional, membres, événements et performance.</p>
            </div>
        </div>
        <span class="flex-shrink-0 text-sm font-bold px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 transition">
            Accéder →
        </span>
    </a>
</div>
@endif

{{-- ── WELCOME MODAL ── --}}
@if($popupEnabled && $prospects->isNotEmpty())
<div id="welcomeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" style="animation:slideUp .3s ease;">

        {{-- Header --}}
        <div class="relative px-7 pt-8 pb-5 text-center" style="background:linear-gradient(135deg,#0f2027,#1a3a4a,#1E8F88);">
            <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 80% 20%,white 1px,transparent 1px);background-size:22px 22px;"></div>
            <div class="relative z-10">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:rgba(255,255,255,0.15);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                @php
                    $firstName    = auth()->user()->first_name;
                    $displayTitle = $popupTitle
                        ? str_replace(':prenom', $firstName, $popupTitle)
                        : "Welcome to LeadXchange, {$firstName}!";
                    $displaySub   = $popupSubtitle ?: 'Here are a few people you might want to connect with';
                @endphp
                <h2 class="text-xl font-bold text-white">{{ $displayTitle }}</h2>
                <p class="text-white/60 text-sm mt-1">{{ $displaySub }}</p>
            </div>
        </div>

        {{-- Recommendations --}}
        <div class="px-7 py-5 space-y-3">
            @foreach($prospects->take(3) as $prospect)
            @php $hue = ($prospect->id * 47) % 360; $hue2 = ($hue + 40) % 360; @endphp
            <div class="flex items-center gap-4 p-3.5 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full flex-shrink-0 overflow-hidden">
                    @if($prospect->profile?->avatar)
                        <img src="{{ $prospect->profile->avatar_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white font-semibold text-sm"
                             style="background:linear-gradient(135deg,hsl({{ $hue }} 60% 55%),hsl({{ $hue2 }} 55% 45%));">
                            {{ strtoupper(substr($prospect->first_name,0,1).substr($prospect->last_name,0,1)) }}
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ member_name($prospect) }}</p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ $prospect->profile?->job_title ?? 'LeadXchange member' }}
                        @if($prospect->company) · {{ $prospect->company->name }} @endif
                    </p>
                    @if($prospect->city)
                    <p class="text-[11px] text-teal-600 mt-0.5 flex items-center gap-1">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $prospect->city->name }}
                        @if($prospect->city_id === auth()->user()->city_id)
                        <span class="px-1.5 py-px rounded-full font-semibold text-[9px] tracking-wide" style="background:#E6F7F4;color:#1E8F88;">Near you</span>
                        @endif
                    </p>
                    @endif
                </div>

                {{-- Connect --}}
                <button onclick="welcomeConnect({{ $prospect->id }}, this)"
                    class="flex-shrink-0 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white transition"
                    style="background:#1E8F88;"
                    onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                    Connect
                </button>
            </div>
            @endforeach
        </div>

        {{-- Footer --}}
        <div class="px-7 pb-7 flex gap-3">
            <button onclick="closeWelcomeModal()"
                class="flex-1 py-3 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                {{ $popupBtnLater ?: 'Maybe later' }}
            </button>
            <a href="{{ route('connections.index') }}"
               onclick="closeWelcomeModal()"
               class="flex-1 py-3 rounded-xl text-sm font-bold text-white text-center transition"
               style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                {{ $popupBtnCta ?: 'Explore network' }}
            </a>
        </div>
    </div>
</div>
@endif

{{-- ── POPUP SOLDE NÉGATIF ── --}}
@if($negativeBalancePopup)
@php
    $totalToPay = number_format($pointsNeeded * $pointsPricePerUnit, 2, ',', ' ');
@endphp
<div id="negativeBalanceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.55);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" style="animation:slideUp .3s ease;">

        {{-- Header --}}
        <div class="px-7 pt-7 pb-5 text-center" style="background:linear-gradient(135deg,#7F1D1D,#B91C1C);">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:rgba(255,255,255,0.15);">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            </div>
            <h2 class="text-lg font-bold text-white">Compte en solde négatif</h2>
            <p class="text-red-200 text-xs mt-1">Depuis plus de 2 mois</p>
        </div>

        {{-- Body --}}
        <div class="px-7 py-6 text-center">
            <p class="text-sm text-gray-700 leading-relaxed mb-4">
                Ton compte est négatif depuis plus de deux mois. Tu ne pourras plus recevoir de leads.
            </p>
            <p class="text-sm text-gray-600 leading-relaxed mb-4">
                Pour obtenir des points, tu dois fournir des leads à la communauté.
            </p>
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5 text-left">
                <p class="text-xs text-amber-800 leading-relaxed">
                    De manière exceptionnelle, tu peux acheter
                    <span class="font-bold">{{ $pointsNeeded }} point{{ $pointsNeeded > 1 ? 's' : '' }}</span>
                    pour remettre ton solde à zéro.
                    @if($pointsPricePerUnit > 0)
                    <br><span class="text-amber-600 font-semibold">Montant : {{ $totalToPay }} €</span>
                    @endif
                </p>
            </div>
            <p class="text-sm font-semibold text-gray-800 mb-5">Souhaites-tu acheter des points ?</p>

            <div class="flex flex-col gap-2.5">
                <form method="POST" action="{{ route('points.buy') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#B91C1C,#7F1D1D);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Oui, acheter {{ $pointsNeeded }} point{{ $pointsNeeded > 1 ? 's' : '' }}
                    </button>
                </form>
                <button onclick="document.getElementById('negativeBalanceModal').style.display='none'"
                        class="w-full py-3 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">
                    Plus tard
                </button>
            </div>
        </div>

    </div>
</div>
@endif

<div class="max-w-7xl mx-auto px-6 lg:px-8 py-7 space-y-6">

    {{-- ── HERO BANNER ── --}}
    <div class="relative overflow-hidden rounded-2xl p-7" style="background: linear-gradient(135deg, #0f2027, #1a3a4a, #1E8F88);">
        {{-- decorative circles --}}
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full opacity-10" style="background:#34d4bf;"></div>
        <div class="absolute right-32 bottom-0 w-32 h-32 rounded-full opacity-10" style="background:#34d4bf;"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                {{-- Badges --}}
                <div class="flex items-center gap-2 mb-3">
                    @php
                        $dashPlanName = auth()->user()->subscription?->plan?->name ?? 'basic';
                        $dashPlanKey = 'basic';
                        if (auth()->user()->isAmbassador()) $dashPlanKey = 'ambassadeur';
                        elseif (auth()->user()->isConsul())  $dashPlanKey = 'consul';
                        elseif ($dashPlanName === 'premium') $dashPlanKey = 'premium';
                    @endphp
                    <img src="{{ asset('images/plans/' . $dashPlanKey . '.jpg') }}"
                         alt="{{ $dashPlanKey }}"
                         onerror="this.style.display='none'"
                         class="h-8 w-auto object-contain drop-shadow-md">
                    <span class="text-xs text-white/50">{{ now()->isoFormat('dddd, D MMMM') }}</span>
                </div>

                {{-- Greeting --}}
                <h1 class="text-3xl font-bold text-white mb-1">
                    Bonjour {{ auth()->user()->first_name }} 👋
                </h1>

                {{-- Profile completion as stars --}}
                @php
                    $stars        = round($completion / 20);
                    $userPlan     = auth()->user()->subscription?->plan;
                    $planLabel    = auth()->user()->planDisplayLabel();
                @endphp
                <div class="flex items-center gap-2 mt-1">
                    <div class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $i <= $stars ? '#FFD700' : 'none' }}" stroke="#FFD700" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-white/60 text-sm">{{ $completion }}% profile completed</span>
                </div>

                {{-- ── Sélecteur de ville ── --}}
                <div class="mt-3" id="city-picker-wrap">

                    {{-- Texte cliquable --}}
                    <button type="button" id="city-picker-btn"
                            class="flex items-center gap-1.5 group focus:outline-none">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2">
                            <circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/>
                        </svg>
                        <span class="text-sm text-white/70 group-hover:text-white underline underline-offset-2 decoration-white/30 transition">
                            {{ $selectedCity?->name ?? 'Toutes les villes' }}
                        </span>
                        <svg id="city-picker-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none"
                             stroke="rgba(255,255,255,0.4)" stroke-width="2.5"
                             style="transition:transform .2s;">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                </div>

                {{-- Dropdown rendu dans le body pour éviter le clipping du overflow-hidden --}}
                @push('scripts')
                <div id="city-picker-dropdown"
                     style="display:none; position:fixed; z-index:9999; background:#1a2e3b;
                            border:1px solid rgba(255,255,255,0.12); border-radius:14px;
                            box-shadow:0 20px 60px rgba(0,0,0,0.5); min-width:200px; overflow:hidden;">

                    <form method="POST" action="{{ route('region.select') }}">
                        @csrf
                        <input type="hidden" name="redirect" value="dashboard">
                        <input type="hidden" name="city_id" id="city-hidden-input" value="">

                        {{-- Toutes les villes --}}
                        <button type="submit"
                                onclick="document.getElementById('city-hidden-input').value=''"
                                style="width:100%; display:flex; align-items:center; gap:10px; padding:11px 16px;
                                       font-size:13px; border:none; cursor:pointer; text-align:left; transition:background .15s;
                                       background:{{ !$selectedCityId ? 'rgba(255,255,255,0.1)' : 'transparent' }};
                                       color:{{ !$selectedCityId ? '#2DD4BF' : 'rgba(255,255,255,0.65)' }};
                                       font-weight:{{ !$selectedCityId ? '600' : '400' }};"
                                onmouseover="if(!this.dataset.active)this.style.background='rgba(255,255,255,0.07)'"
                                onmouseout="if(!this.dataset.active)this.style.background='transparent'"
                                {{ !$selectedCityId ? 'data-active=1' : '' }}>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10A15.3 15.3 0 0 1 8 12a15.3 15.3 0 0 1 4-10z"/>
                            </svg>
                            Toutes les villes
                        </button>

                        <div style="border-top:1px solid rgba(255,255,255,0.08); margin:0 12px;"></div>

                        <div style="max-height:240px; overflow-y:auto;">
                            @foreach($cities as $city)
                            <button type="submit"
                                    onclick="document.getElementById('city-hidden-input').value='{{ $city->id }}'"
                                    style="width:100%; display:flex; align-items:center; gap:10px; padding:11px 16px;
                                           font-size:13px; border:none; cursor:pointer; text-align:left; transition:background .15s;
                                           background:{{ $selectedCityId == $city->id ? 'rgba(255,255,255,0.1)' : 'transparent' }};
                                           color:{{ $selectedCityId == $city->id ? '#2DD4BF' : 'rgba(255,255,255,0.65)' }};
                                           font-weight:{{ $selectedCityId == $city->id ? '600' : '400' }};"
                                    onmouseover="if(!this.dataset.active)this.style.background='rgba(255,255,255,0.07)'"
                                    onmouseout="if(!this.dataset.active)this.style.background='transparent'"
                                    {{ $selectedCityId == $city->id ? 'data-active=1' : '' }}>
                                @if($selectedCityId == $city->id)
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2DD4BF" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                @else
                                    <span style="width:13px; display:inline-block; flex-shrink:0;"></span>
                                @endif
                                {{ $city->name }}
                            </button>
                            @endforeach
                        </div>
                    </form>
                </div>

                <script>
                (function() {
                    const btn = document.getElementById('city-picker-btn');
                    const dd  = document.getElementById('city-picker-dropdown');
                    const ch  = document.getElementById('city-picker-chevron');

                    function openDropdown() {
                        const rect = btn.getBoundingClientRect();
                        dd.style.top  = (rect.bottom + 8) + 'px';
                        dd.style.left = rect.left + 'px';
                        dd.style.display = 'block';
                        ch.style.transform = 'rotate(180deg)';
                    }

                    function closeDropdown() {
                        dd.style.display = 'none';
                        ch.style.transform = '';
                    }

                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dd.style.display === 'none' ? openDropdown() : closeDropdown();
                    });

                    document.addEventListener('click', function(e) {
                        if (!dd.contains(e.target) && e.target !== btn) closeDropdown();
                    });

                    // Repositionner si scroll
                    window.addEventListener('scroll', function() {
                        if (dd.style.display !== 'none') {
                            const rect = btn.getBoundingClientRect();
                            dd.style.top  = (rect.bottom + 8) + 'px';
                            dd.style.left = rect.left + 'px';
                        }
                    }, { passive: true });
                })();
                </script>
                @endpush
            </div>

            {{-- Action buttons --}}
            <div class="flex flex-wrap gap-2">
                @if($completion < 100)
                <a href="{{ route('profile.me') }}"
                   class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white border border-white/20 hover:bg-white/10 transition backdrop-blur-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                    Complete Profile
                </a>
                @else
                <a href="{{ route('profile.me') }}"
                   class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white border border-white/20 hover:bg-white/10 transition backdrop-blur-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Mon profil
                </a>
                @endif
                <span class="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-white/20" style="background:rgba(255,255,255,0.12);">
                    <img src="{{ asset('images/plans/' . $dashPlanKey . '.jpg') }}"
                         alt="{{ $dashPlanKey }}"
                         onerror="this.style.display='none'"
                         class="h-6 w-auto object-contain">
                    <span class="text-xs font-semibold text-white/90">{{ $planLabel }}</span>
                </span>

                {{-- Badge Ambassadeur (doublon retiré — le badge plan $planLabel suffit) --}}
            </div>
        </div>
    </div>

    {{-- ── PROPOSITION ENTERPRISE EN ATTENTE ── --}}
    @if(isset($pendingEnterpriseProposal) && $pendingEnterpriseProposal)
    @php $prop = $pendingEnterpriseProposal; @endphp
    <a href="{{ route('enterprise.proposal.view', $prop->proposal_token) }}"
       class="flex items-center justify-between gap-4 px-5 py-4 rounded-2xl text-white transition hover:opacity-95 flex-wrap"
       style="background:linear-gradient(135deg,#4338CA,#6366F1);">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-[14px]">Vous avez une proposition Pack Entreprise en attente</p>
                <p class="text-indigo-200 text-xs mt-0.5">
                    {{ $prop->company_name }} —
                    {{ $prop->proposed_seats }} licences,
                    {{ number_format((float)$prop->proposed_price, 2, ',', ' ') }} € —
                    reçue le {{ $prop->proposal_sent_at->format('d/m/Y') }}
                </p>
            </div>
        </div>
        <span class="flex-shrink-0 text-sm font-bold px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 transition whitespace-nowrap">
            Voir la proposition →
        </span>
    </a>
    @endif

    {{-- ── STATS ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#E6F7F4;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $connectionCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Connections</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#FEF3C7;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#F59E0B" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $pendingCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Pending requests</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#EDE9FE;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8B5CF6" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $completion }}%</p>
                <p class="text-xs text-gray-500 mt-0.5">Profile score</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background:#FCE7F3;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#EC4899" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $groupCount }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Groups joined</p>
            </div>
        </div>
    </div>

    {{-- ── BOTTOM TWO COLUMNS ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- New prospects --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-gray-900">New prospects for you</h2>
                <a href="{{ route('connections.index') }}"
                   class="text-sm font-semibold transition-colors" style="color:#1E8F88;">See all</a>
            </div>

            @forelse($prospects as $prospect)
            <div class="prospect-row">
                {{-- Avatar --}}
                <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
                     style="background: linear-gradient(135deg, hsl({{ ($prospect->id * 47) % 360 }} 60% 55%), hsl({{ ($prospect->id * 47 + 40) % 360 }} 55% 45%));">
                    {{ strtoupper(substr($prospect->first_name, 0, 1)) }}{{ strtoupper(substr($prospect->last_name, 0, 1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ member_name($prospect) }}</p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ $prospect->profile?->job_title ?? 'LeadXchange member' }}
                        @if($prospect->company) · {{ $prospect->company->name }} @endif
                    </p>
                    @if($prospect->city)
                    <p class="text-[11px] text-teal-600 mt-0.5 flex items-center gap-1">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        {{ $prospect->city->name }}
                        @if($prospect->city_id === auth()->user()->city_id)
                        <span class="px-1.5 py-px rounded-full font-semibold text-[9px] tracking-wide" style="background:#E6F7F4;color:#1E8F88;">Near you</span>
                        @endif
                    </p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="{{ route('profile.show', $prospect->id) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                        View
                    </a>
                    @if(auth()->user()->canFeature('can_send_invitations'))
                    <button onclick="sendConnect({{ $prospect->id }}, this)"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                        style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                        Connect
                    </button>
                    @else
                    <button type="button" onclick="openUpgradeModal('can_send_invitations')"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-indigo-200 text-indigo-500 hover:bg-indigo-50 transition flex items-center gap-1 cursor-pointer"
                            style="background:transparent;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Upgrade
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="py-10 text-center">
                <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:#E6F7F4;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <p class="text-sm text-gray-500">No prospects yet — <a href="{{ route('connections.index') }}" style="color:#1E8F88;" class="font-semibold">explore the network</a></p>
            </div>
            @endforelse
        </div>

        {{-- Sharpen profile --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-1">Sharpen your profile</h2>
            <p class="text-xs text-gray-400 mb-5">Stronger profiles get 3× more inbound leads.</p>

            {{-- Circular progress --}}
            <div class="flex justify-center mb-5">
                <div class="relative w-28 h-28">
                    <svg class="w-full h-full" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#F3F4F6" stroke-width="10"/>
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#1E8F88" stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="{{ round(2 * 3.14159 * 50) }}"
                            stroke-dashoffset="{{ round(2 * 3.14159 * 50 * (1 - $completion / 100)) }}"
                            style="transform:rotate(-90deg);transform-origin:center;transition:stroke-dashoffset 1s ease;"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-bold text-gray-900">{{ $completion }}%</span>
                        <span class="text-xs text-gray-400">complete</span>
                    </div>
                </div>
            </div>

            @if(count($missing) > 0)
            <p class="text-xs font-semibold text-gray-500 mb-3">{{ count($missing) }} field{{ count($missing) > 1 ? 's' : '' }} left</p>
            <div class="space-y-2">
                @foreach(array_slice($missing, 0, 4) as $field)
                <div class="flex items-center gap-3">
                    <div class="w-5 h-5 rounded-full border-2 border-gray-200 flex-shrink-0"></div>
                    <span class="text-sm text-gray-600">{{ $field['label'] }}</span>
                    <svg class="ml-auto text-gray-300 flex-shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-2">
                <p class="text-sm font-semibold text-green-600">🎉 Profile complete!</p>
            </div>
            @endif

            <a href="{{ route('profile.me') }}"
               class="mt-5 block w-full py-3 rounded-xl text-sm font-bold text-white text-center transition"
               style="background:#1E8F88;" onmouseover="this.style.background='#197a74'" onmouseout="this.style.background='#1E8F88'">
                COMPLETE PROFILE
            </a>
        </div>
    </div>

    {{-- ── GROUPS ── --}}
    @if($featuredGroups->isNotEmpty())
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Groupes populaires
                    @if($selectedCity)
                    <span class="text-xs font-normal text-gray-400 ml-1">· {{ $selectedCity->name }}</span>
                    @endif
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Communautés correspondant à vos intérêts</p>
            </div>
            <a href="{{ route('groups.index') }}" class="text-sm font-semibold transition-colors" style="color:#1E8F88;">Voir tout</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($featuredGroups as $group)
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                {{-- Cover --}}
                <div class="h-20 relative" style="background: linear-gradient(135deg, {{ $group->cover_color }}, {{ $group->cover_color }}cc);">
                    <div class="absolute inset-0 flex items-center px-5">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                    </div>
                    @if($group->sector)
                    <span class="absolute top-2.5 right-3 px-2 py-0.5 rounded-full text-xs font-medium text-white bg-black/20">
                        {{ $group->sector->name }}
                    </span>
                    @endif
                </div>
                {{-- Body --}}
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-1 truncate">{{ $group->name }}</h3>
                    @if($group->description)
                    <p class="text-xs text-gray-400 line-clamp-1 mb-3">{{ $group->description }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-2">
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ number_format($group->members_count) }} members
                        </div>
                        @if(in_array($group->id, $memberGroupIds))
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold border"
                              style="border-color:#1E8F88;color:#1E8F88;">Joined</span>
                        @else
                        <a href="{{ route('groups.show', $group->id) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                           style="border:1px solid #E5E7EB;color:#6B7280;">
                            Voir →
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── EVENTS FOR YOU ── --}}
    @if($upcomingEvents->isNotEmpty())
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Événements à venir
                    @if($selectedCity)
                    <span class="text-xs font-normal text-gray-400 ml-1">· {{ $selectedCity->name }}</span>
                    @endif
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">Prochains événements sur LeadXchange</p>
            </div>
            <a href="{{ route('events.index') }}" class="text-sm font-semibold transition-colors" style="color:#1E8F88;">Voir tout</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($upcomingEvents as $event)
            @php
                $typeColors = ['virtual' => '#6366F1', 'in_person' => '#1E8F88', 'hybrid' => '#F59E0B'];
                $typeLabels = ['virtual' => 'Virtual', 'in_person' => 'In-person', 'hybrid' => 'Hybrid'];
                $typeColor  = $typeColors[$event->type] ?? $event->cover_color;
                $typeLabel  = $typeLabels[$event->type] ?? $event->type;
            @endphp
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden" style="transition:box-shadow .2s,transform .2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='';this.style.transform=''">
                {{-- Cover --}}
                <div class="h-24 relative" style="background: linear-gradient(135deg, {{ $event->cover_color }}, {{ $event->cover_color }}cc);">
                    <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 70% 30%,white 1px,transparent 1px);background-size:18px 18px;"></div>
                    <div class="absolute inset-0 flex items-center px-5">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                    </div>
                    <span class="absolute top-3 right-3 px-2.5 py-0.5 rounded-full text-xs font-semibold text-white" style="background:rgba(0,0,0,0.25);">{{ $typeLabel }}</span>
                </div>
                {{-- Body --}}
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-900 leading-snug mb-2 line-clamp-2">{{ $event->title }}</h3>
                    <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-2">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ $event->starts_at->isoFormat('ddd, D MMM · H:mm') }}
                    </div>
                    @if($event->sector)
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium mb-2" style="background:#E6F7F4;color:#1E8F88;">{{ $event->sector->name }}</span>
                    @endif
                    <div class="flex items-center justify-between mt-1">
                        <div class="flex items-center gap-1 text-xs text-gray-400">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            {{ number_format($event->attendees_count) }} attending
                        </div>
                        @if(in_array($event->id, $attendingEventIds))
                        <form method="POST" action="{{ route('events.leave', $event->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition" style="border-color:#1E8F88;color:#1E8F88;" onmouseover="this.style.background='#E6F7F4'" onmouseout="this.style.background='transparent'">Registered ✓</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('events.join', $event->id) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition" style="background:{{ $typeColor }};" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">Register</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    {{-- ── ENTERPRISE TEAM CARD (holders only) ── --}}
    @if(auth()->user()->isEnterpriseHolder())
    @php $elic = auth()->user()->enterpriseLicense()->withCount(['invitations as active_count' => fn($q) => $q->where('status','active')])->first(); @endphp
    @if($elic)
    <div class="mb-8">
        <div class="bg-white rounded-2xl border-2 overflow-hidden"
             style="border-color:#BFDBFE;">
            <div class="h-1.5" style="background:linear-gradient(90deg,#1D4ED8,#2563EB);"></div>
            <div class="px-6 py-5 flex items-center gap-5 flex-wrap">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:#EFF6FF;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1D4ED8" stroke-width="1.8">
                        <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold uppercase tracking-widest mb-0.5" style="color:#1D4ED8;">Pack Entreprise</p>
                    <p class="text-base font-bold text-gray-900 leading-tight">{{ $elic->company_name }}</p>
                    <div class="flex items-center gap-4 mt-1.5">
                        <div class="flex items-baseline gap-1">
                            <span class="text-xl font-extrabold text-gray-900">{{ $elic->seats_used }}</span>
                            <span class="text-xs text-gray-400">/ {{ $elic->seats_total }} licences attribuées</span>
                        </div>
                        @if($elic->seatsAvailable() > 0)
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                            {{ $elic->seatsAvailable() }} disponible{{ $elic->seatsAvailable() > 1 ? 's' : '' }}
                        </span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('enterprise.team') }}"
                   class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90 flex-shrink-0"
                   style="background:linear-gradient(135deg,#1D4ED8,#1E40AF);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Gérer mon équipe
                </a>
            </div>
            {{-- Mini progress bar --}}
            @php $seatPct = $elic->seats_total > 0 ? round($elic->seats_used / $elic->seats_total * 100) : 0; @endphp
            <div class="px-6 pb-4">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] text-gray-400">Utilisation des licences</span>
                    <span class="text-[11px] font-bold text-gray-600">{{ $seatPct }}%</span>
                </div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full rounded-full transition-all"
                         style="width:{{ $seatPct }}%;background:linear-gradient(90deg,#2563EB,#1D4ED8);"></div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ── PLANS & FONCTIONNALITÉS ──
         Consul et Ambassadeur ont un rôle nommé — ils n'achètent pas de plan.
         On masque la section pour eux. --}}
    @if($plans->isNotEmpty() && !$user->isConsul() && !$user->isAmbassador())
    @php
        $currentPlanId = $user->subscription?->plan_id;
        $currentPlan   = $user->subscription?->plan;

        $planThemes = [
            'basic'       => ['color'=>'#64748B','light'=>'#F8FAFC','border'=>'#CBD5E1','icon'=>'<path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>'],
            'premium'     => ['color'=>'#6366F1','light'=>'#EEF2FF','border'=>'#818CF8','icon'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
            'consul'      => ['color'=>'#0D9488','light'=>'#F0FDFA','border'=>'#2DD4BF','icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
            'ambassadeur' => ['color'=>'#D97706','light'=>'#FFFBEB','border'=>'#FBBF24','icon'=>'<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
            'enterprise'  => ['color'=>'#2563EB','light'=>'#EFF6FF','border'=>'#60A5FA','icon'=>'<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>'],
        ];

        $keyPerms = [
            'can_view_member_name'   => 'Voir le nom complet des membres',
            'can_send_invitations'   => 'Envoyer des invitations de connexion',
            'can_send_mail'          => 'Messagerie illimitée',
            'max_leads_per_month'    => 'Leads par mois',
            'can_send_sql'           => 'Leads SQL & SP',
            'can_join_pole'          => 'Rejoindre des groupes',
            'can_create_pole'        => 'Créer des groupes',
            'can_create_events'      => 'Créer des événements',
            'can_organize_regional_events' => 'Événements régionaux',
        ];

        // Consul et Ambassadeur sont des rôles nommés par l'admin, pas des plans achetables.
        // On n'affiche que les plans "achetables" : basic, premium, enterprise.
        $purchasablePlans = ['basic', 'premium', 'enterprise'];

        $sortedPlans      = $plans->sortBy('sort_order')->values();
        $currentSortOrder = $currentPlan?->sort_order ?? 0;

        // Plans visibles : achetables seulement, et >= plan actuel (pas de downgrade)
        $visiblePlans = $sortedPlans->filter(
            fn($p) => in_array($p->name, $purchasablePlans) && $p->sort_order >= $currentSortOrder
        )->values();
    @endphp

    <div>
        {{-- Header --}}
        <div class="flex items-end justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 tracking-tight">Nos offres d'abonnement</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Vous êtes sur le plan
                    <strong class="text-gray-800">{{ $planLabel ?? 'Basic' }}</strong>.
                    Passez à l'offre suivante pour débloquer plus de fonctionnalités.
                </p>
            </div>
            <a href="{{ route('billing.index') }}"
               class="text-xs font-semibold text-gray-400 hover:text-gray-600 transition flex items-center gap-1 whitespace-nowrap">
                Gérer mon abonnement
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </div>

        {{-- Plans — scroll horizontal sur mobile, grille sur desktop --}}
        <div class="flex gap-4 overflow-x-auto pb-2 sm:grid sm:grid-cols-2 lg:grid-cols-{{ min($visiblePlans->count(), 3) }} sm:overflow-visible"
             style="-webkit-overflow-scrolling:touch;scrollbar-width:none;">
            @foreach($visiblePlans as $planIdx => $plan)
            @php
                $t         = $planThemes[$plan->name] ?? $planThemes['basic'];
                $isCurrent = $plan->id === $currentPlanId;
                $perms     = is_array($plan->permissions) ? $plan->permissions : [];
                $feats     = is_array($plan->features) ? $plan->features : [];
                $isHighlighted = !$isCurrent && $planIdx === 1;
            @endphp

            <div class="relative flex flex-col rounded-2xl border-2 overflow-hidden transition hover:shadow-lg flex-shrink-0 w-72 sm:w-auto"
                 style="border-color: {{ $isCurrent ? $t['color'] : ($isHighlighted ? $t['border'] : '#E5E7EB') }};
                        box-shadow: {{ $isHighlighted ? '0 8px 30px rgba(0,0,0,.10)' : 'none' }};">

                {{-- Top color bar --}}
                <div class="h-1.5" style="background: {{ $t['color'] }};"></div>

                {{-- Badge --}}
                @if($isCurrent)
                <div class="absolute top-3 right-3">
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full border"
                          style="color:{{ $t['color'] }};border-color:{{ $t['color'] }};background:{{ $t['light'] }};">
                        ✓ Plan actuel
                    </span>
                </div>
                @elseif($isHighlighted)
                <div class="absolute top-3 right-3">
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full text-white" style="background:#111827;">
                        Recommandé
                    </span>
                </div>
                @endif

                <div class="p-5 flex-1 flex flex-col" style="background:{{ $isCurrent ? $t['light'] : '#fff' }};">

                    {{-- Icon + Nom + Prix --}}
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background:{{ $t['light'] }};">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                 stroke="{{ $t['color'] }}" stroke-width="1.8">
                                {!! $t['icon'] !!}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 leading-tight">{{ $plan->label }}</p>
                            @if($plan->description)
                            <p class="text-[11px] text-gray-400 mt-0.5 leading-snug truncate">{{ $plan->description }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Prix --}}
                    <div class="flex items-baseline gap-1 mb-5">
                        @if($plan->is_enterprise)
                        <span class="text-2xl font-extrabold text-gray-900 tracking-tight">Sur devis</span>
                        <span class="text-xs text-gray-400">multi-licences</span>
                        @elseif(is_null($plan->price) || (float)$plan->price === 0.0)
                        <span class="text-3xl font-extrabold text-gray-900 tracking-tight">Basic</span>
                        <span class="text-xs text-gray-400">pour toujours</span>
                        @else
                        <span class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ currency_format($plan->price) }}</span>
                        <span class="text-xs text-gray-400 mb-0.5">/mois</span>
                        @endif
                    </div>

                    {{-- Fonctionnalités clés (permissions) --}}
                    <ul class="space-y-2 flex-1 mb-5">
                        @foreach($keyPerms as $permKey => $permLabel)
                        @php
                            $val     = $perms[$permKey] ?? false;
                            $enabled = is_bool($val) ? $val : ($val === null ? true : ($val > 0));
                            $isLimit = is_int($val) && $val > 0;
                            $isNull  = $val === null;
                        @endphp
                        <li class="flex items-center gap-2 text-xs {{ $enabled ? 'text-gray-700' : 'text-gray-300' }}">
                            @if($enabled)
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                 stroke="{{ $t['color'] }}" stroke-width="2.5" class="flex-shrink-0">
                                <path d="m5 12 5 5L20 7"/>
                            </svg>
                            @else
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                 stroke="#D1D5DB" stroke-width="2.5" class="flex-shrink-0">
                                <path d="M18 6 6 18M6 6l12 12"/>
                            </svg>
                            @endif
                            <span class="{{ $enabled ? '' : 'line-through' }}">{{ $permLabel }}</span>
                            @if($isLimit)
                            <span class="ml-auto text-[10px] font-bold px-1.5 rounded flex-shrink-0"
                                  style="background:{{ $t['light'] }};color:{{ $t['color'] }};">{{ $val }}</span>
                            @elseif($isNull && $enabled)
                            <span class="ml-auto text-[10px] font-bold px-1.5 rounded flex-shrink-0"
                                  style="background:{{ $t['light'] }};color:{{ $t['color'] }};">∞</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>

                    {{-- CTA --}}
                    @if($isCurrent)
                    @if($plan->is_enterprise && auth()->user()->isEnterpriseHolder())
                    <a href="{{ route('enterprise.team') }}"
                       class="w-full py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 flex items-center justify-center gap-1.5"
                       style="background:{{ $t['color'] }};">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                        Gérer mon équipe →
                    </a>
                    @else
                    <div class="w-full py-2.5 rounded-xl text-xs font-bold text-center border"
                         style="color:{{ $t['color'] }};border-color:{{ $t['color'] }};background:{{ $t['light'] }};">
                        ✓ Votre plan actuel
                    </div>
                    @endif
                    @elseif($plan->stripe_price_id)
                    <form method="POST" action="{{ route('checkout', $plan) }}">
                        @csrf
                        <button type="submit"
                                class="w-full py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90"
                                style="background:{{ $t['color'] }};">
                            Passer à {{ $plan->label }} →
                        </button>
                    </form>
                    @else
                    <a href="{{ route('upgrade') }}"
                       class="w-full py-2.5 rounded-xl text-xs font-bold text-white transition hover:opacity-90 flex items-center justify-center"
                       style="background:{{ $t['color'] }};">
                        Découvrir {{ $plan->label }} →
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Note légale §12.4 --}}
        <p class="text-center text-[11px] text-gray-400 mt-5">
            Paiement sécurisé via Stripe · Résiliation possible à tout moment · Sans engagement · <a href="{{ url('/legal/cgu') }}" target="_blank" class="underline hover:text-gray-600">CGU</a>
        </p>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // ── Welcome modal ──
    @if($popupEnabled && $prospects->isNotEmpty())
    (function () {
        const uid       = '{{ auth()->id() }}';
        const frequency = '{{ $popupFrequency }}';
        const LS_KEY    = 'lx_welcome_shown_' + uid;
        const SS_KEY    = 'lx_welcome_session_' + uid;

        let shouldShow = false;
        if (frequency === 'always') {
            shouldShow = true;
        } else if (frequency === 'session') {
            if (!sessionStorage.getItem(SS_KEY)) {
                shouldShow = true;
                sessionStorage.setItem(SS_KEY, '1');
            }
        } else {
            // once (default)
            if (!localStorage.getItem(LS_KEY)) {
                shouldShow = true;
                localStorage.setItem(LS_KEY, '1');
            }
        }

        if (shouldShow) {
            document.getElementById('welcomeModal').classList.remove('hidden');
        }
    })();
    @endif

    function closeWelcomeModal() {
        const modal = document.getElementById('welcomeModal');
        modal.style.opacity = '0';
        modal.style.transition = 'opacity .2s';
        setTimeout(() => modal.classList.add('hidden'), 200);
    }

    document.getElementById('welcomeModal').addEventListener('click', function(e) {
        if (e.target === this) closeWelcomeModal();
    });

    async function welcomeConnect(userId, btn) {
        btn.disabled = true;
        btn.textContent = '…';
        try {
            const res = await fetch('/api/connections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Authorization': 'Bearer ' + window.API_TOKEN },
                credentials: 'same-origin',
                body: JSON.stringify({ receiver_id: userId })
            });
            if (!res.ok) throw new Error();
            btn.textContent = 'Sent ✓';
            btn.style.background = '#6B7280';
            btn.onmouseover = btn.onmouseout = null;
        } catch {
            btn.disabled = false;
            btn.textContent = 'Connect';
        }
    }

    async function sendConnect(userId, btn) {
        btn.disabled = true;
        btn.textContent = '...';
        try {
            const res = await fetch('/api/connections', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Authorization': 'Bearer ' + window.API_TOKEN },
                credentials: 'same-origin',
                body: JSON.stringify({ receiver_id: userId })
            });
            if (!res.ok) throw new Error();
            btn.textContent = 'Sent';
            btn.style.background = '#6B7280';
            btn.onmouseover = btn.onmouseout = null;
        } catch {
            btn.disabled = false;
            btn.textContent = 'Connect';
        }
    }
</script>
@endpush
