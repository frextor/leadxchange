@extends('admin.layouts.admin')
@section('title', 'Notation')
@section('page-title', 'Notation')

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Administration</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Notation des membres</h1>
        <p class="text-sm text-gray-400 mt-1">Scores calculés sur les 60 derniers jours glissants.</p>
    </div>
    <form method="POST" action="{{ route('admin.notation.recalculate-all') }}">
        @csrf
        <button type="submit"
                class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                style="background:#4338CA;"
                onclick="return confirm('Recalculer les scores de tous les membres ?')">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            Recalculer tout
        </button>
    </form>
</div>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-3 text-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="m9 11 3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- ── Configuration des seuils de badges ──────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-sm font-bold text-gray-900">Seuils des badges</p>
            <p class="text-xs text-gray-400 mt-0.5">Définissez le nombre de points minimum pour obtenir chaque badge.</p>
        </div>
        <form method="POST" action="{{ route('admin.notation.thresholds') }}" id="thresholds-form" class="hidden">@csrf</form>
    </div>
    <div class="grid grid-cols-4 gap-4">
        @php
        $badgeDefs = [
            'bronze'    => ['label' => 'Bronze',    'color' => 'amber',  'key' => 'badge_bronze_min'],
            'argent'    => ['label' => 'Argent',    'color' => 'slate',  'key' => 'badge_argent_min'],
            'or'        => ['label' => 'Or',        'color' => 'yellow', 'key' => 'badge_or_min'],
            'platinium' => ['label' => 'Platinium', 'color' => 'indigo', 'key' => 'badge_platinium_min'],
        ];
        $colorMap = [
            'amber'  => ['bg' => 'bg-amber-50',  'border' => 'border-amber-200',  'text' => 'text-amber-700',  'ring' => 'focus:ring-amber-100'],
            'slate'  => ['bg' => 'bg-slate-50',  'border' => 'border-slate-200',  'text' => 'text-slate-600',  'ring' => 'focus:ring-slate-100'],
            'yellow' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'ring' => 'focus:ring-yellow-100'],
            'indigo' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-indigo-700', 'ring' => 'focus:ring-indigo-100'],
        ];
        @endphp

        @foreach($badgeDefs as $bKey => $bDef)
        @php $c = $colorMap[$bDef['color']]; $current = $thresholds[$bKey] ?? 0; @endphp
        <div class="{{ $c['bg'] }} border {{ $c['border'] }} rounded-2xl p-4 flex flex-col items-center gap-3">
            <div class="text-center">
                <p class="text-xs font-bold {{ $c['text'] }} uppercase tracking-widest">{{ $bDef['label'] }}</p>
                <p class="text-[10px] text-gray-400 mt-0.5">Score minimum</p>
            </div>
            <input type="number"
                   name="{{ $bDef['key'] }}"
                   form="thresholds-form"
                   value="{{ $current }}"
                   min="1"
                   class="w-24 text-center text-2xl font-extrabold {{ $c['text'] }} {{ $c['bg'] }} border-2 {{ $c['border'] }} rounded-xl px-3 py-2 focus:outline-none {{ $c['ring'] }} focus:ring-2 transition">
            <p class="text-[10px] text-gray-400">pts</p>
        </div>
        @endforeach
    </div>
    <div class="mt-4 flex items-center gap-3">
        <button type="submit" form="thresholds-form"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition hover:opacity-90"
                style="background:#4338CA;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 11 3 3L22 4"/></svg>
            Enregistrer et recalculer
        </button>
        <p class="text-xs text-gray-400">Les scores de tous les membres seront recalculés avec les nouveaux seuils.</p>
    </div>
</div>

