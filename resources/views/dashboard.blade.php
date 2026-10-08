@extends('layouts.app2')

@section('title', 'Accueil — LeadXchange')

@push('styles')
<style>
    @keyframes slideUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }

    /* ── Tableau de bord : grille principale et cartes ── */
    .db-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(0,1fr);gap:20px;align-items:start;margin-top:8px}
    .db-col{display:flex;flex-direction:column;gap:18px;min-width:0}
    .db-card{padding:20px}
    .db-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .db-title{margin:0;font-size:15px;font-weight:600;letter-spacing:-.01em}
    .db-sub{margin:3px 0 0;font-size:12.5px;color:var(--muted-fg)}
    .db-muted-link{font-size:12.5px;color:var(--muted-fg);white-space:nowrap;cursor:not-allowed}

    /* Mon activité */
    .db-seg{display:inline-flex;background:var(--muted);border-radius:var(--radius);padding:3px;gap:2px}
    .db-seg button{height:28px;padding:0 11px;border:0;background:transparent;border-radius:6px;font-size:12.5px;color:var(--muted-fg);cursor:pointer;transition:background .15s,color .15s}
    .db-seg button:hover{color:var(--fg-2)}
    .db-seg button.on{background:#fff;color:var(--fg);box-shadow:var(--shadow-sm);font-weight:500}
    .db-mini{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:10px}
    .db-mini-n{display:block;font-size:22px;font-weight:600;letter-spacing:-.02em;font-variant-numeric:tabular-nums;line-height:1.1}
    .db-mini-l{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted-fg);margin-top:3px}
    .db-mini-l i{width:8px;height:8px;border-radius:999px;display:inline-block;flex:none}
    .db-chart{position:relative;height:200px;width:100%}
    .db-chart svg{display:block;overflow:visible}
    .db-chart .ln{fill:none;stroke-width:2;stroke-linejoin:round;stroke-linecap:round}
    .db-chart .grid-l{stroke:var(--border);stroke-width:1}
    .db-chart .axis{font-size:11px;fill:var(--muted-fg)}
    .db-tip{position:absolute;top:0;pointer-events:none;background:var(--fg);color:#fff;border-radius:8px;padding:8px 10px;font-size:12px;line-height:1.5;transform:translate(-50%,-100%);white-space:nowrap;opacity:0;transition:opacity .12s;z-index:2}
    .db-tip.show{opacity:1}

    /* À faire */
    .db-todo{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--border);border-radius:var(--radius);color:var(--fg-2);margin-top:8px;transition:border-color .15s,background .15s}
    .db-todo:hover{border-color:var(--primary);background:var(--primary-soft)}
    .db-todo-ico{width:32px;height:32px;border-radius:9px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center;flex:none}
    .db-todo-t{flex:1;min-width:0;font-size:13.5px;font-weight:500}
    .db-todo > svg{color:var(--muted-fg);width:14px;height:14px;flex:none}

    /* Réseau à développer */
    .db-list{display:flex;flex-direction:column;gap:10px}
    .db-list .mrow{transition:border-color .15s,box-shadow .15s}
    .db-list .mrow:hover{border-color:var(--primary-soft-2);box-shadow:var(--shadow-sm)}

    /* Opportunités (structure prête) */
    .db-opp{border:1px solid var(--border);border-radius:var(--radius-lg);padding:14px;display:flex;flex-direction:column;gap:8px;margin-top:8px;transition:border-color .15s}
    .db-opp:hover{border-color:var(--primary)}
    .db-opp-top{display:flex;align-items:center;gap:10px}
    .db-opp-company{font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .db-opp-cat{font-size:12px;color:var(--muted-fg)}
    .db-opp-title{margin:0;font-size:14.5px;font-weight:600}
    .db-opp-meta{display:flex;flex-wrap:wrap;gap:6px 14px;font-size:12.5px;color:var(--fg-2)}
    .db-opp-meta span{display:inline-flex;align-items:center;gap:5px}
    .db-opp-meta svg{width:13px;height:13px;color:var(--muted-fg)}
    .db-empty{display:flex;flex-direction:column;align-items:center;gap:6px;padding:26px 16px;text-align:center;color:var(--muted-fg);font-size:13px;border:1.5px dashed var(--border);border-radius:var(--radius-lg)}
    .db-empty > svg{color:var(--muted-fg)}

    /* Événements à venir (compact) */
    .db-event{display:flex;align-items:center;gap:14px;padding:12px 20px;border-top:1px solid var(--border);transition:background .15s}
    .db-event:first-child{border-top:0}
    .db-event:hover{background:var(--bg)}
    .db-event-date{width:50px;flex:none;text-align:center;border-radius:10px;background:var(--primary-soft);color:var(--primary);padding:6px 0;display:flex;flex-direction:column;line-height:1.1}
    .db-event-date b{font-size:18px;font-weight:600}
    .db-event-date small{font-size:10.5px;text-transform:uppercase;letter-spacing:.04em}
    .db-event-main{flex:1;min-width:0;display:flex;flex-direction:column;gap:3px}
    .db-event-t{font-size:14px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .db-event-meta{font-size:12.5px;color:var(--muted-fg);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

    /* KPI : ligne de contexte */
    .kpi-sub{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:6px;font-size:11.5px;color:var(--muted-fg)}

    /* Accès rapides : retour visuel au survol */
    .qa a.card{transition:transform .15s,box-shadow .15s,border-color .15s}
    .qa a.card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}

    @media (max-width:1020px){
        .db-grid{grid-template-columns:minmax(0,1fr)}
    }
    @media (max-width:640px){
        .db-card{padding:16px}
        .db-mini-n{font-size:18px}
        .db-chart{height:170px}
        .db-event{padding:12px 16px}
    }
</style>
@endpush

@section('content')

{{-- ── AMBASSADOR BANNER ── --}}
@if(auth()->user()->isAmbassador())
<div class="mb-5">
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
<div id="welcomeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);" onclick="if(event.target===this) closeWelcomeModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" style="animation:slideUp .3s ease;">
        <div class="relative px-7 pt-8 pb-5 text-center" style="background:linear-gradient(135deg,#1E2A99,#4154F4);">
            <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 80% 20%,white 1px,transparent 1px);background-size:22px 22px;"></div>
            <div class="relative z-10">
                <div class="welcome-ico"><x-lx2-icon name="users-round" /></div>
                @php
                    $firstName    = auth()->user()->first_name;
                    $displayTitle = $popupTitle ? str_replace(':prenom', $firstName, $popupTitle) : "Bienvenue sur LeadXchange, {$firstName} !";
                    $displaySub   = $popupSubtitle ?: 'Voici quelques personnes avec qui vous pourriez vous connecter';
                @endphp
                <h2 class="text-xl font-bold text-white">{{ $displayTitle }}</h2>
                <p class="text-white/60 text-sm mt-1">{{ $displaySub }}</p>
            </div>
        </div>
        <div class="px-7 py-5 space-y-3">
            @foreach($prospects->take(3) as $prospect)
            @php $hue = ($prospect->id * 47) % 360; $hue2 = ($hue + 40) % 360; @endphp
            <div class="flex items-center gap-4 p-3.5 rounded-xl border border-gray-100 hover:bg-gray-50 transition">
                <div class="w-11 h-11 rounded-full flex-shrink-0 overflow-hidden">
                    @if($prospect->profile?->avatar)
                        <img src="{{ $prospect->profile->avatar_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white font-semibold text-sm"
                             style="background:linear-gradient(135deg,hsl({{ $hue }} 60% 55%),hsl({{ $hue2 }} 55% 45%));">
                            {{ member_name($prospect, true) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ member_name($prospect) }}</p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ $prospect->profile?->job_title ?? 'Membre LeadXchange' }}
                        @if($prospect->company) · {{ $prospect->company->name }} @endif
                    </p>
                </div>
                <button onclick="welcomeConnect({{ $prospect->id }}, this)"
                    class="flex-shrink-0 px-3.5 py-1.5 rounded-lg text-xs font-bold text-white transition"
                    style="background:var(--primary);">
                    Se connecter
                </button>
            </div>
            @endforeach
        </div>
        <div class="px-7 pb-7 flex gap-3">
            <button onclick="closeWelcomeModal()" class="flex-1 py-3 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                {{ $popupBtnLater ?: 'Plus tard' }}
            </button>
            <a href="{{ route('connections.index') }}" onclick="closeWelcomeModal()"
               class="flex-1 py-3 rounded-xl text-sm font-bold text-white text-center transition" style="background:var(--primary);">
                {{ $popupBtnCta ?: 'Explorer le réseau' }}
            </a>
        </div>
    </div>
