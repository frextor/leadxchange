@extends('layouts.app2')

@section('title', 'Chat — LeadXchange')

{{-- Écran « Chat » — maquette LeadXchange WEB › pageChat(id) --}}

@php
    $me = auth()->user();

    // Libellé d'heure façon maquette : 13:04 · Hier · Lun. · 12/09
    $when = function ($d) {
        if (! $d) return '';
        if ($d->isToday())     return $d->format('H:i');
        if ($d->isYesterday()) return 'Hier';
        if ($d->gt(now()->subDays(6)->startOfDay())) return ucfirst(rtrim($d->locale('fr')->isoFormat('ddd'), '.')) . '.';
        return $d->format('d/m');
    };
    $preview = function ($m) use ($me) {
        if (! $m) return 'Aucun message';
        $txt = match ($m->type ?? 'text') {
            'image' => 'Photo',
            'file'  => $m->filename ?: 'Fichier',
            default => $m->body,
        };
        return ($m->sender_id === $me->id ? 'Vous: ' : '') . \Illuminate\Support\Str::limit($txt, 60);
    };
    $role = fn ($u) => collect([$u->profile?->job_title, $u->company?->name])->filter()->implode(' · ');
    $dayLabel = fn ($d) => $d->isToday() ? "Aujourd'hui" : ($d->isYesterday() ? 'Hier' : ucfirst($d->locale('fr')->isoFormat('dddd D MMMM YYYY')));
@endphp

@section('content')
<x-lx2-header title="Chat" sub="Vos conversations privées" />

