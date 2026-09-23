{{--
    Icônes d'en-tête du design lx2 (maquette « LeadXchange WEB » → topIcons()) :
    Inviter un ami · Messages · Notifications · Menu du compte.
    À inclure à droite du bloc .ph de chaque page lx2 :  @include('layouts.partials.lx2-header-icons')
    Les IDs (notifBellWrap, notifPanel, userDropdown, userPanel…) sont ceux attendus par
    layouts/partials/nav-scripts.blade.php (JS partagé avec l'ancien layout).
--}}
@php
    $lx2User     = auth()->user();
    $lx2Counts   = \App\Support\NavCounts::forCurrentUser();
    $lx2Initials = strtoupper(mb_substr($lx2User->first_name ?? '?', 0, 1) . mb_substr($lx2User->last_name ?? '', 0, 1));
@endphp

<div class="icons">
    <button type="button" class="icon-btn" onclick="lx2OpenInvite()" title="Inviter un ami" aria-label="Inviter un ami">
        <x-lx2-icon name="user-plus" />
    </button>

    <a class="icon-btn" href="{{ route('chat.index') }}" title="Messages" aria-label="Messages">
        <x-lx2-icon name="message-circle" />
        @if($lx2Counts['unreadChatCount'] > 0)<span class="dot"></span>@endif
    </a>

    <div class="lx2-dd" id="notifBellWrap">
        <button type="button" class="icon-btn" id="notifBell" onclick="toggleNotifPanel()" title="Notifications" aria-label="Notifications">
            <x-lx2-icon name="bell" />
            <span id="notifBadge" class="dot {{ $lx2Counts['unreadNotifCount'] > 0 ? '' : 'hidden' }}"></span>
        </button>
        <div id="notifPanel" class="hidden pop notif-pop lx2-dd-pop">
            <div class="card-h">
                <h3>Notifications</h3>
                <button type="button" class="link lx2-linkbtn" onclick="markAllNotifRead()">Tout lire</button>
            </div>
            <div id="notifLoadingState" class="lx2-dd-state"><span class="lx2-spin"></span></div>
            <div id="notifList" style="display:none;max-height:420px;overflow:auto"></div>
            <div id="notifEmpty" class="lx2-dd-state" style="display:none;">Aucune notification</div>
            <span id="notifCountText" hidden></span>
        </div>
    </div>

    <div class="lx2-dd" id="userDropdown">
        <button type="button" class="avatar-btn" onclick="toggleUserMenu()" aria-label="Mon compte">
            @if($lx2User->profile?->avatar)
                <img class="av" src="{{ $lx2User->profile->avatar_url }}" alt="" width="34" height="34" style="width:34px;height:34px">
            @else
                <span class="av-fb" style="width:34px;height:34px;font-size:12px">{{ $lx2Initials }}</span>
            @endif
        </button>
        <div id="userPanel" class="hidden pop lx2-dd-pop">
            <div class="menu">
                <div style="padding:8px 10px">
                    <b style="font-size:13.5px">{{ $lx2User->first_name }} {{ $lx2User->last_name }}</b>
                    <div style="font-size:12px;color:var(--muted-fg)">{{ $lx2User->email }}</div>
                </div>
                <hr class="sep">
                <a href="{{ route('profile.me') }}"><x-lx2-icon name="user" />Mon profil</a>
                <a href="{{ route('points.index') }}"><x-lx2-icon name="wallet" />Mes points
                    <span class="lx2-menu-meta">{{ (int) ($lx2User->points_balance ?? 0) }} pts</span></a>
                <a href="{{ route('billing.index') }}"><x-lx2-icon name="credit-card" />Abonnement</a>
                <a href="{{ route('referral.index') }}"><x-lx2-icon name="user-round-plus" />Parrainage</a>
                <a href="{{ route('support.index') }}"><x-lx2-icon name="life-buoy" />Support</a>
                @if($lx2User->isAmbassador())
                    <a href="{{ route('ambassador.dashboard') }}"><x-lx2-icon name="star" />Espace Ambassadeur</a>
                @elseif($lx2User->isConsul())
                    <a href="{{ route('consul.dashboard') }}"><x-lx2-icon name="star" />Espace Consul</a>
                @endif
                @if($lx2User->isEnterpriseHolder())
                    <a href="{{ route('enterprise.dashboard') }}"><x-lx2-icon name="users" />Espace Entreprise</a>
                @endif
                <hr class="sep">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="color:var(--destructive)"><x-lx2-icon name="log-out" />Se déconnecter</button>
                </form>
            </div>
        </div>
    </div>
</div>
