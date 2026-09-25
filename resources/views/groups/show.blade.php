@extends('layouts.app2')

@section('title', $group->name . ' — LeadXchange')

{{--
    Écran « Groupe » — maquette LeadXchange WEB › pageGroupDetail(invite) + groupAbout(member) + wallHTML()
    Mur (publications, activités, sondages) à gauche, « À propos » + membres à droite.
    Groupe privé avec invitation en attente : mur verrouillé + Décliner / Accepter.
--}}

@php
    $me          = auth()->user();
    $roleLabels  = ['owner' => 'Propriétaire', 'admin' => 'Admin'];
    $shownMembers = $members->take(15);
    $dayLabel = function ($d) {
        if ($d->isToday()) return "Aujourd'hui";
        if ($d->isYesterday()) return 'Hier';
        return ucfirst($d->locale('fr')->isoFormat('dddd D MMMM'));
    };
    $lastDay = null;
@endphp

@section('content')
<x-lx2-header :title="$group->name" :tag="$group->is_public ? 'Public' : 'Privé'" :tag-class="$group->is_public ? 'b-ok' : 'b-orange'" :back="route('groups.index')" />

@if($errors->any())
<div class="lx2-flash b-hot" role="alert"><span>{{ $errors->first() }}</span></div>
@endif

