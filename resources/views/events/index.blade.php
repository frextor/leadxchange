@extends('layouts.app2')

@section('title', 'Événements — LeadXchange')

{{-- Écran « Événements » — maquette LeadXchange WEB › pageEvents() --}}

@php
    $tabs = [null => 'Prochains événements', 'presentiel' => 'En présentiel', 'distanciel' => 'En distanciel'];
    $canCreate = auth()->user()->canFeature('can_organize_group_events');
@endphp

@section('content')
<x-lx2-header title="Événements" sub="Workshops, conférences et rencontres de votre réseau" />

<div class="toolbar">
    <div class="tabs">
        @foreach($tabs as $key => $label)
        <a href="{{ route('events.index', $key ? ['mode' => $key] : []) }}" class="{{ (string) $mode === (string) $key ? 'on' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div class="grow"></div>
    @if($canCreate)
        <a class="btn btn-primary" href="{{ route('events.create') }}"><x-lx2-icon name="plus" /><span class="lbl-m">Créer un événement</span></a>
    @else
        <button type="button" class="btn btn-primary" onclick="openUpgradeModal('can_organize_group_events')"><x-lx2-icon name="plus" /><span class="lbl-m">Créer un événement</span></button>
    @endif
</div>

@if($pendingInvitations->isNotEmpty())
<div class="sh"><h2>Invitations <span class="badge b-warm" style="margin-left:6px">{{ $pendingInvitations->count() }}</span></h2></div>
<div class="grid-3">
    @foreach($pendingInvitations as $inv)
        @include('events._card', ['event' => $inv->event, 'attendingIds' => $attendingIds, 'invitation' => $inv])
    @endforeach
</div>
@endif

<div class="sh"><h2>Mes événements</h2></div>
@if($myEvents->isNotEmpty())
<div class="grid-3">
    @foreach($myEvents as $event)
        @include('events._card', ['event' => $event, 'attendingIds' => $attendingIds])
    @endforeach
</div>
@else
<div class="card empty">Vous n'êtes inscrit à aucun événement à venir.</div>
@endif

<div class="sh"><h2>Recommandés pour vous</h2></div>
@if($suggested->isNotEmpty())
<div class="grid-3">
    @foreach($suggested as $event)
        @include('events._card', ['event' => $event, 'attendingIds' => $attendingIds])
    @endforeach
</div>
@else
<div class="card empty">Aucun événement à venir pour le moment.</div>
@endif

@if($pastEvents->isNotEmpty())
<div class="sh"><h2>Événements passés</h2></div>
<div class="grid-3">
    @foreach($pastEvents as $event)
        @include('events._card', ['event' => $event, 'attendingIds' => $attendingIds])
    @endforeach
</div>
@endif
@endsection
