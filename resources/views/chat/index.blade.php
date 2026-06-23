@extends('layouts.app')
@section('title', 'Messages — LeadXchange')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat-lx.css') }}">
@endpush

@push('scripts')
<script>
// ── cxChat Alpine component ───────────────────────────────────────────────
const CX_CSRF   = '{{ csrf_token() }}';
const CX_UID    = {{ $otherUser?->id ?? 'null' }};
let   CX_LASTID = {{ $messages->last()?->id ?? 0 }};

function cxChat() {
    return {
        contactOpen: true,
        sending: false,
        mediaUrl: null,
        mediaType: null,
        mediaFilename: null,

        init() {
            this.$nextTick(() => {
                const t = document.getElementById('cx-msgs');
                if (t) t.scrollTop = t.scrollHeight;
            });
            if (CX_UID) {
                setInterval(() => cxPoll(this), 3000);
            }
        },

        toggleContact() { this.contactOpen = !this.contactOpen; },

        autoGrow(el) {
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        },

        onKey(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
                e.preventDefault();
                this.send();
                return;
            }
            if (CX_UID) {
                fetch('/chat/' + CX_UID + '/typing', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CX_CSRF, 'Content-Type': 'application/json' },
                }).catch(() => {});
            }
        },

        async send() {
            if (!CX_UID) return;
            const ta   = this.$refs.composer;
            const body = ta?.value?.trim();
            if (!body && !this.mediaUrl) return;
            if (this.sending) return;
            this.sending = true;
            try {
                const res = await fetch('/chat/' + CX_UID, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CX_CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        body,
                        type: this.mediaType || 'text',
                        media_url: this.mediaUrl,
                        filename: this.mediaFilename,
                    }),
                });
                const msg = await res.json();
                if (msg.id) {
                    cxAppendMsg(msg);
                    CX_LASTID = msg.id;
                    if (ta) { ta.value = ''; ta.style.height = 'auto'; }
                    this.mediaUrl = null;
                    this.mediaType = null;
                    this.mediaFilename = null;
                    document.getElementById('cx-mprev').style.display = 'none';
                }
            } finally {
                this.sending = false;
            }
        },
    };
}

// ── Append message to thread ─────────────────────────────────────────────
function cxAppendMsg(msg) {
    const t = document.getElementById('cx-msgs');
    if (!t) return;
    document.getElementById('cx-empty')?.remove();

    const row = document.createElement('div');
    row.id        = 'msg-' + msg.id;
    row.className = 'cx-msg-row' + (msg.is_mine ? ' mine' : '');

    const bub     = document.createElement('div');
    bub.className = 'cx-bubble ' + (msg.is_mine ? 'mine' : 'theirs');

    if (msg.type === 'image' && msg.media_url) {
        const img  = document.createElement('img');
        img.src    = msg.media_url;
        img.style.cssText = 'max-width:220px;border-radius:10px;display:block;cursor:pointer';
        img.onclick = () => window.open(msg.media_url, '_blank');
        bub.appendChild(img);
        if (msg.body) {
            const cap = document.createElement('div');
            cap.style.cssText = 'padding:4px 0 0;font-size:13px';
            cap.textContent = msg.body;
            bub.appendChild(cap);
        }
    } else if (msg.type === 'file' && msg.media_url) {
        const a    = document.createElement('a');
        a.href     = msg.media_url;
        a.target   = '_blank';
        a.rel      = 'noopener';
        a.className = 'cx-bubble-file';
        a.textContent = msg.filename || 'Fichier';
        bub.appendChild(a);
    } else {
        const txt = document.createElement('div');
        txt.textContent = msg.body || '';
        bub.appendChild(txt);
    }

    const d    = new Date(msg.created_at);
    const time = document.createElement('div');
    time.className = 'cx-bubble-time lx-num';
    time.textContent = d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
    if (msg.is_mine) {
        const tick = document.createElement('span');
        tick.className = 'cx-tick';
        tick.textContent = ' ✓✓';
        time.appendChild(tick);
    }
    bub.appendChild(time);
    row.appendChild(bub);
    t.appendChild(row);
    t.scrollTop = t.scrollHeight;
}

