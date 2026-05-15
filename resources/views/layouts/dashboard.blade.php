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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
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
            padding: 0 22px;
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

                {{-- EXCHANGES / LEADS --}}
                <a href="{{ route('leads.index') }}"
                   class="lx-nav-item {{ request()->routeIs('leads.*') ? 'active' : '' }}">
                    <div class="relative">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                            <polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                        </svg>
                        @php $navPending = \App\Models\Lead::where('receiver_id', auth()->id())->where('status','new')->count(); @endphp
                        @if($navPending > 0)
                        <span class="lx-nav-badge">{{ $navPending }}</span>
                        @endif
                    </div>
                    <span>Exchanges</span>
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

                {{-- INBOX (future) --}}
                <a href="#"
                   class="lx-nav-item opacity-50 cursor-not-allowed">
                    <div class="relative">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/>
                            <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
                        </svg>
                        {{-- <span class="lx-nav-badge">5</span> --}}
                    </div>
                    <span>Inbox</span>
                </a>

            </nav>

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
                    </div>
                    <div class="border-t border-gray-100">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

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
            const mi = document.getElementById('membersNavItem');
            const ud = document.getElementById('userDropdown');
            if (mi && !mi.contains(e.target)) document.getElementById('notificationPanel').classList.add('hidden');
            if (ud && !ud.contains(e.target)) document.getElementById('userPanel').classList.add('hidden');
        });

        async function loadRequests() {
            const loading = document.getElementById('loadingState');
            const list    = document.getElementById('requestsList');
            const empty   = document.getElementById('emptyState');
            try {
                loading.style.display = 'block'; list.style.display = 'none'; empty.style.display = 'none';
                const res  = await fetch('/api/connections?type=received&status=pending', { headers: {'Accept':'application/json'}, credentials: 'same-origin' });
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
                await fetch(`/api/connections/${id}/accept`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}, credentials:'same-origin' });
                el.remove(); loadRequests(); toast('Request accepted! 🎉','success');
            } catch { el.style.opacity='1'; el.style.pointerEvents='auto'; }
        }

        async function reject(id) {
            const el = document.getElementById(`req-${id}`);
            el.style.opacity='0.5'; el.style.pointerEvents='none';
            try {
                await fetch(`/api/connections/${id}/reject`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF}, credentials:'same-origin' });
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

        window.addEventListener('DOMContentLoaded', () => {
            fetch('/api/connections?type=received&status=pending', { headers:{'Accept':'application/json'}, credentials:'same-origin' })
                .then(r=>r.json()).then(d=>{ const n=(d.data||[]).length; if(n>0){const b=document.getElementById('membersBadge'); b.textContent=n; b.style.display='flex';} }).catch(()=>{});
            setInterval(()=>{ if(notifLoaded) loadRequests(); }, 30000);
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
    @stack('scripts')
</body>
</html>
