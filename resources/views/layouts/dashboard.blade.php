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
        body {
            font-family: 'Inter', sans-serif;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .badge-pulse {
            animation: pulse 2s infinite;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-enter {
            animation: slideDown 0.2s ease-out;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-50 antialiased">

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

                <!-- Logo & Nav -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-lg">LX</span>
                        </div>
                        <span class="ml-3 text-xl font-bold text-gray-900 hidden sm:block">LeadXchange</span>
                    </a>

                    <div class="hidden md:flex space-x-1">
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition">
                            <i class="fas fa-home mr-2"></i><span class="hidden lg:inline">Dashboard</span>
                        </a>
                        <a href="{{ route('connections.index') }}" class="px-4 py-2 text-sm font-medium {{ request()->routeIs('connections.*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:bg-gray-50' }} rounded-lg transition relative">
                            <i class="fas fa-users mr-2"></i><span class="hidden lg:inline">Network</span>
                        </a>
                        <a href="#" class="px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg transition">
                            <i class="fas fa-envelope mr-2"></i><span class="hidden lg:inline">Messages</span>
                        </a>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="flex items-center space-x-2 sm:space-x-3">

                    <!-- Search (hidden on mobile) -->
                    <div class="hidden lg:block relative">
                        <input type="text" placeholder="Search..." class="w-64 pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>

                    <!-- Notification Bell -->
                    <div class="relative" id="notificationDropdown">
                        <button onclick="toggleNotifications()" class="relative p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <i class="fas fa-bell text-xl"></i>
                            <span id="notificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center badge-pulse" style="display: none;">0</span>
                        </button>

                        <!-- Dropdown -->
                        <div id="notificationPanel" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden dropdown-enter">
                            <div class="bg-gradient-to-r from-teal-500 to-teal-600 px-5 py-4">
                                <h3 class="text-white font-bold text-base sm:text-lg">Connection Requests</h3>
                                <p class="text-teal-100 text-xs sm:text-sm mt-1" id="requestCountText">Loading...</p>
                            </div>

                            <div id="loadingState" class="p-8 text-center">
                                <div class="w-8 h-8 border-4 border-teal-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                                <p class="text-gray-500 text-sm mt-3">Loading...</p>
                            </div>

                            <div id="requestsList" class="max-h-96 overflow-y-auto custom-scrollbar" style="display: none;"></div>

                            <div id="emptyState" class="p-10 text-center" style="display: none;">
                                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-600 font-semibold">No requests</p>
                                <p class="text-gray-400 text-sm mt-2">All caught up! 🎉</p>
                            </div>

                            <div class="border-t border-gray-200 px-5 py-3 bg-gray-50">
                                <a href="{{ route('connections.index') }}" class="text-teal-600 hover:text-teal-700 text-sm font-semibold flex items-center justify-center group">
                                    View all
                                    <i class="fas fa-arrow-right ml-2 text-xs group-hover:translate-x-1 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="relative" id="userDropdown">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 hover:bg-gray-100 rounded-lg px-2 py-1 transition">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-500 rounded-full flex items-center justify-center shadow-md relative">
                                <span class="text-white font-bold text-sm">
                                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                                </span>
                                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full"></span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-xs hidden sm:block"></i>
                        </button>

                        <div id="userPanel" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden dropdown-enter">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>

                            <div class="py-2">
                                <a href="{{ route('profile.me') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-user w-5 text-gray-400"></i>
                                    <span class="ml-3">Profile</span>
                                </a>
                                <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-cog w-5 text-gray-400"></i>
                                    <span class="ml-3">Settings</span>
                                </a>
                                <a href="{{ route('connections.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <i class="fas fa-users w-5 text-gray-400"></i>
                                    <span class="ml-3">Connections</span>
                                </a>
                            </div>

                            <div class="border-t border-gray-200">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 font-medium">
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        <span class="ml-3">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <!-- JavaScript -->
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        let notifLoaded = false;

        function toggleNotifications() {
            const panel = document.getElementById('notificationPanel');
            document.getElementById('userPanel').classList.add('hidden');

            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                if (!notifLoaded) {
                    loadRequests();
                    notifLoaded = true;
                }
            } else {
                panel.classList.add('hidden');
            }
        }

        function toggleUserMenu() {
            const panel = document.getElementById('userPanel');
            document.getElementById('notificationPanel').classList.add('hidden');
            panel.classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const nd = document.getElementById('notificationDropdown');
            const ud = document.getElementById('userDropdown');
            if (!nd.contains(e.target)) document.getElementById('notificationPanel').classList.add('hidden');
            if (!ud.contains(e.target)) document.getElementById('userPanel').classList.add('hidden');
        });

        async function loadRequests() {
            const loading = document.getElementById('loadingState');
            const list = document.getElementById('requestsList');
            const empty = document.getElementById('emptyState');

            try {
                loading.style.display = 'block';
                list.style.display = 'none';
                empty.style.display = 'none';

                const res = await fetch('/api/connections?type=received&status=pending', {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (!res.ok) throw new Error('Failed');
                const data = await res.json();
                displayRequests(data.data || []);
            } catch (e) {
                console.error(e);
                loading.style.display = 'none';
                empty.style.display = 'block';
            }
        }

        function displayRequests(reqs) {
            const loading = document.getElementById('loadingState');
            const list = document.getElementById('requestsList');
            const empty = document.getElementById('emptyState');
            const badge = document.getElementById('notificationBadge');
            const count = document.getElementById('requestCountText');
            const n = reqs.length;

            loading.style.display = 'none';

            if (n > 0) {
                badge.textContent = n;
                badge.style.display = 'flex';
                count.textContent = `${n} pending request${n > 1 ? 's' : ''}`;
            } else {
                badge.style.display = 'none';
                count.textContent = 'No pending requests';
            }

            if (n === 0) {
                list.style.display = 'none';
                empty.style.display = 'block';
                return;
            }

            empty.style.display = 'none';
            list.style.display = 'block';

            list.innerHTML = reqs.map(r => `
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition" id="req-${r.id}">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-teal-600 rounded-full flex items-center justify-center shadow">
                            <span class="text-white font-bold text-sm">${r.user.first_name.charAt(0)}${r.user.last_name.charAt(0)}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">${r.user.first_name} ${r.user.last_name}</p>
                            <p class="text-xs text-gray-500 truncate">${r.user.email}</p>
                            <p class="text-xs text-gray-400 mt-1"><i class="far fa-clock mr-1"></i>${timeAgo(r.created_at)}</p>
                            <div class="flex space-x-2 mt-3">
                                <button onclick="accept(${r.id})" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold py-2 rounded-lg transition">
                                    <i class="fas fa-check mr-1"></i>Accept
                                </button>
                                <button onclick="reject(${r.id})" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold py-2 rounded-lg transition">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function accept(id) {
            const el = document.getElementById(`req-${id}`);
            try {
                el.style.opacity = '0.5';
                el.style.pointerEvents = 'none';
                const res = await fetch(`/api/connections/${id}/accept`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error();
                el.style.transform = 'translateX(100%)';
                el.style.transition = 'all 0.3s';
                setTimeout(() => {
                    el.remove();
                    loadRequests();
                }, 300);
                toast('Request accepted! 🎉', 'success');
            } catch {
                el.style.opacity = '1';
                el.style.pointerEvents = 'auto';
                toast('Failed to accept', 'error');
            }
        }

        async function reject(id) {
            const el = document.getElementById(`req-${id}`);
            try {
                el.style.opacity = '0.5';
                el.style.pointerEvents = 'none';
                const res = await fetch(`/api/connections/${id}/reject`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    credentials: 'same-origin'
                });
                if (!res.ok) throw new Error();
                el.style.transform = 'translateX(-100%)';
                el.style.transition = 'all 0.3s';
                setTimeout(() => {
                    el.remove();
                    loadRequests();
                }, 300);
                toast('Request rejected', 'info');
            } catch {
                el.style.opacity = '1';
                el.style.pointerEvents = 'auto';
                toast('Failed to reject', 'error');
            }
        }

        function timeAgo(d) {
            const diff = new Date() - new Date(d);
            const m = Math.floor(diff / 60000);
            const h = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);
            if (m < 1) return 'Just now';
            if (m < 60) return `${m}m ago`;
            if (h < 24) return `${h}h ago`;
            if (days < 7) return `${days}d ago`;
            return new Date(d).toLocaleDateString();
        }

        function incrementBadge() {
            const badge = document.getElementById('notificationBadge');
            const current = parseInt(badge.textContent) || 0;
            badge.textContent = current + 1;
            badge.style.display = 'flex';
        }

        function prependRequest(data) {
            const list  = document.getElementById('requestsList');
            const empty = document.getElementById('emptyState');
            const count = document.getElementById('requestCountText');

            empty.style.display = 'none';
            list.style.display  = 'block';

            const n = list.children.length + 1;
            count.textContent = `${n} pending request${n > 1 ? 's' : ''}`;

            const item = document.createElement('div');
            item.id        = `req-${data.connection_id}`;
            item.className = 'p-4 border-b border-gray-100 hover:bg-gray-50 transition';
            item.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-teal-600 rounded-full flex items-center justify-center shadow">
                        <span class="text-white font-bold text-sm">${data.sender.first_name.charAt(0)}${data.sender.last_name.charAt(0)}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate">${data.sender.first_name} ${data.sender.last_name}</p>
                        <p class="text-xs text-gray-500 truncate">${data.sender.email}</p>
                        <p class="text-xs text-gray-400 mt-1"><i class="far fa-clock mr-1"></i>Just now</p>
                        <div class="flex space-x-2 mt-3">
                            <button onclick="accept(${data.connection_id})" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold py-2 rounded-lg transition">
                                <i class="fas fa-check mr-1"></i>Accept
                            </button>
                            <button onclick="reject(${data.connection_id})" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold py-2 rounded-lg transition">
                                <i class="fas fa-times mr-1"></i>Reject
                            </button>
                        </div>
                    </div>
                </div>`;
            list.prepend(item);
        }

        function toast(msg, type = 'success') {
            const c = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500'
            };
            const i = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle'
            };
            const t = document.createElement('div');
            t.className = `${c[type]} text-white px-6 py-3 rounded-lg shadow-2xl flex items-center space-x-3 transform transition-all`;
            t.style.transform = 'translateX(400px)';
            t.innerHTML = `<i class="fas ${i[type]}"></i><span class="font-medium">${msg}</span>`;
            document.getElementById('toastContainer').appendChild(t);
            setTimeout(() => t.style.transform = 'translateX(0)', 10);
            setTimeout(() => t.style.transform = 'translateX(400px)', 3000);
            setTimeout(() => t.remove(), 3300);
        }

        window.addEventListener('DOMContentLoaded', () => {
            fetch('/api/connections?type=received&status=pending', {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(d => {
                    const n = (d.data || []).length;
                    if (n > 0) {
                        document.getElementById('notificationBadge').textContent = n;
                        document.getElementById('notificationBadge').style.display = 'flex';
                    }
                })
                .catch(console.error);

            setInterval(() => {
                if (notifLoaded) loadRequests();
            }, 30000);
        });
    </script>

    @if(config('firebase.api_key'))
    <script>
        (function () {
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
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ token, platform: 'web' }),
                        });
                    }
                } catch (e) {
                    console.warn('FCM init:', e.message);
                }
            }

            messaging.onMessage((payload) => {
                const d = payload.data || {};
                incrementBadge();
                if (notifLoaded) {
                    prependRequest({
                        connection_id: d.connection_id,
                        sender: {
                            first_name: d.sender_first_name,
                            last_name:  d.sender_last_name,
                            email:      d.sender_email,
                        },
                    });
                }
                toast(`${d.sender_first_name} ${d.sender_last_name} vous a envoyé une demande de connexion`, 'success');
            });

            initFcm();
        })();
    </script>
    @endif

    @stack('scripts')
</body>

</html>