{{-- Légende formule --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap items-center gap-4 text-xs">
    <p class="font-bold text-gray-500 uppercase tracking-widest text-[10px]">Formule (60j)</p>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 text-teal-700 font-semibold">Lead envoyé <span class="bg-teal-200 rounded-lg px-1.5">+2</span></span>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-red-50 text-red-600 font-semibold">Lead reçu <span class="bg-red-200 rounded-lg px-1.5">-1</span></span>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 font-semibold">MQL <span class="bg-blue-200 rounded-lg px-1.5">+1</span></span>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 font-semibold">SQL <span class="bg-indigo-200 rounded-lg px-1.5">+3</span></span>
    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-violet-50 text-violet-700 font-semibold">SP <span class="bg-violet-200 rounded-lg px-1.5">+5</span></span>
    <div class="ml-auto flex items-center gap-2 text-[10px] font-semibold">
        @foreach($badges as $key => $b)
        <span class="px-2 py-0.5 rounded-full {{ $b['color'] }}">{{ $b['label'] }} ({{ $b['min'] }}{{ $b['max'] ? '–'.$b['max'] : '+' }})</span>
        @endforeach
    </div>
</div>

{{-- Filtres --}}
<form method="GET" class="flex items-center gap-3 mb-5">
    <div class="relative flex-1 max-w-xs">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-300" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email…"
               class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition">
    </div>
    <select name="badge" onchange="this.form.submit()"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white text-gray-700 focus:outline-none focus:border-indigo-400 transition">
        <option value="">Tous les badges</option>
        @foreach($badges as $key => $b)
        <option value="{{ $key }}" {{ request('badge') === $key ? 'selected' : '' }}>{{ $b['label'] }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:#4338CA;">Filtrer</button>
    @if(request()->hasAny(['search','badge']))
    <a href="{{ route('admin.notation.index') }}" class="px-3 py-2 rounded-xl text-sm text-gray-500 border border-gray-200 hover:bg-gray-50 transition">Reset</a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50 text-left">
                    <th class="px-5 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Membre</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Badge</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Score</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center" colspan="3">Leads envoyés (60j)</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Reçus</th>
                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider text-center">Actions</th>
                </tr>
                <tr class="border-b border-gray-50 bg-gray-50/50">
                    <th></th><th></th><th></th>
                    <th class="px-4 pb-2 text-[9px] font-bold text-blue-400 uppercase tracking-wider text-center">MQL</th>
                    <th class="px-4 pb-2 text-[9px] font-bold text-indigo-400 uppercase tracking-wider text-center">SQL</th>
                    <th class="px-4 pb-2 text-[9px] font-bold text-violet-400 uppercase tracking-wider text-center">SP</th>
                    <th></th><th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                @php
                    $d   = $details[$user->id] ?? null;
                    $rec = $received[$user->id] ?? 0;
                    $badge = $badges[$user->badge_level] ?? $badges['neutre'];
                @endphp
                <tr class="hover:bg-gray-50/50 transition group">
                    {{-- Membre --}}
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:linear-gradient(135deg,#6366F1,#4338CA);">
                                {{ strtoupper(substr($user->first_name,0,1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Badge actuel --}}
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $badge['color'] }}">
                            {{ $badge['label'] }}
                        </span>
                    </td>

                    {{-- Score --}}
                    <td class="px-4 py-3 text-center">
                        <span class="text-lg font-extrabold {{ $user->points_balance >= 20 ? 'text-indigo-600' : ($user->points_balance >= 10 ? 'text-teal-600' : 'text-gray-700') }}">
                            {{ $user->points_balance }}
                        </span>
                    </td>

                    {{-- MQL / SQL / SP --}}
                    <td class="px-4 py-3 text-center text-sm font-semibold text-blue-600">{{ $d?->mql ?? 0 }}</td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-indigo-600">{{ $d?->sql_count ?? 0 }}</td>
                    <td class="px-4 py-3 text-center text-sm font-semibold text-violet-600">{{ $d?->sp ?? 0 }}</td>

                    {{-- Reçus --}}
                    <td class="px-4 py-3 text-center text-sm font-semibold text-red-500">{{ $rec }}</td>

                    {{-- Actions --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-end">
                            {{-- Recalculer --}}
                            <form method="POST" action="{{ route('admin.notation.recalculate', $user) }}">
                                @csrf
                                <button type="submit" title="Recalculer"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-teal-600 hover:bg-teal-50 transition">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                </button>
                            </form>

                            {{-- Modifier --}}
                            <button type="button" onclick="openEdit({{ $user->id }}, {{ $user->points_balance }}, '{{ $user->badge_level }}')"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400">Aucun membre trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $users->links() }}</div>
    @endif
</div>

{{-- Modal édition --}}
<div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6">
        <h3 class="text-base font-bold text-gray-900 mb-5">Modifier la notation</h3>
        <form id="edit-form" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Score (points)</label>
                    <input type="number" name="points_balance" id="edit-points"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 transition font-mono text-center text-lg font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Badge</label>
                    <select name="badge_level" id="edit-badge"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50 bg-white">
                        @foreach($badges as $key => $b)
                        <option value="{{ $key }}">{{ $b['label'] }} ({{ $b['min'] }}{{ $b['max'] ? '–'.$b['max'] : '+' }} pts)</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition"
                        style="background:#4338CA;">Enregistrer</button>
                <button type="button" onclick="closeEdit()"
                        class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50 transition">Annuler</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const editRouteBase = '{{ url("/admin/notation") }}';

function openEdit(userId, points, badge) {
    document.getElementById('edit-form').action = editRouteBase + '/' + userId;
    document.getElementById('edit-points').value = points;
    document.getElementById('edit-badge').value  = badge;
    const modal = document.getElementById('edit-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeEdit() {
    const modal = document.getElementById('edit-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEdit(); });
</script>
@endpush
