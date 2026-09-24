@extends('layouts.app2')

@section('title', $event->title . ' — LeadXchange')

{{--
    Écran « Détail d'un événement » — maquette LeadXchange WEB › pageEventDetail(id, owner)
    La vue organisateur (owner) = même écran, avec Inviter / retirer un participant / Supprimer.
--}}

@php
    $me          = auth()->user();
    $modeLabels  = ['virtual' => 'Virtuel', 'in_person' => 'En personne', 'hybrid' => 'Hybride'];
    $modeSubs    = ['virtual' => 'Lien en ligne', 'in_person' => 'Sur place', 'hybrid' => 'Présentiel + lien en ligne'];
    $catLabel    = \App\Models\Event::categoryLabels()[$event->category ?? ''] ?? null;
    $isPast      = $event->starts_at->isPast();
    $capacity    = $event->max_attendees;
    $isFull      = $capacity && $event->attendees_count >= $capacity;
    $isLimited   = ! $isFull && $capacity && ($capacity - $event->attendees_count) <= 5;
    $canOnline   = $event->type !== 'in_person' && $event->meeting_link && ($isAttending || $isOrganizer);
    $canPay      = ! $event->is_free && ! $isAttending && ! $isPast && ! $isFull && $event->is_public && $me->canFeature('can_participate_events');
    $shown       = $attendees->take(12);
@endphp

@section('content')
<x-lx2-header title="" :back="route('events.index')" />

<div class="hero-cover" @unless($event->cover_url) style="background:linear-gradient(135deg,{{ $event->cover_color }},{{ $event->cover_color }}99)" @endunless>
    @if($event->cover_url)<img src="{{ $event->cover_url }}" alt="">@endif
</div>

