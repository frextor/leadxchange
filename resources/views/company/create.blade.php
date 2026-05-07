{{-- resources/views/company/create.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Créer votre entreprise — LeadXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }

        .lx-input {
            width: 100%;
            background: #F9FAFB;
            border: 1.5px solid #E5E7EB;
            border-radius: 12px;
            color: #111827;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
            display: block;
        }
        .lx-input:focus { border-color: #2BB6A3; box-shadow: 0 0 0 3px rgba(43,182,163,.12); }
        .lx-input.lx-error { border-color: #EF4444; }
        .lx-input:read-only { background: #F3F4F6; color: #374151; cursor: default; }

        .gradient-button {
            background: linear-gradient(135deg, #2BB6A3 0%, #1E8F88 100%);
            border: none;
            cursor: pointer;
        }
        .gradient-button:hover { background: linear-gradient(135deg, #25A594 0%, #187A74 100%); }
        .gradient-button:disabled { background: #D1D5DB; cursor: not-allowed; transform: none !important; }

        /* SIRET state badges */
        .siret-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .badge-checking { background: #EFF6FF; color: #3B82F6; }
        .badge-found    { background: #ECFDF5; color: #059669; }
        .badge-warning  { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
        .badge-error    { background: #FEF2F2; color: #DC2626; }
        .badge-inactive { background: #FFFBEB; color: #D97706; }

        .fade-in { animation: fadeIn .25s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        .spinner { animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-lg">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-3">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl text-white text-lg font-bold"
                 style="background:linear-gradient(135deg,#2BB6A3,#1E8F88);">LX</div>
            <span class="text-xl font-semibold text-gray-800">LeadXchange</span>
        </a>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-3xl shadow-xl p-8">

        <h1 class="text-2xl font-bold text-gray-900 text-center mb-1" style="letter-spacing:-0.025em;">
            Rejoindre ou créer une entreprise
        </h1>
        <p class="text-gray-500 text-center mb-8" style="font-size:15px;">
            Recherchez une entreprise existante ou créez-en une nouvelle via son SIRET.
        </p>

        {{-- Server-side errors --}}
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl flex items-start gap-3" style="background:#FEF2F2;border:1px solid #FECACA;">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 rounded-xl text-sm text-red-700" style="background:#FEF2F2;border:1px solid #FECACA;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('company.store') }}" method="POST" id="companyForm">
            @csrf
            <input type="hidden" name="existing_company_id" id="existing_company_id">

            {{-- ──────────── SECTION 1: Search existing ──────────── --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Rechercher une entreprise existante
                </label>
                <div class="relative">
                    <input type="text" id="company_search" autocomplete="off"
                        placeholder="Tapez au moins 3 lettres…"
                        class="lx-input px-4 py-3.5 pr-12">
                    <div id="search-spinner" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                        <svg class="w-5 h-5 text-teal-500 spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>
                    {{-- Autocomplete dropdown — inside relative so it appears directly below the input --}}
                    <div id="autocomplete-results"
                         class="hidden absolute left-0 right-0 top-full z-20 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-52 overflow-y-auto">
                    </div>
                </div>
            </div>

            {{-- ──────────── Selected existing company banner ──────────── --}}
            <div id="selected-info"
                 class="hidden mb-6 p-4 rounded-xl fade-in"
                 style="background:#ECFDF5;border:1.5px solid #6EE7B7;">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-emerald-900 mb-1">Entreprise sélectionnée</p>
                        <p class="text-sm text-emerald-800"><strong>Nom :</strong> <span id="sel-name"></span></p>
                        <p class="text-sm text-emerald-800"><strong>SIRET :</strong> <span id="sel-siret"></span></p>
                        <p class="text-sm text-emerald-800"><strong>Secteur :</strong> <span id="sel-sector"></span></p>
                    </div>
                    <button type="button" onclick="clearSelection()" class="text-emerald-600 hover:text-emerald-800 flex-shrink-0">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                {{-- Position field shown when joining an existing company --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-emerald-900 mb-2" for="position_join">
                        Votre poste dans cette entreprise
                        <span class="text-emerald-600 font-normal">(optionnel)</span>
                    </label>
                    <input type="text" id="position_join" name="position"
                        placeholder="ex : Directeur Commercial, Chef de Projet…"
                        value="{{ old('position') }}"
                        class="w-full px-4 py-3 bg-white border border-emerald-200 rounded-xl text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-transparent transition-all">
                </div>
            </div>

            {{-- ──────────── Divider ──────────── --}}
            <div id="divider" class="flex items-center gap-3 my-6 text-xs text-gray-400 uppercase tracking-wider">
                <span class="flex-1 h-px bg-gray-200"></span>
                ou créez-en une nouvelle
                <span class="flex-1 h-px bg-gray-200"></span>
            </div>

            {{-- ──────────── SECTION 2: New company via SIRET ──────────── --}}
            <div id="new-company-section">

                {{-- SIRET field --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="siret">
                        Numéro SIRET
                        <span class="text-gray-400 font-normal ml-1">(14 chiffres)</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="siret" name="siret"
                            placeholder="12345678901234"
                            maxlength="14"
                            inputmode="numeric"
                            value="{{ old('siret') }}"
                            class="lx-input px-4 py-3.5 pr-14 font-mono tracking-wider @error('siret') lx-error @enderror">
                        {{-- Lookup spinner --}}
                        <div id="siret-spinner" class="hidden absolute right-4 top-1/2 -translate-y-1/2">
                            <svg class="w-5 h-5 text-teal-500 spinner" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                    </div>
                    {{-- SIRET status message --}}
                    <div id="siret-status" class="mt-2 hidden"></div>
                    @error('siret') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Auto-filled company info (shown after SIRET found) --}}
                <div id="siret-result" class="hidden fade-in space-y-4">

                    {{-- Company name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="name">
                            Nom de l'entreprise
                        </label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name') }}"
                            placeholder="Nom de l'entreprise"
                            class="lx-input px-4 py-3.5 @error('name') lx-error @enderror">
                        @error('name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Sector --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="sector_id">
                            Secteur d'activité
                        </label>
                        <select id="sector_id" name="sector_id"
                            class="lx-input px-4 py-3.5 @error('sector_id') lx-error @enderror">
                            <option value="">Sélectionnez un secteur</option>
                            @foreach ($sectors as $sector)
                                <option value="{{ $sector->id }}"
                                    {{ old('sector_id') == $sector->id ? 'selected' : '' }}
                                    data-name="{{ $sector->name }}">
                                    {{ $sector->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sector_id') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Website --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="website">
                            Site web <span class="text-gray-400 font-normal">(optionnel)</span>
                        </label>
                        <input type="url" id="website" name="website"
                            placeholder="https://example.com"
                            value="{{ old('website') }}"
                            class="lx-input px-4 py-3.5 @error('website') lx-error @enderror">
                        @error('website') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Position --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="position">
                            Votre poste dans cette entreprise
                            <span class="text-gray-400 font-normal">(optionnel)</span>
                        </label>
                        <input type="text" id="position" name="position"
                            placeholder="ex : Directeur Commercial, Chef de Projet…"
                            value="{{ old('position') }}"
                            class="lx-input px-4 py-3.5 @error('position') lx-error @enderror">
                        @error('position') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- INSEE verified badge --}}
                    <div class="flex items-center gap-2 text-xs text-emerald-700 font-medium"
                         style="background:#ECFDF5;border:1px solid #6EE7B7;border-radius:10px;padding:8px 12px;">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Établissement vérifié par le registre INSEE
                    </div>

                </div>

            </div>

            {{-- ──────────── Action buttons ──────────── --}}
            <div class="flex gap-3 mt-8">
                <a href="{{ route('dashboard') }}"
                   class="flex-1 text-center font-semibold py-4 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors">
                    Plus tard
                </a>
                <button type="submit" id="submit-btn" disabled
                    class="flex-1 gradient-button text-white font-semibold py-4 rounded-xl uppercase tracking-wider transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                    <span id="submit-text">Créer</span>
                </button>
            </div>
        </form>
    </div>

    <p class="text-center mt-6 text-sm text-gray-400">
        Tapez 3 lettres pour trouver une entreprise · ou entrez son SIRET pour en créer une
    </p>
</div>

<script>
    window._oldSiretVerified = {{ (old('siret') && strlen(old('siret')) === 14 && !$errors->has('siret')) ? 'true' : 'false' }};
</script>
<script>
// ──────────────────────────────────────────────
// State
// ──────────────────────────────────────────────
let searchTimer;
let siretTimer;
let siretVerified = false;
let joiningExisting = false;

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// DOM refs
const searchInput       = document.getElementById('company_search');
const autoResults       = document.getElementById('autocomplete-results');
const searchSpinner     = document.getElementById('search-spinner');
const selectedInfo      = document.getElementById('selected-info');
const divider           = document.getElementById('divider');
const newSection        = document.getElementById('new-company-section');
const siretInput        = document.getElementById('siret');
const siretSpinner      = document.getElementById('siret-spinner');
const siretStatus       = document.getElementById('siret-status');
const siretResult       = document.getElementById('siret-result');
const nameInput         = document.getElementById('name');
const sectorSelect      = document.getElementById('sector_id');
const existingIdInput   = document.getElementById('existing_company_id');
const submitBtn         = document.getElementById('submit-btn');
const submitText        = document.getElementById('submit-text');

// ──────────────────────────────────────────────
// Company name autocomplete (local DB)
// ──────────────────────────────────────────────
searchInput.addEventListener('input', function () {
    const q = this.value.trim();
    clearTimeout(searchTimer);
    autoResults.classList.add('hidden');

    if (q.length < 3) { searchSpinner.classList.add('hidden'); return; }

    searchSpinner.classList.remove('hidden');
    searchTimer = setTimeout(() => fetchCompanies(q), 300);
});

function fetchCompanies(q) {
    fetch(`{{ route('company.search') }}?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        searchSpinner.classList.add('hidden');
        renderResults(data);
    })
    .catch(() => searchSpinner.classList.add('hidden'));
}

function renderResults(companies) {
    if (companies.length === 0) {
        autoResults.innerHTML = `
            <div class="px-4 py-3 text-sm text-gray-500 text-center">
                Aucune entreprise trouvée. Créez-en une nouvelle ci-dessous.
            </div>`;
    } else {
        autoResults.innerHTML = '<div class="py-1">' + companies.map(c => `
            <div onclick='selectCompany(${JSON.stringify(c)})'
                 class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors border-b border-gray-100 last:border-0">
                <p class="font-semibold text-gray-900 text-sm">${c.name}</p>
                <p class="text-xs text-gray-500 mt-0.5">SIRET&nbsp;${c.siret} · ${c.sector}</p>
            </div>`).join('') + '</div>';
    }

    autoResults.classList.remove('hidden');
}

function selectCompany(company) {
    joiningExisting = true;
    siretVerified   = false;

    searchInput.value      = company.name;
    existingIdInput.value  = company.id;

    document.getElementById('sel-name').textContent   = company.name;
    document.getElementById('sel-siret').textContent  = company.siret;
    document.getElementById('sel-sector').textContent = company.sector;

    selectedInfo.classList.remove('hidden');
    divider.classList.add('hidden');
    newSection.classList.add('hidden');
    autoResults.classList.add('hidden');

    submitText.textContent  = 'Rejoindre';
    submitBtn.disabled      = false;
}

function clearSelection() {
    joiningExisting       = false;
    existingIdInput.value = '';

    selectedInfo.classList.add('hidden');
    divider.classList.remove('hidden');
    newSection.classList.remove('hidden');
    searchInput.value = '';
    searchInput.focus();

    submitText.textContent = 'Créer';
    submitBtn.disabled     = !siretVerified;
}

// ──────────────────────────────────────────────
// SIRET lookup (INSEE API via backend proxy)
// ──────────────────────────────────────────────
siretInput.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, ''); // digits only
    const val  = this.value;

    clearTimeout(siretTimer);
    hideSiretResult();

    if (val.length === 14) {
        showBadge('checking', 'Vérification en cours…');
        siretSpinner.classList.remove('hidden');
        siretTimer = setTimeout(() => lookupSiret(val), 500);
    } else {
        siretStatus.classList.add('hidden');
        siretSpinner.classList.add('hidden');
        siretVerified = false;
        submitBtn.disabled = true;
    }
});

function lookupSiret(siret) {
    fetch(`{{ route('company.siret-lookup') }}?siret=${siret}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json().then(data => ({ status: r.status, data })))
    .then(({ status, data }) => {
        siretSpinner.classList.add('hidden');

        if (status === 200 && data.found) {
            if (!data.active) {
                showBadge('inactive', `Établissement ${data.siret} est fermé (radiation INSEE)`);
                siretVerified  = false;
                submitBtn.disabled = true;
                return;
            }

            // ✅ Found & active — auto-fill
            showBadge('found', `Établissement trouvé · ${data.siren}`);
            nameInput.value = data.name ?? '';
            autoSelectSector(data.sector_suggestion);
            siretResult.classList.remove('hidden');
            siretVerified      = true;
            submitBtn.disabled = false;

        } else if (status === 404 || (data && data.found === false)) {
            hideSiretResult();
            showBadge('warning', data.message ?? 'Cet établissement n\'existe pas dans le registre INSEE — création impossible');
            siretVerified  = false;
            submitBtn.disabled = true;

        } else {
            showBadge('error', data.error ?? 'Erreur technique lors de la vérification du SIRET');
            siretVerified  = false;
            submitBtn.disabled = true;
        }
    })
    .catch(() => {
        siretSpinner.classList.add('hidden');
        showBadge('error', 'Impossible de contacter le registre INSEE');
        siretVerified  = false;
        submitBtn.disabled = true;
    });
}

function showBadge(type, message) {
    const classes = {
        checking: 'badge-checking',
        found:    'badge-found',
        error:    'badge-error',
        warning:  'badge-warning',
        inactive: 'badge-inactive',
    };
    const icons = {
        checking: `<svg class="w-3.5 h-3.5 spinner" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>`,
        found:    `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>`,
        warning:  `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`,
        error:    `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>`,
        inactive: `<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`,
    };

    siretStatus.className = `siret-badge ${classes[type]} mt-2`;
    siretStatus.innerHTML = `${icons[type]} <span>${message}</span>`;
    siretStatus.classList.remove('hidden');
}

function hideSiretResult() {
    siretResult.classList.add('hidden');
    nameInput.value = '';
}

function autoSelectSector(sectorName) {
    if (!sectorName) return;
    const opt = Array.from(sectorSelect.options)
        .find(o => o.dataset.name && o.dataset.name.toLowerCase() === sectorName.toLowerCase());
    if (opt) sectorSelect.value = opt.value;
}

// ──────────────────────────────────────────────
// Close autocomplete on outside click
// ──────────────────────────────────────────────
document.addEventListener('click', function (e) {
    if (!searchInput.contains(e.target) && !autoResults.contains(e.target)) {
        autoResults.classList.add('hidden');
    }
});

// Re-enable button if returning from server error (old siret was already verified)
if (window._oldSiretVerified) {
    window.addEventListener('DOMContentLoaded', function () {
        siretResult.classList.remove('hidden');
        siretVerified      = true;
        submitBtn.disabled = false;
        showBadge('found', 'SIRET précédemment vérifié');
    });
}
</script>

</body>
</html>
