@extends('admin.layouts.admin')
@section('title', 'Blocage géographique')
@section('page-title', 'Blocage par région / ville')

@push('styles')
<style>
.geo-tabs { display:flex; gap:6px; margin-bottom:20px; }
.geo-tab {
    flex:1; padding:10px 16px; border-radius:12px; border:1.5px solid #e5e7eb;
    font-size:13px; font-weight:600; color:#64748B; background:#F8FAFC;
    cursor:pointer; transition:all .15s; text-align:center;
}
.geo-tab.active { background:#FEF2F2; border-color:#EF4444; color:#DC2626; }
.geo-panel { display:none; }
.geo-panel.active { display:block; }

.item-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 7px;
}
.geo-item {
    display: flex; align-items: center; gap: 9px;
    padding: 9px 12px; border-radius: 10px;
    border: 1.5px solid #E5E7EB; background: #fff;
    cursor: pointer; transition: all .15s; user-select: none;
}
.geo-item:hover { border-color: #FCA5A5; background: #FFF5F5; }
.geo-item.blocked { border-color: #EF4444; background: #FEF2F2; }
.geo-item.blocked .item-name { color: #DC2626; font-weight: 600; }
.geo-item input { display: none; }
.dot { width:9px; height:9px; border-radius:50%; background:#E5E7EB; flex-shrink:0; transition:background .15s; }
.geo-item.blocked .dot { background:#EF4444; }
.search-input {
    width:100%; padding:9px 14px; border:1.5px solid #E5E7EB;
    border-radius:11px; font-size:13px; outline:none; transition:border-color .15s;
}
.search-input:focus { border-color:#EF4444; }
.badge-count {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700;
    background:#FEF2F2; color:#DC2626; border:1px solid #FECACA;
}
</style>
@endpush

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm">
    <span class="text-gray-400">Paramètres</span>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-gray-700 font-semibold">Blocage géographique</span>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm bg-emerald-50 text-emerald-700 border border-emerald-100 mb-5">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm bg-red-50 border border-red-100 text-red-700 mb-6">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
    <p>Les visiteurs dont l'IP correspond à une ville ou un pays bloqué verront une page d'accès restreint. Les admins ne sont jamais affectés. La détection IP peut ne pas être précise à 100 %.</p>
</div>

<div class="max-w-5xl">

    {{-- Info technique --}}
    <div class="flex items-start gap-3 px-4 py-3 rounded-xl text-xs bg-blue-50 border border-blue-100 text-blue-700 mb-5">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <div>
            <strong>Pourquoi "régions" et non "villes" ?</strong>
            L'API de géolocalisation IP retourne la <em>commune exacte</em> (ex: Lognes, Vincennes, Boulogne-Billancourt…), jamais "Paris" pour quelqu'un en banlieue.
            Bloquer par <strong>région</strong> (ex: Île-de-France) couvre toute la zone parisienne. C'est la méthode fiable.
        </div>
    </div>

    <div class="geo-tabs">
        <button class="geo-tab active" onclick="switchTab('regions', this)">
            🗺️ Par région <span class="badge-count ml-1" id="regionBadge">{{ count($blockedCities) }}</span>
        </button>
        <button class="geo-tab" onclick="switchTab('pays', this)">
            🌍 Par pays <span class="badge-count ml-1" id="countryBadge">{{ count($blockedCountries) }}</span>
        </button>
    </div>

    {{-- ── RÉGIONS ── --}}
    @php
    $frenchRegions = [
        'Île-de-France'          => ['Paris, Lognes, Versailles, Boulogne…',    '🗼'],
        'Auvergne-Rhône-Alpes'   => ['Lyon, Grenoble, Clermont-Ferrand…',       '⛰️'],
        'Hauts-de-France'        => ['Lille, Amiens, Dunkerque…',               '🏭'],
        'Nouvelle-Aquitaine'     => ['Bordeaux, Limoges, Pau…',                 '🍷'],
        'Occitanie'              => ['Toulouse, Montpellier, Nîmes…',           '☀️'],
        'Grand Est'              => ['Strasbourg, Reims, Nancy…',               '🏰'],
        'Provence-Alpes-Côte d\'Azur' => ['Marseille, Nice, Toulon…',          '🌊'],
        'Pays de la Loire'       => ['Nantes, Le Mans, Angers…',               '🏄'],
        'Normandie'              => ['Rouen, Caen, Le Havre…',                  '🚢'],
        'Bretagne'               => ['Rennes, Brest, Quimper…',                 '⚓'],
        'Bourgogne-Franche-Comté'=> ['Dijon, Besançon, Chalon…',               '🍾'],
        'Centre-Val de Loire'    => ['Orléans, Tours, Bourges…',               '🏯'],
        'Corse'                  => ['Ajaccio, Bastia…',                        '🏝️'],
        'Guadeloupe'             => ['Pointe-à-Pitre, Basse-Terre…',           '🌺'],
        'Martinique'             => ['Fort-de-France…',                         '🌴'],
        'Guyane'                 => ['Cayenne…',                                '🌿'],
        'La Réunion'             => ['Saint-Denis, Saint-Pierre…',             '🌋'],
        'Mayotte'                => ['Mamoudzou…',                              '🏖️'],
    ];
    @endphp
    <div class="geo-panel active" id="panel-regions">
        <form method="POST" action="{{ route('admin.super.settings.geo-block.update') }}" id="formVilles">
            @csrf @method('PUT')
            <input type="hidden" name="type" value="cities">
            <input type="hidden" name="config_json" id="jsonVilles">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-800">Régions françaises</span>
                        <span class="badge-count"><span id="cityNum">{{ count($blockedCities) }}</span> bloquée(s)</span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(260px,1fr));" id="cityGrid">
                        @foreach($frenchRegions as $regionName => $meta)
                        @php $isBlocked = in_array($regionName, $blockedCities); @endphp
                        <label class="geo-item {{ $isBlocked ? 'blocked' : '' }}" data-name="{{ strtolower($regionName) }}"
                               style="flex-direction:column;align-items:flex-start;padding:14px 16px;gap:4px;">
                            <div class="flex items-center gap-2 w-full">
                                <input type="checkbox" style="display:none;" name="blocked[]" value="{{ $regionName }}"
                                       {{ $isBlocked ? 'checked' : '' }}
                                       onchange="toggleItem(this,'cityNum')">
                                <span class="dot" style="flex-shrink:0;"></span>
                                <span class="item-name text-sm font-semibold">{{ $meta[2] }} {{ $regionName }}</span>
                            </div>
                            <span class="text-xs text-gray-400 ml-4">{{ $meta[0] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <button type="button" onclick="clearGrid('cityGrid','cityNum')"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-white transition">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Tout débloquer
                    </button>
                    <button type="button" onclick="saveGeo('villes')"
                            class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#EF4444,#DC2626);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── PAYS ── --}}
    <div class="geo-panel" id="panel-pays">
        <form method="POST" action="{{ route('admin.super.settings.geo-block.update') }}" id="formPays">
            @csrf @method('PUT')
            <input type="hidden" name="type" value="countries">
            <input type="hidden" name="config_json" id="jsonPays">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-bold text-gray-800">Pays bloqués</span>
                        <span class="badge-count"><span id="countryNum">{{ count($blockedCountries) }}</span> bloqué(s)</span>
                    </div>
                    <input type="text" class="search-input" style="max-width:200px;"
                           placeholder="Rechercher…" oninput="filterGrid('countryGrid', this.value)">
                </div>
                <div class="p-5">
                    <div class="item-grid" id="countryGrid">
                        @foreach($countries as $code => $name)
                        @php $isBlocked = in_array($code, $blockedCountries); @endphp
                        <label class="geo-item {{ $isBlocked ? 'blocked' : '' }}" data-name="{{ strtolower($name) }}">
                            <input type="checkbox" name="blocked[]" value="{{ $code }}"
                                   {{ $isBlocked ? 'checked' : '' }}
                                   onchange="toggleItem(this,'countryNum')">
                            <span class="dot"></span>
                            <span class="item-name text-sm text-gray-700">{{ $name }}</span>
                            <span class="ml-auto text-[10px] font-mono text-gray-300">{{ $code }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <button type="button" onclick="clearGrid('countryGrid','countryNum')"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-white transition">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Tout débloquer
                    </button>
                    <button type="button" onclick="saveGeo('pays')"
                            class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#EF4444,#DC2626);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.geo-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.geo-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    // tab 'villes' était l'ancien id, maintenant c'est 'regions'
    var panelId = tab === 'villes' ? 'panel-villes' : 'panel-' + tab;
    var el = document.getElementById(panelId) || document.getElementById('panel-regions');
    if (el) el.classList.add('active');
}
function toggleItem(cb, counterId) {
    cb.closest('label').classList.toggle('blocked', cb.checked);
    document.getElementById(counterId).textContent =
        document.querySelectorAll('#' + (counterId === 'cityNum' ? 'cityGrid' : 'countryGrid') + ' input:checked').length;
}
function clearGrid(gridId, counterId) {
    document.querySelectorAll('#' + gridId + ' input:checked').forEach(cb => {
        cb.checked = false; cb.closest('label').classList.remove('blocked');
    });
    document.getElementById(counterId).textContent = 0;
}
function filterGrid(gridId, q) {
    var term = q.toLowerCase().trim();
    document.querySelectorAll('#' + gridId + ' label').forEach(function(label) {
        label.style.display = (!term || (label.getAttribute('data-name') || '').includes(term)) ? '' : 'none';
    });
}

function saveGeo(tab) {
    var gridId  = tab === 'villes' ? 'cityGrid'    : 'countryGrid';
    var jsonId  = tab === 'villes' ? 'jsonVilles'  : 'jsonPays';
    var formId  = tab === 'villes' ? 'formVilles'  : 'formPays';
    var checked = [];
    document.querySelectorAll('#' + gridId + ' input[type=checkbox]:checked').forEach(function(cb) {
        checked.push(cb.value);
    });
    var jsonVal = JSON.stringify(checked);
    var jsonInput = document.getElementById(jsonId);
    jsonInput.value = jsonVal;
    // Vérification avant soumission
    if (jsonInput.value !== jsonVal) {
        alert('Erreur lors de la préparation des données. Réessayez.');
        return;
    }
    document.getElementById(formId).submit();
}
</script>
@endpush
