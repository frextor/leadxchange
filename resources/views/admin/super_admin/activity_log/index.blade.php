@extends('admin.layouts.admin')
@section('title', 'Journal d\'activité')
@section('page-title', 'Journal d\'activité')

@section('content')

{{-- Header --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Super Admin › Sécurité</p>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-3">
            Journal d'activité
            <span class="text-sm font-semibold px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-600">
                {{ number_format($total) }}
            </span>
        </h1>
        <p class="text-sm text-gray-400 mt-1">Traçabilité complète des actions utilisateurs, admin, paiements et sécurité.</p>
    </div>
    @if(request()->hasAny(['category','event','search','user_id','date_from','date_to']))
    <a href="{{ route('admin.super.activity-log.index') }}"
       class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Réinitialiser
    </a>
    @endif
</div>

{{-- Category tabs --}}
@php
$cat = request('category', 'all');
$tabs = [
    'all'      => ['label' => 'Tout', 'color' => ''],
    'security' => ['label' => 'Sécurité', 'color' => 'text-red-600'],
    'auth'     => ['label' => 'Authentification', 'color' => 'text-blue-600'],
    'admin'    => ['label' => 'Administration', 'color' => 'text-purple-600'],
    'payment'  => ['label' => 'Paiements', 'color' => 'text-emerald-600'],
];
@endphp
<div class="flex items-center gap-1 mb-5 bg-gray-100 rounded-xl p-1 w-fit">
    @foreach($tabs as $key => $tab)
    <a href="{{ request()->fullUrlWithQuery(['category' => $key === 'all' ? null : $key, 'page' => null]) }}"
       class="px-4 py-1.5 rounded-lg text-sm font-semibold transition {{ $cat === $key || ($key === 'all' && !$cat) ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }} {{ $tab['color'] }}">
        {{ $tab['label'] }}
    </a>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.super.activity-log.index') }}"
      class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
    @if(request('category'))
    <input type="hidden" name="category" value="{{ request('category') }}">
    @endif

    <div class="flex-1 min-w-[180px]">
        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Recherche</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Description, IP, nom, email…"
               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="min-w-[160px]">
        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Événement</label>
        <select name="event" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <option value="">— Tous —</option>
            @foreach($events as $evtKey => $evtCount)
            <option value="{{ $evtKey }}" {{ request('event') === $evtKey ? 'selected' : '' }}>
                {{ $evtKey }} ({{ $evtCount }})
            </option>
            @endforeach
        </select>
    </div>

    <div class="min-w-[130px]">
        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Depuis</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <div class="min-w-[130px]">
        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Jusqu'au</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-300">
    </div>

    <button type="submit"
            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold transition">
        Filtrer
    </button>
</form>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    @if($logs->isEmpty())
    <div class="py-16 text-center">
        <svg class="mx-auto mb-3 text-gray-300" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        <p class="text-gray-400 text-sm">Aucune entrée trouvée.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Date</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Utilisateur</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Événement</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Description</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">IP</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Détails</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($logs as $log)
                @php
                $category = $log->category();
                $badgeClass = match($category) {
                    'security' => 'bg-red-50 text-red-700 ring-1 ring-red-100',
                    'auth'     => 'bg-blue-50 text-blue-700 ring-1 ring-blue-100',
                    'admin'    => 'bg-purple-50 text-purple-700 ring-1 ring-purple-100',
                    'payment'  => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
                    default    => 'bg-gray-100 text-gray-600',
                };
                $rowHighlight = $category === 'security' ? 'bg-red-50/20' : '';
                @endphp
                <tr class="hover:bg-gray-50/50 transition {{ $rowHighlight }}">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-gray-600 text-xs font-mono">{{ $log->created_at->format('d/m/y') }}</span><br>
                        <span class="text-gray-400 text-xs font-mono">{{ $log->created_at->format('H:i:s') }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($log->causer)
                        <a href="{{ route('admin.users.show', $log->causer) }}"
                           class="font-semibold text-gray-800 hover:text-indigo-600 text-xs">
                            {{ $log->causer->first_name }} {{ $log->causer->last_name }}
                        </a>
                        <p class="text-gray-400 text-xs">{{ $log->causer->email }}</p>
                        @else
                        <span class="text-gray-400 text-xs italic">Système / Anonyme</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold font-mono {{ $badgeClass }}">
                            {{ $log->event }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-700 text-xs max-w-xs">
                        {{ $log->description }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-gray-400 text-xs font-mono">{{ $log->ip_address ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($log->properties)
                        <button type="button"
                                onclick="showProps({{ json_encode($log->properties) }})"
                                class="text-indigo-400 hover:text-indigo-600 transition" title="Voir les détails">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </button>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $logs->links() }}
    </div>
    @endif
    @endif
</div>

{{-- Properties modal (Swal) --}}
@push('scripts')
<script>
function showProps(props) {
    const lines = Object.entries(props).map(([k, v]) =>
        `<tr><td class="pr-4 py-0.5 font-mono text-xs text-gray-400 whitespace-nowrap align-top">${k}</td><td class="py-0.5 text-xs text-gray-800 break-all">${JSON.stringify(v)}</td></tr>`
    ).join('');

    Swal.fire({
        title: 'Propriétés',
        html: `<table class="w-full text-left">${lines}</table>`,
        confirmButtonText: 'Fermer',
        confirmButtonColor: '#4F46E5',
        customClass: { popup: 'swal-lx-popup', title: 'swal-lx-title' },
        width: 520,
    });
}
</script>
@endpush

@endsection
