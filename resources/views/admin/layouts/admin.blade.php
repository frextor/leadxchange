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

        /* Platform active */
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

        /* Super admin active */
        .nav-item.sa.active {
            background: linear-gradient(135deg, #F5F3FF, #EDE9FE);
            color: #6D28D9;
        }
        .nav-item.sa.active svg { color: #7C3AED; }
        .nav-item.sa.active::before { background: #8B5CF6; }
        .nav-item.sa:hover { color: #4C1D95; }
        .nav-item.sa:hover svg { color: #7C3AED; }

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

        /* Topbar */
        .topbar-breadcrumb { font-size: 13px; color: #94A3B8; }
        .topbar-breadcrumb .current { color: #1E293B; font-weight: 600; }

        /* Content area */
        .main-content { background: #F8FAFC; }
    </style>
    @stack('styles')
</head>
<body class="antialiased" style="background:#F8FAFC;">

<div class="flex h-screen overflow-hidden">

    {{-- ══ SIDEBAR ══════════════════════════════════════════════════════════════ --}}
    <aside class="w-60 flex-shrink-0 flex flex-col bg-white" style="border-right:1px solid #E2E8F0;">

        {{-- Logo --}}
        <div class="px-5 pt-5 pb-4" style="border-bottom:1px solid #F1F5F9;">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm"
                     style="background:linear-gradient(135deg,#14B8A6,#0D9488);">LX</div>
                <div>
                    <p class="text-sm font-bold text-slate-800 leading-none">LeadXchange</p>
                    <p class="text-[10px] font-semibold tracking-widest uppercase mt-0.5 text-slate-400">
                        {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Administration' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-3 overflow-y-auto space-y-0.5">

            {{-- ── PLATEFORME ── --}}
            <p class="nav-section">Plateforme</p>

            @if(auth()->user()->role === 'super_admin')
            <a href="{{ route('admin.super.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.super.dashboard') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                Tableau de bord
            </a>
            @else
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                Tableau de bord
            </a>
            @endif

            <a href="{{ route('admin.users.index') }}"
               class="nav-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Utilisateurs
                @php $unv = \App\Models\User::where('role','user')->whereNull('email_verified_at')->count(); @endphp
                @if($unv > 0)<span class="nav-badge text-white" style="background:#F59E0B;">{{ $unv }}</span>@endif
            </a>

            <a href="{{ route('admin.leads.index') }}"
               class="nav-item {{ request()->routeIs('admin.leads*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Leads
                @php $fraud = \App\Models\Lead::where('fraud_reported',true)->count(); @endphp
                @if($fraud > 0)<span class="nav-badge text-white" style="background:#EF4444;">{{ $fraud }}</span>@endif
            </a>

            <a href="{{ route('admin.notation.index') }}"
               class="nav-item {{ request()->routeIs('admin.notation*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Notation & Badges
            </a>

            <a href="{{ route('admin.events.index') }}"
               class="nav-item {{ request()->routeIs('admin.events*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Événements
            </a>

            <a href="{{ route('admin.groups.index') }}"
               class="nav-item {{ request()->routeIs('admin.groups*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/></svg>
                Groupes / Pôles
            </a>

            <a href="{{ route('admin.videos.index') }}"
               class="nav-item {{ request()->routeIs('admin.videos*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>
                Vidéos
                @php $pv = \App\Models\Profile::whereNotNull('presentation_video')->where('presentation_video_status','pending')->count(); @endphp
                @if($pv > 0)<span class="nav-badge text-white" style="background:#EF4444;">{{ $pv }}</span>@endif
            </a>

            {{-- ── SUPER ADMIN ── --}}
            @if(auth()->user()->role === 'super_admin')

            <p class="nav-section" style="color:#8B5CF6;">Super Admin</p>

            <a href="{{ route('admin.super.subscribers.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.subscribers*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                Abonnés
            </a>

            <a href="{{ route('admin.super.reports.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.reports*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Signalements
                @php $pendingRep = \App\Models\UserReport::where('status','pending')->count(); @endphp
                @if($pendingRep > 0)<span class="nav-badge text-white" style="background:#DC2626;">{{ $pendingRep }}</span>@endif
            </a>

            <a href="{{ route('admin.super.ambassadors.manage') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.ambassadors*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Ambassadeurs
            </a>

            <a href="{{ route('admin.super.consul.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.consul*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Consuls
                @php $pc = \App\Models\ConsulRequest::where('status','pending')->count(); @endphp
                @if($pc > 0)<span class="nav-badge text-white" style="background:#7C3AED;">{{ $pc }}</span>@endif
            </a>

            <a href="{{ route('admin.super.admins.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.admins*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M6 20v-2a6 6 0 0 1 12 0v2"/></svg>
                Admins
                @php $adminCount = \App\Models\User::where('role','admin')->count(); @endphp
                @if($adminCount > 0)<span class="nav-badge text-white" style="background:#6366F1;">{{ $adminCount }}</span>@endif
            </a>

            {{-- Plans sub-group --}}
            <div class="mt-1 mb-1 mx-1 rounded-xl overflow-hidden" style="background:#FAFAFF; border:1px solid #EDE9FE;">
                <p class="text-[9px] font-bold uppercase tracking-widest text-violet-400 px-3 pt-2.5 pb-1">Plans & Permissions</p>
                <div class="px-1 pb-2 space-y-0.5">
                    <a href="{{ route('admin.super.plans.index') }}"
                       class="nav-item sa {{ request()->routeIs('admin.super.plans.index') || (request()->routeIs('admin.super.plans*') && !request()->routeIs('admin.super.plans.permissions') && !request()->routeIs('admin.super.plans.permission-labels') && !request()->routeIs('admin.super.plans.stripe*')) ? 'active' : '' }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                        Plans
                    </a>
                    <a href="{{ route('admin.super.plans.permissions') }}"
                       class="nav-item sa {{ request()->routeIs('admin.super.plans.permissions') && !request()->routeIs('admin.super.plans.permission-labels') ? 'active' : '' }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
                        Permissions
                    </a>
                    <a href="{{ route('admin.super.plans.permission-labels') }}"
                       class="nav-item sa {{ request()->routeIs('admin.super.plans.permission-labels') ? 'active' : '' }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Libellés
                    </a>
                    <a href="{{ route('admin.super.plans.stripe') }}"
                       class="nav-item sa {{ request()->routeIs('admin.super.plans.stripe*') ? 'active' : '' }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Stripe
                    </a>
                    <a href="{{ route('admin.super.enterprise.index') }}"
                       class="nav-item sa {{ request()->routeIs('admin.super.enterprise*') ? 'active' : '' }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
                        Licences
                    </a>
                </div>
            </div>

            <a href="{{ route('admin.super.cities.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.cities*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/></svg>
                Régions / Villes
            </a>

            {{-- Configuration sub-group --}}
            <p class="nav-section" style="color:#94A3B8;">Configuration</p>

            <a href="{{ route('admin.super.sectors.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.sectors*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Secteurs
            </a>

            <a href="{{ route('admin.super.countries.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.countries*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                Pays
            </a>

            <a href="{{ route('admin.super.interests.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.interests*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Intérêts
            </a>

            <a href="{{ route('admin.super.settings.currency') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.settings.currency*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Paramètres
            </a>

            <a href="{{ route('admin.super.settings.maintenance') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.settings.maintenance*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                Maintenance
                @php $maintenanceOn = \App\Models\SystemSetting::get('maintenance_banner_enabled', false); @endphp
                @if($maintenanceOn)<span class="nav-badge text-white" style="background:#D97706;">ON</span>@endif
            </a>

            <a href="{{ route('admin.super.email-templates.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.email-templates*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Templates email
            </a>

            <a href="{{ route('admin.super.smtp.index') }}"
               class="nav-item sa {{ request()->routeIs('admin.super.smtp*') ? 'active' : '' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                Email / SMTP
            </a>

            @endif
        </nav>

        {{-- ── User footer ── --}}
        <div class="px-3 py-3" style="border-top:1px solid #F1F5F9;">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-xl hover:bg-slate-50 transition group">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 shadow-sm"
                     style="background:linear-gradient(135deg,#14B8A6,#0D9488);">
                    {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-semibold text-slate-700 truncate leading-none">
                        {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                    </p>
                    <p class="text-[11px] font-medium mt-0.5 truncate"
                       style="color:{{ auth()->user()->role === 'super_admin' ? '#8B5CF6' : '#14B8A6' }};">
                        {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Administrateur' }}
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
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
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white flex items-center justify-between flex-shrink-0 px-6" style="height:52px; border-bottom:1px solid #E2E8F0;">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-1.5 text-sm text-slate-400">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-slate-300"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <span>Admin</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-slate-200"><path d="m9 18 6-6-6-6"/></svg>
                <span class="text-slate-700 font-semibold">@yield('page-title', 'Dashboard')</span>
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
                <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block" style="box-shadow:0 0 0 2px #D1FAE5;"></span>
                    En ligne
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto px-7 pb-10 pt-6 main-content">
            @yield('content')
        </main>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