</div>
@endif

{{-- ── POPUP SOLDE NÉGATIF ── --}}
@if($negativeBalancePopup)
@php $totalToPay = number_format($pointsNeeded * $pointsPricePerUnit, 2, ',', ' '); @endphp
<div id="negativeBalanceModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.55);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden" style="animation:slideUp .3s ease;">
        <div class="px-7 pt-7 pb-5 text-center" style="background:linear-gradient(135deg,#7F1D1D,#B91C1C);">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:rgba(255,255,255,0.15);">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            </div>
            <h2 class="text-lg font-bold text-white">Compte en solde négatif</h2>
            <p class="text-red-200 text-xs mt-1">Depuis plus de 2 mois</p>
        </div>
        <div class="px-7 py-6 text-center">
            <p class="text-sm text-gray-700 leading-relaxed mb-4">Ton compte est négatif depuis plus de deux mois. Tu ne pourras plus recevoir de leads.</p>
            <p class="text-sm text-gray-600 leading-relaxed mb-4">Pour obtenir des points, tu dois fournir des leads à la communauté.</p>
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5 text-left">
                <p class="text-xs text-amber-800 leading-relaxed">
                    De manière exceptionnelle, tu peux acheter <span class="font-bold">{{ $pointsNeeded }} point{{ $pointsNeeded > 1 ? 's' : '' }}</span> pour remettre ton solde à zéro.
                    @if($pointsPricePerUnit > 0)<br><span class="text-amber-600 font-semibold">Montant : {{ $totalToPay }} €</span>@endif
                </p>
            </div>
            <p class="text-sm font-semibold text-gray-800 mb-5">Souhaites-tu acheter des points ?</p>
            <div class="flex flex-col gap-2.5">
                <form method="POST" action="{{ route('points.buy') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90" style="background:linear-gradient(135deg,#B91C1C,#7F1D1D);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Oui, acheter {{ $pointsNeeded }} point{{ $pointsNeeded > 1 ? 's' : '' }}
                    </button>
                </form>
                <button onclick="document.getElementById('negativeBalanceModal').style.display='none'" class="w-full py-3 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">Plus tard</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     EN-TÊTE — maquette « LeadXchange WEB » › pageAccueil()
     (couronne Premium · date · Bonjour + ville · icônes)