<div class="two-col">
    <div style="display:flex;flex-direction:column;gap:18px;min-width:0">
        <div class="card card-pad">
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                @if($catLabel)<span class="badge b-plain">{{ $catLabel }}</span>@endif
                <span class="badge {{ $event->is_free ? 'b-ok' : 'b-soft' }}">{{ $event->is_free ? 'Gratuit' : currency_format($event->price) }}</span>
                @if($isLimited && ! $isPast)<span class="badge b-orange">Places limitées</span>@endif
                @unless($event->is_public)<span class="badge b-orange"><x-lx2-icon name="lock" />Privé</span>@endunless
                @if($isPast)<span class="badge b-grey">Terminé</span>@endif
            </div>
            <h1 style="margin:10px 0 6px;font-size:24px;font-weight:600;text-wrap:balance">{{ $event->title }}</h1>
            <div style="display:flex;gap:14px;flex-wrap:wrap;color:var(--muted-fg);font-size:13px">
                <span style="display:inline-flex;gap:6px;align-items:center"><x-lx2-icon name="calendar" />{{ ucfirst($event->starts_at->locale('fr')->isoFormat('MMMM D, YYYY')) }}</span>
                <span style="display:inline-flex;gap:6px;align-items:center"><x-lx2-icon name="clock" />{{ $event->starts_at->format('H:i') }}</span>
                @if($event->city || ($event->type !== 'virtual' && $event->location))
                <span style="display:inline-flex;gap:6px;align-items:center"><x-lx2-icon name="map-pin" />{{ $event->city?->name ?? $event->location }}</span>
                @endif
            </div>
        </div>

        <div class="card card-pad">
            <h3 style="margin:0 0 8px;font-size:15px;font-weight:600">À propos</h3>
            <p class="prose" style="margin:0;white-space:pre-line">{{ $event->description ?: "L'organisateur n'a pas encore ajouté de description." }}</p>
        </div>

        <div class="card">
            <div class="card-h">
                <h3>Participants <span style="color:var(--muted-fg);font-weight:400" class="num">({{ $event->attendees_count }}{{ $capacity ? '/' . $capacity : '' }})</span></h3>
                @if($isOrganizer && ! $isPast)
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="lx2Dialog('inviteEvDialog')"><x-lx2-icon name="plus" />Inviter</button>
                @endif
            </div>
            <div class="plist" style="padding:4px 18px">
                @forelse($shown as $a)
                    @if(! $loop->first)<hr class="sep">@endif
                    <div class="mrow">
                        <a href="{{ route('profile.show', $a->id) }}"><x-lx2-avatar :user="$a" :size="36" /></a>
                        <div class="who">
                            <a class="nm" href="{{ route('profile.show', $a->id) }}">{{ $a->id === $me->id ? 'Vous' : member_name($a) }}
                                @if($a->pivot->role === 'organizer')<span class="badge b-primary" style="height:20px;font-size:11px">Organisateur</span>@endif
                            </a>
                            <div class="role">{{ $a->profile?->job_title ?? 'Membre LeadXchange' }}@if($a->company) · {{ $a->company->name }}@endif</div>
                        </div>
                        @if($a->id !== $me->id)
                            @if($isOrganizer)
                                <form method="POST" action="{{ route('events.attendees.destroy', [$event->id, $a->id]) }}" onsubmit="return confirm('Retirer ce participant ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="circle-act" style="color:var(--destructive)" aria-label="Retirer {{ member_name($a) }}"><x-lx2-icon name="x" /></button>
                                </form>
                            @elseif(in_array($a->id, $connectedIds))
                                <a class="circle-act" href="{{ route('chat.index', ['with' => $a->id]) }}" aria-label="Envoyer un message"><x-lx2-icon name="message-circle" /></a>
                            @elseif($me->canFeature('can_send_invitations'))
                                <button type="button" class="circle-act" onclick="evConnect({{ $a->id }}, this)" aria-label="Se connecter avec {{ member_name($a) }}"><x-lx2-icon name="user-plus" /></button>
                            @else
                                <button type="button" class="circle-act" onclick="openUpgradeModal('can_send_invitations')" aria-label="Se connecter avec {{ member_name($a) }}"><x-lx2-icon name="user-plus" /></button>
                            @endif
                        @endif
                    </div>
                @empty
                    <div class="empty" style="padding:24px">Aucun participant pour le moment.</div>
                @endforelse
                @if($attendees->count() > 12)
                    <div class="help" style="padding:0 0 12px">+ {{ $attendees->count() - 12 }} autres participants</div>
                @endif
            </div>
        </div>
    </div>

    <aside class="aside-sticky">
        <div class="card card-pad" style="display:flex;flex-direction:column;gap:14px">
            <div class="info-list">
                <div><x-lx2-icon name="calendar" /><span>{{ ucfirst($event->starts_at->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}<small>{{ $event->starts_at->format('H:i') }}@if($event->ends_at) – {{ $event->ends_at->format('H:i') }}@endif</small></span></div>
                @if($event->type !== 'virtual')
                <div><x-lx2-icon name="map-pin" /><span>{{ $event->city?->name ?? 'Lieu' }}<small>{{ $event->location ?: 'Adresse communiquée par l\'organisateur' }}</small></span></div>
                @endif
                <div><x-lx2-icon name="users" /><span>{{ $modeLabels[$event->type] ?? $event->type }}<small>{{ $modeSubs[$event->type] ?? '' }}</small></span></div>
                @if($event->creator)
                <div><x-lx2-icon name="user" /><span>Organisé par {{ $event->created_by === $me->id ? 'vous' : member_name($event->creator) }}@if($event->sector)<small>{{ $event->sector->name }}</small>@endif</span></div>
                @endif
            </div>
            <hr class="sep">
            <div style="display:flex;justify-content:space-between;align-items:baseline">
                <span style="color:var(--muted-fg);font-size:13px">Tarif</span>
                <b style="font-size:20px" class="num">{{ $event->is_free ? 'Gratuit' : currency_format($event->price, 2) }}</b>
            </div>
            @if($capacity)
            <div>
                <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--muted-fg)"><span>Places réservées</span><span class="num">{{ $event->attendees_count }} / {{ $capacity }}</span></div>
                <div class="lx2-bar"><i style="width:{{ min(100, round($event->attendees_count / $capacity * 100)) }}%"></i></div>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:{{ $canOnline ? '1fr 1fr' : '1fr' }};gap:8px">
                @if($canOnline)
                <a class="btn btn-outline-primary btn-sm" href="{{ $event->meeting_link }}" target="_blank" rel="noopener"><x-lx2-icon name="video" />Lien en ligne</a>
                @endif
                <button type="button" class="btn btn-primary btn-sm" onclick="evShare()"><x-lx2-icon name="share-2" />Partager</button>
            </div>

            {{-- Action principale --}}
            @if($isPast)
                <button class="btn btn-secondary btn-block" disabled>Cet événement est terminé</button>
            @elseif($isOrganizer)
                <span class="btn btn-soft btn-block" style="cursor:default">Vous êtes l'organisateur</span>
            @elseif($isAttending)
                <span class="btn btn-soft btn-block" style="cursor:default"><x-lx2-icon name="check" />Vous êtes inscrit</span>
                <form method="POST" action="{{ route('events.leave', $event->id) }}">@csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger-soft btn-block">Quitter l'événement</button></form>
            @elseif($pendingInvitation)
                <p class="help" style="margin:0;text-align:center">Vous êtes invité à cet événement.</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <form method="POST" action="{{ route('events.invitations.decline', $pendingInvitation->id) }}">@csrf<button type="submit" class="btn btn-secondary btn-block">Décliner</button></form>
                    <form method="POST" action="{{ route('events.invitations.accept', $pendingInvitation->id) }}">@csrf<button type="submit" class="btn btn-primary btn-block">Accepter</button></form>
                </div>
            @elseif($isFull)
                <button class="btn btn-soft btn-block" disabled>Complet</button>
            @elseif(! $event->is_public)
                <span class="btn btn-secondary btn-block" style="cursor:default"><x-lx2-icon name="lock" />Sur invitation uniquement</span>
            @elseif(! $me->canFeature('can_participate_events'))
                <button type="button" class="btn btn-primary btn-block" onclick="openUpgradeModal('can_participate_events')">Rejoindre</button>
            @elseif($event->is_free)
                <form method="POST" action="{{ route('events.join', $event->id) }}">@csrf
                    <button type="submit" class="btn btn-primary btn-block">Rejoindre</button></form>
            @else
                <button type="button" class="btn btn-primary btn-block" onclick="openStripeModal()">Rejoindre · {{ currency_format($event->price, 2) }}</button>
            @endif

            @if($isOrganizer)
                <button type="button" class="btn btn-danger btn-block" onclick="lx2Dialog('deleteEvDialog')"><x-lx2-icon name="trash-2" />Supprimer</button>
            @endif
        </div>
    </aside>
</div>

{{-- ── Fenêtres ─────────────────────────────────────────────── --}}
@if($isOrganizer)
<div class="overlay hidden" id="deleteEvDialog" data-dialog>
    <form class="dialog" method="POST" action="{{ route('events.destroy', $event->id) }}">
        @csrf @method('DELETE')
        <div class="dh">
            <div><h3>Supprimer l'événement ?</h3>
                <p>@if($event->attendees_count > 1){{ $event->attendees_count - 1 }} participant{{ $event->attendees_count > 2 ? 's' : '' }} inscrit{{ $event->attendees_count > 2 ? 's' : '' }}. @endif Cette action est définitive.</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="submit" class="btn btn-danger">Supprimer</button>
        </div>
    </form>
</div>
@endif

@if($isOrganizer && ! $isPast)
<div class="overlay hidden" id="inviteEvDialog" data-dialog>
    <div class="dialog">
        <div class="dh">
            <div><h3>Inviter à l'événement</h3><p>{{ $event->title }}</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db">
            <div class="tabs full" style="margin-bottom:12px" id="invTabs">
                <button type="button" class="on" data-pane="invMembers">Membres</button>
                <button type="button" data-pane="invGroups">Groupe</button>
            </div>
            <div id="invMembers">
                @if($eventConnections->isEmpty())
                    <div class="lx2-dd-state">Toutes vos connexions participent déjà, ou vous n'avez pas encore de connexion.</div>
                @else
                    <div class="input-wrap" style="margin-bottom:6px"><x-lx2-icon name="search" /><input class="input" id="invQ" placeholder="Rechercher" autocomplete="off"></div>
                    <div id="invList">
                        @foreach($eventConnections as $c)
                        <div class="mrow inv-row" style="padding:8px 0" data-search="{{ mb_strtolower($c['name']) }}">
                            <span class="av-fb" style="width:34px;height:34px;font-size:12px">{{ mb_strtoupper(mb_substr($c['name'], 0, 1)) }}</span>
                            <div class="who"><div class="nm">{{ $c['name'] }}</div><div class="role">{{ $c['job_title'] ?: 'Membre LeadXchange' }}</div></div>
                            <button type="button" class="btn btn-primary btn-sm" data-inv="{{ $c['id'] }}">Inviter</button>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div id="invGroups" hidden>
                @if($organizerGroups->isEmpty())
                    <div class="lx2-dd-state">Pour inviter un groupe, créez (ou administrez) un groupe portant le même nom que l'événement : « {{ $event->title }} ».</div>
                @else
                    @foreach($organizerGroups as $grp)
                    <form method="POST" action="{{ route('events.invite-group', $event->id) }}" class="mrow" style="padding:8px 0">
                        @csrf <input type="hidden" name="group_id" value="{{ $grp->id }}">
                        <span class="av-fb" style="width:34px;height:34px"><x-lx2-icon name="users" /></span>
                        <div class="who"><div class="nm">{{ $grp->name }}</div><div class="role">{{ $grp->members_count }} membre{{ $grp->members_count > 1 ? 's' : '' }}</div></div>
                        <button type="submit" class="btn btn-primary btn-sm">Inviter le groupe</button>
                    </form>
                    @endforeach
                @endif
            </div>
        </div>
        @if($eventConnections->isNotEmpty())
        <div class="df" id="invAllWrap"><button type="button" class="btn btn-primary btn-block" id="invAll">Tout inviter</button></div>
        @endif
    </div>
</div>
@endif

@if($canPay)
<div class="overlay hidden" id="stripeModal" data-dialog>
    <div class="dialog" style="max-width:400px">
        <div class="dh">
            <div><h3>Paiement sécurisé</h3><p>{{ $event->title }}</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;justify-content:space-between;align-items:baseline;padding:12px 14px;border:1px solid var(--border);border-radius:var(--radius);background:var(--muted)">
                <span style="font-size:13px;color:var(--muted-fg)">Montant total</span><b style="font-size:18px" class="num">{{ currency_format($event->price, 2) }}</b>
            </div>
            <div>
                <span class="label">Carte bancaire</span>
                <div id="card-element" class="input" style="height:auto;padding:11px 12px"></div>
                <p class="field-err" id="card-errors" hidden></p>
            </div>
            <p class="help" id="stripeProcessing" style="margin:0" hidden><span class="lx2-spin" style="width:14px;height:14px;vertical-align:-2px"></span> Paiement en cours…</p>
            <p class="help pos" id="stripeSuccess" style="margin:0" hidden>Paiement confirmé ! Inscription en cours…</p>
        </div>
        <div class="df" style="flex-direction:column;align-items:stretch;gap:8px">
            <button type="button" class="btn btn-primary btn-block" id="stripePayBtn" onclick="confirmStripePayment()"><x-lx2-icon name="credit-card" />Payer {{ currency_format($event->price, 2) }}</button>
            <p class="help" style="margin:0;text-align:center"><x-lx2-icon name="lock" /> Paiement sécurisé par Stripe</p>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name=csrf-token]').content;

