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
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 h-14 flex items-center justify-between">

            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 flex-shrink-0">
                <div class="w-8 h-8 rounded-[10px] flex items-center justify-center text-white font-bold text-[13px]"
                     style="background: linear-gradient(135deg, #34d4bf, #1E8F88);">LX</div>
                <span class="font-semibold text-base text-gray-900 hidden sm:block">LeadXchange</span>
            </a>

            <!-- Nav links -->
            <nav class="hidden md:flex items-center gap-6 text-sm">
                <a href="{{ route('dashboard') }}"
                   class="font-medium transition-colors {{ request()->routeIs('dashboard') ? '' : 'text-gray-500 hover:text-gray-900' }}"
                   @if(request()->routeIs('dashboard')) style="color:#1E8F88;" @endif>
                    Dashboard
                </a>
                <a href="{{ route('profile.me') }}"
                   class="font-medium transition-colors {{ request()->routeIs('profile.*') ? '' : 'text-gray-500 hover:text-gray-900' }}"
                   @if(request()->routeIs('profile.*')) style="color:#1E8F88;" @endif>
                    Profile
                </a>
                <a href="{{ route('connections.index') }}"
                   class="font-medium transition-colors {{ request()->routeIs('connections.*') ? '' : 'text-gray-500 hover:text-gray-900' }}"
                   @if(request()->routeIs('connections.*')) style="color:#1E8F88;" @endif>
                    Network
                </a>
                <a href="{{ route('groups.index') }}"
                   class="font-medium transition-colors {{ request()->routeIs('groups.*') ? '' : 'text-gray-500 hover:text-gray-900' }}"
                   @if(request()->routeIs('groups.*')) style="color:#1E8F88;" @endif>
                    Groups
                </a>
            </nav>

            <!-- Right side -->
            <div class="flex items-center gap-1">

                <!-- Messages icon -->
                <button class="relative w-9 h-9 flex items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 transition">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                </button>

                <!-- Notification Bell -->
                <div class="relative" id="notificationDropdown">
                    <button onclick="toggleNotifications()"
                        class="relative w-9 h-9 flex items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 transition">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                        </svg>
                        <span id="notificationBadge"
                              class="absolute top-1 right-1 w-4 h-4 rounded-full text-white font-bold flex items-center justify-center badge-pulse"
                              style="font-size:9px; background:#EF4444; display:none;">0</span>
                    </button>

                    <div id="notificationPanel" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden dropdown-enter">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900">Connection Requests</h3>
                            <p class="text-xs text-gray-500 mt-0.5" id="requestCountText">Loading...</p>
                        </div>
                        <div id="loadingState" class="p-8 text-center">
                            <div class="w-8 h-8 border-2 border-t-transparent rounded-full animate-spin mx-auto" style="border-color:#2BB6A3; border-top-color:transparent;"></div>
                            <p class="text-gray-400 text-sm mt-3">Loading...</p>
                        </div>
                        <div id="requestsList" class="max-h-96 overflow-y-auto custom-scrollbar" style="display:none;"></div>
                        <div id="emptyState" class="p-10 text-center" style="display:none;">
                            <div class="text-4xl mb-3">📭</div>
                            <p class="text-gray-600 font-semibold text-sm">No pending requests</p>
                            <p class="text-gray-400 text-xs mt-1">All caught up!</p>
                        </div>
                        <div class="border-t border-gray-100 px-5 py-3">
                            <a href="{{ route('connections.index') }}"
                               class="text-sm font-semibold flex items-center justify-center gap-1.5 transition-colors"
                               style="color:#1E8F88;">
                                View all
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="relative ml-1" id="userDropdown">
                    <button onclick="toggleUserMenu()" class="flex items-center gap-2 pl-1 rounded-full hover:bg-gray-100 transition pr-2 py-1">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold text-[13px] flex-shrink-0"
                             style="background: linear-gradient(135deg, hsl(165 60% 60%), hsl(180 55% 45%));">
                            {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                        </div>
                        <svg class="hidden sm:block text-gray-400" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </button>

                    <div id="userPanel" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden dropdown-enter">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="py-1.5">
                            <a href="{{ route('profile.me') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                Profile
                            </a>
                            <a href="{{ route('connections.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-400"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
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

        function toggleNotifications() {
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
            const nd = document.getElementById('notificationDropdown');
            const ud = document.getElementById('userDropdown');
            if (!nd.contains(e.target)) document.getElementById('notificationPanel').classList.add('hidden');
            if (!ud.contains(e.target)) document.getElementById('userPanel').classList.add('hidden');
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
            const badge   = document.getElementById('notificationBadge');
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
            const b=document.getElementById('notificationBadge');
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
                .then(r=>r.json()).then(d=>{ const n=(d.data||[]).length; if(n>0){document.getElementById('notificationBadge').textContent=n; document.getElementById('notificationBadge').style.display='flex';} }).catch(()=>{});
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

    @stack('scripts')
</body>
</html>