════════════════════════════════════════════════════════════════ --}}
@php
    $effectivePlan = $user->effectivePlan();
    $isPaidPlan    = $effectivePlan && in_array($effectivePlan->name, ['premium', 'enterprise'], true);
@endphp
<div class="ph">
    @if($isPaidPlan)
    <img src="{{ asset('images/brand/crown.jpg') }}" alt="{{ $effectivePlan->label }}" style="width:62px;height:66px;object-fit:contain;mix-blend-mode:multiply" class="hide-m">
    @endif
    <div class="t">
        <div class="sub" style="margin:0 0 2px">{{ ucfirst(now()->locale('fr')->isoFormat('dddd, D MMMM')) }}</div>
        <h1 style="font-size:30px;font-weight:500;display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            Bonjour, {{ $user->first_name }}
            <span class="city-dd" id="cityDd">
                <button type="button" class="badge b-plain" style="font-size:11px" onclick="toggleCityDd(event)" aria-haspopup="true" aria-expanded="false">
                    {{ $selectedCity?->name ?? 'Toutes les villes' }} <x-lx2-icon name="chevron-down" />
                </button>
                <form method="POST" action="{{ route('region.select') }}" class="hidden pop menu city-pop" id="cityPop">
                    @csrf
                    <input type="hidden" name="redirect" value="dashboard">
                    <button type="submit" name="city_id" value="" class="{{ $selectedCityId ? '' : 'on' }}">Toutes les villes @unless($selectedCityId)<x-lx2-icon name="check" />@endunless</button>
                    <hr class="sep">
                    @foreach($cities as $city)
                    <button type="submit" name="city_id" value="{{ $city->id }}" class="{{ (int) $selectedCityId === $city->id ? 'on' : '' }}">
                        {{ $city->name }} @if((int) $selectedCityId === $city->id)<x-lx2-icon name="check" />@endif
                    </button>
                    @endforeach
                </form>
            </span>
        </h1>
    </div>
    @include('layouts.partials.lx2-header-icons')
</div>

{{-- ── PROPOSITION ENTERPRISE EN ATTENTE ── --}}
@if(isset($pendingEnterpriseProposal) && $pendingEnterpriseProposal)
@php $prop = $pendingEnterpriseProposal; @endphp
<a href="{{ route('enterprise.proposal.view', $prop->proposal_token) }}"
   class="flex items-center justify-between gap-4 px-5 py-4 rounded-2xl text-white transition hover:opacity-95 flex-wrap mb-5"
   style="background:linear-gradient(135deg,#4338CA,#6366F1);">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <div>
            <p class="font-bold text-[14px]">Vous avez une proposition Pack Entreprise en attente</p>
            <p class="text-indigo-200 text-xs mt-0.5">{{ $prop->company_name }} — {{ $prop->proposed_seats }} licences, {{ number_format((float)$prop->proposed_price, 2, ',', ' ') }} € — reçue le {{ $prop->proposal_sent_at->format('d/m/Y') }}</p>
        </div>
    </div>
    <span class="flex-shrink-0 text-sm font-bold px-4 py-2 rounded-xl bg-white/20 hover:bg-white/30 transition whitespace-nowrap">Voir la proposition →</span>
</a>
@endif

{{-- ══════════════════════════════════════════════════════════════
     KPIs
════════════════════════════════════════════════════════════════ --}}
@php
    $trendBadge = function (array $t): array {
        if ($t['pct'] === null) {
            return $t['now'] > 0 ? ['Nouveau ce mois', 'b-soft'] : ['Aucune activité', 'b-muted'];
        }
        if ($t['pct'] > 0) return ['▲ ' . $t['pct'] . ' %', 'b-ok'];
        if ($t['pct'] < 0) return ['▼ ' . abs($t['pct']) . ' %', 'b-hot'];
        return ['Stable', 'b-muted'];
    };
    $kpiCards = [
        ['lx-send', $leadStats['sent'] ?? 0,            'Leads envoyés',   'Ce mois : ' . $kpiTrends['sent']['now'],            $trendBadge($kpiTrends['sent'])],
        ['check',   $leadStats['converted'] ?? 0,       'Leads convertis', 'Ce mois : ' . $kpiTrends['converted']['now'],       $trendBadge($kpiTrends['converted'])],
        ['wallet',  (int) ($user->points_balance ?? 0), 'Solde du compte', 'Plafond : 30 points',                                 null],
        ['star',    $pointsEarned,                      'Points gagnés',   'Ce mois : +' . $kpiTrends['points']['now'],         $trendBadge($kpiTrends['points'])],
    ];