<div class="card convs {{ $selected ? 'sel' : 'no-sel' }} {{ $canChat ? '' : 'locked' }}" id="convs">
    <div class="conv-list">
        <div style="padding:12px"><div class="input-wrap"><x-lx2-icon name="search" /><input class="input" id="convSearch" type="search" placeholder="Rechercher" autocomplete="off"></div></div>
        <div class="items" id="convItems">
            @foreach($conversations as $item)
                @php $other = $item['other']; $conv = $item['conv']; @endphp
                <a class="conv {{ $otherUser && $otherUser->id === $other->id ? 'on' : '' }}" href="{{ route('chat.index', ['with' => $other->id]) }}"
                   data-uid="{{ $other->id }}" data-search="{{ mb_strtolower(member_name($other)) }}">
                    <x-lx2-avatar :user="$other" :size="40" />
                    <div class="who">
                        <div class="nm">{{ member_name($other) }}<small>{{ $when($conv->last_message_at) }}</small></div>
                        <div class="pv">{{ $preview($conv->lastMessage) }}</div>
                    </div>
                    @if($item['unread'] > 0)<span class="unread" title="{{ $item['unread'] }} non lu{{ $item['unread'] > 1 ? 's' : '' }}"></span>@endif
                </a>
            @endforeach

            {{-- Connexions sans conversation : visibles uniquement pendant une recherche --}}
            @if($connections->isNotEmpty())
            <div class="conv-sep" data-contacts hidden>Démarrer une conversation</div>
            @foreach($connections as $c)
                <a class="conv" href="{{ route('chat.index', ['with' => $c->id]) }}" data-contact data-search="{{ mb_strtolower(member_name($c)) }}" hidden>
                    <x-lx2-avatar :user="$c" :size="40" />
                    <div class="who">
                        <div class="nm">{{ member_name($c) }}</div>
                        <div class="pv">{{ $role($c) ?: 'Nouvelle conversation' }}</div>
                    </div>
                </a>
            @endforeach
            @endif

            @if($conversations->isEmpty())
            <div class="empty" id="convEmpty" style="padding:28px 18px;text-align:center;color:var(--muted-fg);font-size:13px">
                Aucune conversation.@if($connections->isNotEmpty()) Recherchez une connexion pour lui écrire.@else <a class="link" href="{{ route('connections.index') }}">Développez votre réseau</a>@endif
            </div>
            @endif
            <div class="empty" id="convNoMatch" hidden style="padding:28px 18px;text-align:center;color:var(--muted-fg);font-size:13px">Aucun résultat.</div>
        </div>
    </div>

    <div class="conv-view">
        @if($otherUser)
        <div class="card-h">
            <div style="display:flex;align-items:center;gap:10px;min-width:0">
                <a class="back" href="{{ route('chat.index') }}" data-mobile-back aria-label="Retour"><x-lx2-icon name="arrow-left" /></a>
                <x-lx2-avatar :user="$otherUser" :size="34" />
                <div style="min-width:0">
                    <div style="font-weight:600;font-size:14px">{{ member_name($otherUser) }}</div>
                    <div style="font-size:12px;color:var(--muted-fg);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        <span id="chatRole">{{ $role($otherUser) ?: 'Membre LeadXchange' }}</span><span id="chatTyping" hidden style="color:var(--primary)">est en train d'écrire…</span>
                    </div>
                </div>
            </div>
            <a class="btn btn-outline btn-sm" href="{{ route('profile.show', $otherUser->id) }}">Voir le profil</a>
        </div>

        <div class="chat-scroll" id="chatScroll">
            @php $prevDay = null; @endphp
            @forelse($messages as $msg)
                @php $day = $msg->created_at->toDateString(); @endphp
                @if($day !== $prevDay)
                    <div class="day-sep" data-day="{{ $day }}">{{ $dayLabel($msg->created_at) }}</div>
                    @php $prevDay = $day; @endphp
                @endif
                @php $mine = $msg->sender_id === $me->id; $type = $msg->type ?? 'text'; @endphp
                <div class="msg {{ $mine ? 'me' : '' }}" id="msg-{{ $msg->id }}">
                    <x-lx2-avatar :user="$mine ? $me : $otherUser" :size="30" />
                    <div>
                        @if($type === 'image' && $msg->media_url)
                            <a class="imgb" href="{{ $msg->media_url }}" target="_blank" rel="noopener" style="display:block"><img src="{{ $msg->media_url }}" alt="Image" style="display:block;max-width:100%"></a>
                            @if($msg->body)<div class="bubble" style="margin-top:6px">{{ $msg->body }}</div>@endif
                            <div class="time" style="color:var(--muted-fg)">{{ $msg->created_at->format('H:i') }}</div>
                        @else
                            <div class="bubble">
                                @if($type === 'file' && $msg->media_url)
                                    <a class="file-link" href="{{ $msg->media_url }}" target="_blank" rel="noopener"><x-lx2-icon name="file-text" />{{ $msg->filename ?: 'Fichier' }}</a>
                                    @if($msg->body)<div style="margin-top:4px">{{ $msg->body }}</div>@endif
                                @else
                                    {!! nl2br(e($msg->body)) !!}
                                @endif
                                <div class="time">{{ $msg->created_at->format('H:i') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty" id="chatEmpty" style="margin:auto;text-align:center;color:var(--muted-fg);font-size:13.5px">
                    <b style="display:block;color:var(--fg);font-size:14px;margin-bottom:4px">Dites bonjour à {{ $otherUser->first_name }} !</b>
                    Envoyez votre premier message pour démarrer la conversation.
                </div>
            @endforelse
        </div>

        <div class="attach-prev" id="attachPrev" hidden>
            <img id="attachImg" alt="" hidden>
            <span id="attachName"></span>
            <button type="button" class="icon-btn" id="attachCancel" aria-label="Retirer la pièce jointe"><x-lx2-icon name="x" /></button>
        </div>
        <form class="composer" id="chatForm" autocomplete="off">
            <input type="file" id="chatFile" accept="image/*,.pdf,.doc,.docx" hidden>
            <button type="button" class="icon-btn" id="chatAttach" aria-label="Joindre un fichier"><x-lx2-icon name="plus" /></button>
            <input class="input" id="chatMsg" placeholder="Envoyer un message…" maxlength="3000" @disabled(! $canChat)>
            <button type="submit" class="send-btn" id="chatSend" aria-label="Envoyer"><x-lx2-icon name="arrow-up-right" /></button>
        </form>
        @else
        <div class="empty" style="margin:auto;text-align:center;color:var(--muted-fg);font-size:13.5px;padding:24px">
            <b style="display:block;color:var(--fg);font-size:15px;margin-bottom:4px">Vos messages</b>
            Sélectionnez une conversation ou recherchez une connexion pour lui écrire.
        </div>
        @endif
    </div>

    @unless($canChat)
    <div class="lock-over"><div class="card">
        <span class="ico" style="margin:0 auto 10px"><x-lx2-icon name="lock" /></span>
        <b style="display:block;font-size:15px">Messagerie non disponible</b>
        <p style="color:var(--muted-fg);font-size:13px;margin:6px 0 14px">La messagerie instantanée n'est pas incluse dans votre plan actuel.</p>
        <button type="button" class="btn btn-primary" onclick="openUpgradeModal('can_receive_mail')">Voir les plans</button>
    </div></div>
    @endunless
</div>
@endsection

@push('scripts')
<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    const UID  = @json($otherUser?->id);
    const CAN  = @json((bool) $canChat);
    const ME_AV    = @json(view('components.lx2-avatar', ['user' => $me, 'size' => 30])->render());
    const OTHER_AV = @json($otherUser ? view('components.lx2-avatar', ['user' => $otherUser, 'size' => 30])->render() : '');
    const ICON_FILE = @json(\App\Support\Lx2Icons::svg('file-text'));
    let lastId = @json($messages->last()?->id ?? 0);
    const esc = (s) => (s ?? '').toString().replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const hhmm = (d) => String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    const ymd  = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

    /* ── Recherche : conversations + connexions sans conversation ───────── */
    $('convSearch').addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase().trim();
        let shown = 0, contacts = 0;
        document.querySelectorAll('#convItems .conv').forEach(a => {
            const match = a.dataset.search.includes(q);
            const ok = a.hasAttribute('data-contact') ? (q && match) : (!q || match);
            a.hidden = !ok;
            if (ok) { shown++; if (a.hasAttribute('data-contact')) contacts++; }
        });
        const sep = document.querySelector('[data-contacts]');
        if (sep) sep.hidden = contacts === 0;
        if ($('convEmpty')) $('convEmpty').hidden = !!q;
        $('convNoMatch').hidden = !q || shown > 0;
    });

    if (!UID) return;
    const box = $('chatScroll');
    const scrollDown = () => { box.scrollTop = box.scrollHeight; };
    scrollDown();
    window.addEventListener('load', scrollDown);
    box.querySelectorAll('img').forEach(i => i.addEventListener('load', scrollDown, { once: true }));
    // Passage liste ↔ fil (mobile) ou rotation : on reste en bas si on y était
    let atBottom = true;
    box.addEventListener('scroll', () => { atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 40; });
    window.addEventListener('resize', () => { if (atBottom) scrollDown(); });

    /* ── Rendu d'un message (même structure que le rendu Blade) ─────────── */
    function append(m) {
        if (document.getElementById('msg-' + m.id)) return;
        $('chatEmpty')?.remove();
        const d = new Date(m.created_at);
        const days = box.querySelectorAll('.day-sep');
        if (!days.length || days[days.length - 1].dataset.day !== ymd(d)) {
            const sep = document.createElement('div');
            sep.className = 'day-sep'; sep.dataset.day = ymd(d); sep.textContent = "Aujourd'hui";
            box.appendChild(sep);
        }
        let inner;
        if (m.type === 'image' && m.media_url) {
            inner = `<a class="imgb" href="${esc(m.media_url)}" target="_blank" rel="noopener" style="display:block"><img src="${esc(m.media_url)}" alt="Image" style="display:block;max-width:100%"></a>`
                  + (m.body ? `<div class="bubble" style="margin-top:6px">${esc(m.body)}</div>` : '')
                  + `<div class="time" style="color:var(--muted-fg)">${hhmm(d)}</div>`;
        } else {
            const content = (m.type === 'file' && m.media_url)
                ? `<a class="file-link" href="${esc(m.media_url)}" target="_blank" rel="noopener">${ICON_FILE}${esc(m.filename || 'Fichier')}</a>` + (m.body ? `<div style="margin-top:4px">${esc(m.body)}</div>` : '')
                : esc(m.body).replace(/\n/g, '<br>');
            inner = `<div class="bubble">${content}<div class="time">${hhmm(d)}</div></div>`;
        }
        const row = document.createElement('div');
        row.className = 'msg' + (m.is_mine ? ' me' : '');
        row.id = 'msg-' + m.id;
        row.innerHTML = (m.is_mine ? ME_AV : OTHER_AV) + `<div>${inner}</div>`;
        box.appendChild(row);
        box.querySelectorAll('img').forEach(i => i.addEventListener('load', scrollDown, { once: true }));
        scrollDown();
    }

    /* ── Pièce jointe (POST /chat/{id}/media) ───────────────────────────── */
    let media = null;
    const clearMedia = () => { media = null; $('attachPrev').hidden = true; $('attachImg').hidden = true; $('attachName').textContent = ''; };
    $('chatAttach').addEventListener('click', () => CAN && $('chatFile').click());
    $('attachCancel').addEventListener('click', clearMedia);
    $('chatFile').addEventListener('change', async (e) => {
        const f = e.target.files[0];
        e.target.value = '';
        if (!f) return;
        if (f.size > 10 * 1024 * 1024) { toast('Fichier trop lourd (10 Mo maximum).', 'error'); return; }
        const fd = new FormData(); fd.append('file', f);
        $('attachPrev').hidden = false; $('attachName').textContent = 'Envoi de ' + f.name + '…';
        try {
            const r = await fetch('/chat/' + UID + '/media', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }, body: fd });
            const d = await r.json();
            if (!r.ok || !d.url) throw new Error(d.message || d.error || 'Envoi impossible');
            media = { url: d.url, type: d.type, filename: d.filename || f.name };
            if (d.type === 'image') { $('attachImg').src = d.url; $('attachImg').hidden = false; $('attachName').textContent = ''; }
            else { $('attachName').textContent = media.filename; }
            $('chatMsg').focus();
        } catch (err) { clearMedia(); toast(err.message || 'Envoi impossible', 'error'); }
    });

    /* ── Envoi (POST /chat/{id}) ────────────────────────────────────────── */
    let sending = false;
    $('chatForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = $('chatMsg');
        const body = input.value.trim();
        if (!CAN || sending || (!body && !media)) return;
        sending = true; $('chatSend').disabled = true;
        try {
            const r = await fetch('/chat/' + UID, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ body: body || null, type: media ? media.type : 'text', media_url: media?.url ?? null, filename: media?.filename ?? null }),
            });
            const m = await r.json();
            if (!r.ok || !m.id) throw new Error(m.message || m.error || 'Message non envoyé');
            append(m);
            lastId = Math.max(lastId, m.id);
            input.value = ''; clearMedia();
            updateRow(UID, m);
        } catch (err) { toast(err.message || 'Message non envoyé', 'error'); }
        finally { sending = false; $('chatSend').disabled = false; input.focus(); }
    });

    /* ── Indicateur « en train d'écrire » ───────────────────────────────── */
    let typingSent = 0;
    $('chatMsg').addEventListener('input', () => {
        if (Date.now() - typingSent < 3000) return;
        typingSent = Date.now();
        fetch('/chat/' + UID + '/typing', { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).catch(() => {});
    });

    /* ── Nouveaux messages (GET /chat/{id}/poll/{lastId}) ───────────────── */
    let polling = false;
    async function poll() {
        if (polling || document.hidden) return;
        polling = true;
        try {
            const r = await fetch('/chat/' + UID + '/poll/' + lastId, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return;
            const d = await r.json();
            (d.messages || []).forEach(m => { append(m); lastId = Math.max(lastId, m.id); updateRow(UID, m); });
            $('chatTyping').hidden = !d.other_typing;
            $('chatRole').hidden = !!d.other_typing;
        } catch (e) { /* réseau */ }
        finally { polling = false; }
    }
    setInterval(poll, 1500);

    /* ── Liste : aperçu du dernier message + non-lus des autres conversations ── */
    function updateRow(uid, m) {
        const a = document.querySelector(`#convItems .conv[data-uid="${uid}"]`);
        if (!a) return;
        const txt = m.type === 'image' ? 'Photo' : (m.type === 'file' ? (m.filename || 'Fichier') : (m.body || ''));
        a.querySelector('.pv').textContent = (m.is_mine ? 'Vous: ' : '') + txt;
        a.querySelector('.nm small').textContent = hhmm(new Date(m.created_at));
        a.parentNode.insertBefore(a, a.parentNode.firstChild);
    }
    const known = new Map();
    document.querySelectorAll('#convItems .conv[data-uid]').forEach(a => known.set(a.dataset.uid, null));
    async function checkInbox() {
        if (document.hidden) return;
        try {
            const r = await fetch('/chat/inbox/check', { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return;
            const d = await r.json();
            let reload = false;
            for (const c of d.conversations) {
                const id = String(c.other_id);
                if (id === String(UID)) continue;
                if (!known.has(id)) { reload = true; continue; }
                if (known.get(id) === null) { known.set(id, c.last_msg_id); continue; }
                if (c.last_msg_id > known.get(id)) { known.set(id, c.last_msg_id); reload = true; }
            }
            if (reload) {
                const html = await (await fetch(location.href, { credentials: 'same-origin', headers: { 'Accept': 'text/html' } })).text();
                const fresh = new DOMParser().parseFromString(html, 'text/html').getElementById('convItems');
                if (fresh) {
                    $('convItems').innerHTML = fresh.innerHTML;
                    fresh.querySelectorAll('.conv[data-uid]').forEach(a => { if (!known.has(a.dataset.uid)) known.set(a.dataset.uid, null); });
                    $('convSearch').dispatchEvent(new Event('input'));
                }
            }
        } catch (e) { /* réseau */ }
    }
    setInterval(checkInbox, 5000);
    checkInbox();
})();
</script>
@endpush