// ── Poll for new messages ─────────────────────────────────────────────────
async function cxPoll(alpine) {
    if (!CX_UID) return;
    try {
        const r = await fetch('/chat/' + CX_UID + '/poll/' + CX_LASTID);
        const d = await r.json();
        if (d.messages?.length) {
            d.messages.forEach(m => { cxAppendMsg(m); CX_LASTID = m.id; });
        }
        const ti = document.getElementById('cx-typing');
        if (ti) ti.style.display = d.other_typing ? 'flex' : 'none';
    } catch {}
}

// ── Helpers ───────────────────────────────────────────────────────────────
function cxFilter(val, listId) {
    document.querySelectorAll('#' + listId + ' [data-name]').forEach(el => {
        el.style.display = el.dataset.name.includes(val.toLowerCase()) ? '' : 'none';
    });
}

async function cxFile(input, uid) {
    const file = input.files[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    const r = await fetch('/chat/' + uid + '/media', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CX_CSRF },
        body: fd,
    });
    const d = await r.json();
    if (!d.url) return;

    const alpine = document.querySelector('.cx-wrap')?._x_dataStack?.[0];
    if (alpine) { alpine.mediaUrl = d.url; alpine.mediaType = d.type; alpine.mediaFilename = file.name; }

    const w = document.getElementById('cx-mprev');
    w.style.display = 'flex';
    if (d.type === 'image') {
        const img   = document.getElementById('cx-pi');
        img.src     = d.url;
        img.style.display = 'block';
        document.getElementById('cx-pn').textContent = '';
    } else {
        document.getElementById('cx-pi').style.display = 'none';
        document.getElementById('cx-pn').textContent = file.name;
    }
    input.value = '';
}

function cxCancelMedia() {
    const alpine = document.querySelector('.cx-wrap')?._x_dataStack?.[0];
    if (alpine) { alpine.mediaUrl = null; alpine.mediaType = null; alpine.mediaFilename = null; }
    document.getElementById('cx-mprev').style.display = 'none';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('cx-modal')?.classList.remove('open');
});
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

@section('content')

@if(!$canChat)
<x-upgrade-gate feature="chat" :full-page="true"
    title="Messagerie non disponible"
    description="La messagerie instantanée n'est pas incluse dans votre plan actuel." />
@else

@php
  $me      = auth()->user();
  $palette = ['#14A98C','#6C7BE0','#C58A1B','#C9442E','#8B5CF6','#0891B2'];
  $aColor  = fn($u) => $palette[$u->id % count($palette)];
  $aInit   = fn($u) => strtoupper(mb_substr($u->first_name,0,1).mb_substr($u->last_name,0,1));
@endphp

<div class="cx-wrap" x-data="cxChat()" x-init="init()">