@endphp
<div class="kpis">
    @foreach($kpiCards as [$icon, $value, $label, $sub, $badge])
    <div class="card kpi">
        <span class="ico"><x-lx2-icon :name="$icon" /></span>
        <div style="min-width:0">
            <div class="v num">{{ $value }}</div>
            <div class="l">{{ $label }}</div>
            <div class="kpi-sub">
                <span>{{ $sub }}</span>
                @if($badge)<span class="badge {{ $badge[1] }}">{{ $badge[0] }}</span>@endif
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Complétion du profil (masquée quand le profil est complet) ── --}}
@if($completion < 100)
<div class="banner">
    <span class="ring" style="background:conic-gradient(#fff 0 {{ $completion }}%,rgba(255,255,255,.3) 0)"><span>{{ $completion }}%</span></span>
    <div class="tx"><b>Votre profil est presque prêt !</b><small>Complétez les dernières informations pour recevoir des opportunités plus pertinentes.</small></div>
    <a class="btn" href="{{ route('profile.me') }}">Compléter mon profil</a>
</div>
@endif

{{-- ── Fin d'essai « Full Access » Premium proche (≤ 7 jours) ── --}}
@php $trialDaysLeft = auth()->user()->trialDaysRemaining(); @endphp
@if($trialDaysLeft > 0 && $trialDaysLeft <= 7)
<div class="banner">
    <span class="ring" style="background:none;display:grid;place-items:center"><x-lx2-icon name="gem" /></span>
    <div class="tx"><b>Votre essai Premium se termine bientôt</b><small>Il reste {{ $trialDaysLeft }} jour{{ $trialDaysLeft > 1 ? 's' : '' }} — passez à un plan payant pour ne rien perdre.</small></div>
    <a class="btn" href="{{ route('upgrade') }}">Voir les plans</a>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     ACTIONS RAPIDES
════════════════════════════════════════════════════════════════ --}}
<div class="sh"><h2>Actions rapides</h2></div>
<div class="qa">
    <a class="card hi" href="{{ route('leads.create') }}"><span class="tile"><x-lx2-icon name="lx-send" /></span>Envoyer un lead<span class="arr"><x-lx2-icon name="arrow-up-right" /></span></a>
    <a class="card" href="{{ route('leads.index') }}"><span class="tile" style="background:var(--primary)"><x-lx2-icon name="inbox" /></span>Mes leads reçus<span class="arr"><x-lx2-icon name="arrow-up-right" /></span></a>
    <a class="card" href="{{ route('connections.index') }}"><span class="tile" style="background:var(--green)"><x-lx2-icon name="contact" /></span>Mes connexions<span class="arr"><x-lx2-icon name="arrow-up-right" /></span></a>
    <a class="card" href="{{ route('connections.index') }}"><span class="tile" style="background:var(--purple)"><x-lx2-icon name="users-round" /></span>Réseauter<span class="arr"><x-lx2-icon name="arrow-up-right" /></span></a>
</div>

{{-- ══════════════════════════════════════════════════════════════
     SUGGESTIONS DE CONTACTS — maquette › memberRow(id, 'Pour toi')
════════════════════════════════════════════════════════════════ --}}
{{-- ══════════════════════════════════════════════════════════════
     CONTENU PRINCIPAL — gauche : activité, opportunités · droite : à faire, réseau
════════════════════════════════════════════════════════════════ --}}
<div class="db-grid">

    <div class="db-col">

        {{-- Mon activité --}}
        <section class="card db-card">
            <div class="db-card-head">
                <div>
                    <h2 class="db-title">Mon activité</h2>
                    <p class="db-sub">Leads envoyés, reçus et convertis</p>
                </div>
                <div class="db-seg" id="dbSeg" role="group" aria-label="Période">
                    <button type="button" data-days="7">7 jours</button>
                    <button type="button" data-days="30" class="on">30 jours</button>
                    <button type="button" data-days="90">3 mois</button>
                </div>
            </div>

            <div class="db-mini">
                <div><span class="db-mini-n" id="dbSentN">0</span><span class="db-mini-l"><i style="background:var(--primary)"></i>Envoyés</span></div>
                <div><span class="db-mini-n" id="dbRecvN">0</span><span class="db-mini-l"><i style="background:var(--purple)"></i>Reçus</span></div>
                <div><span class="db-mini-n" id="dbConvN">0</span><span class="db-mini-l"><i style="background:var(--green)"></i>Convertis</span></div>
            </div>

            <div class="db-chart" id="dbChart" aria-label="Graphique d'activité"></div>
            <script type="application/json" id="dbActivityData">@json($activity)</script>
        </section>

        {{-- Opportunités disponibles (structure prête : aucune source de données pour le moment) --}}
        <section class="card db-card">
            <div class="db-card-head">
                <div>
                    <h2 class="db-title">🔥 Opportunités disponibles</h2>
                    <p class="db-sub">Demandes ouvertes par les entreprises du réseau</p>
                </div>
                <span class="db-muted-link" title="Bientôt disponible">Voir toutes les opportunités →</span>
            </div>

            @forelse($opportunities as $opp)
            <article class="db-opp">
                <div class="db-opp-top">
                    <span class="av-fb" style="width:36px;height:36px;border-radius:10px">{{ mb_strtoupper(mb_substr($opp['company'], 0, 1)) }}</span>
                    <div style="min-width:0;flex:1">
                        <div class="db-opp-company">{{ $opp['company'] }}</div>
                        <div class="db-opp-cat">{{ $opp['category'] }}</div>
                    </div>
                    <span class="badge {{ $opp['status_class'] }}">{{ $opp['status'] }}</span>
                </div>
                <h3 class="db-opp-title">{{ $opp['title'] }}</h3>
                <div class="db-opp-meta">
                    <span><x-lx2-icon name="map-pin" />{{ $opp['location'] }}</span>
                    <span><b>{{ $opp['budget'] }}</b></span>
                </div>
                <a class="btn btn-outline btn-sm" href="{{ $opp['url'] }}">Voir l’opportunité</a>
            </article>
            @empty
            <div class="db-empty">
                <x-lx2-icon name="flag" />
                <span>Aucune opportunité disponible pour le moment.</span>
                <span class="help" style="margin:0">Les nouvelles demandes des entreprises apparaîtront ici.</span>
            </div>
            @endforelse
        </section>
    </div>

    <div class="db-col">

        {{-- À faire --}}
        <section class="card db-card">
            <div class="db-card-head" style="margin-bottom:10px">
                <div><h2 class="db-title">À faire</h2></div>
                @if($todoItems->isNotEmpty())<span class="badge b-soft">{{ $todoItems->count() }}</span>@endif
            </div>

            @forelse($todoItems as $todo)
            <a class="db-todo" href="{{ $todo['url'] }}">
                <span class="db-todo-ico"><x-lx2-icon :name="$todo['icon']" /></span>
                <span class="db-todo-t">{{ $todo['title'] }}</span>
                @if($todo['count'] !== null)<span class="badge b-hot">{{ $todo['count'] }}</span>@endif
                @if($todo['meta'])<span class="badge b-soft">{{ $todo['meta'] }}</span>@endif
                <x-lx2-icon name="chevron-right" />
            </a>
            @empty
            <div class="db-empty" style="padding:22px 12px">
                <x-lx2-icon name="check" />
                <span>Tout est à jour pour le moment.</span>
            </div>
            @endforelse
        </section>

        {{-- Réseau à développer (suggestions) --}}
        <div class="sh" style="margin-top:6px"><h2>🤝 Réseau à développer</h2><a class="link" href="{{ route('connections.index') }}">Voir tout</a></div>
