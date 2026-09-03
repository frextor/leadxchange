@extends('admin.layouts.admin')
@section('title', 'Menu administration')
@section('page-title', 'Menu d\'administration')

@push('styles')
<style>
.menu-item {
    display: flex; align-items: center; gap: 12px;
    padding: 11px 14px; background: #fff;
    border: 1px solid #e5e7eb; border-radius: 12px;
    cursor: grab; transition: box-shadow .15s, border-color .15s, opacity .15s;
    user-select: none;
}
.menu-item:active { cursor: grabbing; }
.menu-item label, .menu-item input { cursor: pointer; }
.menu-item.sortable-ghost { opacity:.35; border:2px dashed #6366F1; background:#EEF2FF; }
.menu-item.sortable-chosen { box-shadow:0 6px 20px rgba(99,102,241,.15); border-color:#6366F1; }
.drag-handle { color:#d1d5db; flex-shrink:0; transition:color .15s; }
.menu-item:hover .drag-handle { color:#9ca3af; }
.toggle-switch { position:relative; width:38px; height:20px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-track {
    position:absolute; inset:0; border-radius:999px; background:#E5E7EB;
    transition:background .2s; cursor:pointer;
}
.toggle-track::after {
    content:''; position:absolute; top:2px; left:2px;
    width:16px; height:16px; border-radius:50%; background:#fff;
    box-shadow:0 1px 3px rgba(0,0,0,.2); transition:transform .2s;
}
.toggle-switch input:checked + .toggle-track { background:#6366F1; }
.toggle-switch input:checked + .toggle-track::after { transform:translateX(18px); }
.sortable-list { display:flex; flex-direction:column; gap:6px; min-height:40px; }
.section-tabs { display:flex; gap:6px; margin-bottom:20px; }
.section-tab {
    flex:1; padding:10px 16px; border-radius:12px; border:1.5px solid #e5e7eb;
    font-size:13px; font-weight:600; color:#64748B; background:#F8FAFC;
    cursor:pointer; transition:all .15s; text-align:center;
}
.section-tab.active { background:#EEF2FF; border-color:#6366F1; color:#4338CA; }
.section-panel { display:none; }
.section-panel.active { display:block; }
</style>
@endpush

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm">
    <span class="text-gray-400">Paramètres</span>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-gray-700 font-semibold">Menu d'administration</span>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm bg-emerald-50 text-emerald-700 border border-emerald-100 mb-6">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="max-w-2xl">

    <div class="flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm bg-indigo-50 border border-indigo-100 text-indigo-700 mb-6">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <p>Glissez les éléments pour réordonner chaque section. Désactivez le toggle pour masquer un item dans la sidebar.</p>
    </div>

    {{-- Section tabs --}}
    <div class="section-tabs">
        <button class="section-tab active" onclick="switchTab('plateforme', this)">
            🧭 Section Plateforme
        </button>
        <button class="section-tab" onclick="switchTab('superadmin', this)">
            ⚡ Section Super Admin
        </button>
    </div>

    {{-- ── PLATEFORME ── --}}
    <div class="section-panel active" id="panel-plateforme">
        <form method="POST" action="{{ route('admin.super.settings.admin-menu.update') }}" id="form-plateforme">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="plateforme">
            <input type="hidden" name="config_json" id="config_json_plateforme">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-800">Section Plateforme</span>
                    <span class="text-xs text-gray-400">Glissez-déposez pour réordonner</span>
                </div>
                <div class="p-4">
                    <ul class="sortable-list" id="list-plateforme">
                        @foreach($plat as $item)
                        <li class="menu-item" data-key="{{ $item['key'] }}">
                            <span class="drag-handle">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="5" r="1" fill="currentColor"/><circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="9" cy="19" r="1" fill="currentColor"/><circle cx="15" cy="5" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="19" r="1" fill="currentColor"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">{{ $item['label'] }}</p>
                                <p class="text-[10px] text-gray-400 font-mono">{{ $item['key'] }}</p>
                            </div>
                            <span class="item-pos text-[10px] font-bold text-gray-300 w-5 text-center"></span>
                            <label class="toggle-switch">
                                <input type="checkbox" class="vis-toggle" data-key="{{ $item['key'] }}"
                                       {{ ($item['visible'] ?? true) ? 'checked' : '' }}>
                                <span class="toggle-track"></span>
                            </label>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <button type="button" onclick="resetSection('plateforme')"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-white transition">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Réinitialiser
                    </button>
                    <button type="button" onclick="saveSection('plateforme')" class="flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── SUPER ADMIN ── --}}
    <div class="section-panel" id="panel-superadmin">
        <form method="POST" action="{{ route('admin.super.settings.admin-menu.update') }}" id="form-superadmin">
            @csrf @method('PUT')
            <input type="hidden" name="section" value="superadmin">
            <input type="hidden" name="config_json" id="config_json_superadmin">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-800">Section Super Admin</span>
                    <span class="text-xs text-gray-400">Glissez-déposez pour réordonner</span>
                </div>
                <div class="p-4">
                    <ul class="sortable-list" id="list-superadmin">
                        @foreach($sa as $item)
                        <li class="menu-item" data-key="{{ $item['key'] }}">
                            <span class="drag-handle">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="5" r="1" fill="currentColor"/><circle cx="9" cy="12" r="1" fill="currentColor"/><circle cx="9" cy="19" r="1" fill="currentColor"/><circle cx="15" cy="5" r="1" fill="currentColor"/><circle cx="15" cy="12" r="1" fill="currentColor"/><circle cx="15" cy="19" r="1" fill="currentColor"/></svg>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">{{ $item['label'] }}</p>
                                <p class="text-[10px] text-gray-400 font-mono">{{ $item['key'] }}</p>
                            </div>
                            <span class="item-pos text-[10px] font-bold text-gray-300 w-5 text-center"></span>
                            <label class="toggle-switch">
                                <input type="checkbox" class="vis-toggle" data-key="{{ $item['key'] }}"
                                       {{ ($item['visible'] ?? true) ? 'checked' : '' }}>
                                <span class="toggle-track"></span>
                            </label>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <button type="button" onclick="resetSection('superadmin')"
                            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-white transition">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                        Réinitialiser
                    </button>
                    <button type="button" onclick="saveSection('superadmin')" class="flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
const DEFAULTS = {
    plateforme: @json(array_column($plat, 'key')),
    superadmin: @json(array_column($sa,   'key')),
};

// Init sortables
['plateforme','superadmin'].forEach(section => {
    const list = document.getElementById('list-' + section);
    Sortable.create(list, {
        animation: 160,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        filter: '.vis-toggle, .toggle-switch, input, label',
        preventOnFilter: false,
        onEnd: () => updatePositions(section),
    });
    updatePositions(section);
});

function updatePositions(section) {
    document.querySelectorAll('#list-' + section + ' li').forEach((li, i) => {
        const pos = li.querySelector('.item-pos');
        if (pos) pos.textContent = '#' + (i + 1);
    });
}

function saveSection(section) {
    var items = [];
    var lis = document.querySelectorAll('#list-' + section + ' li');
    for (var i = 0; i < lis.length; i++) {
        var li = lis[i];
        var toggle = li.querySelector('.vis-toggle');
        items.push({ key: li.getAttribute('data-key'), visible: toggle ? toggle.checked : true });
    }
    if (items.length === 0) { alert('Erreur: liste vide.'); return; }
    document.getElementById('config_json_' + section).value = JSON.stringify(items);
    document.getElementById('form-' + section).submit();
}

function resetSection(section) {
    if (!confirm('Réinitialiser cette section dans l\'ordre par défaut ?')) return;
    const items = [...document.querySelectorAll('#list-' + section + ' li')];
    const list  = document.getElementById('list-' + section);
    DEFAULTS[section].forEach(key => {
        const li = items.find(el => el.dataset.key === key);
        if (li) {
            li.querySelector('.vis-toggle').checked = true;
            list.appendChild(li);
        }
    });
    updatePositions(section);
}

function switchTab(section, btn) {
    document.querySelectorAll('.section-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('panel-' + section).classList.add('active');
}
</script>
@endpush
