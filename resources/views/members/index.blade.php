@extends('layouts.app')

@section('title', 'Members — LeadXchange')

@push('styles')
<style>
    .ms-input {
        height: 42px; padding: 0 14px; border-radius: 10px;
        border: 1px solid #E5E7EB; background: white;
        font-size: 14px; color: #111827; outline: none; width: 100%;
        font-family: inherit; transition: border-color .15s, box-shadow .15s;
    }
    .ms-input:focus { border-color: #1E8F88; box-shadow: 0 0 0 3px rgba(30,143,136,0.1); }
    .ms-input[type="number"] { -moz-appearance: textfield; }
    .ms-input::-webkit-outer-spin-button,
    .ms-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    select.ms-input { appearance: none; padding-right: 36px; cursor: pointer; }
    .ms-radio  { appearance: none; width: 18px; height: 18px; border-radius: 99px; border: 1.5px solid #D1D5DB; background: white; cursor: pointer; transition: all .12s; flex: none; }
    .ms-radio:checked { border: 5px solid #1E8F88; }
    .ms-checkbox { appearance: none; width: 18px; height: 18px; border-radius: 5px; border: 1.5px solid #D1D5DB; background: white; cursor: pointer; transition: all .12s; flex: none; }
    .ms-checkbox:checked { background: #1E8F88; border-color: #1E8F88;
        background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'><path d='m5 12 5 5L20 7'/></svg>");
        background-repeat: no-repeat; background-position: center; background-size: 12px;
    }
    .member-row:last-child { border-bottom: none !important; }
    .lx-skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200%; animation: skeleton 1.5s infinite; border-radius: 6px; }
    @keyframes skeleton { 0% { background-position: 200% } 100% { background-position: -200% } }
</style>
@endpush

@section('content')
<div class="max-w-[1100px] mx-auto px-6 lg:px-8 py-8">

    {{-- ── TABS BAR ── --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6 shadow-sm">
        <div class="grid grid-cols-3">
            @php
                $tabs = [
                    ['id' => 'recommendations', 'label' => 'Recommendations'],
                    ['id' => 'search',           'label' => 'Search'],
                    ['id' => 'visitors',         'label' => 'Profile visitors', 'badge' => $newVisitorCount],
                ];
            @endphp
            @foreach($tabs as $t)
            <button id="tab-{{ $t['id'] }}" onclick="switchTab('{{ $t['id'] }}')"
                class="tab-btn relative py-[18px] px-4 text-[13px] font-semibold uppercase tracking-[0.08em] inline-flex items-center justify-center gap-2 transition-colors"
                style="color: {{ $initialTab === $t['id'] ? '#1E8F88' : '#9CA3AF' }};">
                {{ $t['label'] }}
                @if(!empty($t['badge']) && $t['badge'] > 0)
                <span id="badge-visitors" class="px-2 py-0.5 rounded-full text-[11px] font-semibold"
                      style="background:#E6F7F4;color:#1E8F88;">{{ $t['badge'] }}</span>
                @else
                <span id="badge-{{ $t['id'] }}" class="px-2 py-0.5 rounded-full text-[11px] font-semibold hidden"
                      style="background:#F3F4F6;color:#9CA3AF;"></span>
                @endif
                <span class="tab-indicator absolute left-0 right-0 bottom-0 h-[3px] rounded-t"
                      style="background: {{ $initialTab === $t['id'] ? '#1E8F88' : 'transparent' }};"></span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- ── PANEL: RECOMMENDATIONS ── --}}
    <div id="panel-recommendations" class="{{ $initialTab !== 'recommendations' ? 'hidden' : '' }}">
        {{-- Title + quick search --}}
        <div class="flex justify-between items-end mb-4 gap-4 flex-wrap">
            <div>
                <h1 class="text-[26px] font-semibold tracking-tight text-gray-900">Members matching your profile</h1>
                <div class="mt-1.5 text-sm text-gray-500">
                    Your interests:
                    <span class="text-gray-700">
                        @forelse($interests->whereIn('id', $userInterests) as $i)
                            {{ $i->icon }} {{ $i->name }}@if(!$loop->last), @endif
                        @empty
                            <span class="italic">None yet —</span>
                        @endforelse
                    </span>
                    <a href="{{ route('profile.me') }}"
                       class="ml-1.5 inline-flex items-center gap-1 font-medium text-[13px]" style="color:#1E8F88;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                        Edit interests
                    </a>
                </div>
            </div>
            <div class="flex gap-2.5">
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input id="rec-search-input" type="text" placeholder="Search members…"
                           class="ms-input pl-10" style="width:220px;"
                           oninput="debounceRecSearch(this.value)">
                </div>
                <button onclick="switchTab('search')"
                    class="h-[42px] px-3.5 rounded-[10px] border border-gray-200 bg-white text-[13.5px] font-medium text-gray-700 inline-flex items-center gap-1.5 hover:bg-gray-50">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filters
                </button>
            </div>
        </div>

        <div id="rec-list" class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm min-h-[200px]">
            <div class="p-12 text-center text-gray-400">
                <div class="w-8 h-8 border-2 border-t-transparent rounded-full animate-spin mx-auto mb-3" style="border-color:#2BB6A3;border-top-color:transparent;"></div>
            </div>
        </div>
        <div id="rec-pagination" class="mt-4 flex justify-between items-center text-[13.5px] text-gray-500"></div>
    </div>

    {{-- ── PANEL: SEARCH ── --}}
    <div id="panel-search" class="{{ $initialTab !== 'search' ? 'hidden' : '' }}">
        <h1 class="text-[26px] font-semibold tracking-tight text-gray-900 mb-4">Member search</h1>

        @if(!auth()->user()->canFeature('can_view_member_name'))
        <div class="bg-white rounded-2xl border border-dashed border-indigo-200 p-14 text-center shadow-sm">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-5 flex items-center justify-center" style="background:#EEF2FF;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#6366F1" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6M8 11h6"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">Recherche manuelle — Plan Premium</h2>
            <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">
                Recherchez des membres par nom, entreprise, secteur, ville ou centres d'intérêt.<br>
                Fonctionnalité réservée aux plans payants.
            </p>
            <button type="button" onclick="openUpgradeModal('can_view_member_name')"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold text-white cursor-pointer"
                    style="background:linear-gradient(135deg,#6366F1,#4F46E5);box-shadow:0 6px 14px -6px rgba(99,102,241,0.5);border:none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                Voir les plans disponibles
            </button>
        </div>
        @else
        <form id="search-form" class="bg-white rounded-2xl border border-gray-200 p-7 shadow-sm" onsubmit="submitSearch(event)">

            {{-- Primary filters (always visible) --}}
            <div class="grid grid-cols-2 gap-5">
                <x-form-field label="Name">
                    <input name="search" type="text" placeholder="Name, position, company, city…" class="ms-input">
                </x-form-field>

                <x-form-field label="Gender">
                    <div class="flex gap-6 pt-1">
                        @foreach(['all' => 'All', 'male' => 'Male', 'female' => 'Female'] as $val => $lbl)
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input type="radio" name="gender" value="{{ $val }}"
                                   {{ $val === 'all' ? 'checked' : '' }}
                                   class="ms-radio">
                            {{ $lbl }}
                        </label>
                        @endforeach
                    </div>
                </x-form-field>
            </div>

            {{-- More filters toggle --}}
            <button type="button" id="filters-toggle" onclick="toggleFilters()"
                class="mt-4 inline-flex items-center gap-1.5 text-[13px] font-medium transition-colors"
                style="color:#1E8F88;">
                <svg id="filters-toggle-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     style="transition: transform .2s;">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                <span id="filters-toggle-label">More filters</span>
            </button>

            {{-- Extended filters (hidden by default) --}}
            <div id="extra-filters" class="hidden">
                <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-5">
                    {{-- LEFT --}}
                    <div class="flex flex-col gap-4">
                        <x-form-field label="Age">
                            <div class="flex gap-2 items-center">
                                <input name="age_min" type="number" placeholder="Min" class="ms-input">
                                <span class="text-gray-400 flex-none">—</span>
                                <input name="age_max" type="number" placeholder="Max" class="ms-input">
                            </div>
                        </x-form-field>

                        <x-form-field label="Interests">
                            <div class="flex flex-wrap gap-2 p-3 rounded-[10px] border border-gray-200 bg-white min-h-[42px]">
                                @foreach($interests as $interest)
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-[13px] text-gray-700 px-2.5 py-1.5 rounded-full border border-gray-200 hover:border-teal-300 has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50 has-[:checked]:text-teal-700 transition-colors">
                                    <input type="checkbox" name="interests[]" value="{{ $interest->id }}" class="sr-only">
                                    {{ $interest->icon }} {{ $interest->name }}
                                </label>
                                @endforeach
                            </div>
                        </x-form-field>

                        <x-form-field label="Company">
                            <input name="company" type="text" placeholder="e.g. Greenway" class="ms-input">
                        </x-form-field>
                    </div>

                    {{-- RIGHT --}}
                    <div class="flex flex-col gap-4">
                        <x-form-field label="City of living">
                            <select name="city_id" class="ms-input">
                                <option value="">— All cities —</option>
                                @foreach($cities->groupBy('country.name') as $country => $group)
                                <optgroup label="{{ $country }}">
                                    @foreach($group as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                        </x-form-field>

                        <x-form-field label="About">
                            <div class="pt-1 space-y-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                                    <input type="checkbox" name="open_to_network" value="1" class="ms-checkbox">
                                    Open to network
                                </label>
                            </div>
                        </x-form-field>
                    </div>
                </div>
            </div>

            {{-- Bottom row --}}
            <div class="mt-5 pt-5 border-t border-gray-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="resetSearch()"
                    class="h-[42px] px-4 rounded-[10px] bg-white border border-gray-200 text-[13.5px] font-medium text-gray-700 hover:bg-gray-50">
                    Reset
                </button>
                <button type="submit"
                    class="h-[42px] px-5 rounded-[10px] text-white text-[13px] font-semibold uppercase tracking-[0.06em] inline-flex items-center gap-2"
                    style="background: linear-gradient(135deg, #2BB6A3, #1E8F88); box-shadow: 0 6px 14px -6px rgba(43,182,163,0.5);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    Search members
                </button>
            </div>
        </form>

        {{-- Results --}}
        <div class="mt-9">
            <div class="flex justify-between items-baseline mb-3.5">
                <h2 id="search-results-title" class="text-lg font-semibold text-gray-900">Recommended for you</h2>
                <span id="search-results-count" class="text-[13px] text-gray-400">Based on your interests</span>
            </div>
            <div id="search-list" class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm min-h-[100px]"></div>
            <div id="search-pagination" class="mt-4 flex justify-between items-center text-[13.5px] text-gray-500"></div>
        </div>
        @endif
    </div>

    {{-- ── PANEL: VISITORS ── --}}
    <div id="panel-visitors" class="{{ $initialTab !== 'visitors' ? 'hidden' : '' }}">
        <div class="flex justify-between items-end mb-4 gap-4 flex-wrap">
            <div>
                <h1 class="text-[26px] font-semibold tracking-tight text-gray-900">Profile visitors</h1>
                <div id="visitors-subtitle" class="mt-1.5 text-sm text-gray-500"></div>
            </div>
            <div class="inline-flex bg-white border border-gray-200 rounded-[10px] p-[3px] gap-0.5">
                <button id="vis-filter-all" onclick="switchVisFilter('all')"
                    class="px-4 py-2 rounded-[7px] text-[13px] font-semibold text-white transition"
                    style="background:#1E8F88;">All</button>
                <button id="vis-filter-new" onclick="switchVisFilter('new')"
                    class="px-4 py-2 rounded-[7px] text-[13px] font-semibold text-gray-500 hover:text-gray-700 transition">
                    New <span id="vis-new-badge"></span>
                </button>
            </div>
        </div>

        {{-- Insight banner --}}
        <div class="border rounded-xl px-4 py-3.5 mb-4 flex items-center gap-3.5"
             style="background:linear-gradient(135deg,rgba(52,212,191,.08),rgba(30,143,136,.04));border-color:#A8E2D9;">
            <div class="w-10 h-10 rounded-[10px] flex items-center justify-center text-white flex-none"
                 style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18M7 14l4-4 4 4 5-5"/></svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-semibold text-gray-900">Your profile is gaining traction</div>
                <div class="text-[13px] text-gray-600 mt-0.5">
                    Complete your profile to attract more relevant connections.
                </div>
            </div>
            <a href="{{ route('profile.me') }}" class="text-[13px] font-semibold whitespace-nowrap" style="color:#1E8F88;">
                View profile →
            </a>
        </div>

        <div id="vis-list" class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm min-h-[100px]">
            <div class="p-12 text-center text-gray-400">
                <div class="w-8 h-8 border-2 border-t-transparent rounded-full animate-spin mx-auto mb-3" style="border-color:#2BB6A3;border-top-color:transparent;"></div>
            </div>
        </div>
        <div id="vis-pagination" class="mt-4 flex justify-between items-center text-[13.5px] text-gray-500"></div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const INITIAL_TAB = '{{ $initialTab }}';

// ── State ──
let recPage = 1, recTotal = 0, recSearch = '', recSearchTimer = null;
let searchPage = 1, searchTotal = 0, searchHasResults = false;
let visFilter = 'all', visPage = 1, visTotal = 0;

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    switchTab(INITIAL_TAB, false);
});

// ── Tab switching ──
function switchTab(tab, pushState = true) {
    ['recommendations', 'search', 'visitors'].forEach(t => {
        const panel = document.getElementById('panel-' + t);
        const btn   = document.getElementById('tab-' + t);
        const ind   = btn.querySelector('.tab-indicator');
        const isActive = t === tab;
        panel.classList.toggle('hidden', !isActive);
        btn.style.color   = isActive ? '#1E8F88' : '#9CA3AF';
        ind.style.background = isActive ? '#1E8F88' : 'transparent';
    });

    if (tab === 'recommendations' && document.getElementById('rec-list').children.length <= 1) loadRec();
    if (tab === 'visitors')        loadVisitors();
    if (tab === 'search' && document.getElementById('search-list').innerHTML === '') loadSearch();
}

// ═══════════════════════════════════════
// RECOMMENDATIONS
// ═══════════════════════════════════════
function debounceRecSearch(val) {
    clearTimeout(recSearchTimer);
    recSearchTimer = setTimeout(() => { recSearch = val; recPage = 1; loadRec(); }, 350);
}

async function loadRec(page = recPage) {
    recPage = page;
    const list = document.getElementById('rec-list');
    list.innerHTML = loadingHtml();

    try {
        const params = new URLSearchParams({ page: recPage, per_page: 10 });
        if (recSearch) params.set('search', recSearch);

        const data = await apiFetch('/api/users/recommendations?' + params);
        recTotal = data.total;

        updateBadge('recommendations', data.total);
        renderMemberList('rec-list', data.users, false);
        renderPagination('rec-pagination', recPage, data.last_page, recTotal, loadRec,
            `Showing ${((recPage-1)*10)+1}–${Math.min(recPage*10, recTotal)} of ${recTotal} members`);
    } catch (e) {
        list.innerHTML = errorHtml(e.message);
    }
}

// ═══════════════════════════════════════
// SEARCH
// ═══════════════════════════════════════
function submitSearch(e) {
    e.preventDefault();
    searchPage = 1;
    searchHasResults = true;
    loadSearch();
}

function resetSearch() {
    document.getElementById('search-form').reset();
    searchHasResults = false;
    searchPage = 1;
    document.getElementById('search-results-title').textContent = 'Recommended for you';
    document.getElementById('search-results-count').textContent = 'Based on your interests';
    // Collapse extra filters on reset
    const extra = document.getElementById('extra-filters');
    if (extra && !extra.classList.contains('hidden')) toggleFilters();
    loadSearch();
}

let filtersOpen = false;
function toggleFilters() {
    filtersOpen = !filtersOpen;
    const extra  = document.getElementById('extra-filters');
    const label  = document.getElementById('filters-toggle-label');
    const icon   = document.getElementById('filters-toggle-icon');
    extra.classList.toggle('hidden', !filtersOpen);
    label.textContent = filtersOpen ? 'Fewer filters' : 'More filters';
    icon.style.transform = filtersOpen ? 'rotate(45deg)' : 'rotate(0deg)';
}

async function loadSearch(page = searchPage) {
    searchPage = page;
    const list = document.getElementById('search-list');
    list.innerHTML = loadingHtml();

    try {
        const params = new URLSearchParams({ page: searchPage, per_page: 10 });

        if (searchHasResults) {
            const fd = new FormData(document.getElementById('search-form'));
            for (const [k, v] of fd.entries()) {
                if (v) params.append(k, v);
            }
        }

        const data = await apiFetch('/api/users?' + params);
        searchTotal = data.total;

        if (searchHasResults) {
            document.getElementById('search-results-title').textContent = 'Search results';
            document.getElementById('search-results-count').textContent = `${data.total} member${data.total !== 1 ? 's' : ''} found`;
        }

        renderMemberList('search-list', data.users, false);
        renderPagination('search-pagination', searchPage, data.last_page, searchTotal, loadSearch,
            searchHasResults ? `${data.total} results` : '');
    } catch (e) {
        list.innerHTML = errorHtml(e.message);
    }
}

// ═══════════════════════════════════════
// VISITORS
// ═══════════════════════════════════════
function switchVisFilter(filter) {
    visFilter = filter; visPage = 1;
    document.getElementById('vis-filter-all').style.background = filter === 'all' ? '#1E8F88' : 'transparent';
    document.getElementById('vis-filter-all').style.color      = filter === 'all' ? 'white' : '#6B7280';
    document.getElementById('vis-filter-new').style.background = filter === 'new' ? '#1E8F88' : 'transparent';
    document.getElementById('vis-filter-new').style.color      = filter === 'new' ? 'white' : '#6B7280';
    loadVisitors();
}

async function loadVisitors(page = visPage) {
    visPage = page;
    const list = document.getElementById('vis-list');
    list.innerHTML = loadingHtml();

    try {
        const data = await apiFetch(`/api/profile/visitors?filter=${visFilter}&page=${visPage}`);
        visTotal = data.total;

        // Update new badge
        const newBadge = document.getElementById('vis-new-badge');
        if (newBadge) newBadge.textContent = data.new_count > 0 ? `(${data.new_count})` : '';

        const badge = document.getElementById('badge-visitors');
        if (badge && data.new_count > 0) {
            badge.textContent = data.new_count;
            badge.classList.remove('hidden');
        } else if (badge) {
            badge.classList.add('hidden');
        }

        document.getElementById('visitors-subtitle').innerHTML =
            `<span class="text-gray-700 font-medium">${data.total}</span> member${data.total !== 1 ? 's' : ''} visited your profile in the last 30 days` +
            (data.new_count > 0 ? ` · <span style="color:#1E8F88;" class="font-medium">${data.new_count} new since your last visit</span>` : '');

        renderVisitorList('vis-list', data.data);
        renderPagination('vis-pagination', visPage, data.last_page, visTotal, loadVisitors,
            `${data.total} total visitor${data.total !== 1 ? 's' : ''}`);
    } catch (e) {
        list.innerHTML = errorHtml(e.message);
    }
}

// ═══════════════════════════════════════
// RENDERING
// ═══════════════════════════════════════
function renderMemberList(containerId, members, hasMore) {
    const list = document.getElementById(containerId);
    if (!members || members.length === 0) {
        list.innerHTML = `<div class="p-16 text-center text-gray-400 text-sm">No members found.</div>`;
        return;
    }
    list.innerHTML = members.map((m, i) => memberRowHtml(m, i === members.length - 1)).join('');
}

function renderVisitorList(containerId, visitors) {
    const list = document.getElementById(containerId);
    if (!visitors || visitors.length === 0) {
        list.innerHTML = `<div class="p-16 text-center text-gray-400 text-sm">No visitors yet.</div>`;
        return;
    }
    list.innerHTML = visitors.map((v, i) => visitorRowHtml(v, i === visitors.length - 1)).join('');
}

function memberRowHtml(m, isLast) {
    const hue  = (m.id * 137) % 360;
    const hue2 = (hue + 40) % 360;
    const initials = ((m.first_name || '?').charAt(0) + (m.last_name || '?').charAt(0)).toUpperCase();

    const subtitle = m.job_title || m.position || '';

    const nearYouBadge = m.same_city
        ? `<span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-semibold ml-1.5" style="background:#E6F7F4;color:#1E8F88;">
              <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              Near you
           </span>`
        : '';

    const sharedHtml = m.shared_interests && m.shared_interests.length > 0
        ? `<div class="text-[13.5px] text-gray-700 flex items-start gap-2 leading-snug">
            <span style="color:#1E8F88;" class="mt-[3px]">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 7h11l-3-3M17 17H6l3 3"/></svg>
            </span>
            <span>
                <span class="font-semibold text-gray-900">${m.shared_interests.length}</span>
                ${m.shared_interests.length > 1 ? 'shared interests' : 'shared interest'}:
                ${m.shared_interests.map(i => `${i.icon||''} ${i.name}`).join(', ')}
            </span>
          </div>`
        : '';

    return `
    <div class="member-row grid items-center gap-6 px-6 py-5 transition-colors hover:bg-gray-50/50 ${isLast ? '' : 'border-b border-gray-100'}"
         style="grid-template-columns: 260px 1fr auto;">

      <div class="flex items-center gap-3.5 min-w-0">
        <div class="w-12 h-12 rounded-full flex-none overflow-hidden">
          ${m.avatar
            ? `<img src="${m.avatar}" alt="${initials}" class="w-full h-full object-cover">`
            : `<div class="w-full h-full grid place-items-center text-white font-semibold text-base" style="background: linear-gradient(135deg, hsl(${hue} 60% 60%), hsl(${hue2} 55% 45%));">${initials}</div>`
          }
        </div>
        <div class="min-w-0">
          <a href="/profile/${m.id}" class="font-semibold text-[15px] hover:underline block" style="color:#1E8F88;">${m.first_name} ${m.last_name}</a>
          ${subtitle ? `<div class="text-[12.5px] text-gray-500 mt-0.5 truncate">${subtitle}</div>` : ''}
          ${m.city ? `<div class="text-[13px] text-gray-400 mt-0.5 flex items-center gap-0.5 flex-wrap">
            <span class="flex items-center gap-1">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
              ${typeof m.city === 'object' ? (m.city.name || '') : m.city}
            </span>
            ${nearYouBadge}
          </div>` : ''}
          ${m.company ? `<div class="text-[12px] text-gray-400 mt-0.5">${m.company.name}</div>` : ''}
        </div>
      </div>

      <div class="flex flex-col gap-1.5">
        ${sharedHtml}
      </div>

      <div>${connectionBtnHtml(m)}</div>
    </div>`;
}

function visitorRowHtml(v, isLast) {
    const hue = (v.id * 137) % 360;
    const hue2 = (hue + 40) % 360;
    const initials = ((v.first_name || '?').charAt(0) + (v.last_name || '?').charAt(0)).toUpperCase();

    return `
    <div class="member-row relative grid items-center gap-6 px-6 py-5 transition-colors hover:bg-gray-50/50 ${isLast ? '' : 'border-b border-gray-100'}"
         style="grid-template-columns: 260px 1fr 160px auto;">
      ${v.is_new ? `<span class="absolute left-0 top-0 bottom-0 w-[3px]" style="background:#1E8F88;"></span>` : ''}

      <div class="flex items-center gap-3.5 min-w-0">
        <div class="relative flex-none w-12 h-12 rounded-full overflow-hidden">
          ${v.avatar
            ? `<img src="${v.avatar}" alt="${initials}" class="w-full h-full object-cover">`
            : `<div class="w-full h-full grid place-items-center text-white font-semibold text-base" style="background: linear-gradient(135deg, hsl(${hue} 60% 60%), hsl(${hue2} 55% 45%));">${initials}</div>`
          }
          ${v.is_new ? `<span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-[2.5px] border-white" style="background:#10B981;"></span>` : ''}
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-2">
            <a href="/profile/${v.id}" class="font-semibold text-[15px] hover:underline" style="color:#1E8F88;">${v.first_name} ${v.last_name}</a>
            ${v.is_new ? `<span class="px-2 py-px rounded-full text-[10px] font-bold uppercase tracking-[0.06em] border" style="background:#E6F7F4;color:#1E8F88;border-color:#A8E2D9;">New</span>` : ''}
          </div>
          ${v.job_title ? `<div class="text-[12.5px] text-gray-500 mt-0.5">${v.job_title}</div>` : ''}
          ${v.city ? `<div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            ${typeof v.city === 'object' ? (v.city.name || '') : v.city}</div>` : ''}
        </div>
      </div>

      <div class="text-[13px] text-gray-600">
        <div class="text-[11px] uppercase tracking-[0.06em] font-semibold text-gray-400 mb-1">Company</div>
        <div class="text-gray-700">${v.company ? v.company.name : '—'}</div>
      </div>

      <div>
        <div class="text-[11px] uppercase tracking-[0.06em] font-semibold text-gray-400 mb-1">Last visit</div>
        <div class="text-[13.5px] text-gray-900 font-medium flex items-center gap-1.5">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#1E8F88" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          ${v.visited_at_human}
        </div>
        ${v.visit_count > 1 ? `<div class="text-[12px] text-gray-400 mt-0.5">${v.visit_count} visits total</div>` : ''}
      </div>

      <div class="flex gap-2">
        <a href="/profile/${v.id}"
           class="w-10 h-10 rounded-[10px] bg-white border border-gray-200 grid place-items-center text-gray-500 hover:bg-gray-50" title="View profile">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
        ${connectionBtnHtml(v, true)}
      </div>
    </div>`;
}

function connectionBtnHtml(m, compact = false) {
    const px = compact ? 'px-3' : 'px-[18px]';
    const label = compact ? 'Add' : 'Add contact';
    const addIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>`;
    const checkIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>`;

    if (m.connection_status === 'accepted') {
        return `<button disabled class="h-10 ${px} rounded-[10px] text-[12.5px] font-semibold uppercase tracking-[0.06em] inline-flex items-center gap-1.5 border" style="background:#E6F7F4;color:#1E8F88;border-color:#A8E2D9;">${checkIcon} Connected</button>`;
    }
    if (m.connection_status === 'pending' && m.i_am_sender) {
        return `<button disabled class="h-10 ${px} rounded-[10px] text-[12.5px] font-semibold uppercase tracking-[0.06em] inline-flex items-center gap-1.5" style="background:#FFFBEB;color:#92400E;border:1px solid #FDE68A;">Pending</button>`;
    }
    if (m.connection_status === 'pending' && m.i_am_receiver) {
        return `<div class="flex gap-1.5">
            <button onclick="acceptRequest(${m.connection_id})" id="accept-${m.connection_id}"
                class="h-10 px-3 rounded-[10px] text-white text-[12px] font-semibold uppercase tracking-[0.06em] inline-flex items-center gap-1"
                style="background:linear-gradient(135deg,#2BB6A3,#1E8F88);">${checkIcon} Accept</button>
            <button onclick="rejectRequest(${m.connection_id},${m.id})" id="reject-${m.connection_id}"
                class="h-10 px-3 rounded-[10px] text-[12px] font-semibold uppercase tracking-[0.06em] inline-flex items-center gap-1"
                style="background:#FEF2F2;color:#DC2626;border:1px solid #FECACA;">Reject</button>
        </div>`;
    }
    return `<button onclick="connect(${m.id})" id="conn-${m.id}"
        class="h-10 ${px} rounded-[10px] text-white text-[12.5px] font-semibold uppercase tracking-[0.06em] inline-flex items-center gap-1.5"
        style="background:linear-gradient(135deg,#2BB6A3,#1E8F88);box-shadow:0 6px 14px -6px rgba(43,182,163,0.5);">
        ${addIcon} ${label}
    </button>`;
}

function renderPagination(containerId, currentPage, lastPage, total, loadFn, info) {
    const el = document.getElementById(containerId);
    if (!el || lastPage <= 1) { if (el) el.innerHTML = ''; return; }

    let pages = '';
    for (let p = Math.max(1, currentPage - 2); p <= Math.min(lastPage, currentPage + 2); p++) {
        const active = p === currentPage;
        pages += `<button onclick="${loadFn.name}(${p})"
            class="min-w-[36px] h-9 px-3 rounded-lg text-[13px] font-medium inline-flex items-center justify-center"
            style="${active ? 'background:#1E8F88;color:white;' : 'background:white;color:#374151;border:1px solid #E5E7EB;'}">${p}</button>`;
    }

    el.innerHTML = `<span>${info}</span>
        <div class="flex gap-1.5">
            ${currentPage > 1 ? `<button onclick="${loadFn.name}(${currentPage - 1})" class="min-w-[36px] h-9 px-3 rounded-lg text-[13px] font-medium bg-white border border-gray-200 text-gray-700 inline-flex items-center justify-center hover:bg-gray-50">Prev</button>` : ''}
            ${pages}
            ${currentPage < lastPage ? `<button onclick="${loadFn.name}(${currentPage + 1})" class="min-w-[36px] h-9 px-3 rounded-lg text-[13px] font-medium bg-white border border-gray-200 text-gray-700 inline-flex items-center justify-center hover:bg-gray-50">Next</button>` : ''}
        </div>`;
}

function updateBadge(tab, count) {
    const el = document.getElementById('badge-' + tab);
    if (!el) return;
    if (count > 0) { el.textContent = count; el.classList.remove('hidden'); }
    else { el.classList.add('hidden'); }
}

// ═══════════════════════════════════════
// CONNECTION ACTIONS
// ═══════════════════════════════════════
async function connect(userId) {
    const btn = document.getElementById('conn-' + userId);
    if (!btn) return;
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
    try {
        const data = await apiFetch('/api/connections', 'POST', { receiver_id: userId });
        btn.outerHTML = `<button disabled class="h-10 px-[18px] rounded-[10px] text-[12.5px] font-semibold uppercase tracking-[0.06em] inline-flex items-center gap-1.5" style="background:#FFFBEB;color:#92400E;border:1px solid #FDE68A;">Pending</button>`;
        toast('Connection request sent!', 'success');
    } catch (e) {
        if (btn) { btn.disabled = false; btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg> Add contact'; }
        toast(e.message || 'Error', 'error');
    }
}

async function acceptRequest(connId) {
    const a = document.getElementById('accept-' + connId);
    const r = document.getElementById('reject-' + connId);
    if (a) { a.disabled = true; a.innerHTML = '…'; }
    if (r) r.disabled = true;
    try {
        await apiFetch(`/api/connections/${connId}/accept`, 'POST', {});
        a?.closest('div')?.parentElement?.querySelector('div:last-child')?.innerHTML
            ?? a?.closest('div')?.replaceWith((() => { const el = document.createElement('span'); el.innerHTML = `<button disabled class="h-10 px-[18px] rounded-[10px] text-[12.5px] font-semibold uppercase tracking-[0.06em] inline-flex items-center gap-1.5 border" style="background:#E6F7F4;color:#1E8F88;border-color:#A8E2D9;">Connected</button>`; return el.firstChild; })());
        // Simpler: reload the current list
        if (document.getElementById('panel-recommendations').classList.contains('hidden') === false) loadRec();
        else if (document.getElementById('panel-visitors').classList.contains('hidden') === false) loadVisitors(visPage);
        toast('Connection accepted!', 'success');
    } catch (e) {
        if (a) { a.disabled = false; a.innerHTML = 'Accept'; }
        if (r) r.disabled = false;
        toast('Error', 'error');
    }
}

async function rejectRequest(connId, userId) {
    const r = document.getElementById('reject-' + connId);
    if (r) { r.disabled = true; r.innerHTML = '…'; }
    try {
        await apiFetch(`/api/connections/${connId}/reject`, 'POST', {});
        if (document.getElementById('panel-recommendations').classList.contains('hidden') === false) loadRec();
        else if (document.getElementById('panel-visitors').classList.contains('hidden') === false) loadVisitors(visPage);
        toast('Request rejected', 'info');
    } catch (e) {
        if (r) { r.disabled = false; r.innerHTML = 'Reject'; }
        toast('Error', 'error');
    }
}

// ═══════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════
async function apiFetch(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + (window.API_TOKEN || ''),
            'X-CSRF-TOKEN': window.CSRF || '',
        },
    };
    if (body) { opts.headers['Content-Type'] = 'application/json'; opts.body = JSON.stringify(body); }
    const res = await fetch(url, opts);
    if (!res.ok) {
        const e = await res.json().catch(() => ({}));
        throw new Error(Object.values(e.errors || {}).flat().join('\n') || e.message || 'Error');
    }
    return res.json();
}

function loadingHtml() {
    return `<div class="p-12 text-center text-gray-400">
        <div class="w-8 h-8 border-2 border-t-transparent rounded-full animate-spin mx-auto mb-3" style="border-color:#2BB6A3;border-top-color:transparent;"></div>
    </div>`;
}

function errorHtml(msg) {
    return `<div class="p-12 text-center text-red-400 text-sm">${msg || 'Failed to load'}</div>`;
}
</script>
@endpush