@if($suggestionsCityWidened)
<div class="lx2-flash b-soft" role="note">
    <span>Aucun membre disponible à <b>{{ $selectedCity?->name }}</b> pour le moment — suggestions élargies à toutes les villes.</span>
</div>
@endif
@if($suggestions->isNotEmpty())
<div class="db-list">
    @foreach($suggestions as $member)
    @php $note = $suggestionRatings[$member->id] ?? null; @endphp
    <div class="card mrow">
        <a href="{{ route('profile.show', $member->id) }}"><x-lx2-avatar :user="$member" :size="40" /></a>
        <div class="who">
            <a class="nm" href="{{ route('profile.show', $member->id) }}">{{ member_name($member) }}
                @if($note)<span class="stars"><x-lx2-icon name="star" />{{ round($note) }}</span>@endif
            </a>
            <div class="role">{{ $member->profile?->job_title ?? 'Membre LeadXchange' }}</div>
            @if($member->profile?->sector)<span class="badge b-outline" style="margin-top:4px">{{ $member->profile->sector }}</span>@endif
        </div>
        @if($pendingSentIds->contains($member->id))
        <span class="badge b-muted">Envoyé</span>
        @elseif($pendingReceivedIds->contains($member->id))
        <span class="badge b-soft">Demande reçue</span>
        @elseif(auth()->user()->canFeature('can_send_invitations'))
        <button type="button" onclick="sendConnect({{ $member->id }}, this)" class="circle-act" aria-label="Se connecter avec {{ member_name($member) }}"><x-lx2-icon name="user-plus" /></button>
        @else
        <button type="button" onclick="openUpgradeModal('can_send_invitations')" class="circle-act" aria-label="Se connecter avec {{ member_name($member) }}"><x-lx2-icon name="user-plus" /></button>
        @endif
    </div>
    @endforeach
</div>
@else
<div class="card empty">Aucune suggestion pour le moment — <a class="link" href="{{ route('connections.index') }}">explorez le réseau</a></div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     ÉVÉNEMENTS À VENIR — maquette › eventCard(e)
════════════════════════════════════════════════════════════════ --}}
    </div>
</div>

<div class="sh"><h2>Événements à venir</h2><a class="link" href="{{ route('events.index') }}">Voir tout</a></div>
@if($upcomingEvents->isNotEmpty())
<section class="card db-card" style="padding:6px 0">
    @foreach($upcomingEvents as $event)
    @php
        $modeLabel   = ['virtual' => 'Virtuel', 'in_person' => 'En personne', 'hybrid' => 'Hybride'][$event->type] ?? 'Événement';
        $isAttending = in_array($event->id, $attendingEventIds);
    @endphp
    <a class="db-event" href="{{ route('events.show', $event->id) }}">
        <span class="db-event-date">
            <b>{{ $event->starts_at->format('d') }}</b>
            <small>{{ $event->starts_at->locale('fr')->isoFormat('MMM') }}</small>
        </span>
        <span class="db-event-main">
            <span class="db-event-t">{{ $event->title }}</span>
            <span class="db-event-meta">{{ $event->starts_at->locale('fr')->isoFormat('HH:mm') }} · {{ $event->city?->name ?? 'Ville non précisée' }} · {{ $modeLabel }}</span>
        </span>
        @if($isAttending)<span class="badge b-ok">Inscrit</span>@endif
        <span class="btn btn-outline btn-sm">Voir</span>
    </a>
    @endforeach
