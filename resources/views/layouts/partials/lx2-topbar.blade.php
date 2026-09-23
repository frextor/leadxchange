{{--
    Barre du haut du nouveau design (lx2) : demandes de connexion, notifications, menu utilisateur.
    Les IDs des éléments sont identiques à ceux de l'ancien layout pour réutiliser
    layouts/partials/nav-scripts.blade.php sans duplication de JS.
--}}
@php
    $lx2User     = auth()->user();
    $lx2Initials = strtoupper(mb_substr($lx2User->first_name ?? '?', 0, 1) . mb_substr($lx2User->last_name ?? '', 0, 1));
    $lx2CityId   = session('selected_city_id', $lx2User->city_id);
    $lx2CityName = $lx2CityId ? optional(\App\Models\City::find($lx2CityId))->name : null;
    $lx2Points   = (int) ($lx2User->points_balance ?? 0);
@endphp

<header class="lx2-top">
    <a class="brand-m" href="{{ route('dashboard') }}" aria-label="Accueil">
        <img src="{{ asset('images/brand/logo-mark.svg') }}" alt="">
        <span>LeadXchange</span>
    </a>
    <div class="spacer"></div>

    <div class="icons">
        {{-- Demandes de connexion --}}
        <div class="lx2-dd" id="membersNavItem">
            <button type="button" class="icon-btn" onclick="toggleMembers()" aria-label="Demandes de connexion" title="Demandes de connexion">
                <x-lx2-icon name="user-plus" />
                <span id="membersBadge" class="cnt" style="display:none;">0</span>
            </button>
            <div id="notificationPanel" class="hidden pop notif-pop lx2-dd-pop">
                <div class="lx2-dd-head">
                    <div>
                        <b>Demandes de connexion</b>
                        <small id="requestCountText">Chargement…</small>
                    </div>
                    <a class="link" href="{{ route('connections.index') }}">Voir tout</a>
                </div>
                <div id="loadingState" class="lx2-dd-state"><span class="lx2-spin"></span></div>
                <div id="requestsList" class="lx2-dd-list" style="display:none;"></div>
                <div id="emptyState" class="lx2-dd-state" style="display:none;">
                    <x-lx2-icon name="inbox" />
                    <p>Aucune demande en attente</p>
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="lx2-dd" id="notifBellWrap">
            <button type="button" class="icon-btn" id="notifBell" onclick="toggleNotifPanel()" aria-label="Notifications" title="Notifications">
                <x-lx2-icon name="bell" />
                <span id="notifBadge" class="cnt {{ $unreadNotifCount > 0 ? '' : 'hidden' }}">{{ $unreadNotifCount > 9 ? '9+' : ($unreadNotifCount ?: '') }}</span>
            </button>
            <div id="notifPanel" class="hidden pop notif-pop lx2-dd-pop">
                <div class="lx2-dd-head">
                    <div>
                        <b>Notifications</b>
                        <small id="notifCountText"></small>
                    </div>
                    <button type="button" class="link" onclick="markAllNotifRead()">Tout lire</button>
                </div>
                <div id="notifLoadingState" class="lx2-dd-state"><span class="lx2-spin"></span></div>
                <div id="notifList" class="lx2-dd-list" style="display:none;"></div>
                <div id="notifEmpty" class="lx2-dd-state" style="display:none;">
                    <x-lx2-icon name="bell" />
                    <p>Aucune notification</p>
                </div>
            </div>
        </div>

        {{-- Menu utilisateur --}}
        <div class="lx2-dd" id="userDropdown">
            <button type="button" class="avatar-btn" onclick="toggleUserMenu()" aria-label="Mon compte" title="Mon compte">
                @if($lx2User->profile?->avatar)
                    <img class="av" src="{{ $lx2User->profile->avatar_url }}" alt="" style="width:34px;height:34px;">
                @else
                    <span class="av-fb" style="width:34px;height:34px;font-size:12.5px;">{{ $lx2Initials }}</span>
                @endif
            </button>
            <div id="userPanel" class="hidden pop menu lx2-dd-pop" style="width:260px;">
                <div class="lx2-user-head">
                    <b>{{ $lx2User->first_name }} {{ $lx2User->last_name }}</b>
                    <small>{{ $lx2User->email }}</small>
                    @if($lx2CityName)
                        <span class="badge b-soft" style="margin-top:6px;height:20px;font-size:11px;"><x-lx2-icon name="map-pin" /> {{ $lx2CityName }}</span>
                    @endif
                </div>
                <hr class="sep">
                <a href="{{ route('profile.me') }}"><x-lx2-icon name="contact" /> Mon profil</a>
                <a href="{{ route('points.index') }}">
                    <x-lx2-icon name="wallet" /> Mes points
                    <span class="badge {{ $lx2Points >= 0 ? 'b-ok' : 'b-hot' }}" style="margin-left:auto;height:20px;font-size:11px;">{{ $lx2Points > 0 ? '+' : '' }}{{ $lx2Points }} pts</span>
                </a>
                <a href="{{ route('billing.index') }}"><x-lx2-icon name="gem" /> Mon abonnement</a>
                <a href="{{ route('referral.index') }}"><x-lx2-icon name="user-plus" /> Parrainage</a>
                <a href="{{ route('support.index') }}"><x-lx2-icon name="message-circle" /> Support</a>
                @if($lx2User->isAmbassador() || $lx2User->isConsul() || $lx2User->isEnterpriseHolder())
                <hr class="sep">
                @if($lx2User->isAmbassador())
                    <a href="{{ route('ambassador.dashboard') }}" style="color:var(--primary);font-weight:500;"><x-lx2-icon name="star" /> Espace Ambassadeur</a>
                @elseif($lx2User->isConsul())
                    <a href="{{ route('consul.dashboard') }}" style="color:var(--purple);font-weight:500;"><x-lx2-icon name="star" /> Espace Consul</a>
                @endif
                @if($lx2User->isEnterpriseHolder())
                    <a href="{{ route('enterprise.dashboard') }}" style="color:var(--primary);font-weight:500;"><x-lx2-icon name="users" /> Espace Entreprise</a>
                @endif
                @endif
                <hr class="sep">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="color:var(--destructive);"><x-lx2-icon name="log-out" /> Déconnexion</button>
                </form>
            </div>
        </div>
    </div>
</header>

{{-- Détail d'une notification (utilisé par nav-scripts) --}}
<div id="notifDetailModal" class="hidden lx2-modal" onclick="if(event.target===this) closeNotifDetail()">
    <div class="card lx2-modal-box">
        <div class="lx2-dd-head">
            <b id="notifDetailTitle"></b>
            <button type="button" class="icon-btn" style="border:0" onclick="closeNotifDetail()" aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div style="padding:14px 16px;">
            <p id="notifDetailBody" style="margin:0;color:var(--fg-2);line-height:1.6;"></p>
            <p id="notifDetailDate" style="margin:10px 0 0;color:var(--muted-fg);font-size:12px;"></p>
        </div>
        <div id="notifDetailActions" style="display:flex;gap:8px;padding:0 16px 16px;"></div>
    </div>
</div>
