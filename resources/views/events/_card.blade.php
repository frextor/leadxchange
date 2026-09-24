{{--
    Carte d'événement — maquette LeadXchange WEB › eventCard(e)
    Paramètres : $event (avec previewPeople — EventService::attachPreviewAttendees),
                 $attendingIds (ids des événements où l'utilisateur est inscrit),
                 $invitation (optionnel : invitation en attente → Décliner / Accepter).
--}}
@php
    $me          = auth()->user();
    $invitation  = $invitation ?? null;
    $modeLabels  = ['virtual' => 'Virtuel', 'in_person' => 'En personne', 'hybrid' => 'Hybride'];
    $catLabels   = \App\Models\Event::categoryLabels();
    $catLabel    = $catLabels[$event->category] ?? ($event->category ? ucfirst(str_replace('_', ' ', $event->category)) : 'Événement');
    $isPast      = $event->starts_at->isPast();
    $isOrganizer = $event->created_by === $me->id;
    $isAttending = in_array($event->id, $attendingIds ?? []);
    $isFull      = $event->max_attendees !== null && $event->attendees_count >= $event->max_attendees;
    $isLimited   = ! $isFull && $event->max_attendees !== null && ($event->max_attendees - $event->attendees_count) <= 5;
    $people      = $event->previewPeople ?? collect();
    $more        = max(0, (int) $event->attendees_count - $people->count());
@endphp
<article class="card ecard">
    <a class="cover" href="{{ route('events.show', $event->id) }}">
        @if($event->cover_url)
            <img src="{{ $event->cover_url }}" alt="">
        @else
            <div style="width:100%;height:100%;background:linear-gradient(135deg,{{ $event->cover_color }},{{ $event->cover_color }}99);"></div>
        @endif
        <span class="tl">
            <span class="badge b-plain">{{ $catLabel }}</span>
            <span class="badge {{ $event->is_free ? 'b-ok' : 'b-soft' }}">{{ $event->is_free ? 'Gratuit' : currency_format($event->price) }}</span>
            @if($isLimited && ! $isPast)<span class="badge b-orange">Places limitées</span>@endif
            @unless($event->is_public)<span class="badge b-orange">Privé</span>@endunless
        </span>
    </a>
    <div class="body">
        <a href="{{ route('events.show', $event->id) }}"><h3>{{ $event->title }}</h3></a>
        <div class="when">{{ ucfirst($event->starts_at->locale('fr')->isoFormat('MMMM D, YYYY [à] H:mm')) }}</div>
        <div class="meta">
            @if($people->isNotEmpty())
            <span class="stack">@foreach($people as $p)<x-lx2-avatar :user="$p" :size="26" />@endforeach @if($more > 0)<span class="more">+{{ $more }}</span>@endif</span>
            @endif
            <span>Participants · <span class="mode">{{ $modeLabels[$event->type] ?? $event->type }}</span></span>
        </div>

        @unless($isPast)
        <div class="actions">
            @if($invitation)
                <form method="POST" action="{{ route('events.invitations.decline', $invitation->id) }}" style="flex:1;display:flex">@csrf
                    <button type="submit" class="btn btn-secondary" style="flex:1">Décliner</button></form>
                <form method="POST" action="{{ route('events.invitations.accept', $invitation->id) }}" style="flex:1;display:flex">@csrf
                    <button type="submit" class="btn btn-primary" style="flex:1">Accepter</button></form>
            @elseif($isOrganizer)
                <a class="btn btn-soft" href="{{ route('events.show', $event->id) }}">Organisateur · Gérer</a>
            @elseif($isAttending)
                <form method="POST" action="{{ route('events.leave', $event->id) }}" style="flex:1;display:flex">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-soft" style="flex:1">Quitter</button></form>
            @elseif($isFull)
                <button class="btn btn-soft" disabled>Complet</button>
            @elseif(! $me->canFeature('can_participate_events'))
                <button type="button" class="btn btn-primary" onclick="openUpgradeModal('can_participate_events')">Rejoindre</button>
            @elseif($event->is_free && $event->is_public)
                <form method="POST" action="{{ route('events.join', $event->id) }}" style="flex:1;display:flex">@csrf
                    <button type="submit" class="btn btn-primary" style="flex:1">Rejoindre</button></form>
            @else
                <a class="btn btn-primary" href="{{ route('events.show', $event->id) }}">Rejoindre</a>
            @endif
        </div>
        @endunless
    </div>
</article>
