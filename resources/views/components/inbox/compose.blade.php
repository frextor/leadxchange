{{-- Inline reply — shown only for message kind --}}
<div
    class="ix-reply-card"
    x-show="selectedItem && selectedItem.kind === 'message'"
    x-cloak
>
    <div class="ix-reply-header"
         x-text="'Répondre à ' + (selectedItem?.actor_name || 'l\'expéditeur')">
    </div>

    <textarea
        class="ix-reply-textarea"
        x-model="replyBody"
        placeholder="Tapez votre réponse…"
        rows="3"
        @keydown="handleReplyKey($event)"
        aria-label="Corps de la réponse"
    ></textarea>

    <div class="ix-reply-footer">
        <span class="ix-reply-hint">⌘↵ pour envoyer</span>
        <button
            type="button"
            class="ix-btn-sm"
            @click="sendReply()"
            :disabled="!replyBody.trim() || replySending"
        >
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
            <span x-text="replySending ? 'Envoi…' : 'Envoyer'"></span>
        </button>
    </div>
</div>