{{-- ── LEFT: Conversations ─────────────────────────────────────────── --}}
<aside class="cx-sidebar">
    <div class="cx-sidebar__head">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <span class="cx-sidebar__title">Messages</span>
            <button class="cx-btn-new" onclick="document.getElementById('cx-modal').classList.add('open')">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouveau
            </button>
        </div>
        <div class="cx-search">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" placeholder="Rechercher…" oninput="cxFilter(this.value,'cx-conv-list')">
        </div>
    </div>

    <div class="cx-conv-list cx-scroll" id="cx-conv-list">
        @forelse($conversations as $item)
        @php
            $other   = $item['other'];
            $conv    = $item['conv'];
            $unread  = $item['unread'];
            $preview = $conv->lastMessage?->body ?? 'Aucun message';
            $isMe    = $conv->lastMessage?->sender_id === $me->id;
            $active  = $otherUser && $otherUser->id == $other->id;
        @endphp
        <a href="{{ route('chat.index', ['with' => $other->id]) }}"
           class="cx-conv-item {{ $active ? 'active' : '' }}"
           data-name="{{ strtolower($other->full_name ?? $other->first_name) }}">
            <div class="cx-avatar">
                <div class="cx-avatar__img" style="background:{{ $aColor($other) }};">
                    @if($other->profile?->avatar)<img src="{{ $other->profile->avatar_url }}" alt="">
                    @else{{ $aInit($other) }}@endif
                </div>
                <span class="cx-avatar__presence"></span>
            </div>
            <div class="cx-conv-body">
                <div class="cx-conv-row1">
                    <span class="cx-conv-name {{ $unread > 0 ? 'unread' : '' }}">{{ $other->full_name ?? $other->first_name }}</span>
                    @if($conv->last_message_at)
                    <span class="cx-conv-time lx-num">{{ $conv->last_message_at->diffForHumans(null,true) }}</span>
                    @endif
                </div>
                <div class="cx-conv-row2">
                    <span class="cx-conv-preview {{ $unread > 0 ? 'unread' : '' }}">
                        {{ $isMe ? 'Vous : ' : '' }}{{ mb_strimwidth($preview, 0, 48, '…') }}
                    </span>
                    @if($unread > 0)
                    <span class="cx-unread-badge lx-num">{{ $unread }}</span>
                    @elseif($isMe)
                    <span class="cx-read-tick">✓✓</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <div style="padding:40px 20px;text-align:center;color:var(--ink-faint);">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;display:block;opacity:.3"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <div style="font-size:13px;font-weight:500;color:var(--ink-dim);">Aucune conversation</div>
        </div>
        @endforelse
    </div>
</aside>

