{{--
    Carte de groupe — maquette LeadXchange WEB › groupCard(g)
    Paramètres : $group (avec previewPeople — EventService::attachPreviewMembers),
                 $role (owner|admin|member|null), $requested (demande d'adhésion en attente),
                 $invitation (optionnel : invitation en attente → Décliner / Accepter).
--}}
@php
    $role       = $role ?? null;
    $requested  = $requested ?? false;
    $invitation = $invitation ?? null;
    $people     = $group->previewPeople ?? collect();
    $count      = (int) ($group->members_count ?? 0);
    $more       = max(0, $count - $people->count());
    $canJoin    = auth()->user()->canFeature('can_join_pole');
@endphp
<article class="card ecard" data-search="{{ mb_strtolower($group->name . ' ' . ($group->sector?->name ?? '') . ' ' . ($group->city?->name ?? '')) }}">
    <a class="cover" href="{{ route('groups.show', $group->id) }}">
        @if($group->cover_url)
            <img src="{{ $group->cover_url }}" alt="">
        @else
            <div style="width:100%;height:100%;background:linear-gradient(135deg,{{ $group->cover_color }},{{ $group->cover_color }}99);"></div>
        @endif
        <span class="tl">
            <span class="badge {{ $group->is_public ? 'b-ok' : 'b-orange' }}">{{ $group->is_public ? 'Public' : 'Privé' }}</span>
            @if(in_array($role, ['owner', 'admin']))<span class="badge b-primary">{{ $role === 'owner' ? 'Propriétaire' : 'Admin' }}</span>@endif
        </span>
    </a>
    <div class="body">
        <a href="{{ route('groups.show', $group->id) }}"><h3>{{ $group->name }}</h3></a>
        <div class="desc">{{ $group->description ? \Illuminate\Support\Str::limit($group->description, 70) : ($group->sector?->name ?? 'Groupe LeadXchange') }}</div>
        <div class="meta">
            @if($people->isNotEmpty())
            <span class="stack">@foreach($people as $p)<x-lx2-avatar :user="$p" :size="26" />@endforeach @if($more > 0)<span class="more">+{{ $more }}</span>@endif</span>
            @endif
            <span>{{ $count }} membre{{ $count > 1 ? 's' : '' }}</span>
        </div>
        <div class="actions">
            @if($invitation)
                <form method="POST" action="{{ route('groups.invitations.decline', $invitation->id) }}" style="flex:1;display:flex">@csrf
                    <button type="submit" class="btn btn-secondary" style="flex:1">Décliner</button></form>
                @if($canJoin)
                <form method="POST" action="{{ route('groups.invitations.accept', $invitation->id) }}" style="flex:1;display:flex">@csrf
                    <button type="submit" class="btn btn-primary" style="flex:1">Accepter</button></form>
                @else
                <button type="button" class="btn btn-primary" onclick="openUpgradeModal('can_join_pole')">Accepter</button>
                @endif
            @elseif($role === 'owner')
                <a class="btn btn-soft" href="{{ route('groups.show', $group->id) }}">Gérer le groupe</a>
            @elseif($role)
                <form method="POST" action="{{ route('groups.leave', $group->id) }}" style="flex:1;display:flex" onsubmit="return confirm('Quitter ce groupe ?')">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-soft" style="flex:1">Quitter</button></form>
            @elseif($requested)
                <button class="btn btn-soft" disabled>Demande envoyée</button>
            @elseif(! $canJoin)
                <button type="button" class="btn btn-primary" onclick="openUpgradeModal('can_join_pole')">Rejoindre</button>
            @else
                <form method="POST" action="{{ route('groups.join', $group->id) }}" style="flex:1;display:flex">@csrf
                    <button type="submit" class="btn btn-primary" style="flex:1">Rejoindre</button></form>
            @endif
        </div>
    </div>
</article>
