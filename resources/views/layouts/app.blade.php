<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @if(config('firebase.api_key'))
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .playfair { font-family: 'Playfair Display', Georgia, serif; }
        @keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.05)} }
        .badge-pulse { animation: pulse 2s infinite; }
        @keyframes slideDown { from{opacity:0;transform:translateY(-10px)} to{opacity:1;transform:translateY(0)} }
        .dropdown-enter { animation: slideDown 0.2s ease-out; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        /* ── Nav items ── */
        .lx-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 0 16px;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            cursor: pointer;
            position: relative;
            transition: color 0.15s;
            text-decoration: none;
            height: 100%;
        }
        .lx-nav-item:hover { color: #475569; }
        .lx-nav-item.active { color: #1E8F88; }
        .lx-nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 3px;
            background: #1E8F88;
            border-radius: 3px 3px 0 0;
        }
        .lx-nav-badge {
            position: absolute;
            top: 8px; right: 14px;
            background: #EF4444;
            color: #fff;
            border-radius: 9999px;
            font-size: 9px;
            font-weight: 700;
            min-width: 17px;
            height: 17px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            line-height: 1;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">

    @php
        $authId = auth()->id();
        $pendingLeadsCount = \DB::table('leads')->where('receiver_id', $authId)->where('status', 'pending')->count();
        $unreadChatCount = \DB::table('messages')
            ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->where(function ($q) use ($authId) {
                $q->where('conversations.user1_id', $authId)->orWhere('conversations.user2_id', $authId);
            })
            ->where('messages.sender_id', '!=', $authId)
            ->whereNull('messages.read_at')
            ->count();
        $unreadNotifCount = \DB::table('notifications')->where('user_id', $authId)->where('is_read', false)->count();
    @endphp

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50" style="height:72px;">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 h-full flex items-stretch justify-between">

            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 flex-shrink-0 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                     style="background: linear-gradient(135deg, #34d4bf, #1E8F88);">LX</div>
                <span class="font-semibold text-[15px] text-gray-900 hidden sm:block">LeadXchange</span>
            </a>

            <!-- Nav links — icon + label -->
            <nav class="hidden md:flex items-stretch">

                {{-- START --}}
                <a href="{{ route('dashboard') }}"
                   class="lx-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
                    </svg>
                    <span>Start</span>
                </a>

                {{-- MEMBERS --}}
                <div class="relative lx-nav-item {{ request()->routeIs('connections.*') ? 'active' : '' }}"
                     id="membersNavItem" onclick="toggleMembers()" style="cursor:pointer;">
                    <div class="relative">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                        <span id="membersBadge" class="lx-nav-badge" style="display:none;">0</span>
                    </div>
                    <span>Members</span>

                    {{-- Connection requests dropdown --}}
                    <div id="notificationPanel" class="hidden absolute top-full right-0 mt-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden dropdown-enter" style="top:72px;">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">Connection Requests</h3>
                                <p class="text-xs text-gray-500 mt-0.5" id="requestCountText">Loading...</p>
                            </div>
                            <a href="{{ route('connections.index') }}" class="text-xs font-semibold" style="color:#1E8F88;">View all →</a>
                        </div>
                        <div id="loadingState" class="p-8 text-center">
                            <div class="w-8 h-8 border-2 border-t-transparent rounded-full animate-spin mx-auto" style="border-color:#2BB6A3; border-top-color:transparent;"></div>
                            <p class="text-gray-400 text-sm mt-3">Loading...</p>
                        </div>
                        <div id="requestsList" class="max-h-80 overflow-y-auto custom-scrollbar" style="display:none;"></div>
                        <div id="emptyState" class="p-10 text-center" style="display:none;">
                            <div class="text-4xl mb-3">📭</div>
                            <p class="text-gray-600 font-semibold text-sm">No pending requests</p>
                            <p class="text-gray-400 text-xs mt-1">All caught up!</p>
                        </div>
                    </div>
                </div>

                {{-- MARKETPLACE (future) --}}
                <a href="#"
                   class="lx-nav-item opacity-50 cursor-not-allowed">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                    <span>Marketplace</span>
                </a>


                {{-- NETWORK --}}
                <a href="{{ route('connections.index') }}"
                   class="lx-nav-item {{ request()->routeIs('connections.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/>
                        <path d="M12 7v4M12 11l-5.5 6M12 11l5.5 6"/>
                    </svg>
                    <span>Network</span>
                </a>

                {{-- GROUPS --}}
                <a href="{{ route('groups.index') }}"
                   class="lx-nav-item {{ request()->routeIs('groups.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Groupes</span>
                </a>

                {{-- EVENTS --}}
                <a href="{{ route('events.index') }}"
                   class="lx-nav-item {{ request()->routeIs('events.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>Événements</span>
                </a>

                {{-- LEADS --}}
                <a href="{{ route('leads.index') }}"
                   class="lx-nav-item {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <div class="relative">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        @if($pendingLeadsCount > 0)
                        <span class="lx-nav-badge">{{ $pendingLeadsCount > 9 ? '9+' : $pendingLeadsCount }}</span>
                        @endif
                    </div>
                    <span>Leads</span>
                </a>

                {{-- CHAT --}}
                <a href="{{ route('chat.index') }}"
                   class="lx-nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <div class="relative">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        @if($unreadChatCount > 0)
                        <span class="lx-nav-badge">{{ $unreadChatCount > 9 ? '9+' : $unreadChatCount }}</span>
                        @endif
                    </div>
                    <span>Chat</span>
                </a>

            </nav>

            <!-- Notification bell -->
            <div class="relative flex items-center" id="notifBellWrap">
                <button onclick="toggleNotifPanel()" id="notifBell"
                        class="relative flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition text-gray-400 hover:text-gray-600 mr-1">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    @if($unreadNotifCount > 0)
                    <span id="notifBadge" class="absolute top-1.5 right-1.5 w-4 h-4 flex items-center justify-center rounded-full text-[9px] font-bold text-white" style="background:#EF4444;">
                        {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
                    </span>
                    @else
                    <span id="notifBadge" class="hidden absolute top-1.5 right-1.5 w-4 h-4 flex items-center justify-center rounded-full text-[9px] font-bold text-white" style="background:#EF4444;"></span>
                    @endif
                </button>

                <!-- Notifications dropdown -->
                <div id="notifPanel" class="hidden absolute right-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden dropdown-enter" style="top:calc(100% + 8px);">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-900 text-sm">Notifications</h3>
                            <p id="notifCountText" class="text-xs text-gray-400 mt-0.5"></p>
                        </div>
                        <button onclick="markAllNotifRead()" class="text-xs font-semibold hover:underline" style="color:#1E8F88;">Tout lire</button>
                    </div>
                    <div id="notifLoadingState" class="p-8 text-center">
                        <div class="w-7 h-7 border-2 border-t-transparent rounded-full animate-spin mx-auto" style="border-color:#2BB6A3; border-top-color:transparent;"></div>
                        <p class="text-gray-400 text-xs mt-2">Chargement…</p>
                    </div>
                    <div id="notifList" class="max-h-80 overflow-y-auto custom-scrollbar" style="display:none;"></div>
                    <div id="notifEmpty" class="p-10 text-center" style="display:none;">
                        <div class="text-3xl mb-2">🔔</div>
                        <p class="text-gray-600 font-semibold text-sm">Aucune notification</p>
                    </div>
                </div>
            </div>

            <!-- Right: avatar + dropdown -->
            <div class="flex items-center relative" id="userDropdown">
                <button onclick="toggleUserMenu()" class="flex items-center gap-2 rounded-full hover:bg-gray-100 transition px-2 py-1.5 h-full">
                    @if(auth()->user()->profile?->avatar)
                        <img src="{{ auth()->user()->profile->avatar_url }}"
                             alt="{{ auth()->user()->first_name }}"
                             class="w-9 h-9 rounded-full object-cover flex-shrink-0 border-2 border-transparent"
                             style="border-color:#1E8F88;">
                    @else
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
                             style="background: linear-gradient(135deg, #34d4bf, #1E8F88);">
                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <svg class="text-gray-400" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                </button>

                <div id="userPanel" class="hidden absolute right-0 top-full mt-1 w-60 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden dropdown-enter">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3">
                        @if(auth()->user()->profile?->avatar)
                            <img src="{{ auth()->user()->profile->avatar_url }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                        @else
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
                                 style="background: linear-gradient(135deg, #34d4bf, #1E8F88);">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <div class="py-1.5">
                        <a href="{{ route('profile.me') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                            Profile
                        </a>
                        <a href="{{ route('groups.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Groups
                        </a>
                        <a href="{{ route('events.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            Events
                        </a>
                        <a href="{{ route('connections.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 7v4M12 11l-5.5 6M12 11l5.5 6"/></svg>
                            Network
                        </a>
                        <a href="{{ route('leads.index') }}" class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <span class="flex items-center gap-3">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                Leads
                            </span>
                            @if($pendingLeadsCount > 0)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white flex-shrink-0" style="background:#EF4444;">{{ $pendingLeadsCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('chat.index') }}" class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <span class="flex items-center gap-3">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                Chat
                            </span>
                            @if($unreadChatCount > 0)
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full text-white flex-shrink-0" style="background:#EF4444;">{{ $unreadChatCount }}</span>
                            @endif
                        </a>
                    </div>
                    <div class="border-t border-gray-100 py-1">
                        {{-- §12 CGU — Mon abonnement --}}
                        <a href="{{ route('billing.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            Mon abonnement
                        </a>
                        {{-- §10.8 RGPD --}}
                        <a href="{{ route('rgpd.request') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Mes droits RGPD
                        </a>
                    </div>
                    <div class="border-t border-gray-100">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- §5.3 — Bandeau annonce maintenance programmée -->
    @php $maintenanceEnabled = \App\Models\SystemSetting::get('maintenance_banner_enabled', false); @endphp
    @if($maintenanceEnabled)
    <div class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm flex-wrap"
         style="background:#1E293B; color:#CBD5E1;">
        <div class="flex items-center gap-2.5 flex-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FBBF24" stroke-width="2" class="flex-shrink-0">
                <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span style="color:#FBBF24; font-weight:600;">Maintenance programmée</span>
            <span>{{ \App\Models\SystemSetting::get('maintenance_banner_message', '') }}</span>
        </div>
        <a href="mailto:contact@leadxchange.com" class="text-xs font-semibold whitespace-nowrap" style="color:#2DD4B0;">
            Nous contacter →
        </a>
    </div>
    @endif

    <!-- §3.3 CGU — Bannière mise à jour si utilisateur n'a pas accepté la version courante -->
    @auth
    @php
        $cguCurrentVersion = \App\Models\SystemSetting::get('cgu_current_version', '1.1');
        $userCguVersion    = auth()->user()->cgu_version;
        $cguNeedsAcceptance = $userCguVersion !== $cguCurrentVersion;
    @endphp
    @if($cguNeedsAcceptance)
    <div id="cgu-update-banner" class="bg-amber-50 border-b border-amber-200 px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" class="flex-shrink-0"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <p class="text-sm text-amber-800">
                    <span class="font-semibold">Nos Conditions Générales d'Utilisation ont été mises à jour (v{{ $cguCurrentVersion }}).</span>
                    La poursuite de l'utilisation vaut acceptation.
                    <a href="{{ url('/legal/cgu') }}" target="_blank" class="underline font-semibold ml-1">Lire les CGU →</a>
                </p>
            </div>
            <form method="POST" action="{{ route('cgu.accept') }}" class="flex-shrink-0">
                @csrf
                <button type="submit"
                        class="px-4 py-1.5 rounded-lg text-sm font-semibold text-white transition hover:opacity-90"
                        style="background:#D97706;">
                    J'accepte la v{{ $cguCurrentVersion }}
                </button>
            </form>
        </div>
    </div>
    @endif
    @endauth

    <!-- Main Content -->
    <main class="min-h-screen" id="main-content">
        @yield('content')
    </main>

    <!-- §9 — Footer légal -->
    <footer class="border-t border-gray-100 bg-white mt-8 py-4 px-6">
        <div class="max-w-7xl mx-auto flex items-center justify-between flex-wrap gap-3 text-xs text-gray-400">
            <span>© {{ date('Y') }} X-tensia SAS — LeadXchange</span>
            <div class="flex items-center gap-4">
                <a href="{{ url('/legal/cgu') }}" target="_blank" class="hover:text-gray-600 transition">CGU</a>
                <a href="{{ url('/legal/privacy') }}" target="_blank" class="hover:text-gray-600 transition">Confidentialité</a>
                <a href="{{ route('rgpd.request') }}" class="hover:text-gray-600 transition">Mes droits RGPD</a>
                <a href="mailto:contact@leadxchange.com" class="hover:text-gray-600 transition">Contact</a>
            </div>
        </div>
    </footer>

    <!-- §15 CNIL — Bannière de consentement cookies -->
    @if(!isset($_COOKIE['lx_cookie_consent']))
    <div id="lx-cookie-banner"
         class="fixed bottom-0 left-0 right-0 z-50 bg-gray-900 text-white px-4 py-4 shadow-2xl"
         style="border-top:2px solid #14A98C;">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1 text-sm text-gray-300 leading-relaxed">
                <span class="text-white font-semibold">🍪 Cookies & Confidentialité</span> —
                Nous utilisons des cookies essentiels au fonctionnement et des cookies analytiques pour améliorer votre expérience.
                <a href="{{ url('/legal/privacy') }}" target="_blank" class="underline text-teal-400 hover:text-teal-300 ml-1">En savoir plus</a>
            </div>
            <div class="flex gap-3 flex-shrink-0">
                <button onclick="lxCookieRefuse()"
                        class="px-4 py-2 rounded-xl text-sm font-semibold border border-gray-600 text-gray-300 hover:bg-gray-800 transition">
                    Refuser
                </button>
                <button onclick="lxCookieAccept()"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                        style="background:#14A98C;">
                    Accepter
                </button>
            </div>
        </div>
    </div>
    <script>
    function lxSetCookie(name, value, days) {
        const d = new Date(); d.setTime(d.getTime() + days*24*60*60*1000);
        document.cookie = name + '=' + value + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax';
    }
    function lxCookieAccept() {
        lxSetCookie('lx_cookie_consent', 'accepted', 395); // ~13 mois CNIL
        document.getElementById('lx-cookie-banner').style.display = 'none';
    }
    function lxCookieRefuse() {
        lxSetCookie('lx_cookie_consent', 'refused', 395);
        document.getElementById('lx-cookie-banner').style.display = 'none';
    }
    </script>
    @endif

    <!-- Toast -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        let notifLoaded = false;

        function toggleMembers() {
            const panel = document.getElementById('notificationPanel');
            document.getElementById('userPanel').classList.add('hidden');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                if (!notifLoaded) { loadRequests(); notifLoaded = true; }
            } else { panel.classList.add('hidden'); }
        }

        function toggleUserMenu() {
            document.getElementById('notificationPanel').classList.add('hidden');
            document.getElementById('userPanel').classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const mi  = document.getElementById('membersNavItem');
            const ud  = document.getElementById('userDropdown');
            const nb  = document.getElementById('notifBellWrap');
            if (mi && !mi.contains(e.target)) document.getElementById('notificationPanel').classList.add('hidden');
            if (ud && !ud.contains(e.target)) document.getElementById('userPanel').classList.add('hidden');
            if (nb && !nb.contains(e.target)) document.getElementById('notifPanel').classList.add('hidden');
        });

        async function loadRequests() {
            const loading = document.getElementById('loadingState');
            const list    = document.getElementById('requestsList');
            const empty   = document.getElementById('emptyState');
            try {
                loading.style.display = 'block'; list.style.display = 'none'; empty.style.display = 'none';
                const res  = await fetch('/api/connections?type=received&status=pending', { headers: {'Accept':'application/json','Authorization':'Bearer '+window.API_TOKEN}, credentials: 'same-origin' });
                if (!res.ok) throw new Error();
                const data = await res.json();
                displayRequests(data.data || []);
            } catch { loading.style.display='none'; empty.style.display='block'; }
        }

        function displayRequests(reqs) {
            const loading = document.getElementById('loadingState');
            const list    = document.getElementById('requestsList');
            const empty   = document.getElementById('emptyState');
            const badge   = document.getElementById('membersBadge');
            const count   = document.getElementById('requestCountText');
            const n = reqs.length;
            loading.style.display = 'none';
            if (n > 0) { badge.textContent=n; badge.style.display='flex'; count.textContent=`${n} pending request${n>1?'s':''}`; }
            else { badge.style.display='none'; count.textContent='No pending requests'; }
            if (n === 0) { list.style.display='none'; empty.style.display='block'; return; }
            empty.style.display = 'none'; list.style.display = 'block';
            list.innerHTML = reqs.map(r => `
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition" id="req-${r.id}">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0" style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                            ${r.user.first_name.charAt(0)}${r.user.last_name.charAt(0)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">${r.user.first_name} ${r.user.last_name}</p>
                            <p class="text-xs text-gray-400 mt-0.5">${timeAgo(r.created_at)}</p>
                            <div class="flex gap-2 mt-2">
                                <button onclick="accept(${r.id})" class="flex-1 text-xs font-semibold py-1.5 rounded-lg text-white" style="background:#1E8F88;">Accept</button>
                                <button onclick="reject(${r.id})" class="flex-1 text-xs font-semibold py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">Reject</button>
                            </div>
                        </div>
                    </div>
                </div>`).join('');
        }

        async function accept(id) {
            const el = document.getElementById(`req-${id}`);
            el.style.opacity='0.5'; el.style.pointerEvents='none';
            try {
                await fetch(`/api/connections/${id}/accept`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                el.remove(); loadRequests(); toast('Request accepted! 🎉','success');
            } catch { el.style.opacity='1'; el.style.pointerEvents='auto'; }
        }

        async function reject(id) {
            const el = document.getElementById(`req-${id}`);
            el.style.opacity='0.5'; el.style.pointerEvents='none';
            try {
                await fetch(`/api/connections/${id}/reject`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                el.remove(); loadRequests(); toast('Request rejected','info');
            } catch { el.style.opacity='1'; el.style.pointerEvents='auto'; }
        }

        function timeAgo(d) {
            const diff=new Date()-new Date(d),m=Math.floor(diff/60000),h=Math.floor(diff/3600000),days=Math.floor(diff/86400000);
            if(m<1)return'Just now'; if(m<60)return`${m}m ago`; if(h<24)return`${h}h ago`; if(days<7)return`${days}d ago`;
            return new Date(d).toLocaleDateString();
        }

        function incrementBadge() {
            const b=document.getElementById('membersBadge');
            b.textContent=parseInt(b.textContent||0)+1; b.style.display='flex';
        }

        function toast(msg, type='success') {
            const c={success:'bg-green-500',error:'bg-red-500',info:'bg-blue-500'};
            const t=document.createElement('div');
            t.className=`${c[type]} text-white px-6 py-3 rounded-lg shadow-2xl flex items-center space-x-3 transform transition-all`;
            t.style.transform='translateX(400px)';
            t.innerHTML=`<i class="fas fa-check-circle"></i><span class="font-medium">${msg}</span>`;
            document.getElementById('toastContainer').appendChild(t);
            setTimeout(()=>t.style.transform='translateX(0)',10);
            setTimeout(()=>t.style.transform='translateX(400px)',3000);
            setTimeout(()=>t.remove(),3300);
        }

        // ── Notifications panel ──────────────────────────────────────
        let notifPanelLoaded = false;

        window.toggleNotifPanel = function() {
            const panel = document.getElementById('notifPanel');
            document.getElementById('userPanel').classList.add('hidden');
            document.getElementById('notificationPanel').classList.add('hidden');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                if (!notifPanelLoaded) { loadNotifications(); notifPanelLoaded = true; }
            } else {
                panel.classList.add('hidden');
            }
        };

        async function loadNotifications() {
            try {
                const res  = await fetch('/api/notifications', { headers:{'Accept':'application/json','Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                const data = await res.json();
                renderNotifications(data.notifications || []);
                updateNotifBadge(data.unread_count || 0);
            } catch {
                document.getElementById('notifLoadingState').style.display = 'none';
                document.getElementById('notifEmpty').style.display = 'block';
            }
        }

        function renderNotifications(items) {
            const loading = document.getElementById('notifLoadingState');
            const list    = document.getElementById('notifList');
            const empty   = document.getElementById('notifEmpty');
            const countEl = document.getElementById('notifCountText');
            loading.style.display = 'none';
            const unread = items.filter(n => !n.is_read).length;
            countEl.textContent = unread > 0 ? `${unread} non lue${unread > 1 ? 's' : ''}` : 'Tout lu';
            if (!items.length) { empty.style.display = 'block'; return; }
            empty.style.display = 'none';
            list.style.display  = 'block';
            list.innerHTML = items.map(n => `
                <div class="flex items-start gap-3 px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50 transition ${n.is_read ? 'opacity-70' : ''}" id="notif-${n.id}">
                    <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0 ${n.is_read ? 'bg-gray-200' : 'bg-teal-500'}"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 leading-tight">${escapeHtml(n.title)}</p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">${escapeHtml(n.body)}</p>
                        <p class="text-[10px] text-gray-300 mt-1">${timeAgo(n.created_at)}</p>
                    </div>
                    <button onclick="deleteNotif(${n.id})" class="text-gray-200 hover:text-red-400 transition flex-shrink-0 mt-0.5">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>`).join('');
        }

        function updateNotifBadge(count) {
            const badge = document.getElementById('notifBadge');
            if (!badge) return;
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.classList.remove('hidden');
                badge.style.display = 'flex';
            } else {
                badge.classList.add('hidden');
                badge.style.display = 'none';
            }
        }

        window.markAllNotifRead = async function() {
            try {
                await fetch('/api/notifications/read-all', { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                notifPanelLoaded = false;
                loadNotifications();
                updateNotifBadge(0);
            } catch {}
        };

        window.deleteNotif = async function(id) {
            const el = document.getElementById('notif-' + id);
            if (el) el.style.opacity = '0.3';
            try {
                await fetch('/api/notifications/' + id, { method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                if (el) el.remove();
            } catch { if (el) el.style.opacity = '1'; }
        };

        function escapeHtml(s) {
            return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // ── Refresh badge every 60s ──────────────────────────────────
        window.addEventListener('DOMContentLoaded', () => {
            fetch('/api/connections?type=received&status=pending', { headers:{'Accept':'application/json','Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' })
                .then(r=>r.json()).then(d=>{ const n=(d.data||[]).length; if(n>0){const b=document.getElementById('membersBadge'); b.textContent=n; b.style.display='flex';} }).catch(()=>{});
            setInterval(()=>{ if(notifLoaded) loadRequests(); }, 30000);
            setInterval(async()=>{
                try {
                    const r = await fetch('/api/notifications', { headers:{'Accept':'application/json','Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                    const d = await r.json();
                    updateNotifBadge(d.unread_count || 0);
                    if (notifPanelLoaded) { notifPanelLoaded = false; loadNotifications(); notifPanelLoaded = true; }
                } catch {}
            }, 60000);
        });
    </script>

    @if(config('firebase.api_key'))
    <script>
        (function() {
            firebase.initializeApp({ apiKey:'{{ config("firebase.api_key") }}', authDomain:'{{ config("firebase.auth_domain") }}', projectId:'{{ config("firebase.project_id") }}', storageBucket:'{{ config("firebase.storage_bucket") }}', messagingSenderId:'{{ config("firebase.messaging_sender_id") }}', appId:'{{ config("firebase.app_id") }}' });
            const messaging=firebase.messaging(), vapidKey='{{ config("firebase.vapid_key") }}';
            async function initFcm() {
                try {
                    if(await Notification.requestPermission()!=='granted') return;
                    const swReg=await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    const token=await messaging.getToken({vapidKey,serviceWorkerRegistration:swReg});
                    if(token) await fetch('/api/device-token',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({token,platform:'web'})});
                } catch(e){console.warn('FCM:',e.message);}
            }
            messaging.onMessage(payload=>{ const d=payload.data||{}; incrementBadge(); toast(`${d.sender_first_name} ${d.sender_last_name} vous a envoyé une demande`,'success'); });
            initFcm();
        })();
    </script>
    @endif

    <script>
        window.API_TOKEN = '{{ session("web_api_token", "") }}';
        window.CSRF      = '{{ csrf_token() }}';
    </script>

    @php
        // Build a JS-accessible map: feature key → {label, plan_label, plan_price}
        $lxPermsMap = [];
        $allPlans = \App\Models\Plan::where('is_active', true)->orderBy('price')->get();
        foreach (\App\Http\Controllers\Admin\SuperAdmin\PlanController::PERMISSIONS as $pKey => $pDef) {
            $label = \App\Models\PermissionDefinition::labelFor($pKey)
                   ?? ucfirst(str_replace('_', ' ', $pKey));
            $upgradePlan = $allPlans->first(function ($p) use ($pKey) {
                $perms = is_array($p->permissions) ? $p->permissions : [];
                if (! array_key_exists($pKey, $perms)) return false;
                $val = $perms[$pKey];
                return $val === true || (is_int($val) && $val > 0);
            });
            $lxPermsMap[$pKey] = [
                'label'      => $label,
                'plan_label' => $upgradePlan?->label ?? null,
                'plan_price' => $upgradePlan ? ($upgradePlan->price > 0 ? number_format($upgradePlan->price, 2, ',', ' ') . ' €/mois' : 'Gratuit') : null,
            ];
        }
    @endphp

    {{-- ── Global Upgrade Modal ──────────────────────────────────── --}}
    <div id="lx-upgrade-modal"
         style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px;">
        <div id="lx-upgrade-box"
             style="background:#fff;border-radius:24px;max-width:420px;width:100%;box-shadow:0 25px 60px rgba(0,0,0,.18);overflow:hidden;transform:scale(.95);opacity:0;transition:transform .2s ease,opacity .2s ease;">

            {{-- Header gradient --}}
            <div style="background:linear-gradient(135deg,#6366F1,#4338CA);padding:28px 28px 20px;text-align:center;">
                <div style="width:52px;height:52px;border-radius:16px;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h2 style="color:#fff;font-size:17px;font-weight:700;margin:0 0 4px;">Fonctionnalité Premium</h2>
                <p id="lx-upgrade-label" style="color:rgba(255,255,255,.75);font-size:13px;margin:0;"></p>
            </div>

            {{-- Body --}}
            <div style="padding:24px 28px;">
                <p id="lx-upgrade-desc" style="color:#475569;font-size:14px;line-height:1.6;margin:0 0 16px;text-align:center;"></p>

                <div id="lx-upgrade-plan-wrap" style="display:none;background:linear-gradient(135deg,#EEF2FF,#E0E7FF);border-radius:14px;padding:14px 18px;margin-bottom:20px;text-align:center;">
                    <p style="font-size:11px;color:#6366F1;font-weight:600;text-transform:uppercase;letter-spacing:.06em;margin:0 0 4px;">Disponible à partir du plan</p>
                    <p id="lx-upgrade-plan-name" style="font-size:15px;font-weight:700;color:#4338CA;margin:0 0 2px;"></p>
                    <p id="lx-upgrade-plan-price" style="font-size:12px;color:#6366F1;margin:0;"></p>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;">
                    <a href="{{ route('upgrade') }}"
                       style="display:flex;align-items:center;justify-content:center;gap:8px;padding:13px 20px;border-radius:14px;background:linear-gradient(135deg,#6366F1,#4338CA);color:#fff;font-size:14px;font-weight:600;text-decoration:none;transition:opacity .15s;"
                       onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        Voir les plans & upgrader
                    </a>
                    <button onclick="lxCloseUpgradeModal()"
                            style="padding:11px 20px;border-radius:14px;border:1.5px solid #E2E8F0;background:transparent;color:#64748B;font-size:13px;font-weight:500;cursor:pointer;transition:background .15s;"
                            onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                        Continuer avec mon plan actuel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.LX_PERMS = @json($lxPermsMap);

        window.openUpgradeModal = function(feature) {
            const perm  = window.LX_PERMS[feature] || {};
            const label = perm.label || feature.replace(/_/g,' ');
            const modal = document.getElementById('lx-upgrade-modal');
            const box   = document.getElementById('lx-upgrade-box');

            document.getElementById('lx-upgrade-label').textContent = '« ' + label + ' »';
            document.getElementById('lx-upgrade-desc').textContent  =
                'Cette fonctionnalité n\'est pas incluse dans votre plan actuel. Passez à un plan supérieur pour y accéder.';

            const planWrap = document.getElementById('lx-upgrade-plan-wrap');
            if (perm.plan_label) {
                document.getElementById('lx-upgrade-plan-name').textContent  = perm.plan_label;
                document.getElementById('lx-upgrade-plan-price').textContent = perm.plan_price || '';
                planWrap.style.display = 'block';
            } else {
                planWrap.style.display = 'none';
            }

            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                box.style.transform = 'scale(1)';
                box.style.opacity   = '1';
            });

            document.addEventListener('keydown', lxUpgradeKeydown);
        };

        window.lxCloseUpgradeModal = function() {
            const modal = document.getElementById('lx-upgrade-modal');
            const box   = document.getElementById('lx-upgrade-box');
            box.style.transform = 'scale(.95)';
            box.style.opacity   = '0';
            setTimeout(() => modal.style.display = 'none', 200);
            document.removeEventListener('keydown', lxUpgradeKeydown);
        };

        function lxUpgradeKeydown(e) {
            if (e.key === 'Escape') lxCloseUpgradeModal();
        }

        document.getElementById('lx-upgrade-modal').addEventListener('click', function(e) {
            if (e.target === this) lxCloseUpgradeModal();
        });

        @if(session('upgrade_feature'))
        window.addEventListener('DOMContentLoaded', function() {
            openUpgradeModal('{{ session("upgrade_feature") }}');
        });
        @endif
    </script>

    @stack('scripts')
</body>
</html>