{{-- ── CENTER: Thread ──────────────────────────────────────────────── --}}
@if($otherUser)
@php $oc = $aColor($otherUser); $oi = $aInit($otherUser); @endphp
<main class="cx-thread">

    {{-- Header --}}
    <header class="cx-thread-head">
        <div class="cx-avatar" style="flex-shrink:0;">
            <div class="cx-avatar__img" style="background:{{ $oc }};width:40px;height:40px;font-size:14px;">
                @if($otherUser->profile?->avatar)<img src="{{ $otherUser->profile->avatar_url }}" alt="">
                @else{{ $oi }}@endif
            </div>
            <span class="cx-avatar__presence"></span>
        </div>
        <div class="cx-thread-head__info">
            <div class="cx-thread-head__name">{{ $otherUser->full_name }}</div>
            @php $sub = collect([$otherUser->profile?->job_title, $otherUser->company?->name])->filter()->implode(' · '); @endphp
            @if($sub)<div class="cx-thread-head__sub">{{ $sub }}</div>@endif
        </div>
        <button class="cx-btn-lead">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Lead
        </button>
        <button class="cx-head-btn" :class="{ active: contactOpen }" @click="toggleContact()" title="Informations contact">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </button>
    </header>

    {{-- Messages --}}
    <div id="cx-msgs" class="cx-messages cx-scroll">

        @forelse($messages as $i => $msg)
        @php
            $isMine  = $msg->sender_id === $me->id;
            $msgType = $msg->type ?? 'text';
            $prev    = $messages[$i-1] ?? null;
            $next    = $messages[$i+1] ?? null;
            $newDay  = !$prev || !$prev->created_at->isSameDay($msg->created_at);
            $grouped = $prev && $prev->sender_id===$msg->sender_id && $prev->created_at->diffInMinutes($msg->created_at)<3;
            $isLast  = !$next || $next->sender_id!==$msg->sender_id;
        @endphp

        @if($newDay)
        <div class="cx-day-sep">
            <span>{{ $msg->created_at->isToday() ? "Aujourd'hui" : ($msg->created_at->isYesterday() ? 'Hier' : $msg->created_at->isoFormat('D MMMM YYYY')) }}</span>
        </div>
        @endif

        <div id="msg-{{ $msg->id }}" class="cx-msg-row {{ $isMine ? 'mine' : '' }} {{ $grouped ? 'grouped' : '' }}">
            @if(!$isMine)
            <div class="cx-msg-avatar {{ !$isLast ? 'hidden' : '' }}" style="background:{{ $oc }};">{{ $oi }}</div>
            @endif
            <div class="cx-bubble {{ $isMine ? 'mine' : 'theirs' }}">
                @if($msgType === 'image' && $msg->media_url)
                    <img src="{{ $msg->media_url }}" alt="image" style="max-width:220px;border-radius:10px;display:block;cursor:pointer;" onclick="window.open(this.src,'_blank')">
                    @if($msg->body)<div style="font-size:13px;padding:4px 0 0;">{{ $msg->body }}</div>@endif
                @elseif($msgType === 'file' && $msg->media_url)
                    <a href="{{ $msg->media_url }}" target="_blank" rel="noopener" class="cx-bubble-file">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:.7"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        {{ $msg->filename ?? 'Fichier' }}
                    </a>
                @else
                    <div>{{ $msg->body }}</div>
                @endif
                <div class="cx-bubble-time lx-num">
                    {{ $msg->created_at->format('H:i') }}
                    @if($isMine)<span class="cx-tick {{ $msg->read_at ? 'read' : '' }}">✓✓</span>@endif
                </div>
            </div>
        </div>
        @empty
        <div id="cx-empty" class="cx-empty" style="flex:1;min-height:200px;">
            <div class="cx-empty__icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--ink-faint)" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="cx-empty__title">Dites bonjour à {{ $otherUser->first_name }} !</div>
            <div class="cx-empty__sub">Envoyez votre premier message pour démarrer la conversation.</div>
        </div>
        @endforelse

        <div id="cx-typing" class="cx-msg-row" style="display:none;margin-top:4px;">
            <div class="cx-msg-avatar" style="background:{{ $oc }};">{{ $oi }}</div>
            <div class="cx-typing"><span class="cx-typing-dot"></span><span class="cx-typing-dot"></span><span class="cx-typing-dot"></span></div>
        </div>
    </div>

    {{-- Composer --}}
    <div class="cx-composer">
        <div id="cx-mprev" class="cx-media-preview" style="display:none;">
            <img id="cx-pi" src="" style="display:none;">
            <span id="cx-pn" style="font-size:12px;color:var(--accent-ink);"></span>
            <button type="button" onclick="cxCancelMedia()" style="background:none;border:none;cursor:pointer;color:var(--ink-faint);font-size:18px;margin-left:auto;">×</button>
        </div>
        <div class="cx-composer-card">
            <textarea x-ref="composer" class="cx-composer-textarea" placeholder="Écrivez un message…" rows="1"
                      @input="autoGrow($el)" @keydown="onKey($event)"></textarea>
            <div class="cx-composer-bar">
                <input type="file" id="cx-fi" accept="image/*,.pdf,.doc,.docx" style="display:none;" onchange="cxFile(this,{{ $otherUser->id }})">
                <button type="button" class="cx-composer-action" onclick="document.getElementById('cx-fi').click()" title="Pièce jointe">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                </button>
                <span class="cx-hint">Envoyer <kbd>⌘↵</kbd></span>
                <button type="button" class="cx-send-btn" :disabled="sending" @click="send()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m22 2-7 20-4-9-9-4 20-7z"/></svg>
                </button>
            </div>
        </div>
    </div>
</main>

