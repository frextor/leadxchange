<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/logo-mark.svg') }}">
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
        ['pendingLeadsCount' => $pendingLeadsCount, 'unreadChatCount' => $unreadChatCount, 'unreadNotifCount' => $unreadNotifCount]
            = \App\Support\NavCounts::forCurrentUser();
    @endphp

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50" style="height:72px;">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 h-full flex items-stretch justify-between">

            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 flex-shrink-0 py-4">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                    <img src="{{ asset('images/brand/logo-mark.svg') }}" alt="LeadXchange" class="w-full h-full">
                </div>
                <span class="font-semibold text-[15px] text-gray-900 hidden sm:block">LeadXchange</span>
            </a>

            <!-- Nav links — icon + label (order & visibility from admin menu settings) -->
            @php
                $navConfigRow = \App\Models\SystemSetting::where('key','nav_menu_config')->first();
                $navMenuItems = ($navConfigRow && $navConfigRow->value)
                    ? (json_decode($navConfigRow->value, true) ?? \App\Http\Controllers\Admin\SuperAdmin\SettingsController::defaultMenuItems())
                    : \App\Http\Controllers\Admin\SuperAdmin\SettingsController::defaultMenuItems();
            @endphp
            <nav class="hidden md:flex items-stretch">

                @foreach($navMenuItems as $navItem)
                @if(!($navItem['visible'] ?? true)) @continue @endif

                @if($navItem['key'] === 'dashboard')
                {{-- START --}}
                <a href="{{ route('dashboard') }}"
                   class="lx-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
                    </svg>
                    <span>{{ $navItem['label'] }}</span>
                </a>

                @elseif($navItem['key'] === 'members')
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
                    <span>{{ $navItem['label'] }}</span>

                    {{-- Connection requests dropdown --}}
                    <div id="notificationPanel" class="hidden absolute top-full right-0 mt-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden dropdown-enter" style="top:72px;">
                        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-gray-900 text-sm">Demandes de connexion</h3>
                                <p class="text-xs text-gray-500 mt-0.5" id="requestCountText">Chargement...</p>
                            </div>
                            <a href="{{ route('connections.index') }}" class="text-xs font-semibold" style="color:#1E8F88;">Voir tout →</a>
                        </div>
                        <div id="loadingState" class="p-8 text-center">
                            <div class="w-8 h-8 border-2 border-t-transparent rounded-full animate-spin mx-auto" style="border-color:#2BB6A3; border-top-color:transparent;"></div>
                            <p class="text-gray-400 text-sm mt-3">Chargement...</p>
                        </div>
                        <div id="requestsList" class="max-h-80 overflow-y-auto custom-scrollbar" style="display:none;"></div>
                        <div id="emptyState" class="p-10 text-center" style="display:none;">
                            <div class="text-4xl mb-3">📭</div>
                            <p class="text-gray-600 font-semibold text-sm">Aucune demande en attente</p>
                            <p class="text-gray-400 text-xs mt-1">Vous êtes à jour !</p>
                        </div>
                    </div>
                </div>

                @elseif($navItem['key'] === 'network')
                {{-- NETWORK --}}
                <a href="{{ route('connections.index') }}"
                   class="lx-nav-item {{ request()->routeIs('connections.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/>
                        <path d="M12 7v4M12 11l-5.5 6M12 11l5.5 6"/>
                    </svg>
                    <span>{{ $navItem['label'] }}</span>
                </a>

                @elseif($navItem['key'] === 'groups')
                {{-- GROUPS --}}
                <a href="{{ route('groups.index') }}"
                   class="lx-nav-item {{ request()->routeIs('groups.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>{{ $navItem['label'] }}</span>
                </a>

                @elseif($navItem['key'] === 'events')
                {{-- EVENTS --}}
                <a href="{{ route('events.index') }}"
                   class="lx-nav-item {{ request()->routeIs('events.*') ? 'active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span>{{ $navItem['label'] }}</span>
                </a>

                @elseif($navItem['key'] === 'leads')
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
                    <span>{{ $navItem['label'] }}</span>
                </a>

                @elseif($navItem['key'] === 'chat')
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
                    <span>{{ $navItem['label'] }}</span>
                </a>
                @endif

                @endforeach

                {{-- CONSULS (ambassadors only — always shown, not in config) --}}
                @if(auth()->user()->isAmbassador())
                <a href="{{ route('ambassador.consuls.index') }}"
                   class="lx-nav-item {{ request()->routeIs('ambassador.consuls.*') ? 'active' : '' }}">
                    <div class="relative">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        @php $pendingConsulsCount = \App\Models\User::where('consul_status','pending')->where(function($q){ $u = auth()->user(); $u->region_id ? $q->where('region_id',$u->region_id) : $q->where('city_id',$u->city_id); })->count(); @endphp
                        @if($pendingConsulsCount > 0)
                        <span class="lx-nav-badge">{{ $pendingConsulsCount > 9 ? '9+' : $pendingConsulsCount }}</span>
                        @endif
                    </div>
                    <span>Consuls</span>
                </a>
                @endif

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

            <!-- Notification detail modal -->
            <div id="notifDetailModal" class="hidden fixed inset-0 z-[999] flex items-center justify-center p-4" style="background:rgba(15,23,42,.45);" onclick="if(event.target===this) closeNotifDetail()">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3">
                        <h3 id="notifDetailTitle" class="text-sm font-bold text-gray-900 leading-snug"></h3>
                        <button onclick="closeNotifDetail()" class="text-gray-300 hover:text-gray-500 flex-shrink-0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="px-5 py-4">
                        <p id="notifDetailBody" class="text-sm text-gray-600 leading-relaxed"></p>
                        <p id="notifDetailDate" class="text-xs text-gray-300 mt-3"></p>
                    </div>
                    <div id="notifDetailActions" class="px-5 pb-5 flex gap-2"></div>
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
                            @php
                                $navSelectedCityId = session('selected_city_id', auth()->user()->city_id);
                                $navCityName = $navSelectedCityId
                                    ? optional(\App\Models\City::find($navSelectedCityId))->name
                                    : null;
                            @endphp
                            @if($navCityName)
                            <p class="text-[10px] font-semibold mt-0.5 flex items-center gap-1" style="color:#1E8F88;">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/></svg>
                                {{ $navCityName }}
                            </p>
                            @endif
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
                        <a href="{{ route('referral.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                            Parrainage
                        </a>
                    </div>
                    <div class="border-t border-gray-100 py-1">
                        @if(auth()->user()->isConsul() && !auth()->user()->isAmbassador())
                        <a href="{{ route('consul.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2" class="flex-shrink-0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span class="font-semibold" style="color:#7C3AED;">Espace Consul</span>
                        </a>
                        @endif
                        @if(auth()->user()->isEnterpriseHolder())
                        <a href="{{ route('enterprise.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4338CA" stroke-width="2" class="flex-shrink-0"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                            <span class="font-semibold" style="color:#4338CA;">Espace Entreprise</span>
                        </a>
                        @endif
                        {{-- §12 CGU — Mon abonnement --}}
                        <a href="{{ route('billing.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            Mon abonnement
                        </a>
                        {{-- Points --}}
                        <a href="{{ route('points.index') }}" class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <span class="flex items-center gap-3">
                                <span style="font-size:15px;line-height:1;">⭐</span>
                                Mes Points
                            </span>
                            @php $navPointsBalance = (int)(auth()->user()->points_balance ?? 0); @endphp
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-full flex-shrink-0"
                                  style="background:{{ $navPointsBalance >= 0 ? '#ECFDF5' : '#FEF2F2' }};
                                         color:{{ $navPointsBalance >= 0 ? '#059669' : '#DC2626' }};">
                                {{ $navPointsBalance > 0 ? '+' : '' }}{{ $navPointsBalance }} pts
                            </span>
                        </a>
                        {{-- Support --}}
                        <a href="{{ route('support.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400 flex-shrink-0"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            Support
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

    @include('layouts.partials.banners')

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
                <a href="{{ route('support.index') }}" class="hover:text-gray-600 transition">Support</a>
                <a href="mailto:contact@leadxchange.com" class="hover:text-gray-600 transition">Contact</a>
            </div>
        </div>
    </footer>

    @include('layouts.partials.cookie-banner')

    @include('layouts.partials.nav-scripts')

    @include('layouts.partials.firebase')

    @include('layouts.partials.api-token')

    @include('layouts.partials.upgrade-modal')

    @include('layouts.partials.sweetalert')
    @stack('scripts')
</body>
</html>