</section>
@else
<div class="card empty">Aucun événement à venir — <a class="link" href="{{ route('events.index') }}">explorez les événements</a></div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     GROUPES POPULAIRES — maquette › groupCard(g)
════════════════════════════════════════════════════════════════ --}}
<div class="sh"><h2>Groupes populaires</h2><a class="link" href="{{ route('groups.index') }}">Voir tout</a></div>
@if($featuredGroups->isNotEmpty())
<div class="grid-3">
    @foreach($featuredGroups as $group)
        @include('groups._card', ['group' => $group, 'role' => in_array($group->id, $memberGroupIds) ? 'member' : null])
    @endforeach
</div>
@else
<div class="card empty">Aucun groupe pour le moment — <a class="link" href="{{ route('groups.index') }}">explorez les groupes</a></div>
@endif

{{-- ── ENTERPRISE TEAM CARD (holders only) ── --}}
@if(auth()->user()->isEnterpriseHolder())
@php $elic = auth()->user()->enterpriseLicense()->withCount(['invitations as active_count' => fn($q) => $q->where('status','active')])->first(); @endphp
@if($elic)
<div class="card" style="margin-top:28px;border:1.5px solid #BFDBFE;overflow:hidden;">
    <div style="height:6px;background:linear-gradient(90deg,#1D4ED8,#2563EB);"></div>
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;padding:20px 24px;">
        <div style="background:#EFF6FF;width:48px;height:48px;border-radius:12px;display:grid;place-items:center;flex:none;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1D4ED8" stroke-width="1.8"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
        </div>
        <div style="flex:1;min-width:0;">
            <p style="margin:0 0 2px;color:#1D4ED8;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;">Pack Entreprise</p>
            <p style="margin:0;font-weight:700;">{{ $elic->company_name }}</p>
            <div style="display:flex;align-items:center;gap:16px;margin-top:6px;">
                <span style="font-weight:800;font-size:18px;">{{ $elic->seats_used }}<span style="font-size:12px;color:var(--muted-fg);font-weight:500;"> / {{ $elic->seats_total }} licences</span></span>
                @if($elic->seatsAvailable() > 0)
                <span class="badge b-ok">{{ $elic->seatsAvailable() }} disponible{{ $elic->seatsAvailable() > 1 ? 's' : '' }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('enterprise.team') }}" class="btn btn-primary">Gérer mon équipe</a>
    </div>
</div>
@endif
@endif

{{-- ══════════════════════════════════════════════════════════════
     NOS OFFRES D'ABONNEMENT — maquette › plansHTML()
     Logique métier inchangée (checkout Stripe, annuel/mensuel, pas de downgrade).
════════════════════════════════════════════════════════════════ --}}
@if($plans->isNotEmpty() && !$user->isConsul() && !$user->isAmbassador())
@php
    $currentPlan      = $effectivePlan;
    $currentPlanId    = $currentPlan?->id;
    $purchasablePlans = ['basic', 'premium', 'enterprise'];
    $currentSortOrder = $currentPlan?->sort_order ?? 0;
    $visiblePlans = $plans->sortBy('sort_order')->values()->filter(
        fn($p) => in_array($p->name, $purchasablePlans) && $p->sort_order >= $currentSortOrder
    )->values();
    $hasAnnualPlans = $visiblePlans->contains(fn($p) => !empty($p->annual_price));

    // Fonctionnalités affichées sur les cartes (même liste que la maquette)
    $keyPerms = [
        'can_view_member_name'         => 'Voir le nom complet des membres',
        'can_send_invitations'         => 'Envoyer des invitations de connexion',
        'can_send_mail'                => 'Messagerie <b>illimitée</b>',
        'can_send_sql'                 => '<b>Leads SQL & SP</b>',
        'can_join_pole'                => 'Rejoindre des groupes',
        'can_create_events'            => '<b>Créer des événements</b>',
        'can_organize_regional_events' => 'Événements régionaux',
        'max_leads_per_month'          => 'Leads par mois',
        'can_create_pole'              => 'Créer des groupes',
    ];
@endphp
@if($visiblePlans->isNotEmpty())
<div class="sh">
    <h2>Nos offres d'abonnement</h2>
    @if($hasAnnualPlans)
    <div class="tabs" id="lx2PlanTabs">
        <button type="button" class="on" onclick="lx2SetBilling('monthly', this)">Mensuelle</button>
        <button type="button" onclick="lx2SetBilling('annual', this)">Annuel</button>
    </div>
    @endif