<div class="group-layout">
    {{-- ── Mur ─────────────────────────────────────────────── --}}
    <div class="card chat {{ $locked ? 'locked' : '' }}">
        <div class="card-h"><h3>Mur</h3><span style="font-size:12.5px;color:var(--muted-fg)" class="num">{{ $group->members_count }} membre{{ $group->members_count > 1 ? 's' : '' }}</span></div>

        <div class="chat-scroll" id="chatScroll">
            @if($locked)
                {{-- Aperçu flouté (aucun contenu réel n'est chargé pour un non-membre) --}}
                @foreach([['', 'Bienvenue dans le groupe !'], ['me', 'Merci, ravi de rejoindre la communauté.'], ['', 'Prochaine rencontre le mois prochain.']] as [$cls, $txt])
                <div class="msg {{ $cls }}"><span class="av-fb" style="width:30px;height:30px"></span><div><div class="bubble">{{ $txt }}</div></div></div>
                @endforeach
            @else
                @if($posts->hasMorePages())
                    <a class="day-sep" href="{{ $posts->nextPageUrl() }}">Messages plus anciens</a>
                @endif
                @forelse($wall as $entry)
                    @php $item = $entry['item']; $day = $dayLabel($entry['at']); @endphp
                    @if($day !== $lastDay)<div class="day-sep">{{ $day }}</div>@php $lastDay = $day; @endphp @endif

                    @if($entry['kind'] === 'poll')
                        @php
                            $mine  = $item->user_id === $me->id;
                            $total = max(1, (int) $item->votes_count);
                            $voted = $myVotes[$item->id] ?? null;
                        @endphp
                        <div class="msg {{ $mine ? 'me' : '' }}">
                            <x-lx2-avatar :user="$item->user" :size="30" />
                            <div><div class="bubble poll" data-poll="{{ $item->id }}">
                                @unless($mine)<div class="n">{{ $item->user ? member_name($item->user) : 'Membre' }}</div>@endunless
                                <b style="font-size:13px">Sondage · {{ $item->question }}</b>
                                @foreach($item->options as $opt)
                                @php $pct = round($opt->votes_count / $total * 100); @endphp
                                <button type="button" class="opt" data-option="{{ $opt->id }}" @disabled(! $isMember)>
                                    <span class="r"><span class="ring {{ $voted === $opt->id ? 'on' : '' }}"></span>{{ $opt->text }}<span class="v num" data-count>{{ $opt->votes_count }}</span></span>
                                    <span class="bar"><i style="width:{{ $item->votes_count ? $pct : 0 }}%"></i></span>
                                </button>
                                @endforeach
                                <div class="time">{{ $item->created_at->format('H:i') }} · <span data-total>{{ $item->votes_count }}</span> vote{{ $item->votes_count > 1 ? 's' : '' }}</div>
                            </div></div>
                        </div>
                    @else
                        @php $mine = $item->user_id === $me->id; @endphp
                        <div class="msg {{ $mine ? 'me' : '' }}" id="post-{{ $item->id }}">
                            <a href="{{ $item->author ? route('profile.show', $item->author->id) : '#' }}"><x-lx2-avatar :user="$item->author" :size="30" /></a>
                            <div style="min-width:0">
                                <div class="bubble {{ $item->type === 'activity' ? 'activity' : '' }}" @if($item->photo_path && ! $item->body) style="padding:6px" @endif>
                                    @unless($mine)<div class="n" @if($item->photo_path && ! $item->body) style="padding:2px 6px 6px" @endif>{{ $item->author ? member_name($item->author) : 'Membre supprimé' }}</div>@endunless
                                    @if($item->type === 'activity')
                                        <div class="act-h"><x-lx2-icon name="calendar" /><b>{{ $item->activity_title }}</b></div>
                                        @if($item->activity_date)<div class="act-d">{{ ucfirst($item->activity_date->locale('fr')->isoFormat('dddd D MMMM [à] HH:mm')) }}</div>@endif
                                    @endif
                                    @if($item->body)<div style="white-space:pre-line">{{ $item->body }}</div>@endif
                                    @if($item->photo_path)<a class="imgb" href="{{ $item->photo_url }}" target="_blank" rel="noopener" @if($item->body) style="margin-top:6px;display:block" @endif><img src="{{ $item->photo_url }}" alt="Photo partagée"></a>@endif
                                    <div class="time" @if($item->photo_path && ! $item->body) style="padding:0 6px" @endif>{{ $item->created_at->format('H:i') }}</div>
                                </div>
                                <div class="msg-tools">
                                    @if($isMember)<button type="button" class="link lx2-linkbtn" data-reply="{{ $item->id }}">Répondre{{ $item->comments->isNotEmpty() ? ' · ' . $item->comments->count() : '' }}</button>@endif
                                    @if($mine || $isAdmin)
                                    <form method="POST" action="{{ route('groups.posts.destroy', [$group->id, $item->id]) }}" onsubmit="return confirm('Supprimer cette publication ?')">
                                        @csrf @method('DELETE')<button type="submit" class="lx2-linkbtn" style="color:var(--muted-fg)">Supprimer</button>
                                    </form>
                                    @endif
                                </div>
                                @if($item->comments->isNotEmpty())
                                <div class="replies">
                                    @foreach($item->comments as $c)
                                    <div class="reply"><x-lx2-avatar :user="$c->author" :size="22" /><div><b>{{ $c->user_id === $me->id ? 'Vous' : ($c->author ? member_name($c->author) : 'Membre') }}</b> {{ $c->body }}<small>{{ $c->created_at->locale('fr')->diffForHumans() }}</small></div></div>
                                    @endforeach
                                </div>
                                @endif
                                @if($isMember)
                                <form method="POST" action="{{ route('groups.comments.store', [$group->id, $item->id]) }}" class="reply-form" id="reply-{{ $item->id }}" hidden>
                                    @csrf
                                    <input class="input" name="body" maxlength="1000" required placeholder="Votre réponse…">
                                    <button type="submit" class="btn btn-primary btn-sm">Envoyer</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="empty" style="margin:auto">Aucun message pour le moment.@if($isMember) Lancez la conversation !@endif</div>
                @endforelse
            @endif
        </div>

        @if($locked)
            <div class="lock-over"><div class="card">
                <div style="width:44px;height:44px;border-radius:999px;background:var(--primary-soft);color:var(--primary);display:grid;place-items:center;margin:0 auto 10px"><x-lx2-icon name="lock" /></div>
                <b style="font-size:15px">Groupe privé</b>
                <p style="color:var(--muted-fg);font-size:13px;margin:6px 0 16px">
                    {{ $pendingInvitation?->inviter ? member_name($pendingInvitation->inviter) : "L'administrateur" }} vous invite à rejoindre ce groupe. Acceptez pour voir le mur et participer aux échanges.
                </p>
                <div style="display:flex;gap:10px">
                    <form method="POST" action="{{ route('groups.invitations.decline', $pendingInvitation->id) }}" style="flex:1">@csrf<button type="submit" class="btn btn-secondary btn-block">Décliner</button></form>
                    @if($me->canFeature('can_join_pole'))
                    <form method="POST" action="{{ route('groups.invitations.accept', $pendingInvitation->id) }}" style="flex:1">@csrf<button type="submit" class="btn btn-primary btn-block">Accepter</button></form>
                    @else
                    <button type="button" class="btn btn-primary" style="flex:1" onclick="openUpgradeModal('can_join_pole')">Accepter</button>
                    @endif
                </div>
            </div></div>
        @elseif($isMember)
            <form class="composer" method="POST" action="{{ route('groups.posts.store', $group->id) }}" enctype="multipart/form-data" id="composer">
                @csrf
                <div style="position:relative">
                    <button type="button" class="icon-btn" id="attachBtn" aria-label="Ajouter"><x-lx2-icon name="plus" /></button>
                    <div class="pop menu hidden" id="attachPop" style="left:0;bottom:calc(100% + 8px)">
                        <button type="button" id="pickPhoto"><x-lx2-icon name="image-plus" />Photo</button>
                        <button type="button" onclick="lx2Dialog('pollDialog')"><x-lx2-icon name="check" />Sondage</button>
                        @if($isAdmin)<button type="button" onclick="lx2Dialog('activityDialog')"><x-lx2-icon name="calendar" />Activité</button>@endif
                    </div>
                </div>
                <input type="file" name="photo" id="postPhoto" accept="image/jpeg,image/png,image/webp" hidden>
                <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:6px">
                    <span class="badge b-soft" id="photoChip" hidden style="align-self:flex-start"></span>
                    <input class="input" name="body" id="wallMsg" maxlength="2000" placeholder="Envoyer un message…" autocomplete="off">
                </div>
                <button class="send-btn" type="submit" aria-label="Envoyer"><x-lx2-icon name="arrow-up-right" /></button>
            </form>
        @else
            <div class="composer" style="justify-content:space-between">
                <span style="font-size:13px;color:var(--muted-fg)">Rejoignez le groupe pour participer aux échanges.</span>
                @if($hasPendingRequest)
                    <button class="btn btn-soft btn-sm" disabled>Demande envoyée</button>
                @elseif($me->canFeature('can_join_pole'))
                    <form method="POST" action="{{ route('groups.join', $group->id) }}">@csrf<button type="submit" class="btn btn-primary btn-sm">Rejoindre</button></form>
                @else
                    <button type="button" class="btn btn-primary btn-sm" onclick="openUpgradeModal('can_join_pole')">Rejoindre</button>
                @endif
            </div>
        @endif
    </div>

    {{-- ── À propos ────────────────────────────────────────── --}}
    <aside style="display:flex;flex-direction:column;gap:14px;min-width:0">
        @if($isAdmin && $pendingRequests->isNotEmpty())
        <div class="card">
            <div class="card-h"><h3>Demandes d'adhésion <span class="badge b-warm" style="margin-left:4px">{{ $pendingRequests->count() }}</span></h3></div>
            <div class="plist" style="padding:4px 18px">
                @foreach($pendingRequests as $req)
                    @if(! $loop->first)<hr class="sep">@endif
                    <div class="mrow" style="padding:10px 0">
                        <x-lx2-avatar :user="$req->user" :size="32" />
                        <div class="who"><div class="nm" style="font-size:13px">{{ $req->user ? member_name($req->user) : '—' }}</div><div class="role">{{ $req->user?->profile?->job_title ?? 'Membre LeadXchange' }}</div></div>
                        <form method="POST" action="{{ route('groups.requests.reject', [$group->id, $req->user_id]) }}">@csrf<button type="submit" class="circle-act" aria-label="Refuser"><x-lx2-icon name="x" /></button></form>
                        <form method="POST" action="{{ route('groups.requests.approve', [$group->id, $req->user_id]) }}">@csrf<button type="submit" class="btn btn-primary btn-sm">Accepter</button></form>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card">
            <div style="padding:12px 12px 0">
                @if($group->cover_url)
                    <img src="{{ $group->cover_url }}" alt="" style="border-radius:6px;aspect-ratio:16/9;object-fit:cover;width:100%">
                @else
                    <div style="border-radius:6px;aspect-ratio:16/9;background:linear-gradient(135deg,{{ $group->cover_color }},{{ $group->cover_color }}99)"></div>
                @endif
            </div>
            <div class="card-pad" style="display:flex;flex-direction:column;gap:14px">
                <div>
                    <h3 style="margin:0 0 6px;font-size:14px;font-weight:600">À propos</h3>
                    <p class="prose" style="margin:0;font-size:13px;white-space:pre-line">{{ $group->description ?: 'Aucune description pour le moment.' }}</p>
                    @if($group->sector)<div style="margin-top:8px"><span class="badge b-outline">{{ $group->sector->name }}</span></div>@endif
                </div>

                @if($group->creator)
                <div>
                    <h3 style="margin:0 0 8px;font-size:13px;font-weight:600">Créé par</h3>
                    <a class="mrow" style="padding:0" href="{{ route('profile.show', $group->creator->id) }}">
                        <x-lx2-avatar :user="$group->creator" :size="32" />
                        <div class="who"><div class="nm" style="font-size:13px">{{ $group->creator->id === $me->id ? 'Vous' : member_name($group->creator) }}</div><div class="role">{{ $group->creator->profile?->job_title ?? 'Membre LeadXchange' }}</div></div>
                    </a>
                </div>
                @endif

                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin:0 0 8px">
                        <h3 style="margin:0;font-size:13px;font-weight:600">Membres du groupe ({{ $group->members_count }})</h3>
                        @if($isAdmin)<button type="button" class="btn btn-outline-primary btn-sm" onclick="lx2Dialog('inviteGrpDialog')"><x-lx2-icon name="plus" />Inviter</button>@endif
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px">
                        @foreach($shownMembers as $m)
                        @php $r = $m->pivot->role; @endphp
                        <div class="mrow" style="padding:0;position:relative">
                            <a href="{{ route('profile.show', $m->id) }}"><x-lx2-avatar :user="$m" :size="32" /></a>
                            <div class="who">
                                <a class="nm" style="font-size:13px" href="{{ route('profile.show', $m->id) }}">{{ $m->id === $me->id ? 'Vous' : member_name($m) }}
                                    @if(isset($roleLabels[$r]))<span class="badge b-primary" style="height:18px;font-size:10.5px">{{ $roleLabels[$r] }}</span>@endif
                                </a>
                                <div class="role">{{ $m->profile?->job_title ?? 'Membre LeadXchange' }}</div>
                            </div>
                            @if($isAdmin && $m->id !== $me->id && $r !== 'owner' && ($isOwner || $r === 'member'))
                            <button type="button" class="circle-act" data-member-menu="{{ $m->id }}" aria-label="Gérer {{ member_name($m) }}" style="width:28px;height:28px"><x-lx2-icon name="ellipsis-vertical" /></button>
                            <div class="pop menu hidden member-pop" id="mm-{{ $m->id }}" style="right:0;top:calc(100% + 4px)">
                                @if($isOwner)
                                    @if($r === 'admin')
                                    <form method="POST" action="{{ route('groups.members.demote', [$group->id, $m->id]) }}">@csrf<button type="submit"><x-lx2-icon name="user" />Retirer les droits admin</button></form>
                                    @else
                                    <form method="POST" action="{{ route('groups.members.promote', [$group->id, $m->id]) }}">@csrf<button type="submit"><x-lx2-icon name="star" />Nommer admin</button></form>
                                    @endif
                                @endif
                                <form method="POST" action="{{ route('groups.members.destroy', [$group->id, $m->id]) }}" onsubmit="return confirm('Retirer ce membre du groupe ?')">@csrf @method('DELETE')<button type="submit"><x-lx2-icon name="x" />Retirer du groupe</button></form>
                                <form method="POST" action="{{ route('groups.members.block', [$group->id, $m->id]) }}" onsubmit="return confirm('Bloquer ce membre ? Il ne pourra plus rejoindre le groupe.')">@csrf<button type="submit" style="color:var(--destructive)"><x-lx2-icon name="shield" />Bloquer</button></form>
                            </div>
                            @endif
                        </div>
                        @endforeach
                        @if($members->count() > 15)<div class="help" style="margin:0">+ {{ $members->count() - 15 }} autres membres</div>@endif
                    </div>
                </div>

                <div style="font-size:12px;color:var(--muted-fg)">Créé le {{ $group->created_at->format('d/m/Y') }}@if($group->city) à {{ $group->city->name }}@endif</div>

                @if($isOwner)
                    <button type="button" class="btn btn-danger btn-block" onclick="lx2Dialog('deleteGrpDialog')"><x-lx2-icon name="trash-2" />Supprimer le groupe</button>
                @elseif($isMember)
                    <form method="POST" action="{{ route('groups.leave', $group->id) }}" onsubmit="return confirm('Quitter ce groupe ?')">@csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger-soft btn-block">Quitter</button></form>
                @endif
            </div>
        </div>
    </aside>
</div>

{{-- ── Fenêtres ─────────────────────────────────────────────── --}}
@if($isMember)
<div class="overlay hidden" id="pollDialog" data-dialog>
    <form class="dialog" id="pollForm">
        <div class="dh">
            <div><h3>Créer un sondage</h3><p>Visible par tous les membres du groupe</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:14px">
            <div class="field"><label class="label" for="pollQ">Question</label><input class="input" id="pollQ" maxlength="500" required placeholder="Posez votre question"></div>
            <div class="field"><span class="label">Options <span style="color:var(--muted-fg);font-weight:400">(2 à 4)</span></span>
                <div id="pollOpts" style="display:flex;flex-direction:column;gap:8px">
                    @for($i = 1; $i <= 4; $i++)<input class="input" maxlength="200" placeholder="Option {{ $i }}{{ $i > 2 ? ' (facultatif)' : '' }}" @if($i <= 2) required @endif>@endfor
                </div>
            </div>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="submit" class="btn btn-primary">Publier le sondage</button>
        </div>
    </form>
</div>
@endif

@if($isAdmin)
<div class="overlay hidden" id="activityDialog" data-dialog>
    <form class="dialog" method="POST" action="{{ route('groups.activities.store', $group->id) }}">
        @csrf
        <div class="dh">
            <div><h3>Planifier une activité</h3><p>Elle apparaîtra sur le mur du groupe.</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db" style="display:flex;flex-direction:column;gap:14px">
            <div class="field"><label class="label" for="actTitle">Titre</label><input class="input" id="actTitle" name="activity_title" value="{{ old('activity_title') }}" maxlength="150" required placeholder="Ex. Petit-déjeuner du réseau"></div>
            <div class="field"><label class="label" for="actDate">Date et heure</label><input class="input" id="actDate" name="activity_date" type="datetime-local" value="{{ old('activity_date') }}" min="{{ now()->format('Y-m-d\TH:i') }}" required></div>
            <div class="field"><label class="label" for="actBody">Description</label><textarea class="textarea" id="actBody" name="body" maxlength="1000" placeholder="Détails, lieu, programme…">{{ old('body') }}</textarea></div>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="submit" class="btn btn-primary">Créer l'activité</button>
        </div>
    </form>
</div>

<div class="overlay hidden" id="inviteGrpDialog" data-dialog>
    <div class="dialog">
        <div class="dh">
            <div><h3>Inviter au groupe</h3><p>{{ $group->name }}</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db">
            <div class="input-wrap" style="margin-bottom:6px"><x-lx2-icon name="search" /><input class="input" id="gInvQ" placeholder="{{ $canInviteAll ? 'Rechercher un membre (nom ou email)' : 'Rechercher une connexion' }}" autocomplete="off"></div>
            <div id="gInvList">
                @forelse($connections as $c)
                <div class="mrow inv-row" style="padding:8px 0" data-search="{{ mb_strtolower($c->first_name . ' ' . $c->last_name) }}">
                    <x-lx2-avatar :user="$c" :size="34" />
                    <div class="who"><div class="nm">{{ member_name($c) }}</div><div class="role">{{ $c->profile?->job_title ?? 'Membre LeadXchange' }}</div></div>
                    <button type="button" class="btn btn-primary btn-sm" data-ginv="{{ $c->id }}">Inviter</button>
                </div>
                @empty
                <div class="lx2-dd-state" id="gInvEmpty">{{ $canInviteAll ? 'Recherchez un membre par son nom ou son email.' : "Toutes vos connexions sont déjà membres, ou vous n'avez pas encore de connexion." }}</div>
                @endforelse
            </div>
            <div id="gInvSearchRes"></div>
        </div>
        @if($me->isAmbassador())
        <form class="df" method="POST" action="{{ route('groups.invite-region', $group->id) }}" onsubmit="return confirm('Inviter tous les membres de votre région ?')">
            @csrf <button type="submit" class="btn btn-outline btn-block"><x-lx2-icon name="users" />Inviter toute ma région</button>
        </form>
        @endif
    </div>
</div>
@endif

@if($isOwner)
<div class="overlay hidden" id="deleteGrpDialog" data-dialog>
    <form class="dialog" method="POST" action="{{ route('groups.destroy', $group->id) }}">
        @csrf @method('DELETE')
        <div class="dh">
            <div><h3>Supprimer le groupe ?</h3><p>Les publications et l'historique du groupe seront supprimés. Cette action est définitive.</p></div>
            <button type="button" class="x" data-close aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" data-close>Annuler</button>
            <button type="submit" class="btn btn-danger">Supprimer</button>
        </div>
    </form>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const GROUP_ID = {{ $group->id }};

    // Mur : afficher les messages les plus récents
    const scroll = $('chatScroll');
    if (scroll) scroll.scrollTop = scroll.scrollHeight;

    async function api(url, method, body) {
        const res = await fetch(url, { method, credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Authorization': 'Bearer ' + (window.API_TOKEN || '') },
            body: body ? JSON.stringify(body) : null });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Une erreur est survenue.');
        return data;
    }

    // Répondre à une publication
    document.addEventListener('click', (e) => {
        const r = e.target.closest('[data-reply]');
        if (r) { const f = $('reply-' + r.dataset.reply); f.hidden = !f.hidden; if (!f.hidden) f.querySelector('input').focus(); }
        const mm = e.target.closest('[data-member-menu]');
        document.querySelectorAll('.member-pop').forEach(p => { if (!mm || p.id !== 'mm-' + mm.dataset.memberMenu) p.classList.add('hidden'); });
        if (mm) $('mm-' + mm.dataset.memberMenu).classList.toggle('hidden');
        const pop = $('attachPop');
        if (pop && !e.target.closest('#attachBtn') && !e.target.closest('#attachPop')) pop.classList.add('hidden');
    });

    // Composer : menu +, photo
    $('attachBtn')?.addEventListener('click', () => $('attachPop').classList.toggle('hidden'));
    $('pickPhoto')?.addEventListener('click', () => { $('attachPop').classList.add('hidden'); $('postPhoto').click(); });
    $('postPhoto')?.addEventListener('change', (e) => {
        const f = e.target.files[0];
        if (f && f.size > 3 * 1024 * 1024) { toast('Image trop lourde (3 Mo maximum).', 'error'); e.target.value = ''; return; }
        $('photoChip').hidden = !f; $('photoChip').textContent = f ? '📷 ' + f.name : '';
    });
    $('composer')?.addEventListener('submit', (e) => {
        if (!$('wallMsg').value.trim() && !$('postPhoto').files.length) { e.preventDefault(); $('wallMsg').focus(); }
    });

    // Sondages (API existante /api/groups/{group}/polls)
    document.querySelectorAll('[data-poll]').forEach(poll => poll.addEventListener('click', async (e) => {
        const opt = e.target.closest('[data-option]');
        if (!opt || opt.disabled) return;
        try {
            const { data } = await api(`/api/groups/${GROUP_ID}/polls/${poll.dataset.poll}/vote`, 'POST', { option_id: +opt.dataset.option });
            const total = Math.max(1, data.total_votes);
            data.options.forEach(o => {
                const b = poll.querySelector(`[data-option="${o.id}"]`);
                b.querySelector('[data-count]').textContent = o.votes_count;
                b.querySelector('.bar i').style.width = Math.round(o.votes_count / total * 100) + '%';
                b.querySelector('.ring').classList.toggle('on', o.id === data.user_vote_option_id);
            });
            poll.querySelector('[data-total]').textContent = data.total_votes;
        } catch (err) { toast(err.message, 'error'); }
    }));
    $('pollForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const options = [...$('pollOpts').querySelectorAll('input')].map(i => i.value.trim()).filter(Boolean);
        if (options.length < 2) { toast('Ajoutez au moins 2 options.', 'error'); return; }
        try {
            await api(`/api/groups/${GROUP_ID}/polls`, 'POST', { question: $('pollQ').value.trim(), options });
            location.reload();
        } catch (err) { toast(err.message, 'error'); }
    });

    // Inviter au groupe (formulaire existant groups.invite)
    async function invite(btn) {
        if (btn.dataset.done) return;
        btn.disabled = true;
        try {
            const res = await fetch(@json(route('groups.invite', $group->id)), { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'text/html' },
                body: new URLSearchParams({ _token: CSRF, user_id: btn.dataset.ginv }) });
            if (!res.ok) throw new Error();
            btn.dataset.done = 1; btn.className = 'btn btn-soft btn-sm'; btn.textContent = 'Envoyé';
        } catch { btn.disabled = false; toast('Invitation impossible. Réessayez.', 'error'); }
    }
    document.getElementById('inviteGrpDialog')?.addEventListener('click', (e) => { const b = e.target.closest('[data-ginv]'); if (b) invite(b); });

    @if($isAdmin && $canInviteAll)
    // Consuls / ambassadeurs : recherche parmi tous les membres
    let t;
    $('gInvQ')?.addEventListener('input', (e) => {
        const q = e.target.value.trim();
        document.querySelectorAll('#gInvList .inv-row').forEach(r => r.hidden = q && !r.dataset.search.includes(q.toLowerCase()));
        clearTimeout(t);
        if (q.length < 2) { $('gInvSearchRes').innerHTML = ''; return; }
        t = setTimeout(async () => {
            try {
                const res = await fetch(@json(route('groups.users.search')) + `?q=${encodeURIComponent(q)}&group_id=${GROUP_ID}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const users = await res.json();
                $('gInvSearchRes').innerHTML = users.length ? '<div class="help">Tous les membres</div>' + users.map(u => `<div class="mrow" style="padding:8px 0"><span class="av-fb" style="width:34px;height:34px">${escapeHtml(u.name[0] || '?')}</span><div class="who"><div class="nm">${escapeHtml(u.name)}</div><div class="role">${escapeHtml(u.email)}</div></div><button type="button" class="btn btn-primary btn-sm" data-ginv="${u.id}">Inviter</button></div>`).join('') : '';
            } catch {}
        }, 300);
    });
    @elseif($isAdmin)
    $('gInvQ')?.addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim();
        document.querySelectorAll('#gInvList .inv-row').forEach(r => r.hidden = q && !r.dataset.search.includes(q));
    });
    @endif
})();
</script>
@endpush
