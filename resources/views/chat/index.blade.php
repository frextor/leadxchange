@extends('layouts.app')

@section('title', 'Messages — LeadXchange')

@push('styles')
<style>
    .chat-wrap   { height: calc(100vh - 72px); display: flex; overflow: hidden; }
    .conv-list   { width: 320px; flex-shrink: 0; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; background: #fff; }
    .conv-item   { display: flex; align-items: center; gap: 12px; padding: 14px 16px; cursor: pointer; border-bottom: 1px solid #f3f4f6; text-decoration: none; transition: background .15s; }
    .conv-item:hover { background: #f9fafb; }
    .conv-item.active { background: #f0fdfa; }
    .conv-avatar { width: 44px; height: 44px; border-radius: 50%; background: #1E8F88; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 15px; flex-shrink: 0; }
    .conv-avatar.purple { background: #6366f1; }
    .conv-avatar.amber  { background: #f59e0b; }
    .unread-badge { background: #1E8F88; color: #fff; border-radius: 9999px; font-size: 11px; font-weight: 700; min-width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; padding: 0 5px; }
    .msg-thread  { flex: 1; display: flex; flex-direction: column; background: #f8fafc; overflow: hidden; }
    .bubble-mine  { background: #1E8F88; color: #fff; border-radius: 18px 18px 4px 18px; }
    .bubble-their { background: #fff; color: #1f2937; border-radius: 18px 18px 18px 4px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    .chat-input { resize: none; min-height: 44px; max-height: 120px; }
    .empty-state-chat { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #9ca3af; gap: 12px; }
    .typing-dot {
        display: inline-block; width: 7px; height: 7px; border-radius: 50%;
        background: #9ca3af; animation: typingBounce 1.2s infinite;
    }
    .typing-dot:nth-child(2) { animation-delay: .2s; }
    .typing-dot:nth-child(3) { animation-delay: .4s; }
    @@keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: .6; }
        30%            { transform: translateY(-6px); opacity: 1; }
    }
</style>
@endpush

@section('content')
<div class="chat-wrap">

    {{-- ── Left: conversation list ── --}}
    <div class="conv-list">

        {{-- Header --}}
        <div style="padding:16px; border-bottom:1px solid #e5e7eb;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h2 style="font-size:18px; font-weight:700; color:#111827;">Messages</h2>
                <button onclick="document.getElementById('newChatModal').style.display='flex'"
                        style="background:#1E8F88; color:#fff; border:none; border-radius:8px; padding:6px 14px; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    New
                </button>
            </div>
            <input type="text" placeholder="Search conversations…" oninput="filterConvs(this.value)"
                   style="width:100%; padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
        </div>

        {{-- List --}}
        <div style="overflow-y:auto; flex:1;" class="custom-scrollbar">
            @forelse($conversations as $item)
                @php
                    $colors = ['#1E8F88','#6366f1','#f59e0b','#ef4444','#8b5cf6'];
                    $avatarColor = $colors[$item['other']->id % count($colors)];
                    $initials = strtoupper(mb_substr($item['other']->first_name,0,1).mb_substr($item['other']->last_name,0,1));
                    $preview = $item['conv']->lastMessage?->body ?? 'No messages yet';
                    $isActive = $otherUser && $otherUser->id == $item['other']->id;
                @endphp
                <a href="{{ route('chat.index', ['with' => $item['other']->id]) }}"
                   class="conv-item {{ $isActive ? 'active' : '' }}"
                   data-name="{{ strtolower($item['other']->full_name) }}">
                    <div class="conv-avatar" style="background:{{ $avatarColor }};">{{ $initials }}</div>
                    <div style="flex:1; min-width:0;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                            <span style="font-size:14px; font-weight:{{ $item['unread'] > 0 ? '700' : '600' }}; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px;">
                                {{ $item['other']->full_name }}
                            </span>
                            @if($item['conv']->last_message_at)
                                <span style="font-size:11px; color:#9ca3af; flex-shrink:0; margin-left:4px;">
                                    {{ $item['conv']->last_message_at->diffForHumans(null, true) }}
                                </span>
                            @endif
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:13px; color:{{ $item['unread'] > 0 ? '#111827' : '#6b7280' }}; font-weight:{{ $item['unread'] > 0 ? '500' : '400' }}; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px;">
                                {{ mb_strimwidth($preview, 0, 50, '…') }}
                            </span>
                            @if($item['unread'] > 0)
                                <span class="unread-badge">{{ $item['unread'] }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div style="padding:40px 24px; text-align:center; color:#9ca3af; font-size:13px; line-height:1.6;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;" opacity=".4"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <div style="font-weight:500; margin-bottom:4px;">No conversations yet</div>
                    <div>Start chatting with one of your connections.</div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- ── Right: message thread ── --}}
    @if($otherUser)
    @php
        $colors = ['#1E8F88','#6366f1','#f59e0b','#ef4444','#8b5cf6'];
        $otherColor = $colors[$otherUser->id % count($colors)];
        $otherInitials = strtoupper(mb_substr($otherUser->first_name,0,1).mb_substr($otherUser->last_name,0,1));
    @endphp
    <div class="msg-thread">

        {{-- Thread header --}}
        <div style="background:#fff; border-bottom:1px solid #e5e7eb; padding:14px 24px; display:flex; align-items:center; gap:14px; flex-shrink:0;">
            <div class="conv-avatar" style="background:{{ $otherColor }};">{{ $otherInitials }}</div>
            <div>
                <div style="font-weight:700; font-size:15px; color:#111827;">{{ $otherUser->full_name }}</div>
                @if($otherUser->position || $otherUser->company)
                    <div style="font-size:13px; color:#6b7280;">
                        {{ $otherUser->position }}{{ $otherUser->position && $otherUser->company ? ' · ' : '' }}{{ $otherUser->company?->name }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Messages --}}
        <div id="msgThread" class="custom-scrollbar" style="flex:1; overflow-y:auto; padding:24px; display:flex; flex-direction:column; gap:10px;">
            @forelse($messages as $msg)
                @php $isMine = $msg->sender_id === auth()->id(); @endphp
                <div id="msg-{{ $msg->id }}" style="display:flex; justify-content:{{ $isMine ? 'flex-end' : 'flex-start' }};">
                    <div class="{{ $isMine ? 'bubble-mine' : 'bubble-their' }}" style="max-width:65%; padding:10px 14px;">
                        <div style="font-size:14px; line-height:1.55; word-break:break-word;">{{ $msg->body }}</div>
                        <div style="font-size:11px; margin-top:5px; opacity:.65; text-align:right;">
                            {{ $msg->created_at->format('H:i') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state-chat" id="emptyState">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" opacity=".4"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span style="font-size:14px;">Say hello to {{ $otherUser->first_name }}!</span>
                </div>
            @endforelse
        </div>

        {{-- Input bar --}}
        <div style="background:#fff; border-top:1px solid #e5e7eb; padding:16px 24px; flex-shrink:0;">
            <form id="sendForm" onsubmit="sendMessage(event)" style="display:flex; gap:12px; align-items:flex-end;">
                <textarea id="msgInput"
                          class="chat-input"
                          placeholder="Type a message… (Enter to send, Shift+Enter for new line)"
                          rows="1"
                          style="flex:1; padding:10px 14px; border:1px solid #e5e7eb; border-radius:12px; font-size:14px; font-family:inherit; outline:none; line-height:1.5; box-sizing:border-box; transition:border-color .15s;"
                          onfocus="this.style.borderColor='#1E8F88'"
                          onblur="this.style.borderColor='#e5e7eb'"></textarea>
                <button type="submit" id="sendBtn"
                        style="background:#1E8F88; color:#fff; border:none; border-radius:12px; width:44px; height:44px; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; transition:opacity .15s;"
                        title="Send">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    @else
    {{-- Empty — no conversation selected --}}
    <div style="flex:1; display:flex; align-items:center; justify-content:center; background:#f8fafc;">
        <div style="text-align:center; color:#9ca3af;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" style="margin:0 auto 16px;" opacity=".3"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <div style="font-size:16px; font-weight:600; margin-bottom:6px;">Your messages</div>
            <div style="font-size:14px; max-width:240px; margin:0 auto;">Send a message to one of your connections to get started.</div>
            <button onclick="document.getElementById('newChatModal').style.display='flex'"
                    style="margin-top:20px; background:#1E8F88; color:#fff; border:none; border-radius:10px; padding:10px 24px; font-size:14px; font-weight:600; cursor:pointer;">
                Start a conversation
            </button>
        </div>
    </div>
    @endif

</div>

{{-- ── New Chat Modal ── --}}
<div id="newChatModal" onclick="if(event.target===this)this.style.display='none'"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:999; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:16px; width:420px; max-width:95vw; box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden;">
        <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size:16px; font-weight:700; color:#111827;">New Message</h3>
            <button onclick="document.getElementById('newChatModal').style.display='none'"
                    style="background:none; border:none; cursor:pointer; color:#6b7280; font-size:20px; line-height:1;">×</button>
        </div>
        <div style="padding:16px 24px; border-bottom:1px solid #e5e7eb;">
            <input type="text" placeholder="Search connections…" oninput="filterContacts(this.value)"
                   style="width:100%; padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
        </div>
        <div id="contactList" style="max-height:320px; overflow-y:auto;" class="custom-scrollbar">
            @forelse($connections as $c)
                @php
                    $colors2 = ['#1E8F88','#6366f1','#f59e0b','#ef4444','#8b5cf6'];
                    $cColor = $colors2[$c->id % count($colors2)];
                    $cInit = strtoupper(mb_substr($c->first_name,0,1).mb_substr($c->last_name,0,1));
                @endphp
                <div onclick="window.location.href='{{ route('chat.index', ['with' => $c->id]) }}'"
                     data-name="{{ strtolower($c->full_name) }}"
                     style="display:flex; align-items:center; gap:12px; padding:12px 24px; cursor:pointer; transition:background .15s;"
                     onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <div style="width:40px; height:40px; border-radius:50%; background:{{ $cColor }}; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:14px; flex-shrink:0;">
                        {{ $cInit }}
                    </div>
                    <div>
                        <div style="font-size:14px; font-weight:600; color:#111827;">{{ $c->full_name }}</div>
                        @if($c->position)
                            <div style="font-size:12px; color:#6b7280;">{{ $c->position }}{{ $c->company ? ' · '.$c->company->name : '' }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="padding:32px 24px; text-align:center; color:#9ca3af; font-size:13px;">
                    No connections yet. Connect with members to start chatting.
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const WITH_USER_ID = @json($otherUser?->id);
    const CSRF         = document.querySelector('meta[name=csrf-token]').content;
    let   lastMsgId    = {{ $messages->last()?->id ?? 0 }};
    let   sending      = false;
    let   lastTypingSent = 0;

    // ── Scroll to bottom on load ──
    const thread = document.getElementById('msgThread');
    if (thread) thread.scrollTop = thread.scrollHeight;

    // ── Auto-resize textarea + typing signal ──
    const input = document.getElementById('msgInput');
    if (input) {
        input.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';

            // Envoyer le signal "en train d'écrire" (max 1 fois / 2 s)
            if (!WITH_USER_ID) return;
            const now = Date.now();
            if (now - lastTypingSent > 2000) {
                lastTypingSent = now;
                fetch(`/chat/${WITH_USER_ID}/typing`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF },
                }).catch(() => {});
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('sendForm').dispatchEvent(new Event('submit'));
            }
        });
        input.focus();
    }

    // ── Send message ──
    window.sendMessage = async function (e) {
        e.preventDefault();
        if (!input || sending || !WITH_USER_ID) return;

        const body = input.value.trim();
        if (!body) return;

        sending = true;
        const btn = document.getElementById('sendBtn');
        if (btn) btn.style.opacity = '.5';

        // Optimistic render
        const tmpId = 'tmp-' + Date.now();
        appendBubble({ id: tmpId, body, created_at: new Date().toISOString(), is_mine: true });
        input.value = '';
        input.style.height = 'auto';

        try {
            const res  = await fetch(`/chat/${WITH_USER_ID}`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body:    JSON.stringify({ body }),
            });
            const data = await res.json();
            if (data.id) {
                const tmp = document.getElementById('msg-' + tmpId);
                if (tmp) tmp.id = 'msg-' + data.id;
                lastMsgId = Math.max(lastMsgId, data.id);
            }
        } catch (err) {
            console.error('Send failed', err);
        } finally {
            sending = false;
            if (btn) btn.style.opacity = '1';
        }
    };

    // ── Indicateur "en train d'écrire" ──
    function setTypingIndicator(active) {
        const t   = document.getElementById('msgThread');
        const existing = document.getElementById('typingIndicator');
        if (active && !existing && t) {
            const wrap = document.createElement('div');
            wrap.id = 'typingIndicator';
            wrap.style.cssText = 'display:flex;justify-content:flex-start;margin-top:4px;';
            wrap.innerHTML =
                '<div class="bubble-their" style="padding:10px 14px;">' +
                    '<div style="display:flex;gap:5px;align-items:center;height:16px;">' +
                        '<span class="typing-dot"></span>' +
                        '<span class="typing-dot"></span>' +
                        '<span class="typing-dot"></span>' +
                    '</div>' +
                '</div>';
            t.appendChild(wrap);
            t.scrollTop = t.scrollHeight;
        } else if (!active && existing) {
            existing.remove();
        }
    }

    // ── Poll pour les nouveaux messages (toutes les 3 s) ──
    if (WITH_USER_ID) {
        setInterval(async () => {
            try {
                const res  = await fetch(`/chat/${WITH_USER_ID}/poll/${lastMsgId}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                });
                const data = await res.json();

                // Afficher/masquer l'indicateur de frappe
                setTypingIndicator(!!data.other_typing);

                if (data.messages?.length) {
                    // Masquer l'indicateur avant d'ajouter le vrai message
                    setTypingIndicator(false);
                    data.messages.forEach(m => {
                        if (!document.getElementById('msg-' + m.id)) {
                            appendBubble(m);
                            lastMsgId = Math.max(lastMsgId, m.id);
                        }
                    });
                }
            } catch (_) {}
        }, 3000);
    }

    // ── Append a message bubble ──
    function appendBubble(msg) {
        const t = document.getElementById('msgThread');
        if (!t) return;

        // Remove empty state
        const es = document.getElementById('emptyState');
        if (es) es.remove();

        const time = new Date(msg.created_at).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        const wrap = document.createElement('div');
        wrap.id = 'msg-' + msg.id;
        wrap.style.cssText = 'display:flex;justify-content:' + (msg.is_mine ? 'flex-end' : 'flex-start') + ';';

        const bubble = document.createElement('div');
        bubble.className = msg.is_mine ? 'bubble-mine' : 'bubble-their';
        bubble.style.cssText = 'max-width:65%;padding:10px 14px;';
        bubble.innerHTML =
            '<div style="font-size:14px;line-height:1.55;word-break:break-word;">' + escHtml(msg.body) + '</div>' +
            '<div style="font-size:11px;margin-top:5px;opacity:.65;text-align:right;">' + time + '</div>';

        wrap.appendChild(bubble);
        t.appendChild(wrap);
        t.scrollTop = t.scrollHeight;
    }

    function escHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/\n/g, '<br>');
    }

    // ── Search conversations ──
    window.filterConvs = function (q) {
        document.querySelectorAll('.conv-item[data-name]').forEach(el => {
            el.style.display = el.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
        });
    };

    // ── Search contacts in modal ──
    window.filterContacts = function (q) {
        document.querySelectorAll('#contactList [data-name]').forEach(el => {
            el.style.display = el.dataset.name.includes(q.toLowerCase()) ? '' : 'none';
        });
    };
})();
</script>
@endpush
@endsection
