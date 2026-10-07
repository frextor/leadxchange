@extends('layouts.app2')

@section('title', 'Réseau — LeadXchange')

{{--
    Écran « Réseau » — maquette LeadXchange WEB › pageReseau(tab)
    Onglets Pour toi · Contacts · Visiteurs. Les données viennent des API JSON existantes
    (architecture hybride) : /api/users/recommendations, /api/users, /api/connections, /api/profile/visitors.
--}}

@push('styles')
<style>
    .net-banner{gap:0;margin-bottom:18px}
    .net-banner .tx b{display:block}
    .net-banner .tx small{display:block;margin-top:2px}
    .net-stats{display:flex;align-items:stretch;gap:28px;margin-left:auto;padding-left:24px}
    .net-stats .st{text-align:center;position:relative}
    .net-stats .st::before{content:"";position:absolute;left:-14px;top:50%;transform:translateY(-50%);width:1px;height:28px;background:rgba(255,255,255,.3)}
    .net-stats .st:first-child::before{display:none}
    .net-stats .st .n{display:block;font-size:20px;font-weight:600;line-height:1.1}
    .net-stats .st .l{display:block;font-size:11.5px;opacity:.85;margin-top:2px;white-space:nowrap}
    .net-stats .st .dot{display:inline-block;width:6px;height:6px;border-radius:999px;background:#fff;margin-left:4px;vertical-align:middle}
    @media (max-width:720px){.net-banner{flex-wrap:wrap}.net-stats{margin-left:0;padding-left:0;padding-top:12px;width:100%;justify-content:space-between}.net-stats .st::before{display:none}}
    #sortPop{right:0;top:calc(100% + 6px);width:170px;padding:0}
    #sortPop li button:hover{background:var(--surface-2,#F6F7FB)}
    #netState .ic{display:inline-flex;width:44px;height:44px;border-radius:999px;background:var(--muted);align-items:center;justify-content:center;margin-bottom:12px;color:var(--muted-fg)}
    #netState .ic svg{width:18px;height:18px}
    #netState p{margin:0;font-size:13.5px}
    #netState .lx2-linkbtn{display:inline-block;margin-top:10px}
</style>
@endpush

@section('content')
<x-lx2-header title="Réseau" sub="Développez votre réseau de partenaires d'affaires" />

<div class="banner net-banner">
    <span class="ring" style="background:none;display:grid;place-items:center"><x-lx2-icon name="trending-up" /></span>
    <div class="tx"><b>Votre réseau prend de l'élan</b><small>Les profils les plus pertinents pour vous, cette semaine.</small></div>
    <div class="net-stats">
        <div class="st"><span class="n">{{ $suggestionsCount }}</span><span class="l">Suggestions</span></div>
        <div class="st"><span class="n">{{ $contactsCount }}</span><span class="l">Contacts</span></div>
        <div class="st"><span class="n">{{ $recentVisitorsCount }}{{ $newVisitorCount > 0 ? ' ' : '' }}@if($newVisitorCount > 0)<span class="dot"></span>@endif</span><span class="l">Visites récentes</span></div>
    </div>
</div>

<div class="toolbar">
    <div class="chips" id="netTabs">
        <button type="button" class="chip" data-tab="recommendations">Pour toi</button>
        <button type="button" class="chip" data-tab="contacts">Contacts</button>
        <button type="button" class="chip" data-tab="visitors">Visiteurs @if($newVisitorCount > 0)<span class="n num">{{ $newVisitorCount }}</span>@endif</button>
    </div>
    <div class="grow"></div>
    <div class="input-wrap" style="width:320px;max-width:100%">
        <x-lx2-icon name="search" />
        <input class="input" id="memberSearch" type="search" placeholder="Rechercher des membres" autocomplete="off">
    </div>
    <div style="width:200px;max-width:100%">
        <select class="select" id="netSector" aria-label="Secteur">
            <option value="">Tous les secteurs</option>
            @foreach($sectors as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
        </select>
    </div>
    <div class="lx2-dd" id="filtersDd">
        <button type="button" class="btn btn-outline" id="filtersBtn" aria-haspopup="true" aria-expanded="false">
            <x-lx2-icon name="settings" /><span class="lbl-m">Filtres</span>
            <span class="badge b-soft" id="filtersCount" hidden style="margin-left:4px">0</span>
        </button>
        <div class="pop filters-pop hidden" id="filtersPop">
            <div class="card-h">
                <h3>Filtres</h3>
                <button type="button" class="link lx2-linkbtn" id="filtersReset">Réinitialiser</button>
            </div>
            <div style="padding:14px;display:flex;flex-direction:column;gap:14px;max-height:60vh;overflow:auto">
                <div class="field" style="position:relative" id="filterCompanyField">
                    <label class="label" for="filterCompany">Entreprise</label>
                    <input class="input" id="filterCompany" type="text" placeholder="Nom de l'entreprise" autocomplete="off">
                    <div class="pop hidden" id="filterCompanyPop" role="listbox" style="left:0;right:0;top:100%;margin-top:4px;max-height:200px;overflow:auto"></div>
                </div>
                <div class="field">
                    <span class="label">Centres d'intérêt</span>
                    <div style="display:flex;flex-direction:column;gap:8px;max-height:220px;overflow:auto">
                        @foreach($interests as $interest)
                        <label class="check">
                            <input type="checkbox" class="filter-interest" value="{{ $interest->id }}"><span class="box"></span>
                            <span>{{ $interest->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@unless($canViewName)
<div class="lx2-flash b-soft" role="note">
    <span>Avec votre offre, seuls les prénoms des membres sont visibles.</span>
    <button type="button" class="link lx2-linkbtn" onclick="openUpgradeModal('can_view_member_name')" style="opacity:1">Voir les offres</button>
</div>
@endunless

@if($selectedCityId)
{{-- Masqué sur « Pour toi » : ce filtre y est géré côté serveur, avec repli si la ville choisie n'a personne. --}}
<div class="lx2-flash b-soft" role="note" id="cityFilterNotice" @if($initialTab === 'recommendations') hidden @endif>
    <span>Filtré sur la ville sélectionnée à l'accueil : <b>{{ $cities->firstWhere('id', $selectedCityId)?->name }}</b>. Seuls les membres de cette ville sont affichés.</span>
    <a class="link lx2-linkbtn" href="{{ route('dashboard') }}" style="opacity:1">Changer la ville</a>
</div>
{{-- Affiché uniquement sur « Pour toi » quand le serveur a dû élargir la recherche (aucun candidat dans la ville choisie). --}}
<div class="lx2-flash b-soft" role="note" id="cityWidenedNotice" hidden>
    <span>Aucun membre disponible à <b>{{ $cities->firstWhere('id', $selectedCityId)?->name }}</b> pour le moment — résultats élargis à toutes les villes.</span>
</div>
@endif

<div class="sh">
    <h2 id="netTitle">Membres pour toi</h2>
    <div class="lx2-dd" id="sortDd">
        <button type="button" class="link lx2-linkbtn" id="sortBtn" aria-haspopup="true" aria-expanded="false" style="display:flex;align-items:center;gap:5px;opacity:1">
            <x-lx2-icon name="trending-up" /><span id="sortLabel">Pertinence</span>
        </button>
        <div class="pop hidden" id="sortPop">
            <ul style="padding:6px 0">
                <li><button type="button" class="link lx2-linkbtn" data-sort="relevance" style="display:block;width:100%;text-align:left;padding:8px 14px;opacity:1">Pertinence</button></li>
                <li><button type="button" class="link lx2-linkbtn" data-sort="recent" style="display:block;width:100%;text-align:left;padding:8px 14px;opacity:1">Plus récents</button></li>
                <li><button type="button" class="link lx2-linkbtn" data-sort="name" style="display:block;width:100%;text-align:left;padding:8px 14px;opacity:1">Nom (A→Z)</button></li>
            </ul>
        </div>
    </div>
</div>
<div class="grid-2" id="netList"></div>
<div id="netState" class="card empty" hidden></div>
<div style="text-align:center;margin-top:16px"><button type="button" class="btn btn-outline" id="netMore" hidden>Charger plus</button></div>
@endsection

@push('scripts')
<script>
(function () {
    const CAN_VIEW_NAME = @json($canViewName);
    const CAN_INVITE    = @json($canInvite);
    const ICON = {
        plus:   @json(\App\Support\Lx2Icons::svg('user-plus')),
        msg:    @json(\App\Support\Lx2Icons::svg('message-circle')),
        star:   @json(\App\Support\Lx2Icons::svg('star')),
        x:      @json(\App\Support\Lx2Icons::svg('x')),
        check:  @json(\App\Support\Lx2Icons::svg('check')),
        users:  @json(\App\Support\Lx2Icons::svg('users')),
        search: @json(\App\Support\Lx2Icons::svg('search')),
    };
    const $ = (id) => document.getElementById(id);
    const esc = (s) => escapeHtml(s == null ? '' : s);

    let tab = @json($initialTab), page = 1, lastPage = 1, items = [], loading = false, searchTimer;

    /* ── API (session + jeton web-spa, comme le reste de l'app) ── */
    async function api(url, method = 'GET', body = null) {
        const res = await fetch(url, {
            method, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                       'Authorization': 'Bearer ' + (window.API_TOKEN || '') },
            body: body ? JSON.stringify(body) : null,
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.success === false) throw new Error(data.message || 'Une erreur est survenue.');
        return data;
    }

    /* ── Rendu d'une ligne (maquette › memberRow) ── */
    function name(m) { return CAN_VIEW_NAME && m.last_name ? `${m.first_name} ${m.last_name}` : (m.first_name || 'Membre'); }
    function initials(m) { return ((m.first_name || '?')[0] + (CAN_VIEW_NAME && m.last_name ? m.last_name[0] : '')).toUpperCase(); }
    function avatar(m) {
        return m.avatar ? `<img class="av" src="${esc(m.avatar)}" alt="" style="width:40px;height:40px">`
                        : `<span class="av-fb" style="width:40px;height:40px;font-size:14px">${esc(initials(m))}</span>`;
    }
    function action(m) {
        if (tab === 'contacts' || m.connection_status === 'accepted')
            return `<a class="circle-act" href="/chat?with=${m.id}" aria-label="Envoyer un message">${ICON.msg}</a>`;
        if (m.connection_status === 'pending' && m.i_am_sender)
            return `<span class="btn btn-soft btn-sm">Envoyé</span>`;
        if (m.connection_status === 'pending' && m.i_am_receiver)
            return `<span style="display:flex;gap:6px">
                <button type="button" class="btn btn-primary btn-sm" data-accept="${m.connection_id}">Accepter</button>
                <button type="button" class="circle-act" data-reject="${m.connection_id}" aria-label="Refuser">${ICON.x}</button></span>`;
        return `<button type="button" class="circle-act" data-connect="${m.id}" aria-label="Se connecter avec ${esc(name(m))}">${ICON.plus}</button>`;
    }
    function row(m) {
        const stars = m.rating && m.rating.stars > 0 ? `<span class="stars">${ICON.star}${m.rating.stars}</span>` : '';
        const role  = [m.job_title, m.company && m.company.name].filter(Boolean).join(' · ') || 'Membre LeadXchange';
        const visits = tab === 'visitors'
            ? `<div class="visits"><b>${m.visit_count} visite${m.visit_count > 1 ? 's' : ''}</b> · ${esc(m.visited_at_human)}${m.is_new ? ' · <span class="badge b-soft" style="height:18px;font-size:10.5px">Nouveau</span>' : ''}</div>` : '';
        return `<div class="card mrow" data-id="${m.id}">
            <a href="/profile/${m.id}">${avatar(m)}</a>
            <div class="who"><a class="nm" href="/profile/${m.id}">${esc(name(m))} ${stars}</a><div class="role">${esc(role)}</div>${visits}</div>
            <span class="net-act">${action(m)}</span></div>`;
    }

    /* ── Chargement ── */
    const sectorOk = (m) => { const s = $('netSector').value; if (!s) return true;
        const ids = [].concat(m.sector_ids || [], m.services_offered || [], m.looking_for || []).map(String);
        return ids.includes(s) || (m.company && m.company.sector && String(m.company.sector.id) === s); };
    const searchOk = (m) => { const q = $('memberSearch').value.toLowerCase().trim(); if (!q || tab === 'recommendations') return true;
        return [name(m), m.job_title, m.company && m.company.name, typeof m.city === 'object' && m.city ? m.city.name : m.city].join(' ').toLowerCase().includes(q); };

    /* ── Ville (sélecteur global choisi sur l'accueil) ──
       « Pour toi » est déjà filtré/replié côté serveur (getRecommendedUsers) : ne pas re-filtrer ici,
       sinon les résultats élargis par le repli (autres villes) seraient aussitôt exclus. ── */
    const SELECTED_CITY_ID = @json($selectedCityId);
    const CITY_NAMES = @json($cities->pluck('name', 'id'));
    const cityOk = (m) => { if (!SELECTED_CITY_ID || tab === 'recommendations') return true;
        const mCity = typeof m.city === 'object' && m.city ? m.city : (m.city ? { name: m.city } : null);
        if (!mCity) return false;
        return (mCity.id != null && String(mCity.id) === String(SELECTED_CITY_ID)) || mCity.name === CITY_NAMES[SELECTED_CITY_ID]; };

    /* ── Filtres (panneau « Filtres ») : Entreprise, Centres d'intérêt ── */
    const companyOk = (m) => { const q = $('filterCompany').value.toLowerCase().trim(); if (!q) return true;
        return !!(m.company && m.company.name && m.company.name.toLowerCase().includes(q)); };
    const interestsOk = (m) => { const wanted = selectedInterests(); if (!wanted.length) return true;
        const mine = (m.interest_ids || []).map(String);
        return wanted.some(id => mine.includes(id)); };
    function selectedInterests() {
        return Array.from(document.querySelectorAll('.filter-interest:checked')).map(el => el.value);
    }
    function updateFiltersCount() {
        const n = (($('filterCompany').value.trim()) ? 1 : 0) + selectedInterests().length;
        $('filtersCount').hidden = n === 0;
        $('filtersCount').textContent = n;
    }

    /* ── Tri (Pertinence / Plus récents / Nom) ── */
    let sortMode = 'relevance';
    const SORT_LABELS = { relevance: 'Pertinence', recent: 'Plus récents', name: 'Nom (A→Z)' };
    function sortList(list) {
        const sorted = list.slice();
        if (sortMode === 'recent') {
            sorted.sort((a, b) => new Date(b.joined_at || 0) - new Date(a.joined_at || 0));
        } else if (sortMode === 'name') {
            sorted.sort((a, b) => name(a).localeCompare(name(b), 'fr'));
        } else if (tab === 'recommendations') {
            sorted.sort((a, b) => (b.rec_score || 0) - (a.rec_score || 0));
        }
        return sorted;
    }

    function render() {
        const list = sortList(items.filter(m => sectorOk(m) && searchOk(m) && companyOk(m) && cityOk(m) && interestsOk(m)));
        $('netList').innerHTML = list.map(row).join('');
        const q = $('memberSearch').value.trim();
        const total = tab === 'recommendations' ? null : list.length;
        $('netTitle').innerHTML = tab === 'contacts'  ? `<span class="num">${total}</span> contact${total > 1 ? 's' : ''}`
                                : tab === 'visitors'  ? `<span class="num">${total}</span> visiteur${total > 1 ? 's' : ''} de ton profil`
                                : q.length >= 3       ? `Résultats pour « ${esc(q)} »` : 'Membres pour toi';
        $('netState').hidden = list.length > 0 || loading;
        if (!loading) {
            const isSearch = tab === 'recommendations' && q.length >= 3;
            const icon = isSearch ? ICON.search : ICON.users;
            const text = tab === 'contacts'   ? "Vous n'avez pas encore de contact."
                       : tab === 'visitors'    ? "Personne n'a encore visité votre profil."
                       : isSearch              ? `Aucun membre ne correspond à « ${esc(q)} ».`
                                                : 'Aucune suggestion pour le moment.';
            const cta = tab === 'contacts' ? '<button type="button" class="link lx2-linkbtn" id="netEmptyGoTo" style="opacity:1">Explorer « Pour toi »</button>' : '';
            $('netState').innerHTML = `<span class="ic">${icon}</span><p>${text}</p>${cta}`;
        }
        $('netMore').hidden = !(tab !== 'contacts' && page < lastPage);
    }

    async function load(reset = true) {
        if (reset) { page = 1; items = []; }
        loading = true;
        $('netState').hidden = false; $('netState').innerHTML = '<span class="lx2-spin"></span>';
        if (reset) $('netList').innerHTML = '';
        const q = $('memberSearch').value.trim();
        try {
            let data, list;
            if (tab === 'contacts') {
                data = await api('/api/connections?type=connections');
                list = (data.data || []).map(c => Object.assign({}, c.user, { connection_status: 'accepted', connection_id: c.id }));
                lastPage = 1;
            } else if (tab === 'visitors') {
                data = await api(`/api/profile/visitors?filter=all&page=${page}`);
                list = data.data || []; lastPage = data.last_page || 1;
            } else if (q.length >= 3) {
                const cityParam = `&city_id=${SELECTED_CITY_ID ?? 0}`;
                try { data = await api(`/api/users?page=${page}&search=${encodeURIComponent(q)}`); }
                catch { data = await api(`/api/users/recommendations?page=${page}&include_pending=1&search=${encodeURIComponent(q)}${cityParam}`); }
                list = data.users || []; lastPage = data.last_page || 1;
            } else {
                const cityParam = `&city_id=${SELECTED_CITY_ID ?? 0}`;
                data = await api(`/api/users/recommendations?page=${page}&include_pending=1${cityParam}`);
                list = data.users || []; lastPage = data.last_page || 1;
            }
            const widenedNotice = $('cityWidenedNotice');
            if (widenedNotice) widenedNotice.hidden = !(tab === 'recommendations' && data && data.city_widened);
            items = items.concat(list);
        } catch (e) {
            toast(e.message, 'error');
        }
        loading = false;
        render();
    }

    function setTab(t, push = true) {
        tab = t;
        document.querySelectorAll('#netTabs .chip').forEach(c => c.classList.toggle('on', c.dataset.tab === t));
        $('memberSearch').placeholder = t === 'recommendations' ? 'Rechercher des membres' : (t === 'contacts' ? 'Rechercher un contact' : 'Rechercher un visiteur');
        const cityNotice = document.getElementById('cityFilterNotice');
        if (cityNotice) cityNotice.hidden = (t === 'recommendations');
        if (push) history.replaceState(null, '', t === 'recommendations' ? location.pathname : `?tab=${t}`);
        load();
    }

    $('netTabs').addEventListener('click', (e) => { const c = e.target.closest('[data-tab]'); if (c && c.dataset.tab !== tab) setTab(c.dataset.tab); });
    $('netSector').addEventListener('change', (e) => { e.target.classList.toggle('filled', !!e.target.value); render(); });
    $('memberSearch').addEventListener('input', () => {
        if (tab !== 'recommendations') { render(); return; }
        clearTimeout(searchTimer);
        const q = $('memberSearch').value.trim();
        if (q.length > 0 && q.length < 3) return;           // recherche à partir de 3 caractères
        searchTimer = setTimeout(() => load(), 350);
    });
    $('netMore').addEventListener('click', () => { page++; load(false); });
    $('netState').addEventListener('click', (e) => { if (e.target.closest('#netEmptyGoTo')) setTab('recommendations'); });

    /* ── Panneau « Filtres » (Entreprise, Centres d'intérêt) ── */
    const filtersBtn = $('filtersBtn'), filtersPop = $('filtersPop');
    const toggleFilters = (open) => { filtersPop.classList.toggle('hidden', !open); filtersBtn.setAttribute('aria-expanded', open); };
    filtersBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleFilters(filtersPop.classList.contains('hidden')); });
    document.addEventListener('click', (e) => { if (!$('filtersDd').contains(e.target)) toggleFilters(false); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { toggleFilters(false); toggleSort(false); closeFilterCompanyPop(); closeCityDd(); } });

    /* ── Panneau de tri (Pertinence / Plus récents / Nom) ── */
    const sortBtn = $('sortBtn'), sortPop = $('sortPop');
    const toggleSort = (open) => { sortPop.classList.toggle('hidden', !open); sortBtn.setAttribute('aria-expanded', open); };
    sortBtn.addEventListener('click', (e) => { e.stopPropagation(); toggleSort(sortPop.classList.contains('hidden')); });
    document.addEventListener('click', (e) => { if (!$('sortDd').contains(e.target)) toggleSort(false); });
    sortPop.addEventListener('click', (e) => {
        const b = e.target.closest('[data-sort]'); if (!b) return;
        sortMode = b.dataset.sort;
        $('sortLabel').textContent = SORT_LABELS[sortMode];
        toggleSort(false);
        render();
    });

    let filterTimer;
    const onFilterChange = () => { updateFiltersCount(); clearTimeout(filterTimer); filterTimer = setTimeout(render, 150); };
    $('filterCompany').addEventListener('input', onFilterChange);
    document.querySelectorAll('.filter-interest').forEach(el => el.addEventListener('change', onFilterChange));
    $('filtersReset').addEventListener('click', () => {
        $('filterCompany').value = '';
        document.querySelectorAll('.filter-interest:checked').forEach(el => el.checked = false);
        $('filterCompanyPop').classList.add('hidden');
        updateFiltersCount(); render();
    });

    /* ── Entreprise (panneau Filtres) : suggestions issues des entreprises déjà enregistrées ── */
    const filterCompanyPop = $('filterCompanyPop');
    let filterCompanyTimer;
    const closeFilterCompanyPop = () => filterCompanyPop.classList.add('hidden');
    $('filterCompany').addEventListener('input', () => {
        clearTimeout(filterCompanyTimer);
        const q = $('filterCompany').value.trim();
        if (q.length < 3) { closeFilterCompanyPop(); return; }
        filterCompanyTimer = setTimeout(async () => {
            try {
                const data = await api(`/api/companies/search?q=${encodeURIComponent(q)}`);
                const list = data.data || [];
                filterCompanyPop.innerHTML = list.length
                    ? list.map(c => `<button type="button" class="dest-opt" role="option" data-name="${esc(c.name)}">
                        <span style="flex:1;min-width:0">${esc(c.name)}${c.sector ? `<small>${esc(c.sector)}</small>` : ''}</span></button>`).join('')
                    : '<div class="lx2-dd-state">Aucune entreprise trouvée</div>';
                filterCompanyPop.classList.remove('hidden');
            } catch { closeFilterCompanyPop(); }
        }, 300);
    });
    filterCompanyPop.addEventListener('click', (e) => {
        const o = e.target.closest('.dest-opt');
        if (!o) return;
        $('filterCompany').value = o.dataset.name;
        closeFilterCompanyPop();
        onFilterChange();
    });
    document.addEventListener('click', (e) => { if (!$('filterCompanyField').contains(e.target)) closeFilterCompanyPop(); });

    /* ── Actions de connexion ── */
    $('netList').addEventListener('click', async (e) => {
        const b = e.target.closest('[data-connect],[data-accept],[data-reject]');
        if (!b) return;
        const wrap = b.closest('.net-act');
        if (b.dataset.connect && !CAN_INVITE) { openUpgradeModal('can_send_invitations'); return; }
        wrap.querySelectorAll('button').forEach(x => x.disabled = true);
        try {
            if (b.dataset.connect) {
                await api('/api/connections', 'POST', { receiver_id: +b.dataset.connect });
                wrap.innerHTML = '<span class="btn btn-soft btn-sm">Envoyé</span>';
                toast('Demande de connexion envoyée');
            } else if (b.dataset.accept) {
                await api(`/api/connections/${b.dataset.accept}/accept`, 'POST', {});
                wrap.innerHTML = `<a class="circle-act" href="/chat?with=${b.closest('.mrow').dataset.id}" aria-label="Envoyer un message">${ICON.msg}</a>`;
                toast('Connexion acceptée 🎉');
            } else {
                await api(`/api/connections/${b.dataset.reject}/reject`, 'POST', {});
                wrap.innerHTML = `<button type="button" class="circle-act" data-connect="${b.closest('.mrow').dataset.id}" aria-label="Se connecter">${ICON.plus}</button>`;
                toast('Demande refusée');
            }
        } catch (err) {
            wrap.querySelectorAll('button').forEach(x => x.disabled = false);
            toast(err.message, 'error');
        }
    });

    setTab(tab, false);
})();
</script>
@endpush