function evShare() {
    const data = { title: @json($event->title), url: location.href };
    if (navigator.share) { navigator.share(data).catch(() => {}); return; }
    navigator.clipboard?.writeText(location.href).then(() => toast('Lien de l\'événement copié')).catch(() => toast(location.href));
}

async function evConnect(userId, btn) {
    btn.disabled = true;
    try {
        const res = await fetch('/api/connections', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Authorization': 'Bearer ' + (window.API_TOKEN || '') },
            credentials: 'same-origin',
            body: JSON.stringify({ receiver_id: userId }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Une erreur est survenue.');
        btn.innerHTML = '✓'; btn.style.color = 'var(--green)';
        toast('Demande de connexion envoyée');
    } catch (e) { btn.disabled = false; toast(e.message, 'error'); }
}

@if($isOrganizer && ! $isPast)
(function () {
    document.getElementById('invTabs').addEventListener('click', (e) => {
        const b = e.target.closest('[data-pane]');
        if (!b) return;
        document.querySelectorAll('#invTabs button').forEach(x => x.classList.toggle('on', x === b));
        document.getElementById('invMembers').hidden = b.dataset.pane !== 'invMembers';
        document.getElementById('invGroups').hidden  = b.dataset.pane !== 'invGroups';
        const all = document.getElementById('invAllWrap'); if (all) all.hidden = b.dataset.pane !== 'invMembers';
    });

    document.getElementById('invQ')?.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.inv-row').forEach(r => r.hidden = q && !r.dataset.search.includes(q));
    });

    async function invite(btn) {
        if (btn.dataset.done) return;
        btn.disabled = true;
        const body = new URLSearchParams({ _token: CSRF_TOKEN, user_id: btn.dataset.inv });
        try {
            const res = await fetch(@json(route('events.invite', $event->id)), { method: 'POST', body, credentials: 'same-origin', headers: { 'Accept': 'text/html' } });
            if (!res.ok) throw new Error();
            btn.dataset.done = '1';
            btn.className = 'btn btn-soft btn-sm'; btn.textContent = 'Envoyé';
        } catch { btn.disabled = false; toast('Invitation impossible. Réessayez.', 'error'); }
    }
    document.getElementById('invList')?.addEventListener('click', (e) => { const b = e.target.closest('[data-inv]'); if (b) invite(b); });
    document.getElementById('invAll')?.addEventListener('click', async (e) => {
        e.target.disabled = true;
        for (const b of document.querySelectorAll('#invList [data-inv]')) { if (!b.closest('.inv-row').hidden) await invite(b); }
        e.target.disabled = false;
        toast('Invitations envoyées');
    });
})();
@endif
</script>

