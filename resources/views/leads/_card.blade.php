{{-- Carte de lead dans la liste — maquette › .lcard --}}
@php
    $isSentBox = $box === 'sent';
    $other     = $isSentBox ? $lead->receiver : $lead->sender;
    $title     = $lead->company_name ?: ($lead->contact_name ?: 'Lead #' . $lead->id);
    $search    = mb_strtolower(implode(' ', array_filter([
        $lead->company_name, $lead->contact_name, $lead->contact_position,
        $lead->sector?->name, $other ? member_name($other) : null,
    ])));
@endphp
<a class="lcard {{ $isOn ? 'on' : '' }}" href="{{ route('leads.show', $lead->id) }}"
   data-status="{{ $lead->status }}" data-search="{{ $search }}">
    <div class="top">
        <div class="row1">
            @if($lead->sector)<span class="badge b-outline">{{ $lead->sector->name }}</span>@endif
            <span class="badge b-plain num">{{ $lead->created_at->format('Y-m-d') }}</span>
        </div>
        <div class="from">{{ $isSentBox ? 'Envoyé à ' : '' }}{{ $other ? member_name($other) : 'Membre supprimé' }}</div>
        <h3>{{ $title }}</h3>
    </div>
    <div class="bot">
        @include('leads._badges', ['lead' => $lead])
    </div>
</a>
