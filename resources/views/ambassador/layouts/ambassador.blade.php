<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Ambassadeur') — LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if(config('firebase.api_key'))
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { font-family: 'Inter', -apple-system, sans-serif; }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }

        /* ── Nav items ── */
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 10px;
            font-size: 13.5px; font-weight: 500;
            color: #64748B; transition: all .12s;
            text-decoration: none; white-space: nowrap;
            position: relative;
        }
        .nav-item svg { flex-shrink: 0; color: #94A3B8; transition: color .12s; }
        .nav-item:hover { background: #F1F5F9; color: #1E293B; }
        .nav-item:hover svg { color: #475569; }

        .nav-item.active {
            background: linear-gradient(135deg, #F0FDFA, #CCFBF1);
            color: #0F766E;
            font-weight: 600;
        }
        .nav-item.active svg { color: #0F766E; }
        .nav-item.active::before {
            content: '';
            position: absolute; left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%; border-radius: 0 3px 3px 0;
            background: #14B8A6;
        }

        /* Section labels */
        .nav-section {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: #94A3B8;
            padding: 3px 12px; margin-top: 20px; margin-bottom: 4px;
        }

        /* Badge */
        .nav-badge {
            margin-left: auto; font-size: 10px; font-weight: 700;
            padding: 2px 6px; border-radius: 99px; line-height: 1.4;
            flex-shrink: 0;
        }

        /* Content area */
        .main-content { background: #F8FAFC; }

        /* KPI cards */
        .amb-kpi-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            padding: 20px 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            transition: box-shadow .15s;
        }
        .amb-kpi-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,.08); }

        /* Progress bar */
        .amb-progress-bar {
            height: 6px;
            border-radius: 99px;
            background: #E2E8F0;
            overflow: hidden;
        }
        .amb-progress-fill {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, #14B8A6, #2DD4BF);
            transition: width .6s ease;
        }

        /* Mobile sidebar */
        @media (max-width: 768px) {
            #amb-sidebar { transform: translateX(-100%); }
            #amb-sidebar.open { transform: translateX(0); }
            #amb-content { margin-left: 0 !important; }
        }

        @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        .fade-in { animation: fadeIn .25s ease forwards; }
    </style>
    @stack('styles')
</head>
<body class="antialiased" style="background:#F8FAFC;">

<div class="flex h-screen overflow-hidden">

    {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════════ --}}
    <aside id="amb-sidebar" class="w-60 flex-shrink-0 flex flex-col bg-white"
           style="border-right:1px solid #E2E8F0; position:fixed; top:0; left:0; bottom:0; z-index:40; transition:transform .25s ease;">

        {{-- Logo --}}
        <div class="px-5 pt-5 pb-4" style="border-bottom:1px solid #F1F5F9;">
            <a href="{{ route('ambassador.dashboard') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm overflow-hidden">
                    <img src="{{ asset('images/brand/logo-mark.svg') }}" alt="LeadXchange" class="w-full h-full">
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-800 leading-none">LeadXchange</p>
                    <p class="text-[10px] font-semibold tracking-widest uppercase mt-0.5 text-teal-500">Ambassadeur</p>
                </div>
            </a>
        </div>

        {{-- Ambassador card --}}
        @php $amb = auth()->user(); @endphp
        <div class="mx-3 mt-3 mb-1 px-3 py-2.5 rounded-xl" style="background:linear-gradient(135deg,#F0FDFA,#CCFBF1); border:1px solid #99F6E4;">
            <div class="flex items-center gap-2.5">
                @if($amb->profile?->avatar)
                    <img src="{{ $amb->profile->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 ring-2 ring-teal-200">
                @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm"
                         style="background:linear-gradient(135deg,#14B8A6,#0D9488);">
                        {{ strtoupper(substr($amb->first_name,0,1)) }}{{ strtoupper(substr($amb->last_name,0,1)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-[12px] font-semibold text-slate-800 truncate leading-none">{{ $amb->first_name }} {{ $amb->last_name }}</p>
                    <p class="text-[10px] font-semibold text-teal-600 mt-0.5 flex items-center gap-1">
                        <span>🏅</span> Ambassadeur
                    </p>
                </div>
            </div>
            @if($amb->region || $amb->city)
            <p class="text-[11px] mt-1.5 flex items-center gap-1 text-teal-700">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $amb->region?->name ?? $amb->city?->name ?? 'Région non définie' }}
            </p>
            @endif
        </div>

        {{-- Navigation --}}
        <nav id="amb-sidebar-nav" class="flex-1 px-3 py-2 overflow-y-auto space-y-0.5">

            <p class="nav-section">Tableau de bord</p>

            <a href="{{ route('ambassador.dashboard') }}"
               class="nav-item {{ request()->routeIs('ambassador.dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                Tableau de bord
            </a>

            <p class="nav-section">Ma Région</p>

            <a href="{{ route('ambassador.members.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.members.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Mes Membres
            </a>

            <a href="{{ route('ambassador.events.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.events.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Événements
            </a>

            <a href="{{ route('ambassador.consuls.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.consuls.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Mes Consuls
            </a>

            <p class="nav-section">Activité</p>

            <a href="{{ route('ambassador.leads.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.leads.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Leads
            </a>

            <a href="{{ route('ambassador.invitations.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.invitations.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                Invitations
            </a>

            <a href="{{ route('ambassador.communication.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.communication.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Communication
            </a>

            <p class="nav-section">Performance</p>

            <a href="{{ route('ambassador.performance.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.performance.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Performance
            </a>

            <a href="{{ route('ambassador.ranking.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.ranking.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Classement
            </a>

            <a href="{{ route('ambassador.reports.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.reports.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Rapports
            </a>

            <p class="nav-section">Compte</p>

            <a href="{{ route('ambassador.profile.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.profile.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
                Mon Profil
            </a>

            <a href="{{ route('ambassador.notifications.index') }}"
               class="nav-item {{ request()->routeIs('ambassador.notifications.*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notifications
            </a>

            <a href="{{ route('dashboard') }}" class="nav-item" style="margin-top:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Espace Membre
            </a>

        </nav>

        {{-- User footer --}}
        <div class="px-3 py-3" style="border-top:1px solid #F1F5F9;">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-xl hover:bg-slate-50 transition group">
                @if($amb->profile?->avatar)
                    <img src="{{ $amb->profile->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm"
                         style="background:linear-gradient(135deg,#14B8A6,#0D9488);">
                        {{ strtoupper(substr($amb->first_name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-slate-700 truncate leading-none">
                        {{ $amb->first_name }} {{ $amb->last_name }}
                    </p>
                    <p class="text-[11px] font-medium text-teal-500 mt-0.5">Ambassadeur</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    {{-- ══ MAIN ══════════════════════════════════════════════════════════════════ --}}
    <div id="amb-content" class="flex-1 flex flex-col overflow-hidden" style="margin-left:240px;">

        {{-- Topbar --}}
        <header class="bg-white flex items-center justify-between flex-shrink-0 px-6" style="height:52px; border-bottom:1px solid #E2E8F0;">

            {{-- Mobile hamburger + breadcrumb --}}
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-slate-600">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="flex items-center gap-1.5 text-sm text-slate-400">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-300"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <span>Ambassadeur</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-200"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="text-slate-700 font-semibold">@yield('page-title', 'Dashboard')</span>
                </div>
            </div>

            {{-- Right side --}}
            <div class="flex items-center gap-3">
                @if(session('success'))
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 border border-red-100">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ session('error') }}
                </div>
                @endif

                <a href="{{ route('ambassador.notifications.index') }}"
                   class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </a>

                <a href="{{ route('ambassador.profile.index') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                    @if(auth()->user()->profile?->avatar)
                        <img src="{{ auth()->user()->profile->avatar_url }}" class="w-7 h-7 rounded-full object-cover ring-2 ring-teal-200">
                    @else
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0 shadow-sm"
                             style="background:linear-gradient(135deg,#14B8A6,#0D9488);">
                            {{ strtoupper(substr(auth()->user()->first_name,0,1)) }}
                        </div>
                    @endif
                    <span class="text-[13px] font-semibold text-slate-700 hidden sm:block">{{ auth()->user()->first_name }}</span>
                </a>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-7 pb-10 pt-6 main-content fade-in">
            @yield('content')
        </main>
    </div>

</div>

{{-- Mobile overlay --}}
<div id="sidebarOverlay" onclick="toggleSidebar()"
     class="hidden fixed inset-0 bg-black bg-opacity-40 z-30 md:hidden"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.CSRF = '{{ csrf_token() }}';

@if(config('firebase.api_key'))
(function() {
    try {
        firebase.initializeApp({
            apiKey:            '{{ config("firebase.api_key") }}',
            authDomain:        '{{ config("firebase.auth_domain") }}',
            projectId:         '{{ config("firebase.project_id") }}',
            storageBucket:     '{{ config("firebase.storage_bucket") }}',
            messagingSenderId: '{{ config("firebase.messaging_sender_id") }}',
            appId:             '{{ config("firebase.app_id") }}',
        });
        const messaging = firebase.messaging();
        const vapidKey  = '{{ config("firebase.vapid_key") }}';

        async function initFcm() {
            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') return;
                const swReg = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                const token = await messaging.getToken({ vapidKey, serviceWorkerRegistration: swReg });
                if (token) {
                    await fetch('/api/device-token', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.CSRF },
                        credentials: 'same-origin',
                        body: JSON.stringify({ token, platform: 'web' }),
                    });
                }
            } catch (e) { console.warn('FCM:', e.message); }
        }

        messaging.onMessage(payload => {
            const d = payload.data || {};
            const title = d.title || 'LeadXchange';
            const body  = d.body  || '';
            if (Notification.permission === 'granted') {
                new Notification(title, { body, icon: '/favicon.ico' });
            }
        });

        initFcm();
    } catch(e) { console.warn('Firebase init:', e.message); }
})();
@endif

// Sidebar scroll persistence
(function () {
    var nav = document.getElementById('amb-sidebar-nav');
    if (!nav) return;
    var key = 'ambSidebarScroll';
    var saved = sessionStorage.getItem(key);
    if (saved) nav.scrollTop = parseInt(saved, 10);
    nav.addEventListener('scroll', function () {
        sessionStorage.setItem(key, nav.scrollTop);
    }, { passive: true });
})();

function toggleSidebar() {
    document.getElementById('amb-sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('hidden');
}

function swalDelete(btn, name) {
    Swal.fire({
        title: 'Supprimer ?',
        html: '<span style="color:#64748B;font-size:14px;">Voulez-vous supprimer <strong style="color:#0F172A;">' + name + '</strong> ?<br>Cette action est <strong>irréversible</strong>.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#F8FAFC',
        customClass: { popup:'swal-lx-popup', title:'swal-lx-title', cancelButton:'swal-lx-cancel' },
        reverseButtons: true,
        focusCancel: true,
    }).then(function(r) { if (r.isConfirmed) btn.closest('form').submit(); });
}
</script>
<style>
.swal-lx-popup  { border-radius:20px!important; padding:2rem!important; font-family:'Inter',sans-serif!important; box-shadow:0 20px 60px rgba(0,0,0,.12)!important; }
.swal-lx-title  { font-size:18px!important; font-weight:700!important; color:#0F172A!important; }
.swal-lx-cancel { color:#64748B!important; font-weight:600!important; border:1px solid #E2E8F0!important; background:#F8FAFC!important; }
.swal-lx-cancel:hover { background:#F1F5F9!important; }
.swal2-icon.swal2-warning { border-color:#FCD34D!important; color:#F59E0B!important; }
</style>
@stack('scripts')
</body>
</html>
