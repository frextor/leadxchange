@extends('layouts.app2')

@section('title', 'Leads — LeadXchange')

{{--
    Écran « Leads » — maquette LeadXchange WEB › pageLeads(selId)
    Liste à gauche, détail du lead sélectionné à droite.
    /leads            → premier lead de l'onglet affiché (mobile : la liste)
    /leads/{id}       → ce lead sélectionné             (mobile : le détail)
--}}

@php
    $statusLabels = [
        'all'       => 'Tous',
        'new'       => 'Nouveau',
        'accepted'  => 'Accepté',
        'rejected'  => 'Rejeté',
        'converted' => 'Converti',
        'expired'   => 'Expiré',
    ];
    $statusCounts = collect($statusLabels)->map(fn($l, $k) => $k === 'all' ? $leads->count() : $leads->where('status', $k)->count());
    $canSend      = $currentUser->canFeature('can_send_leads');
@endphp

@section('content')
<x-lx2-header title="Leads" sub="Suivez les leads reçus et envoyés à votre réseau" />

<div class="toolbar">
    <div class="tabs">
        <a href="{{ route('leads.index') }}" class="lx2-tab {{ $box === 'received' ? 'on' : '' }}">Reçus</a>
        <a href="{{ route('leads.index', ['box' => 'sent']) }}" class="lx2-tab {{ $box === 'sent' ? 'on' : '' }}">Envoyés</a>
    </div>
    <div class="input-wrap" style="width:260px;max-width:100%">
        <x-lx2-icon name="search" />
        <input class="input" id="leadSearch" type="search" placeholder="Rechercher un lead" autocomplete="off">
    </div>
    <div class="grow"></div>
    @if($canSend)
        <a class="btn btn-primary" href="{{ route('leads.create') }}"><x-lx2-icon name="plus" /><span class="lbl-m">Envoyer un lead</span></a>
    @else
        <button type="button" class="btn btn-primary" onclick="openUpgradeModal('can_send_leads')"><x-lx2-icon name="plus" /><span class="lbl-m">Envoyer un lead</span></button>
    @endif
</div>

<div class="chips scroll" style="margin-bottom:18px" id="leadChips">
    @foreach($statusLabels as $key => $label)
    <button type="button" class="chip {{ $key === 'all' ? 'on' : '' }}" data-filter="{{ $key }}">{{ $label }}<span class="n num">{{ $statusCounts[$key] }}</span></button>
    @endforeach
</div>

<div class="split {{ $isSelected ? 'sel' : 'no-sel' }}">
    <div class="lead-col">
        <div class="lead-list" id="leadList">
            @forelse($leads as $lead)
                @include('leads._card', ['lead' => $lead, 'box' => $box, 'isOn' => $selected && $selected->id === $lead->id])
            @empty
                <div class="card empty">
                    @if($box === 'sent')
                        Vous n'avez encore envoyé aucun lead.
                        @if($canSend)<div style="margin-top:12px"><a class="btn btn-primary" href="{{ route('leads.create') }}">Envoyer votre premier lead</a></div>@endif
                    @else
                        Aucun lead reçu pour le moment. Les leads envoyés par votre réseau apparaîtront ici.
                    @endif
                </div>
            @endforelse
            <div class="card empty" id="leadNoMatch" hidden>Aucun lead ne correspond à ce filtre.</div>
        </div>
    </div>
    <div class="detail">
        @if($selected)
            @include('leads._detail', ['lead' => $selected, 'box' => $box])
        @endif
    </div>
</div>

@if($selected)
    @include('leads._dialogs', ['lead' => $selected])
@endif
@endsection

@push('scripts')
<script>
(function () {
    let filter = 'all';
    const search = document.getElementById('leadSearch');
    const list   = document.getElementById('leadList');
    const noMatch = document.getElementById('leadNoMatch');

    function apply() {
        const q = (search.value || '').toLowerCase().trim();
        let shown = 0;
        list.querySelectorAll('.lcard').forEach(card => {
            const ok = (filter === 'all' || card.dataset.status === filter) && (!q || card.dataset.search.includes(q));
            card.hidden = !ok;
            if (ok) shown++;
        });
        noMatch.hidden = shown > 0 || !list.querySelector('.lcard');
    }

    document.getElementById('leadChips').addEventListener('click', (e) => {
        const chip = e.target.closest('.chip');
        if (!chip) return;
        filter = chip.dataset.filter;
        document.querySelectorAll('#leadChips .chip').forEach(c => c.classList.toggle('on', c === chip));
        apply();
    });
    search.addEventListener('input', apply);
})();
</script>
@endpush
