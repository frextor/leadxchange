{{-- Détail d'un lead — maquette › leadDetail(l) --}}
@php
    $isSentBox  = $box === 'sent';
    $other      = $isSentBox ? $lead->receiver : $lead->sender;
    $title      = $lead->company_name ?: ($lead->contact_name ?: 'Lead #' . $lead->id);
    $me         = $currentUser;

    $canAct     = ! $isSentBox && $lead->isNew();
    $canConvert = ! $isSentBox && $lead->isAccepted();
    $canRate    = ! $isSentBox && ($lead->isAccepted() || $lead->isConverted()) && ! $lead->hasRatingBy($me->id);
    $myRating   = ! $isSentBox ? $lead->ratings->firstWhere('rater_id', $me->id) : null;
    $avgRating  = $lead->average_rating;
    $canReport  = ! $isSentBox && ! $lead->isNew() && ! $lead->isRejected() && ! $lead->isFraudReported();
    $canRemind  = $isSentBox && ($lead->isNew() || $lead->isAccepted()) && $other;

    $daysLeft       = $lead->deadline ? (int) now()->startOfDay()->diffInDays($lead->deadline->copy()->startOfDay(), false) : null;
    $deadlinePassed = $daysLeft !== null && $daysLeft < 0;
    $ratingDue      = $lead->rating_due_at ? \Carbon\Carbon::parse($lead->rating_due_at) : null;
@endphp

<a class="btn btn-ghost btn-sm" href="{{ route('leads.index', $isSentBox ? ['box' => 'sent'] : []) }}" style="align-self:flex-start" data-mobile-back>
    <x-lx2-icon name="arrow-left" />Retour aux leads
</a>

<div class="card">
    <div class="card-pad" style="display:flex;flex-direction:column;gap:10px">
        <div class="row1" style="display:flex;gap:8px;flex-wrap:wrap">
            @if($lead->sector)<span class="badge b-outline">{{ $lead->sector->name }}</span>@endif
            <span class="badge b-plain num">{{ $lead->created_at->format('Y-m-d') }}</span>
        </div>
        <h2 style="margin:0;font-size:20px;font-weight:600;line-height:1.3;text-wrap:balance">{{ $title }}</h2>
        <div style="display:flex;gap:6px;flex-wrap:wrap">@include('leads._badges', ['lead' => $lead])</div>
    </div>
    <hr class="sep">
    <div class="mrow" style="padding:14px 18px">
        @if($other)
            <a href="{{ route('profile.show', $other->id) }}"><x-lx2-avatar :user="$other" :size="40" /></a>
            <div class="who">
                <div class="role">{{ $isSentBox ? 'Envoyé à' : 'Envoyé par' }}</div>
                <a class="nm" href="{{ route('profile.show', $other->id) }}">{{ member_name($other) }}
                    @if($otherRating)<span class="stars"><x-lx2-icon name="star" />{{ round($otherRating) }}</span>@endif
                </a>
            </div>
            <a class="btn btn-outline btn-sm" href="{{ route('chat.index', ['with' => $other->id]) }}"><x-lx2-icon name="message-circle" />Message</a>
        @else
            <div class="who"><div class="role">Membre supprimé</div></div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-h"><h3>Informations de contact</h3></div>
    <dl class="kv card-pad" style="margin:0">
        <div><dt>Contact</dt><dd>{{ $lead->contact_name ?: '—' }}</dd></div>
        <div><dt>Poste</dt><dd>{{ $lead->contact_position ?: '—' }}</dd></div>
        <div><dt>Email</dt><dd>@if($lead->contact_email)<a class="link" style="font-weight:400" href="mailto:{{ $lead->contact_email }}">{{ $lead->contact_email }}</a>@else — @endif</dd></div>
        <div><dt>Téléphone</dt><dd class="num">@if($lead->contact_phone)<a href="tel:{{ preg_replace('/\s+/', '', $lead->contact_phone) }}">{{ $lead->contact_phone }}</a>@else — @endif</dd></div>
        <div><dt>Entreprise</dt><dd>{{ $lead->company_name ?: '—' }}</dd></div>
        <div><dt>Secteur</dt><dd>@if($lead->sector)<span class="badge b-outline">{{ $lead->sector->name }}</span>@else — @endif</dd></div>
        <div>
            <dt>Échéance</dt>
            <dd class="num">
                {{ $lead->deadline?->format('Y-m-d') ?? '—' }}
                @if($lead->deadline && ($lead->isNew() || $lead->isAccepted()))
                    <span class="badge {{ $deadlinePassed ? 'b-hot' : ($daysLeft <= 4 ? 'b-warm' : 'b-muted') }}" style="height:20px;font-size:11px;margin-left:4px">
                        {{ $deadlinePassed ? 'Dépassée' : ($daysLeft === 0 ? "Aujourd'hui" : 'J-' . $daysLeft) }}
                    </span>
                @endif
            </dd>
        </div>
        <div>
            <dt>Note</dt>
            <dd>
                @if($myRating)
                    <span class="stars"><x-lx2-icon name="star" />{{ number_format($myRating->average_note, 1) }}</span> <span style="font-size:12px">votre note</span>
                @elseif($avgRating !== null)
                    <span class="stars"><x-lx2-icon name="star" />{{ number_format($avgRating, 1) }}</span>
                @else
                    —
                @endif
            </dd>
        </div>
        <div class="full"><dt>Description</dt><dd>{!! $lead->description ? nl2br(e($lead->description)) : '—' !!}</dd></div>
    </dl>

    @if($canAct || $canConvert || $canRate || $canReport || $canRemind)
    <div class="detail-foot">
        @if($canReport)
            <button type="button" class="btn btn-ghost" style="margin-right:auto;color:var(--muted-fg)" onclick="lx2Dialog('reportDialog')"><x-lx2-icon name="flag" />Signaler</button>
        @endif
        @if($canAct)
            <form method="POST" action="{{ route('leads.reject', $lead->id) }}">@csrf<button type="submit" class="btn btn-secondary">Refuser</button></form>
            <button type="button" class="btn btn-primary" onclick="lx2Dialog('acceptDialog')">Accepter</button>
        @endif
        @if($canRate)
            <button type="button" class="btn btn-outline" onclick="lx2Dialog('rateDialog')"><x-lx2-icon name="star" />Noter le lead</button>
        @endif
        @if($canConvert)
            <form method="POST" action="{{ route('leads.convert', $lead->id) }}">@csrf<button type="submit" class="btn btn-primary">Marquer converti</button></form>
        @endif
        @if($canRemind)
            <a class="btn btn-outline" href="{{ route('chat.index', ['with' => $other->id]) }}">Relancer</a>
        @endif
    </div>
    @endif
</div>

@if($canRate && $ratingDue)
    <p class="help" style="margin:0">Notez ce lead avant le {{ $ratingDue->locale('fr')->isoFormat('D MMMM') }} : au-delà de 4★ de moyenne, l'expéditeur reçoit un bonus.</p>
@endif
<p class="help" style="margin:0">
    <b>Responsabilité partagée (CGU §7.6)</b> — l'émetteur est responsable de la licéité des données transmises,
    le receveur de leur traitement ultérieur.
</p>
