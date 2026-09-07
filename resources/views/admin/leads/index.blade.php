@extends('admin.layouts.admin')

@section('title', 'Leads')
@section('page-title', 'Leads')
@section('page-subtitle', $leads->total() . ' leads au total')

@section('content')

{{-- KPI --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    @foreach([['Total', $counts['total'], '#2F44E0'],['En attente', $counts['pending'], '#D97706'],['Convertis', $counts['converted'], '#059669'],['Fraudes', $counts['fraud'], '#DC2626']] as [$label,$val,$color])
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
        <p class="text-xs font-medium text-gray-400">{{ $label }}</p>
        <p class="text-2xl font-bold mt-1" style="color:{{ $color }};">{{ $val }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Recherche</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Entreprise, contact, email…"
               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Statut</label>
        <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            <option value="">Tous</option>
            @foreach(['new'=>'Nouveau','accepted'=>'Accepté','rejected'=>'Refusé','converted'=>'Converti','expired'=>'Expiré'] as $val=>$label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Qualification</label>
        <select name="qualification" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            <option value="">Toutes</option>
            <option value="chaud"  {{ request('qualification') === 'chaud'  ? 'selected' : '' }}>Chaud</option>
            <option value="tiede"  {{ request('qualification') === 'tiede'  ? 'selected' : '' }}>Tiède</option>
            <option value="froid"  {{ request('qualification') === 'froid'  ? 'selected' : '' }}>Froid</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Secteur</label>
        <select name="sector_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-teal-400">
            <option value="">Tous</option>
            @foreach($sectors as $s)
            <option value="{{ $s->id }}" {{ request('sector_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-center gap-2 pb-0.5">
        <input type="checkbox" name="fraud" value="1" id="fraud" {{ request('fraud') ? 'checked' : '' }} class="rounded">
        <label for="fraud" class="text-sm font-medium text-red-600">Fraudes only</label>
    </div>
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:#2F44E0;">Filtrer</button>
        @if(request()->hasAny(['search','status','qualification','sector_id','fraud']))
        <a href="{{ route('admin.leads.index') }}" class="px-4 py-2 rounded-lg text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50">Reset</a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                <th class="px-5 py-3 text-left">Contact</th>
                <th class="px-4 py-3 text-left">Expéditeur → Destinataire</th>
                <th class="px-4 py-3 text-left">Secteur</th>
                <th class="px-4 py-3 text-left">Qualif.</th>
                <th class="px-4 py-3 text-left">Statut</th>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($leads as $lead)
            @php
                $statusMap = ['new'=>['Nouveau','#FEF3C7','#92400E'],'accepted'=>['Accepté','#ECFDF5','#065F46'],'rejected'=>['Refusé','#FEF2F2','#991B1B'],'converted'=>['Converti','#E0F2FE','#0C4A6E'],'expired'=>['Expiré','#F9FAFB','#6B7280']];
                [$sLabel,$sBg,$sTxt] = $statusMap[$lead->status] ?? ['—','#F9FAFB','#6B7280'];
                $qualColors = ['chaud'=>'#FEF2F2:#DC2626','tiede'=>'#FEF3C7:#D97706','froid'=>'#EFF6FF:#1D4ED8'];
            @endphp
            <tr class="hover:bg-gray-50 transition {{ $lead->fraud_reported ? 'bg-red-50' : '' }}">
                <td class="px-5 py-3">
                    <div class="font-medium text-gray-900">{{ $lead->company_name }}</div>
                    <div class="text-xs text-gray-400">{{ $lead->contact_name }}</div>
                    @if($lead->fraud_reported)
                    <span class="text-[10px] font-bold text-red-600">⚠ Fraude signalée</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    <span class="font-medium">{{ $lead->sender?->first_name }} {{ $lead->sender?->last_name }}</span>
                    <span class="text-gray-400 mx-1">→</span>
                    <span class="font-medium">{{ $lead->receiver?->first_name }} {{ $lead->receiver?->last_name }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ $lead->sector?->name ?? '—' }}</td>
                <td class="px-4 py-3">
                    @if($lead->qualification)
                    @php [$qBg,$qTxt] = explode(':', $qualColors[$lead->qualification] ?? '#F9FAFB:#6B7280'); @endphp
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold capitalize" style="background:{{ $qBg }};color:{{ $qTxt }};">{{ $lead->qualification }}</span>
                    @else
                    <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-[11px] font-semibold" style="background:{{ $sBg }};color:{{ $sTxt }};">{{ $sLabel }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-400">{{ $lead->created_at->format('d/m/Y') }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('admin.leads.show', $lead) }}" class="text-xs font-medium text-teal-600 hover:underline">Voir</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-400">Aucun lead trouvé.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($leads->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $leads->links() }}</div>
    @endif
</div>
@endsection