</div>
<div class="plans">
    @foreach($visiblePlans as $plan)
    @php
        $isCurrent     = $plan->id === $currentPlanId;
        $isPremium     = $plan->name === 'premium';
        $isEnterprise  = $plan->is_enterprise || $plan->name === 'enterprise';
        $annualMonthly = $plan->annual_price ? round($plan->annual_price / 12, 0) : null;
        $perms         = is_array($plan->permissions) ? $plan->permissions : [];
    @endphp
    <div class="{{ $isPremium ? 'plan-pop' : 'card' }}">
        @if($isPremium)<div class="hd">Le plus populaire</div>@endif
        <div class="{{ $isPremium ? 'plan' : 'plan' }}">
            <div class="pn" @if($isPremium) style="color:var(--primary)" @endif>
                {{ $plan->label }}
                @if($isCurrent)<span class="badge b-plain" style="font-weight:500">Plan actuel</span>
                @elseif($isPremium)<span class="badge b-primary">Recommandé</span>@endif
            </div>

            @if($isEnterprise)
                <div class="price">Sur devis</div>
            @elseif((float) $plan->price === 0.0)
                <div class="price">Gratuit</div>
            @else
                <div class="price price-monthly-block">{{ currency_format($plan->price) }} <small>/mois<br><span style="font-weight:400">facturation mensuelle</span></small></div>
                @if($annualMonthly)
                <div class="price price-annual-block" style="display:none">{{ currency_format($annualMonthly) }} <small>/mois<br><span style="font-weight:400">facturation annuelle</span></small></div>
                @endif
            @endif

            <p>{{ $plan->description ?: 'Plan LeadXchange' }}</p>

            @if($isCurrent)
                @if($isEnterprise && $user->isEnterpriseHolder())
                <a href="{{ route('enterprise.team') }}" class="btn btn-primary btn-block">Gérer mon équipe</a>
                @else
                <button class="btn btn-secondary btn-block" disabled>Votre plan actuel</button>
                @endif
            @elseif($isEnterprise)
                <a href="{{ route('upgrade') }}#contact" class="btn btn-primary btn-block">Passer à l'offre {{ \Illuminate\Support\Str::lower($plan->label) }}</a>
            @elseif($plan->stripe_price_id)
                <form method="POST" action="{{ route('checkout', $plan) }}" class="lx2-checkout-form" data-has-annual="{{ $plan->stripe_annual_price_id ? '1' : '0' }}">
                    @csrf
                    <input type="hidden" name="billing_period" value="monthly" class="lx2-billing-period-input">
                    <button type="submit" class="btn btn-primary btn-block">Passer au {{ \Illuminate\Support\Str::lower($plan->label) }}</button>
                </form>
            @else
                <a href="{{ route('upgrade') }}" class="btn btn-outline btn-block">Voir l'offre</a>
            @endif

            <ul>
                @foreach($keyPerms as $permKey => $permLabel)
                @php
                    // null = illimité (autorisé) ; clé absente = non inclus — même règle que User::canFeature()
                    $val     = array_key_exists($permKey, $perms) ? $perms[$permKey] : false;
                    $enabled = is_bool($val) ? $val : ($val === null ? true : ($val > 0));
                @endphp
                <li class="{{ $enabled ? '' : 'no' }}">
                    <x-lx2-icon :name="$enabled ? 'check' : 'x'" />
                    <span>{!! $permLabel !!}@if(is_int($val) && $val > 0) <b class="num">({{ $val }})</b>@endif</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endforeach
</div>
@endif
@endif

@endsection

@push('scripts')
<script>
/* Appels API : même authentification que le reste de l'app (session + jeton web-spa) */
async function lxApi(url, method = 'GET', body = null) {
    const res = await fetch(url, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Authorization': 'Bearer ' + (window.API_TOKEN || ''),
        },
        credentials: 'same-origin',
        body: body ? JSON.stringify(body) : null,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Une erreur est survenue. Réessayez.');
    return data;
}

/* ── Popup de bienvenue : respecte la fréquence réglée dans l'admin (once / session / always) ── */
@if($popupEnabled && $prospects->isNotEmpty())
(function () {
    const uid       = '{{ auth()->id() }}';
    const frequency = @json($popupFrequency);
    const LS_KEY    = 'lx_welcome_shown_' + uid;
    const SS_KEY    = 'lx_welcome_session_' + uid;

    let shouldShow = false;
    try {
        if (frequency === 'always') {
            shouldShow = true;
        } else if (frequency === 'session') {
            if (!sessionStorage.getItem(SS_KEY)) { shouldShow = true; sessionStorage.setItem(SS_KEY, '1'); }
        } else if (!localStorage.getItem(LS_KEY)) {  // "once" (défaut)
            shouldShow = true; localStorage.setItem(LS_KEY, '1');
        }
    } catch (e) {
        shouldShow = frequency === 'always';  // stockage bloqué (navigation privée) : on n'insiste pas
    }

    if (shouldShow) {
        document.addEventListener('DOMContentLoaded', () => document.getElementById('welcomeModal').classList.remove('hidden'));
    }
})();
@endif

function closeWelcomeModal() {
    const m = document.getElementById('welcomeModal');
    if (m) m.classList.add('hidden');
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') { closeWelcomeModal(); closeCityDd(); }
});

async function welcomeConnect(userId, btn) {
    btn.disabled = true;
    btn.textContent = '…';
    try {
        await lxApi('/api/connections', 'POST', { receiver_id: userId });
        btn.textContent = 'Envoyé ✓';
        btn.style.background = 'var(--green)';
    } catch (e) {
        btn.disabled = false;
        btn.textContent = 'Se connecter';
        if (typeof toast === 'function') toast(e.message, 'error');
    }
}

async function sendConnect(userId, btn) {
    btn.disabled = true;
    try {
        await lxApi('/api/connections', 'POST', { receiver_id: userId });
        btn.innerHTML = '✓';
        btn.style.color = 'var(--green)';
        btn.title = 'Demande envoyée';
        if (typeof toast === 'function') toast('Demande de connexion envoyée', 'success');
    } catch (e) {
        btn.disabled = false;
        if (typeof toast === 'function') toast(e.message, 'error');
    }
}