@if($canPay)
<script src="https://js.stripe.com/v3/"></script>
<script>
(function () {
    const EVENT_ID = {{ $event->id }};
    const JOIN_URL = @json(route('events.join', $event->id));
    let stripe, cardElement, clientSecret;
    const errEl = document.getElementById('card-errors');
    const showErr = (m) => { errEl.textContent = m; errEl.hidden = false; };

    async function initStripe() {
        try {
            const cfg = await (await fetch('/api/payments/config', { headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + window.API_TOKEN }, credentials: 'same-origin' })).json();
            if (!cfg.publishable_key) throw new Error();
            stripe = Stripe(cfg.publishable_key);
            cardElement = stripe.elements().create('card', { style: { base: { fontSize: '14px', color: '#09090B', fontFamily: 'Inter, sans-serif', '::placeholder': { color: '#A1A1AA' } } } });
            cardElement.mount('#card-element');
            cardElement.on('change', (e) => { if (e.error) showErr(e.error.message); else errEl.hidden = true; });
        } catch { showErr('Impossible de charger Stripe. Réessayez.'); }
    }

    window.openStripeModal = async function () {
        lx2Dialog('stripeModal');
        if (!stripe) await initStripe();
        try {
            const res = await fetch('/api/payments/events/' + EVENT_ID + '/intent', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + window.API_TOKEN, 'X-CSRF-TOKEN': CSRF_TOKEN },
                credentials: 'same-origin',
            });
            clientSecret = (await res.json()).client_secret;
        } catch { showErr('Erreur lors de la création du paiement.'); }
    };

    window.confirmStripePayment = async function () {
        if (!stripe || !cardElement || !clientSecret) return;
        const btn = document.getElementById('stripePayBtn');
        btn.disabled = true; errEl.hidden = true;
        document.getElementById('stripeProcessing').hidden = false;
        const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, { payment_method: { card: cardElement } });
        document.getElementById('stripeProcessing').hidden = true;
        if (error) { showErr(error.message); btn.disabled = false; return; }
        if (paymentIntent && paymentIntent.status === 'succeeded') {
            document.getElementById('stripeSuccess').hidden = false;
            const form = document.createElement('form');
            form.method = 'POST'; form.action = JOIN_URL;
            form.innerHTML = '<input type="hidden" name="_token" value="' + CSRF_TOKEN + '">';
            document.body.appendChild(form);
            setTimeout(() => form.submit(), 1500);
        }
    };
})();
</script>
@endif
@endpush
