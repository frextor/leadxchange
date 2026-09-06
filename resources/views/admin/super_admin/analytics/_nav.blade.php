<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#7C3AED,#6D28D9);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            Analytics
        </h1>
        <p class="text-xs text-slate-400 mt-0.5 ml-10">Tableau de bord analytique Super Admin — {{ now()->locale('fr')->isoFormat('D MMM YYYY') }}</p>
    </div>
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block" style="box-shadow:0 0 0 3px #D1FAE5;"></span>
        Données en direct
    </div>
</div>

<nav class="bg-white border border-gray-100 rounded-2xl shadow-sm mb-6 px-2 py-1.5">
    <div class="flex items-center gap-0.5 overflow-x-auto" style="scrollbar-width:none;">
        @php
        $tabs = [
            ['route' => 'admin.super.analytics.overview',      'label' => 'Vue d\'ensemble', 'icon' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
            ['route' => 'admin.super.analytics.users',         'label' => 'Utilisateurs',    'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
            ['route' => 'admin.super.analytics.leads',         'label' => 'Leads',           'icon' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'],
            ['route' => 'admin.super.analytics.connections',   'label' => 'Connexions',      'icon' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>'],
            ['route' => 'admin.super.analytics.events',        'label' => 'Événements',      'icon' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
            ['route' => 'admin.super.analytics.subscriptions', 'label' => 'Abonnements',     'icon' => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>'],
            ['route' => 'admin.super.analytics.ambassadors',   'label' => 'Ambassadeurs',    'icon' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>'],
            ['route' => 'admin.super.analytics.enterprise',    'label' => 'Entreprise',      'icon' => '<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>'],
            ['route' => 'admin.super.analytics.regional',      'label' => 'Régions',         'icon' => '<circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.4 7.05 11.5 7.35 11.76a1 1 0 0 0 1.3 0C12.95 21.5 20 15.4 20 10a8 8 0 0 0-8-8z"/>'],
            ['route' => 'admin.super.analytics.system',        'label' => 'Système',         'icon' => '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>'],
        ];
        @endphp
        @foreach($tabs as $tab)
        @php $active = request()->routeIs($tab['route']); @endphp
        <a href="{{ route($tab['route']) }}"
           class="flex-shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl text-[13px] font-medium transition-all whitespace-nowrap
                  {{ $active ? 'text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}"
           style="{{ $active ? 'background:linear-gradient(135deg,#7C3AED,#6D28D9);' : '' }}">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                {!! $tab['icon'] !!}
            </svg>
            {{ $tab['label'] }}
        </a>
        @endforeach
    </div>
</nav>
