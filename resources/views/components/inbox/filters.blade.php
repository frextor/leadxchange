<aside class="ix-sidebar" aria-label="Filtres">

    {{-- All --}}
    <button type="button" class="ix-filter-btn" :class="{ active: filter === 'all' }" @click="filter = 'all'">
        <span class="ix-filter-left">
            <svg class="ix-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/>
            </svg>
            Tous
        </span>
        <span class="ix-filter-count" x-text="filterCounts.all || ''"></span>
    </button>

    {{-- Unread --}}
    <button type="button" class="ix-filter-btn" :class="{ active: filter === 'unread' }" @click="filter = 'unread'">
        <span class="ix-filter-left">
            <svg class="ix-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3" fill="currentColor"/><circle cx="12" cy="12" r="10"/>
            </svg>
            Non lus
        </span>
        <span class="ix-filter-count" x-show="filterCounts.unread > 0" x-text="filterCounts.unread"></span>
    </button>

    {{-- Mentions --}}
    <button type="button" class="ix-filter-btn" :class="{ active: filter === 'mentions' }" @click="filter = 'mentions'">
        <span class="ix-filter-left">
            <svg class="ix-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/>
            </svg>
            Mentions
        </span>
        <span class="ix-filter-count" x-show="filterCounts.mentions > 0" x-text="filterCounts.mentions"></span>
    </button>

    {{-- Leads --}}
    <button type="button" class="ix-filter-btn" :class="{ active: filter === 'leads' }" @click="filter = 'leads'">
        <span class="ix-filter-left">
            <svg class="ix-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                <polyline points="16 7 22 7 22 13"/>
            </svg>
            Leads
        </span>
        <span class="ix-filter-count" x-show="filterCounts.leads > 0" x-text="filterCounts.leads"></span>
    </button>

    {{-- Système --}}
    <button type="button" class="ix-filter-btn" :class="{ active: filter === 'system' }" @click="filter = 'system'">
        <span class="ix-filter-left">
            <svg class="ix-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Système
        </span>
        <span class="ix-filter-count" x-show="filterCounts.system > 0" x-text="filterCounts.system"></span>
    </button>

    {{-- Réseau --}}
    <button type="button" class="ix-filter-btn" :class="{ active: filter === 'network' }" @click="filter = 'network'">
        <span class="ix-filter-left">
            <svg class="ix-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
            Réseau
        </span>
        <span class="ix-filter-count" x-show="filterCounts.network > 0" x-text="filterCounts.network"></span>
    </button>

    {{-- Archives --}}
    <button type="button" class="ix-filter-btn" :class="{ active: filter === 'archived' }" @click="filter = 'archived'">
        <span class="ix-filter-left">
            <svg class="ix-filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="21 8 21 21 3 21 3 8"/>
                <rect x="1" y="3" width="22" height="5"/>
                <line x1="10" y1="12" x2="14" y2="12"/>
            </svg>
            Archives
        </span>
        <span class="ix-filter-count" x-show="filterCounts.archived > 0" x-text="filterCounts.archived"></span>
    </button>

    <div class="ix-sidebar-sep"></div>

    {{-- Keyboard shortcuts card --}}
    <div class="ix-shortcuts" aria-label="Raccourcis clavier">
        <p class="ix-shortcuts-title">Raccourcis</p>
        <div class="ix-shortcut-row">
            <span>Navigation</span>
            <span><kbd class="ix-kbd">j</kbd> <kbd class="ix-kbd">k</kbd></span>
        </div>
        <div class="ix-shortcut-row">
            <span>Archiver</span>
            <kbd class="ix-kbd">E</kbd>
        </div>
        <div class="ix-shortcut-row">
            <span>Lu / Non lu</span>
            <kbd class="ix-kbd">R</kbd>
        </div>
        <div class="ix-shortcut-row">
            <span>Recherche</span>
            <kbd class="ix-kbd">⌘K</kbd>
        </div>
    </div>

</aside>
