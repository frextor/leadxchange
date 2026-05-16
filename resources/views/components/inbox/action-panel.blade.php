{{-- Shown only when kind = lead-received (regardless of whether already acted upon) --}}
<div
    class="ix-action-panel"
    x-show="selectedItem && selectedItem.kind === 'lead-received'"
    x-cloak
>
    <div class="ix-action-header">
        <span class="ix-action-label">Action requise</span>
        <span class="ix-action-deadline">Réponse attendue sous 7 jours</span>
    </div>
    <div class="ix-action-btns">
        <button
            type="button"
            class="ix-btn-primary"
            @click="acceptLead()"
            :disabled="acceptSending"
        >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span x-text="acceptSending ? 'En cours…' : 'Accepter le lead'"></span>
        </button>

        <button
            type="button"
            class="ix-btn-outline"
            @click="rejectLead()"
            :disabled="rejectSending"
        >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            <span x-text="rejectSending ? 'En cours…' : 'Refuser'"></span>
        </button>

        <a
            class="ix-btn-ghost"
            :href="selectedItem?.lead_id ? '/leads/' + selectedItem.lead_id : '#'"
        >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                <polyline points="15 3 21 3 21 9"/>
                <line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
            Voir le détail
        </a>
    </div>
</div>
