@extends('ambassador.layouts.ambassador')
@section('title', 'Rapports')
@section('page-title', 'Rapports')
@section('page-subtitle', 'Exportez vos données régionales')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">

    @php $reports = [
        [
            'type'        => 'members',
            'title'       => 'Rapport Membres',
            'description' => 'Liste complète des membres de votre région avec leurs informations de profil, abonnement et activité.',
            'count'       => $stats['members'],
            'label'       => 'membre(s)',
            'emoji'       => '👥',
            'color'       => 'from-teal-500 to-teal-700',
        ],
        [
            'type'        => 'events',
            'title'       => 'Rapport Événements',
            'description' => 'Tous vos événements avec dates, participants, types et statistiques de présence.',
            'count'       => $stats['events'],
            'label'       => 'événement(s)',
            'emoji'       => '📅',
            'color'       => 'from-indigo-500 to-indigo-700',
        ],
        [
            'type'        => 'leads',
            'title'       => 'Rapport Leads',
            'description' => 'Vos leads générés avec entreprises, contacts, qualifications et statuts de conversion.',
            'count'       => $stats['leads'],
            'label'       => 'lead(s)',
            'emoji'       => '⚡',
            'color'       => 'from-amber-500 to-amber-700',
        ],
    ]; @endphp

    @foreach($reports as $report)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="h-28 bg-gradient-to-br {{ $report['color'] }} p-5 flex items-end">
            <div>
                <span class="text-3xl">{{ $report['emoji'] }}</span>
                <p class="text-white font-bold mt-1">{{ $report['title'] }}</p>
            </div>
        </div>
        <div class="p-5">
            <p class="text-sm text-slate-600 mb-4 leading-relaxed">{{ $report['description'] }}</p>

            <div class="flex items-center justify-between mb-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-slate-800">{{ number_format($report['count']) }}</p>
                    <p class="text-xs text-slate-400">{{ $report['label'] }}</p>
                </div>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('ambassador.reports.export', $report['type']) }}"
                       class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white"
                       style="background:linear-gradient(135deg,#14B8A6,#0F766E);">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export CSV
                    </a>
                    <button disabled
                            class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border border-gray-200 text-slate-400 cursor-not-allowed">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
                        Export PDF
                        <span class="text-[9px] text-slate-300">Bientôt</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-5 bg-blue-50 border border-blue-200 rounded-2xl p-5">
    <p class="text-sm font-semibold text-blue-800 mb-1">📊 Rapports PDF avancés</p>
    <p class="text-xs text-blue-600">
        Les rapports PDF avec graphiques intégrés, branding LeadXchange et synthèse exécutive seront disponibles prochainement.
        Les exports CSV sont disponibles dès maintenant et compatibles avec Excel, Google Sheets et tout outil de data.
    </p>
</div>

@endsection
