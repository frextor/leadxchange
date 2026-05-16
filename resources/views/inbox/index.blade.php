@extends('layouts.dashboard')

@section('title', 'Inbox — LeadXchange')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;1,400;1,500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/inbox.css') }}">
@endpush

@section('content')

{{-- Data for Alpine (must be in <script>, NOT in x-data="" attrs to avoid JSON_HEX_QUOT issues) --}}
<script>
    window.__inboxData        = {!! json_encode($itemsData,   JSON_HEX_TAG|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) !!};
    window.__inboxConnections = {!! json_encode($connections, JSON_HEX_TAG|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) !!};
    window.__inboxLeads       = {!! json_encode($leads,       JSON_HEX_TAG|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE) !!};
</script>

<div
    class="ix-page"
    x-data="inboxApp()"
    @keydown.window="handleKey($event)"
>

    {{-- ════════════ PAGE HEADER ════════════ --}}
    <header class="ix-pagehead">
        <div class="ix-pagehead-left">
            <h1 class="ix-pagetitle">Inbox</h1>
            <p class="ix-pagesubtitle">
                <span x-text="unreadCount" style="font-family:var(--font-mono);"></span>
                <span x-text="unreadCount === 1 ? ' non lu' : ' non lus'"></span>
                &nbsp;sur&nbsp;
                <span x-text="items.filter(i=>!i.archived).length" style="font-family:var(--font-mono);"></span>
            </p>
        </div>
        <div class="ix-pagehead-right">
            {{-- Search --}}
            <div class="ix-search-wrap">
                <span class="ix-search-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </span>
                <input
                    x-ref="searchInput"
                    class="ix-search-input"
                    type="text"
                    placeholder="Rechercher…"
                    x-model="search"
                    @keydown.stop
                    aria-label="Rechercher dans l'inbox"
                >
                <span class="ix-search-kbd">⌘K</span>
            </div>

            {{-- Compose button --}}
            <button class="ix-compose-btn" @click="openDrawer()" type="button">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nouveau message
            </button>
        </div>
    </header>

    {{-- ════════════ 3-COLUMN SHELL ════════════ --}}
    <div class="ix-shell">

        {{-- Column 1: Filters --}}
        <x-inbox.filters />

        {{-- Column 2: Item list --}}
        <div class="ix-list-col" role="list" aria-label="Messages">
            <div class="ix-list-scroll">

                {{-- Empty state --}}
                <div class="ix-list-empty" x-show="filteredItems.length === 0" x-cloak>
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.35">
                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                        <polyline points="2,4 12,13 22,4"/>
                    </svg>
                    <span>Aucun élément</span>
                </div>

                {{-- Grouped items --}}
                <template x-for="group in groupedItems" :key="group.key">
                    <div>
                        {{-- Group header --}}
                        <div class="ix-group-hd" role="rowgroup">
                            <span class="ix-group-label" x-text="group.label"></span>
                            <span class="ix-group-count" x-text="group.items.length"></span>
                        </div>

                        {{-- Rows --}}
                        <template x-for="item in group.items" :key="item.id">
                            <button
                                class="ix-row"
                                :class="{
                                    'ix-selected': selectedId === item.id,
                                    'ix-unread':   !item.read,
                                }"
                                @click="select(item.id)"
                                :aria-selected="selectedId === item.id"
                                role="option"
                                type="button"
                            >
                                {{-- Dot --}}
                                <span class="ix-dot" aria-hidden="true"></span>

                                {{-- Kind icon --}}
                                <span
                                    class="ix-icon"
                                    :class="'ix-icon-' + item.kind"
                                    x-html="iconMap[item.kind] || ''"
                                    aria-hidden="true"
                                ></span>

                                {{-- Body --}}
                                <div class="ix-row-body">
                                    <div class="ix-row-line1">
                                        <span class="ix-row-title" x-text="item.title"></span>
                                    </div>
                                    <div class="ix-row-preview" x-text="item.actor_name ? item.actor_name + (item.preview ? ' · ' + item.preview : '') : (item.preview || '')"></div>
                                </div>

                                {{-- Right: ref + time --}}
                                <div class="ix-row-right">
                                    <span class="ix-ref-pill" x-show="item.lead_ref" x-text="item.lead_ref"></span>
                                    <span class="ix-row-time" x-text="formatTime(item.ts)"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </template>

            </div>
        </div>

        {{-- Column 3: Detail pane --}}
        <div
            class="ix-detail-col"
            aria-live="polite"
            aria-label="Détail du message"
        >

            {{-- Empty state --}}
            <x-inbox.empty />

            {{-- Detail view --}}
            <template x-if="selectedItem">
                <div style="display:contents">

                    {{-- Top bar: breadcrumb + actions --}}
                    <div class="ix-detail-topbar">
                        <div class="ix-breadcrumb">
                            <button class="ix-breadcrumb-back" @click="backToList()" type="button" aria-label="Retour à la liste">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                                Inbox
                            </button>
                            <span class="ix-breadcrumb-sep" aria-hidden="true">/</span>
                            <span class="ix-breadcrumb-id" x-text="selectedItem.ref_id"></span>
                            <span class="ix-breadcrumb-sep" aria-hidden="true">/</span>
                            <span
                                class="ix-tag"
                                :class="tagMap[selectedItem.kind]?.cls"
                                x-text="tagMap[selectedItem.kind]?.label"
                            ></span>
                        </div>
                        <div class="ix-topbar-actions">
                            <button
                                class="ix-topbar-btn"
                                :class="{ active: !selectedItem.read }"
                                @click="toggleRead(selectedItem.id)"
                                type="button"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <span x-text="selectedItem.read ? 'Marquer non lu' : 'Marquer lu'"></span>
                            </button>
                            <button
                                class="ix-topbar-btn"
                                @click="archive(selectedItem.id)"
                                type="button"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="21 8 21 21 3 21 3 8"/>
                                    <rect x="1" y="3" width="22" height="5"/>
                                    <line x1="10" y1="12" x2="14" y2="12"/>
                                </svg>
                                Archiver
                            </button>
                        </div>
                    </div>

                    {{-- Scrollable body --}}
                    <div class="ix-detail-scroll">

                        {{-- Title block --}}
                        <div class="ix-title-block">
                            <h2 class="ix-detail-h2" x-text="selectedItem.title"></h2>
                            <div class="ix-meta-row">
                                <span class="ix-avatar-sm" x-text="initials(selectedItem.actor_name || 'SY')"></span>
                                <strong class="ix-meta-actor" x-text="selectedItem.actor_name || 'Système LeadXchange'"></strong>
                                <em class="ix-meta-role" x-show="selectedItem.actor_title || selectedItem.actor_company"
                                    x-text="[selectedItem.actor_title, selectedItem.actor_company].filter(Boolean).join(' · ')">
                                </em>
                                <span class="ix-meta-ts" x-text="formatDateTime(selectedItem.ts)"></span>
                                <a
                                    class="ix-lead-pill"
                                    x-show="selectedItem.lead_ref"
                                    x-text="selectedItem.lead_ref"
                                    :href="selectedItem.lead_id ? '/leads/' + selectedItem.lead_id : '#'"
                                ></a>
                            </div>
                        </div>

                        {{-- Body text --}}
                        <div class="ix-body-text" x-html="renderBody(selectedItem.body)"></div>

                        {{-- Action panel for lead-received --}}
                        <x-inbox.action-panel />

                        {{-- Inline reply for messages --}}
                        <x-inbox.compose />

                    </div>
                </div>
            </template>

        </div>{{-- /detail-col --}}

    </div>{{-- /ix-shell --}}

    {{-- ════════════ COMPOSE DRAWER ════════════ --}}
    <x-compose.drawer />

    {{-- ════════════ TOASTER ════════════ --}}
    <div class="ix-toaster" role="status" aria-live="assertive" aria-atomic="false">
        <template x-for="t in toasts" :key="t.id">
            <div
                class="ix-toast"
                :class="'ix-toast-' + t.type"
                x-text="t.msg"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            ></div>
        </template>
    </div>

</div>{{-- /ix-page --}}

@endsection

@push('scripts')
{{-- inbox.js must be non-defer (defines inboxApp before Alpine initializes) --}}
<script src="{{ asset('js/inbox.js') }}"></script>
{{-- @alpinejs/focus plugin (defer, before Alpine) --}}
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
{{-- Alpine itself --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