function lx2SetBilling(period, btn) {
    document.querySelectorAll('#lx2PlanTabs button').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    document.querySelectorAll('.price-monthly-block').forEach(el => el.style.display = period === 'monthly' ? '' : 'none');
    document.querySelectorAll('.price-annual-block').forEach(el => el.style.display = period === 'annual' ? '' : 'none');
    document.querySelectorAll('.lx2-billing-period-input').forEach(input => {
        const form = input.closest('.lx2-checkout-form');
        const hasAnnual = form.dataset.hasAnnual === '1';
        input.value = (period === 'annual' && hasAnnual) ? 'annual' : 'monthly';
    });
}

/* ── Mon activité : graphique SVG léger, période au choix (7 jours, 30 jours, 3 mois) ── */
(function () {
    const dataEl = document.getElementById('dbActivityData');
    const chart  = document.getElementById('dbChart');
    const seg    = document.getElementById('dbSeg');
    if (!dataEl || !chart || !seg) return;

    const data = JSON.parse(dataEl.textContent);
    const SERIES = [
        { key: 'sent',      label: 'Envoyés',  color: '#4154F4' },
        { key: 'received',  label: 'Reçus',    color: '#9E6EF5' },
        { key: 'converted', label: 'Convertis', color: '#22C55E' },
    ];
    const NS = 'http://www.w3.org/2000/svg';
    const tip = document.createElement('div');
    tip.className = 'db-tip';
    chart.appendChild(tip);
    let period = '30';

    function sumOf(arr) { return arr.reduce((a, b) => a + b, 0); }

    function render() {
        const d = data[period];
        const W = chart.clientWidth, H = chart.clientHeight;
        if (!W || !H) return;
        const pad = { l: 30, r: 18, t: 10, b: 24 };
        const iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
        const n = d.labels.length;
        const max = Math.max(4, ...SERIES.flatMap(s => d[s.key]));
        const x = i => pad.l + (n === 1 ? iw / 2 : i * iw / (n - 1));
        const y = v => pad.t + ih - (v / max) * ih;

        let g = '';
        for (let k = 0; k <= 4; k++) {
            const v = Math.round(max * k / 4), yy = y(v);
            g += `<line class="grid-l" x1="${pad.l}" x2="${W - pad.r}" y1="${yy}" y2="${yy}"/>`;
            g += `<text class="axis" x="${pad.l - 8}" y="${yy + 4}" text-anchor="end">${v}</text>`;
        }
        const step = Math.max(1, Math.ceil(n / 6));
        d.labels.forEach((lab, i) => {
            if (i % step === 0 || i === n - 1) {
                g += `<text class="axis" x="${x(i)}" y="${H - 6}" text-anchor="middle">${lab}</text>`;
            }
        });
        SERIES.forEach(s => {
            const pts = d[s.key].map((v, i) => `${x(i)},${y(v)}`).join(' ');
            g += `<polyline class="ln" fill="none" stroke="${s.color}" points="${pts}"/>`;
        });
        g += `<line id="dbCursor" x1="0" x2="0" y1="${pad.t}" y2="${pad.t + ih}" stroke="var(--border)" stroke-width="1" style="display:none"/>`;
        g += `<rect class="hit" x="${pad.l}" y="${pad.t}" width="${iw}" height="${ih}" fill="transparent"/>`;

        chart.querySelectorAll('svg').forEach(el => el.remove());
        const svg = document.createElementNS(NS, 'svg');
        svg.setAttribute('width', W);
        svg.setAttribute('height', H);
        svg.setAttribute('role', 'img');
        svg.innerHTML = g;
        chart.insertBefore(svg, tip);

        const cursor = svg.querySelector('#dbCursor');
        svg.querySelector('.hit').addEventListener('mousemove', (e) => {
            const rect = svg.getBoundingClientRect();
            const px = e.clientX - rect.left;
            const i = Math.max(0, Math.min(n - 1, Math.round((px - pad.l) / (iw / Math.max(1, n - 1)))));
            cursor.setAttribute('x1', x(i));
            cursor.setAttribute('x2', x(i));
            cursor.style.display = '';
            tip.innerHTML = `<b>${d.labels[i]}</b><br>` + SERIES.map(s =>
                `<span style="color:${s.color}">●</span> ${s.label} : ${d[s.key][i]}`).join('<br>');
            tip.style.left = x(i) + 'px';
            tip.style.top = (pad.t + 4) + 'px';
            tip.classList.add('show');
        });
        svg.querySelector('.hit').addEventListener('mouseleave', () => {
            cursor.style.display = 'none';
            tip.classList.remove('show');
        });
    }

    function summary() {
        const d = data[period];
        document.getElementById('dbSentN').textContent = sumOf(d.sent);
        document.getElementById('dbRecvN').textContent = sumOf(d.received);
        document.getElementById('dbConvN').textContent = sumOf(d.converted);
    }

    seg.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', () => {
            period = btn.dataset.days;
            seg.querySelectorAll('button').forEach(b => b.classList.toggle('on', b === btn));
            summary();
            render();
        });
    });

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(render, 120);
    });

    summary();
    render();
})();
</script>
@endpush