{{-- ── RIGHT: Contact panel ─────────────────────────────────────────── --}}
<aside class="cx-contact" x-show="contactOpen">
    <div class="cx-contact__head">
        <div class="cx-contact__avatar" style="background:{{ $oc }};">
            @if($otherUser->profile?->avatar)
                <img src="{{ $otherUser->profile->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
            @else{{ $oi }}@endif
            <span class="cx-avatar__presence"></span>
        </div>
        <div class="cx-contact__name">{{ $otherUser->full_name }}</div>
        @if($sub ?? '')<div class="cx-contact__sub">{{ $sub }}</div>@endif
        @if($otherUser->subscription?->plan)
        <div class="cx-tier lx-num">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            {{ $otherUser->subscription->plan->label }}
        </div>
        @endif
    </div>

    <div class="cx-contact__actions">
        <button class="cx-contact-btn primary">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Envoyer un lead
        </button>
        <a href="{{ route('profile.show', $otherUser->id) }}" class="cx-contact-btn ghost">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profil
        </a>
    </div>

    <div class="cx-contact-section">
        <div class="cx-section-title">Statistiques</div>
        @php
            $ex = \App\Models\Lead::where(fn($q)=>$q
                ->where(fn($q2)=>$q2->where('sender_id',$me->id)->where('receiver_id',$otherUser->id))
                ->orWhere(fn($q2)=>$q2->where('sender_id',$otherUser->id)->where('receiver_id',$me->id))
            )->count();
        @endphp
        <div class="cx-stat-row">
            <span class="cx-stat-label">Leads échangés</span>
            <span class="cx-stat-val lx-num">{{ $ex }}</span>
        </div>
        <div class="cx-stat-row">
            <span class="cx-stat-label">Score</span>
            <span class="cx-stat-val lx-num">{{ $otherUser->points_balance ?? 0 }} pts</span>
        </div>
        <div class="cx-stat-row">
            <span class="cx-stat-label">Badge</span>
            <span class="cx-stat-val" style="font-size:12px;">{{ ucfirst($otherUser->badge_level ?? 'neutre') }}</span>
        </div>
    </div>

    @if($otherUser->city)
    <div class="cx-contact-section">
        <div class="cx-section-title">Localisation</div>
        <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--ink-dim);">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ $otherUser->city->name }}
        </div>
    </div>
    @endif
</aside>

@else
<div class="cx-empty" style="flex:1;">
    <div class="cx-empty__icon">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--ink-faint)" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
    </div>
    <div class="cx-empty__title">Vos messages</div>
    <div class="cx-empty__sub">Sélectionnez une conversation ou démarrez-en une nouvelle.</div>
    <button class="cx-btn-new" onclick="document.getElementById('cx-modal').classList.add('open')">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau message
    </button>
</div>
@endif

</div>{{-- cx-wrap --}}

{{-- ── Modal nouvelle conversation ─────────────────────────────────── --}}
<div id="cx-modal" class="cx-modal-backdrop" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="cx-modal">
        <div class="cx-modal-head">
            <span class="cx-modal-title">Nouveau message</span>
            <button class="cx-modal-close" onclick="document.getElementById('cx-modal').classList.remove('open')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding:11px 14px;border-bottom:1px solid var(--line);">
            <div class="cx-search">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" placeholder="Rechercher une connexion…" oninput="cxFilter(this.value,'cx-clist')">
            </div>
        </div>
        <div id="cx-clist" class="cx-scroll" style="max-height:300px;">
            @forelse($connections as $c)
            @php $cc=$aColor($c); $ci=$aInit($c); @endphp
            <a href="{{ route('chat.index', ['with' => $c->id]) }}"
               data-name="{{ strtolower($c->full_name ?? $c->first_name) }}"
               style="display:flex;align-items:center;gap:11px;padding:10px 16px;text-decoration:none;border-bottom:1px solid var(--line-soft);transition:background .12s;"
               onmouseover="this.style.background='var(--card-2)'" onmouseout="this.style.background=''">
                <div style="width:38px;height:38px;border-radius:50%;background:{{ $cc }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">{{ $ci }}</div>
                <div>
                    <div style="font-size:13.5px;font-weight:600;color:var(--ink-on);">{{ $c->full_name ?? $c->first_name }}</div>
                    @if($c->company)<div style="font-size:12px;color:var(--ink-dim);">{{ $c->company->name }}</div>@endif
                </div>
            </a>
            @empty
            <div style="padding:28px;text-align:center;color:var(--ink-faint);font-size:13px;">Aucune connexion.</div>
            @endforelse
        </div>
    </div>
</div>

@endif

{{-- scripts already pushed at top --}}

@endsection
