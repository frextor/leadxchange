<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Espace Entreprise') — LeadXchange</title>
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
        .nav-item:hover { background: #F1F5F9; color: #1E293B; }
        .nav-item:hover svg { color: #475569; }

        .nav-item.active {
            background: linear-gradient(135deg, #EEF2FF, #E0E7FF);
            color: #4338CA;
            font-weight: 600;
        }
        .nav-item.active svg { color: #4338CA; }
        .nav-item.active::before {
            content: '';
            position: absolute; left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%; border-radius: 0 3px 3px 0;
            background: #6366F1;
        }

        .nav-section {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: #94A3B8;
            padding: 3px 12px; margin-top: 20px; margin-bottom: 4px;
        }

        .nav-badge {
            margin-left: auto; font-size: 10px; font-weight: 700;
            padding: 2px 6px; border-radius: 99px; line-height: 1.4;
            flex-shrink: 0;
        }

        .main-content { background: #F8FAFC; }

        @media (max-width: 768px) {
            #ent-sidebar { transform: translateX(-100%); }
            #ent-sidebar.open { transform: translateX(0); }
            #ent-content { margin-left: 0 !important; }
        }

        @keyframes fadeIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
        .fade-in { animation: fadeIn .25s ease forwards; }
    </style>
    @stack('styles')
</head>
<body class="antialiased" style="background:#F8FAFC;">

<div class="flex h-screen overflow-hidden">

    {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════════ --}}
    <aside id="ent-sidebar" class="w-60 flex-shrink-0 flex flex-col bg-white"
           style="border-right:1px solid #E2E8F0; position:fixed; top:0; left:0; bottom:0; z-index:40; transition:transform .25s ease;">

        {{-- Logo --}}
        <div class="px-5 pt-5 pb-4" style="border-bottom:1px solid #F1F5F9;">
            <a href="{{ route('enterprise.dashboard') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm"
                     style="background:linear-gradient(135deg,#6366F1,#4338CA);">LX</div>
                <div>
                    <p class="text-sm font-bold text-slate-800 leading-none">LeadXchange</p>
                    <p class="text-[10px] font-semibold tracking-widest uppercase mt-0.5 text-indigo-500">Entreprise</p>
                </div>
            </a>
        </div>

        {{-- Company card --}}
        @php
            $entHolder    = auth()->user();
            $sidebarLicense = $license ?? $entHolder->enterpriseLicense()->first();
        @endphp
        <div class="mx-3 mt-3 mb-1 px-3 py-2.5 rounded-xl" style="background:linear-gradient(135deg,#EEF2FF,#E0E7FF); border:1px solid #C7D2FE;">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-xs flex-shrink-0 shadow-sm"
                     style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[12px] font-semibold text-slate-800 truncate leading-none">{{ $sidebarLicense?->company_name ?? 'Mon entreprise' }}</p>
                    <p class="text-[10px] font-semibold text-indigo-600 mt-0.5 flex items-center gap-1">
                        <span>💼</span> Titulaire du pack
                    </p>
                </div>
            </div>
            @if($sidebarLicense)
            <p class="text-[11px] mt-1.5 flex items-center gap-1 text-indigo-700">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                {{ $sidebarLicense->seats_used }}/{{ $sidebarLicense->seats_total }} licences utilisées
            </p>
            @endif
        </div>

        {{-- Navigation --}}
        <nav id="ent-sidebar-nav" class="flex-1 px-3 py-2 overflow-y-auto space-y-0.5">

            <p class="nav-section">Tableau de bord</p>

            <a href="{{ route('enterprise.dashboard') }}"
               class="nav-item {{ request()->routeIs('enterprise.dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                Tableau de bord
            </a>

            <p class="nav-section">Mon équipe</p>

            <a href="{{ route('enterprise.team') }}"
               class="nav-item {{ request()->routeIs('enterprise.team') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Membres &amp; licences
            </a>

            <p class="nav-section">Compte</p>

            <a href="{{ route('dashboard') }}" class="nav-item" style="margin-top:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Espace Membre
            </a>

        </nav>

        {{-- User footer --}}
        <div class="px-3 py-3" style="border-top:1px solid #F1F5F9;">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-xl hover:bg-slate-50 transition group">
                @if($entHolder->profile?->avatar)
                    <img src="{{ $entHolder->profile->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm"
                         style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        {{ strtoupper(substr($entHolder->first_name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-slate-700 truncate leading-none">
                        {{ $entHolder->first_name }} {{ $entHolder->last_name }}
                    </p>
                    <p class="text-[11px] font-medium text-indigo-500 mt-0.5">Titulaire Entreprise</p>
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
    <div id="ent-content" class="flex-1 flex flex-col overflow-hidden" style="margin-left:240px;">

        <header class="bg-white flex items-center justify-between flex-shrink-0 px-6" style="height:52px; border-bottom:1px solid #E2E8F0;">

            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-slate-600">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="flex items-center gap-1.5 text-sm text-slate-400">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-300"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
                    <span>Entreprise</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-200"><path d="m9 18 6-6-6-6"/></svg>
                    <span class="text-slate-700 font-semibold">@yield('page-title', 'Dashboard')</span>
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
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ session('error') }}
                </div>
                @endif
                @if(session('info'))
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                    {{ session('info') }}
                </div>
                @endif

                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 hover:opacity-80 transition">
                    @if($entHolder->profile?->avatar)
                        <img src="{{ $entHolder->profile->avatar_url }}" class="w-7 h-7 rounded-full object-cover ring-2 ring-indigo-200">
                    @else
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white font-bold text-[10px] flex-shrink-0 shadow-sm"
                             style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                            {{ strtoupper(substr($entHolder->first_name,0,1)) }}
                        </div>
                    @endif
                    <span class="text-[13px] font-semibold text-slate-700 hidden sm:block">{{ $entHolder->first_name }}</span>
                </a>
            </div>
        </header>

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
    var nav = document.getElementById('ent-sidebar-nav');
    if (!nav) return;
    var key = 'entSidebarScroll';
    var saved = sessionStorage.getItem(key);
    if (saved) nav.scrollTop = parseInt(saved, 10);
    nav.addEventListener('scroll', function () {
        sessionStorage.setItem(key, nav.scrollTop);
    }, { passive: true });
})();

function toggleSidebar() {
    document.getElementById('ent-sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('hidden');
}
</script>
@stack('scripts')
</body>
</html>
