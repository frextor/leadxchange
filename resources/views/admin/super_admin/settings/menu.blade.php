@extends('admin.layouts.admin')
@section('title', 'Paramétrage du menu')
@section('page-title', 'Menu de navigation')

@push('styles')
<style>
.menu-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px; background: #fff;
    border: 1.5px solid #E5E7EB; border-radius: 14px;
    transition: border-color .15s;
}
.menu-row:hover { border-color: #C7D2FE; }
.move-btn {
    width: 30px; height: 30px; border-radius: 8px;
    border: 1px solid #E5E7EB; background: #F8FAFC;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s; flex-shrink: 0;
    font-size: 14px; line-height: 1;
}
.move-btn:hover:not(:disabled) { background: #EEF2FF; border-color: #A5B4FC; }
.move-btn:disabled { opacity: .25; cursor: default; }
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
</style>
@endpush

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm">
    <span class="text-gray-400">Paramètres</span>
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-300"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-gray-700 font-semibold">Menu de navigation</span>
</div>

@if(session('success'))
<div class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm bg-emerald-50 text-emerald-700 border border-emerald-100 mb-6">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="max-w-xl">

    <div class="flex items-start gap-3 px-4 py-3.5 rounded-xl text-sm bg-indigo-50 border border-indigo-100 text-indigo-700 mb-6">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        Utilisez ↑ ↓ pour réordonner. Le toggle active/désactive l'élément dans le menu.
    </div>

    <form method="POST" action="{{ route('admin.super.settings.menu.update') }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <span class="text-sm font-bold text-gray-800">Éléments du menu</span>
            </div>

            <div class="p-4 space-y-2" id="menu-list">
                @foreach($items as $i => $item)
                <div class="menu-row" id="row-{{ $i }}">
                    {{-- Position number --}}
                    <span class="text-xs font-bold text-gray-300 w-5 text-center">{{ $i + 1 }}</span>

                    {{-- Move buttons --}}
                    <div class="flex flex-col gap-1">
                        <button type="button" class="move-btn" onclick="moveUp({{ $i }})"
                                {{ $i === 0 ? 'disabled' : '' }}>↑</button>
                        <button type="button" class="move-btn" onclick="moveDown({{ $i }})"
                                {{ $i === count($items) - 1 ? 'disabled' : '' }}>↓</button>
                    </div>

                    {{-- Hidden order input --}}
                    <input type="hidden" name="order[]" value="{{ $item['key'] }}">

                    {{-- Label --}}
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-800">{{ $item['label'] }}</p>
                        <p class="text-[11px] text-gray-400 font-mono">{{ $item['key'] }}</p>
                    </div>

                    {{-- Visibility toggle --}}
                    <label class="toggle-switch">
                        <input type="checkbox" name="visible[]" value="{{ $item['key'] }}"
                               {{ ($item['visible'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-track"></span>
                    </label>
                </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                        style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m5 12 5 5L20 7"/></svg>
                    Enregistrer
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function moveUp(idx) {
    var list  = document.getElementById('menu-list');
    var rows  = list.querySelectorAll('.menu-row');
    if (idx === 0) return;
    list.insertBefore(rows[idx], rows[idx - 1]);
    renumber();
}
function moveDown(idx) {
    var list = document.getElementById('menu-list');
    var rows = list.querySelectorAll('.menu-row');
    if (idx >= rows.length - 1) return;
    list.insertBefore(rows[idx + 1], rows[idx]);
    renumber();
}
function renumber() {
    var rows = document.querySelectorAll('#menu-list .menu-row');
    rows.forEach(function(row, i) {
        row.id = 'row-' + i;
        row.querySelector('span').textContent = i + 1;
        var btns = row.querySelectorAll('.move-btn');
        btns[0].disabled = (i === 0);
        btns[0].setAttribute('onclick', 'moveUp(' + i + ')');
        btns[1].disabled = (i === rows.length - 1);
        btns[1].setAttribute('onclick', 'moveDown(' + i + ')');
    });
}
</script>
@endpush
