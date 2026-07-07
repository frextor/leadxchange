@extends('ambassador.layouts.ambassador')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Activité des 30 derniers jours dans votre région')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- New members --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <span class="text-lg">👥</span>
            <h3 class="text-sm font-bold text-slate-800">Nouveaux membres ({{ $newMembers->count() }})</h3>
        </div>
        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($newMembers as $member)
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0"
                     style="background:linear-gradient(135deg,#2DD4BF,#14A98C);">
                    {{ strtoupper(substr($member->first_name,0,1)) }}{{ strtoupper(substr($member->last_name,0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-slate-800">{{ $member->first_name }} {{ $member->last_name }}</p>
                    <p class="text-[11px] text-slate-400">{{ $member->city?->name }} · {{ $member->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Nouveau</span>
            </div>
            @empty
            <div class="py-8 text-center text-sm text-slate-400">Aucun nouveau membre ces 30 derniers jours.</div>
            @endforelse
        </div>
    </div>

    {{-- Event registrations --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <span class="text-lg">📅</span>
            <h3 class="text-sm font-bold text-slate-800">Inscriptions événements ({{ $eventRegistrations->count() }})</h3>
        </div>
        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($eventRegistrations as $reg)
            <div class="px-5 py-3">
                <p class="text-[13px] font-semibold text-slate-800">{{ $reg->first_name }} {{ $reg->last_name }}</p>
                <p class="text-[11px] text-slate-400">s'est inscrit(e) à <span class="font-medium text-slate-600">{{ $reg->title }}</span></p>
                <p class="text-[10px] text-slate-300 mt-0.5">{{ \Carbon\Carbon::parse($reg->registered_at)->diffForHumans() }}</p>
            </div>
            @empty
            <div class="py-8 text-center text-sm text-slate-400">Aucune inscription récente.</div>
            @endforelse
        </div>
    </div>

    {{-- Leads activity --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <span class="text-lg">⚡</span>
            <h3 class="text-sm font-bold text-slate-800">Activité Leads ({{ $recentLeads->count() }})</h3>
        </div>
        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($recentLeads as $lead)
            @php $colors = ['accepted'=>'text-emerald-600 bg-emerald-50','rejected'=>'text-red-600 bg-red-50','converted'=>'text-purple-600 bg-purple-50']; @endphp
            <div class="px-5 py-3 flex items-center gap-3">
                <div class="flex-1">
                    <p class="text-[13px] font-semibold text-slate-800">{{ $lead->company_name }}</p>
                    <p class="text-[11px] text-slate-400">Lead envoyé à {{ $lead->receiver?->first_name }}</p>
                </div>
                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full {{ $colors[$lead->status] ?? '' }}">
                    {{ ucfirst($lead->status) }}
                </span>
            </div>
            @empty
            <div class="py-8 text-center text-sm text-slate-400">Aucune activité lead récente.</div>
            @endforelse
        </div>
    </div>

    {{-- Invitation responses --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <span class="text-lg">📨</span>
            <h3 class="text-sm font-bold text-slate-800">Réponses invitations ({{ $invitationResponses->count() }})</h3>
        </div>
        <div class="divide-y divide-gray-50 max-h-80 overflow-y-auto">
            @forelse($invitationResponses as $inv)
            @php $icons = ['accepted'=>'✅','declined'=>'❌']; @endphp
            <div class="px-5 py-3">
                <p class="text-[13px] font-semibold text-slate-800">
                    {{ $icons[$inv->status] ?? '' }} {{ $inv->user?->first_name }} {{ $inv->user?->last_name }}
                </p>
                <p class="text-[11px] text-slate-400">
                    {{ $inv->status === 'accepted' ? 'a accepté' : 'a refusé' }} l'invitation à
                    <span class="font-medium text-slate-600">{{ $inv->event?->title }}</span>
                </p>
                <p class="text-[10px] text-slate-300 mt-0.5">{{ $inv->updated_at->diffForHumans() }}</p>
            </div>
            @empty
            <div class="py-8 text-center text-sm text-slate-400">Aucune réponse récente.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
