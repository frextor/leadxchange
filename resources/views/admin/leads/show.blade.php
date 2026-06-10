@extends('admin.layouts.admin')

@section('title', 'Lead — ' . $lead->company_name)
@section('page-title', $lead->company_name)
@section('page-subtitle', 'Détail du lead #' . $lead->id)

@section('content')
<div class="grid gap-5 lg:grid-cols-[1fr_320px] items-start">

    {{-- LEFT --}}
    <div class="space-y-4">

        {{-- Main info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            @if($lead->fraud_reported)
            <div class="mb-4 px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2" style="background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Fraude signalée — {{ $lead->fraud_reason ?? 'Raison non précisée' }}
            </div>
            @endif

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Contact</p>
                    <div class="space-y-2 text-sm">
                        <div><span class="text-gray-400">Entreprise :</span> <span class="font-medium text-gray-900">{{ $lead->company_name }}</span></div>
                        <div><span class="text-gray-400">Nom :</span> <span class="font-medium text-gray-900">{{ $lead->contact_name }}</span></div>
                        @if($lead->contact_email)<div><span class="text-gray-400">Email :</span> <a href="mailto:{{ $lead->contact_email }}" class="text-teal-600 hover:underline">{{ $lead->contact_email }}</a></div>@endif
                        @if($lead->contact_phone)<div><span class="text-gray-400">Tél :</span> <span class="font-medium text-gray-900">{{ $lead->contact_phone }}</span></div>@endif
                        @if($lead->contact_position)<div><span class="text-gray-400">Poste :</span> <span class="font-medium text-gray-900">{{ $lead->contact_position }}</span></div>@endif
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Lead</p>
                    <div class="space-y-2 text-sm">
                        @if($lead->sector)<div><span class="text-gray-400">Secteur :</span> <span class="font-medium text-gray-900">{{ $lead->sector->name }}</span></div>@endif
                        @if($lead->qualification)
                        @php $qc = ['chaud'=>'#DC2626','tiede'=>'#D97706','froid'=>'#1D4ED8']; @endphp
                        <div><span class="text-gray-400">Qualification :</span> <span class="font-semibold capitalize" style="color:{{ $qc[$lead->qualification] ?? '#6B7280' }};">{{ $lead->qualification }}</span></div>
                        @endif
                        @if($lead->deadline)<div><span class="text-gray-400">Deadline :</span> <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($lead->deadline)->format('d/m/Y') }}</span></div>@endif
                        <div><span class="text-gray-400">Créé le :</span> <span class="font-medium text-gray-900">{{ $lead->created_at->format('d/m/Y H:i') }}</span></div>
                    </div>
                </div>
            </div>

            @if($lead->description)
            <div class="mt-5 pt-5 border-t border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Description</p>
                <p class="text-sm text-gray-700">{{ $lead->description }}</p>
            </div>
            @endif
        </div>

        {{-- Ratings --}}
        @if($lead->ratings->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Notations ({{ $lead->ratings->count() }})</p>
            @foreach($lead->ratings as $rating)
            <div class="border border-gray-100 rounded-lg p-4 mb-3 last:mb-0">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-900">{{ $rating->rater?->first_name }} {{ $rating->rater?->last_name }}</span>
                    <span class="text-lg font-bold" style="color:#1E8F88;">{{ number_format($rating->average_note, 1) }}/5</span>
                </div>
                <div class="grid grid-cols-3 gap-3 text-xs">
                    <div class="text-center p-2 bg-gray-50 rounded-lg"><p class="text-gray-400">Qualité</p><p class="font-bold text-gray-900 mt-1">{{ $rating->quality }}/5</p></div>
                    <div class="text-center p-2 bg-gray-50 rounded-lg"><p class="text-gray-400">Pertinence</p><p class="font-bold text-gray-900 mt-1">{{ $rating->relevance }}/5</p></div>
                    <div class="text-center p-2 bg-gray-50 rounded-lg"><p class="text-gray-400">Réactivité</p><p class="font-bold text-gray-900 mt-1">{{ $rating->reactivity }}/5</p></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- RIGHT --}}
    <div class="space-y-4">

        {{-- Status --}}
        @php
            $statusMap = ['new'=>['Nouveau','#FEF3C7','#92400E'],'accepted'=>['Accepté','#ECFDF5','#065F46'],'rejected'=>['Refusé','#FEF2F2','#991B1B'],'converted'=>['Converti','#E0F2FE','#0C4A6E'],'expired'=>['Expiré','#F9FAFB','#6B7280']];
            [$sLabel,$sBg,$sTxt] = $statusMap[$lead->status] ?? ['—','#F9FAFB','#6B7280'];
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Statut</p>
            <span class="px-3 py-1.5 rounded-full text-sm font-semibold" style="background:{{ $sBg }};color:{{ $sTxt }};">{{ $sLabel }}</span>
            @if($lead->points_deducted)
            <p class="text-xs text-red-500 mt-2">Points déduits après 15j sans notation</p>
            @endif
        </div>

        {{-- Sender --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Expéditeur</p>
            @if($lead->sender)
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">{{ strtoupper(substr($lead->sender->first_name,0,1).substr($lead->sender->last_name,0,1)) }}</div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $lead->sender->first_name }} {{ $lead->sender->last_name }}</p>
                    <a href="{{ route('admin.users.show', $lead->sender) }}" class="text-xs text-teal-600 hover:underline">Voir le profil →</a>
                </div>
            </div>
            @endif
        </div>

        {{-- Receiver --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Destinataire</p>
            @if($lead->receiver)
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:linear-gradient(135deg,#34d4bf,#1E8F88);">{{ strtoupper(substr($lead->receiver->first_name,0,1).substr($lead->receiver->last_name,0,1)) }}</div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $lead->receiver->first_name }} {{ $lead->receiver->last_name }}</p>
                    <a href="{{ route('admin.users.show', $lead->receiver) }}" class="text-xs text-teal-600 hover:underline">Voir le profil →</a>
                </div>
            </div>
            @endif
        </div>

        <a href="{{ route('admin.leads.index') }}" class="block text-center py-2.5 rounded-lg text-sm font-medium text-gray-500 border border-gray-200 hover:bg-gray-50">← Retour à la liste</a>
    </div>
</div>
@endsection
