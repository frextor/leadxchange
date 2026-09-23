{{-- Pastilles qualification + statut — maquette › QUAL / STAT --}}
@php
    $qualBadge = ['chaud' => ['Chaud', 'b-hot'], 'tiede' => ['Tiède', 'b-warm'], 'froid' => ['Froid', 'b-soft']];
    $statBadge = [
        'new'       => ['Nouveau',  'b-soft'],
        'accepted'  => ['Accepté',  'b-ok'],
        'rejected'  => ['Rejeté',   'b-hot'],
        'converted' => ['Converti', 'b-soft'],
        'expired'   => ['Expiré',   'b-grey'],
    ];
    [$qLabel, $qClass] = $qualBadge[$lead->qualification] ?? [ucfirst((string) $lead->qualification), 'b-grey'];
    [$sLabel, $sClass] = $statBadge[$lead->status] ?? [ucfirst((string) $lead->status), 'b-grey'];
@endphp
<span class="badge {{ $qClass }}">{{ $qLabel }}</span>
<span class="badge {{ $sClass }}">{{ $sLabel }}</span>
@if($lead->lead_type)<span class="badge b-muted">{{ $lead->lead_type }}</span>@endif
@if($lead->isFraudReported())<span class="badge b-orange">Signalé</span>@endif
