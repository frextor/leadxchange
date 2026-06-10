<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * { font-family: 'Inter', -apple-system, sans-serif; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 4px; }

        .nav-item {
            display: flex; align-items: center; gap: 9px;
            padding: 7px 10px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            color: #6B7280; transition: all .15s;
            text-decoration: none; white-space: nowrap;
        }
        .nav-item svg { opacity: .7; flex-shrink: 0; transition: opacity .15s; }
        .nav-item:hover { background: rgba(255,255,255,.06); color: #E5E7EB; }
        .nav-item:hover svg { opacity: 1; }
        .nav-item.active { background: rgba(45,212,191,.1); color: #2DD4BF; font-weight: 600; }
        .nav-item.active svg { opacity: 1; }

        .nav-item.sa { color: #6B7280; }
        .nav-item.sa:hover { background: rgba(167,139,250,.08); color: #C4B5FD; }
        .nav-item.sa.active { background: rgba(167,139,250,.12); color: #C4B5FD; font-weight: 600; }

        .nav-section {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; color: #374151;
            padding: 4px 10px; margin-top: 18px; margin-bottom: 2px;
        }
        .nav-badge {
            margin-left: auto; font-size: 9px; font-weight: 700;
            padding: 2px 5px; border-radius: 99px; line-height: 1;
            flex-shrink: 0;
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased" style="background:#F1F5F9;">

<div class="flex h-screen overflow-hidden">

    {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════ --}}
    <aside class="w-56 flex-shrink-0 flex flex-col" style="background:#0F172A;">

        {{-- Logo --}}
        <div class="px-4 pt-5 pb-4" style="border-bottom:1px solid rgba(255,255,255,.05);">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white font-bold text-[11px] flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">LX</div>
                <div>
                    <p class="text-sm font-bold text-white leading-none tracking-tight">LeadXchange</p>
                    <p class="text-[9px] font-semibold tracking-widest uppercase mt-0.5" style="color:#374151;">Console Admin</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-2.5 py-3 overflow-y-auto">

            <p class="nav-section">Plateforme</p>

            @if(auth()->user()->role === 'super_admin')
            <a href="{{ route('admin.super.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.super.dashboard') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Tableau de bord
            </a>
            @else
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Tableau de bord
            </a>
            @endif

            <a href="{{ route('admin.users.index') }}"
               class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Utilisateurs
                @php $unv = \App\Models\User::where('role','user')->whereNull('email_verified_at')->count(); @endphp
                @if($unv > 0)<span class="nav-badge text-white" style="background:#F59E0B;">{{ $unv }}</span>@endif
            </a>

            <a href="{{ route('admin.leads.index') }}"
               class="nav-item {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Leads
                @php $fraud = \App\Models\Lead::where('fraud_reported',true)->count(); @endphp
                @if($fraud > 0)<span class="nav-badge text-white" style="background:#EF4444;">{{ $fraud }}</span>@endif
            </a>

            <a href="{{ route('admin.events.index') }}"
               class="nav-item {{ request()->routeIs('admin.events*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Événements
            </a>

            <a href="{{ route('admin.groups.index') }}"
               class="nav-item {{ request()->routeIs('admin.groups*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0-3-3.85"/></svg>
                Groupes
            </a>

            <a href="{{ route('admin.videos.index') }}"
               class="nav-item {{ request()->routeIs('admin.videos*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                Vidéos
                @php $pv = \App\Models\Profile::whereNotNull('presentation_video')->where('presentation_video_status','pending')->count(); @endphp
                @if($pv > 0)<span class="nav-badge text-white" style="background:#EF4444;">{{ $pv }}</span>@endif
            </a>

            @if(auth()->user()->role === 'super_admin')
            <p class="nav-section" style="color:#4C1D95; letter-spacing:.05em;">Super Admin</p>

            <a href="{{ route('admin.super.subscribers.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.subscribers*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                Abonnés
            </a>

            <a href="{{ route('admin.super.ambassadors.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.ambassadors*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Ambassadeurs
                @php $pa = \App\Models\User::where('ambassador_status','pending')->count(); @endphp
                @if($pa > 0)<span class="nav-badge text-white" style="background:#7C3AED;">{{ $pa }}</span>@endif
            </a>

            <a href="{{ route('admin.super.admins.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.admins*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Admins
                @php $adminCount = \App\Models\User::where('role','admin')->count(); @endphp
                @if($adminCount > 0)<span class="nav-badge text-white" style="background:#6366F1;">{{ $adminCount }}</span>@endif
            </a>

            <a href="{{ route('admin.super.plans.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.plans*') && !request()->routeIs('admin.super.plans.permissions') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                Plans
            </a>

            <a href="{{ route('admin.super.plans.permissions') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.plans.permissions') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
                Permissions plans
            </a>

            <a href="{{ route('admin.super.cities.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.cities*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/></svg>
                Régions
            </a>

            <p class="nav-section">Configuration</p>

            <a href="{{ route('admin.super.sectors.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.sectors*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Secteurs
            </a>

            <a href="{{ route('admin.super.countries.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.countries*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                Pays
            </a>

            <a href="{{ route('admin.super.interests.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.interests*') ? 'active' : '' }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Intérêts
            </a>
            @endif
        </nav>

        {{-- User footer --}}
        <div class="px-3 py-4" style="border-top:1px solid rgba(255,255,255,.05);">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#0D9488);">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-white truncate leading-none">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                    <p class="text-[10px] font-medium mt-0.5" style="color:#2DD4BF;">
                        {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion"
                            class="w-7 h-7 rounded-lg flex items-center justify-center transition hover:bg-red-500/10"
                            style="color:#4B5563;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ══ MAIN ══════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white border-b border-gray-200 px-6 py-0 flex items-center justify-between flex-shrink-0" style="height:48px;">
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="opacity-50"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <span>Admin</span>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="opacity-30"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-gray-800 font-semibold">@yield('page-title', 'Dashboard')</span>
            </div>

            <div class="flex items-center gap-3">
                @if(session('success'))
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-50 text-teal-700 border border-teal-100">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 text-red-600 border border-red-100">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ session('error') }}
                </div>
                @endif
                <div class="flex items-center gap-1.5 text-xs text-gray-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
                    En ligne
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-7 pb-10 pt-6" style="background:#F1F5F9;">
            @yield('content')
        </main>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function swalDelete(btn, name) {
    Swal.fire({
        title: 'Supprimer ?',
        html: '<span style="color:#6B7280;font-size:14px;">Voulez-vous supprimer <strong style="color:#111827;">' + name + '</strong> ?<br>Cette action est <strong>irréversible</strong>.</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#F1F5F9',
        customClass: {
            popup:         'swal-admin-popup',
            title:         'swal-admin-title',
            cancelButton:  'swal-admin-cancel',
        },
        reverseButtons: true,
        focusCancel: true,
    }).then(function(r) {
        if (r.isConfirmed) btn.closest('form').submit();
    });
}
</script>
<style>
.swal-admin-popup  { border-radius: 20px !important; padding: 2rem !important; font-family: 'Inter', sans-serif !important; box-shadow: 0 25px 60px rgba(0,0,0,.15) !important; }
.swal-admin-title  { font-size: 18px !important; font-weight: 700 !important; color: #111827 !important; }
.swal-admin-cancel { color: #6B7280 !important; font-weight: 600 !important; border: 1px solid #E5E7EB !important; }
.swal-admin-cancel:hover { background: #F9FAFB !important; }
.swal2-icon.swal2-warning { border-color: #FCD34D !important; color: #F59E0B !important; }
</style>
@stack('scripts')
</body>
</html>
