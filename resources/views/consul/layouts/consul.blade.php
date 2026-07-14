<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Consul') — LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { font-family: 'Inter', -apple-system, sans-serif; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 10px;
            font-size: 13.5px; font-weight: 500;
            color: #64748B; transition: all .12s;
            text-decoration: none; white-space: nowrap;
            position: relative;
        }
        .nav-item svg { flex-shrink: 0; color: #94A3B8; transition: color .12s; }
        .nav-item:hover { background: #F5F3FF; color: #1E293B; }
        .nav-item:hover svg { color: #6D28D9; }
        .nav-item.active {
            background: linear-gradient(135deg, #F5F3FF, #EDE9FE);
            color: #5B21B6; font-weight: 600;
        }
        .nav-item.active svg { color: #6D28D9; }
        .nav-item.active::before {
            content: '';
            position: absolute; left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%; border-radius: 0 3px 3px 0;
            background: #7C3AED;
        }
        .nav-section {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: #94A3B8;
            padding: 3px 12px; margin-top: 20px; margin-bottom: 4px;
        }
        .main-content { background: #F8FAFC; }
        @media (max-width: 768px) {
            #consul-sidebar { transform: translateX(-100%); }
            #consul-sidebar.open { transform: translateX(0); }
            #consul-content { margin-left: 0 !important; }
        }
        @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        .fade-in { animation: fadeIn .25s ease forwards; }
    </style>
    @stack('styles')
</head>
<body class="antialiased" style="background:#F8FAFC;">

<div class="flex h-screen overflow-hidden">

    {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════════ --}}
    <aside id="consul-sidebar" class="w-60 flex-shrink-0 flex flex-col bg-white"
           style="border-right:1px solid #E2E8F0; position:fixed; top:0; left:0; bottom:0; z-index:40; transition:transform .25s ease;">

        {{-- Logo --}}
        <div class="px-5 pt-5 pb-4" style="border-bottom:1px solid #F1F5F9;">
            <a href="{{ route('consul.dashboard') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm"
                     style="background:linear-gradient(135deg,#7C3AED,#5B21B6);">LX</div>
                <div>
                    <p class="text-sm font-bold text-slate-800 leading-none">LeadXchange</p>
                    <p class="text-[10px] font-semibold tracking-widest uppercase mt-0.5" style="color:#7C3AED;">Consul</p>
                </div>
            </a>
        </div>

        {{-- Consul card --}}
        @php $consul = auth()->user(); @endphp
        <div class="mx-3 mt-3 mb-1 px-3 py-2.5 rounded-xl" style="background:linear-gradient(135deg,#F5F3FF,#EDE9FE); border:1px solid #DDD6FE;">
            <div class="flex items-center gap-2.5">
                @if($consul->profile?->avatar)
                    <img src="{{ $consul->profile->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0 ring-2 ring-purple-200">
                @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm"
                         style="background:linear-gradient(135deg,#7C3AED,#5B21B6);">
                        {{ strtoupper(substr($consul->first_name,0,1)) }}{{ strtoupper(substr($consul->last_name,0,1)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-[12px] font-semibold text-slate-800 truncate leading-none">{{ $consul->first_name }} {{ $consul->last_name }}</p>
                    <p class="text-[10px] font-semibold mt-0.5 flex items-center gap-1" style="color:#6D28D9;">
                        <span>🏛</span> Consul
                    </p>
                </div>
            </div>
            @if($consul->city)
            <p class="text-[11px] mt-1.5 flex items-center gap-1" style="color:#5B21B6;">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $consul->city->name }}
            </p>
            @endif
        </div>

        {{-- Navigation --}}
        <nav id="consul-sidebar-nav" class="flex-1 px-3 py-2 overflow-y-auto space-y-0.5">

            <p class="nav-section">Tableau de bord</p>

            <a href="{{ route('consul.dashboard') }}"
               class="nav-item {{ request()->routeIs('consul.dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                Vue d'ensemble
            </a>

            <p class="nav-section">Mes Groupes</p>

            @foreach($__consulGroups ?? [] as $__g)
            <a href="{{ route('consul.group.members', $__g) }}"
               class="nav-item {{ request()->is('consul/groupes/'.$__g->id.'*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span class="truncate">{{ $__g->name }}</span>
                <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full flex-shrink-0" style="background:#EDE9FE;color:#6D28D9;">{{ $__g->members_count }}</span>
            </a>
            @endforeach

            <p class="nav-section">Compte</p>

            <a href="{{ route('dashboard') }}" class="nav-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Espace Membre
            </a>

        </nav>

        {{-- User footer --}}
        <div class="px-3 py-3" style="border-top:1px solid #F1F5F9;">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-xl hover:bg-slate-50 transition">
                @if($consul->profile?->avatar)
                    <img src="{{ $consul->profile->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm"
                         style="background:linear-gradient(135deg,#7C3AED,#5B21B6);">
                        {{ strtoupper(substr($consul->first_name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-slate-700 truncate leading-none">{{ $consul->first_name }} {{ $consul->last_name }}</p>
                    <p class="text-[11px] font-medium mt-0.5" style="color:#7C3AED;">Consul</p>
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
    <div id="consul-content" class="flex-1 flex flex-col overflow-hidden" style="margin-left:240px;">

        {{-- Topbar --}}
        <header class="bg-white flex items-center justify-between flex-shrink-0 px-6" style="height:52px; border-bottom:1px solid #E2E8F0;">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-slate-600">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="flex items-center gap-1.5 text-sm text-slate-400">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-300"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span>Consul</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-200"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="text-slate-700 font-semibold">@yield('page-title', 'Vue d\'ensemble')</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if(session('success'))
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 border border-red-100">
                    {{ session('error') }}
                </div>
                @endif
                <div class="flex items-center gap-2">
                    @if(auth()->user()->profile?->avatar)
                        <img src="{{ auth()->user()->profile->avatar_url }}" class="w-7 h-7 rounded-full object-cover ring-2 ring-purple-200">
                    @else
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0 shadow-sm"
                             style="background:linear-gradient(135deg,#7C3AED,#5B21B6);">
                            {{ strtoupper(substr(auth()->user()->first_name,0,1)) }}
                        </div>
                    @endif
                    <span class="text-[13px] font-semibold text-slate-700 hidden sm:block">{{ auth()->user()->first_name }}</span>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-7 pb-10 pt-6 main-content fade-in">
            @yield('content')
        </main>
    </div>

</div>

<div id="sidebarOverlay" onclick="toggleSidebar()"
     class="hidden fixed inset-0 bg-black bg-opacity-40 z-30 md:hidden"></div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.CSRF = '{{ csrf_token() }}';
(function () {
    var nav = document.getElementById('consul-sidebar-nav');
    if (!nav) return;
    var saved = sessionStorage.getItem('consulSidebarScroll');
    if (saved) nav.scrollTop = parseInt(saved, 10);
    nav.addEventListener('scroll', function () {
        sessionStorage.setItem('consulSidebarScroll', nav.scrollTop);
    }, { passive: true });
})();
function toggleSidebar() {
    document.getElementById('consul-sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('hidden');
}
</script>
@stack('scripts')
</body>
</html>
