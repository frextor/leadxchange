{{-- ================================================================
     COMPOSE DRAWER — "Nouveau message"
     Slides from the right. Requires @alpinejs/focus for x-trap.
     All state lives in the parent inboxApp() Alpine component.
     ================================================================ --}}

{{-- Scrim --}}
<div
    class="cm-scrim"
    x-show="drawerOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="closeDrawer()"
    aria-hidden="true"
    x-cloak
></div>

{{-- Drawer panel --}}
<div
    role="dialog"
    aria-modal="true"
    aria-labelledby="cm-title"
    class="cm-drawer"
    x-show="drawerOpen"
    x-transition:enter="cm-drawer-anim-enter"
    x-transition:enter-start="cm-drawer-from"
    x-transition:enter-end="cm-drawer-to"
    x-transition:leave="cm-drawer-anim-leave"
    x-transition:leave-start="cm-drawer-to"
    x-transition:leave-end="cm-drawer-from"
    x-trap.noscroll="drawerOpen"
    @keydown="handleDrawerKey($event)"
    x-cloak
    style="transform-origin:right center;"
>

    {{-- ── HEADER ── --}}
    <div class="cm-header">
        <div class="cm-header-left">
            <span class="cm-eyebrow">Nouveau message</span>
            <h2 class="cm-title" id="cm-title">
                Écrire à <em>votre réseau</em>
            </h2>
        </div>
        <div class="cm-header-right">
            {{-- Draft pill (appears when form is dirty) --}}
            <span
                class="cm-draft-pill"
                x-show="drawerDirty"
                x-transition.opacity
            >
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
                Brouillon enregistré
            </span>
            {{-- Close --}}
            <button
                type="button"
                class="cm-close-btn"
                @click="closeDrawer()"
                aria-label="Fermer le drawer"
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── BODY ── --}}
    <div class="cm-body">

        {{-- ── ROW: À (Combobox destinataires) ── --}}
        <div class="cm-row" style="align-items:flex-start;">
            <span class="cm-row-label" id="cm-label-to">À</span>
            <div class="cm-row-field" style="flex:1;">
                <div class="cm-combo-wrap" x-data>

                    {{-- Token + input field --}}
                    <div
                        class="cm-combo-field"
                        role="combobox"
                        :aria-expanded="recipientDropdownOpen"
                        aria-haspopup="listbox"
                        aria-labelledby="cm-label-to"
                        @click="$el.querySelector('.cm-combo-input').focus()"
                    >
                        {{-- Tokens --}}
                        <template x-for="token in recipientTokens" :key="token.id">
                            <span class="cm-token">
                                <span class="cm-token-avatar" x-text="token.initials || initials(token.name)"></span>
                                <span x-text="token.name"></span>
                                <button
                                    type="button"
                                    class="cm-token-remove"
                                    @click.stop="removeRecipient(token.id)"
                                    :aria-label="'Retirer ' + token.name"
                                >×</button>
                            </span>
                        </template>

                        {{-- Input --}}
                        <input
                            type="text"
                            class="cm-combo-input"
                            x-model="recipientQuery"
                            :placeholder="recipientTokens.length === 0 ? 'À : taper un nom ou une entreprise…' : ''"
                            @focus="onComboFocus()"
                            @blur="onComboBlur()"
                            @input="onComboInput()"
                            @keydown="onComboKeydown($event)"
                            autocomplete="off"
                            aria-autocomplete="list"
                            aria-label="Ajouter un destinataire"
                        >
                    </div>

                    {{-- Dropdown --}}
                    <div
                        class="cm-combo-dropdown"
                        role="listbox"
                        x-show="recipientDropdownOpen && filteredConnections.length > 0"
                        x-cloak
                    >
                        <template x-for="c in filteredConnections" :key="c.id">
                            <button
                                type="button"
                                class="cm-combo-option"
                                role="option"
                                @mousedown.prevent
                                @click="pickConnection(c)"
                            >
                                <span class="cm-opt-avatar" x-text="c.initials || initials(c.name)"></span>
                                <span class="cm-opt-info">
                                    <strong class="cm-opt-name" x-text="c.name"></strong>
                                    <span class="cm-opt-sub"
                                          x-text="[c.position, c.company].filter(Boolean).join(' · ')"></span>
                                </span>
                                <span class="cm-opt-pts" x-text="c.points + ' pts'"></span>
                            </button>
                        </template>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── ROW: Objet ── --}}
        <div class="cm-row">
            <span class="cm-row-label" id="cm-label-subject">OBJET</span>
            <div class="cm-row-field" style="flex:1;">
                <input
                    type="text"
                    class="cm-subject-input"
                    x-model="composeSubject"
                    placeholder="Donnez un titre clair à votre message (facultatif)"
                    maxlength="150"
                    aria-labelledby="cm-label-subject"
                >
            </div>
        </div>

        {{-- ── ROW: Contexte (lead picker) ── --}}
        <div class="cm-row" style="align-items:flex-start;">
            <span class="cm-row-label" id="cm-label-ctx">CONTEXTE</span>
            <div class="cm-row-field" style="flex:1;">
                <div class="cm-lead-ctx-wrap" x-data>

                    {{-- Empty state: dashed button --}}
                    <template x-if="!linkedLead">
                        <div style="position:relative;">
                            <button
                                type="button"
                                class="cm-lead-btn"
                                @click="leadDropdownOpen = !leadDropdownOpen"
                                aria-labelledby="cm-label-ctx"
                                :aria-expanded="leadDropdownOpen"
                                aria-haspopup="listbox"
                            >
                                <span style="display:flex;align-items:center;gap:7px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                        <polyline points="15 3 21 3 21 9"/>
                                        <line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                    Lier à un lead
                                </span>
                                <em class="cm-lead-btn-em">optionnel · permet de garder le contexte</em>
                            </button>

                            {{-- Lead dropdown --}}
                            <div
                                class="cm-lead-dropdown"
                                role="listbox"
                                x-show="leadDropdownOpen && leads.length > 0"
                                @click.outside="leadDropdownOpen = false"
                                x-cloak
                            >
                                <template x-for="lead in leads" :key="lead.id">
                                    <button
                                        type="button"
                                        class="cm-lead-option"
                                        role="option"
                                        @mousedown.prevent
                                        @click="selectLead(lead)"
                                    >
                                        <span class="cm-lead-ref" x-text="lead.ref"></span>
                                        <span class="cm-heat-chip" :class="heatClass(lead.heat)" x-text="heatLabel(lead.heat)"></span>
                                        <span class="cm-lead-detail"
                                              x-text="[lead.company, lead.contact].filter(Boolean).join(' · ')"></span>
                                    </button>
                                </template>
                                <template x-if="leads.length === 0">
                                    <p style="padding:12px 10px;font-size:12px;color:var(--ink-400);text-align:center;">
                                        Aucun lead récent
                                    </p>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Selected state: chip --}}
                    <template x-if="linkedLead">
                        <div class="cm-lead-chip" role="status">
                            <span class="cm-lead-ref" x-text="linkedLead.ref"></span>
                            <span class="cm-heat-chip" :class="heatClass(linkedLead.heat)" x-text="heatLabel(linkedLead.heat)"></span>
                            <strong style="font-size:12.5px;color:var(--ink-900);" x-text="linkedLead.company"></strong>
                            <em style="font-size:12px;color:var(--ink-500);" x-text="linkedLead.contact ? '· ' + linkedLead.contact : ''"></em>
                            <button
                                type="button"
                                class="cm-lead-chip-remove"
                                @click="removeLead()"
                                aria-label="Retirer le lead lié"
                            >×</button>
                        </div>
                    </template>

                </div>
            </div>
        </div>

        {{-- ── EDITOR (full-width, no grid) ── --}}
        <div class="cm-editor">

            {{-- Toolbar --}}
            <div class="cm-toolbar" role="toolbar" aria-label="Outils de formatage">
                <button type="button" class="cm-toolbar-btn" title="Gras" aria-label="Gras"><strong>B</strong></button>
                <button type="button" class="cm-toolbar-btn" title="Italique" aria-label="Italique"><em>I</em></button>
                <button type="button" class="cm-toolbar-btn" title="Lien" aria-label="Insérer un lien">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                </button>

                <span class="cm-toolbar-sep" aria-hidden="true"></span>

                <button type="button" class="cm-toolbar-btn" title="Liste" aria-label="Liste à puces">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                        <line x1="8" y1="18" x2="21" y2="18"/>
                        <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/>
                        <line x1="3" y1="18" x2="3.01" y2="18"/>
                    </svg>
                </button>
                <button type="button" class="cm-toolbar-btn" title="Citation" aria-label="Bloc citation">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/>
                        <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
                    </svg>
                </button>
                <button type="button" class="cm-toolbar-btn" title="Code" aria-label="Bloc de code">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
                    </svg>
                </button>

                {{-- Push-right group --}}
                <span class="cm-toolbar-sep cm-toolbar-push" aria-hidden="true"></span>

                <button type="button" class="cm-toolbar-btn" title="Pièce jointe" aria-label="Joindre un fichier">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                </button>
                <button type="button" class="cm-toolbar-btn teal" title="Mention" aria-label="Mentionner quelqu'un">@</button>
            </div>

            {{-- Textarea --}}
            <textarea
                class="cm-textarea"
                x-model="composeBody"
                placeholder="Bonjour,&#10;&#10;Écrivez ici votre message…"
                @keydown="handleDrawerKey($event)"
                aria-label="Corps du message"
                aria-multiline="true"
            ></textarea>

            {{-- Meta footer (word + char count) --}}
            <div class="cm-meta-footer">
                <span class="cm-word-count">
                    <span x-text="wordCount" style="font-family:var(--font-mono);"></span> mots
                    ·
                    <span x-text="charCount" style="font-family:var(--font-mono);"></span> caractères
                </span>
                <span class="cm-meta-kbd">
                    <kbd style="font-family:var(--font-mono);font-size:11px;background:var(--surface);border:1px solid var(--ink-200);border-radius:3px;padding:1px 5px;">⌘</kbd>
                    <kbd style="font-family:var(--font-mono);font-size:11px;background:var(--surface);border:1px solid var(--ink-200);border-radius:3px;padding:1px 5px;">↵</kbd>
                    pour envoyer
                </span>
            </div>

        </div>{{-- /cm-editor --}}

        {{-- ── OPTIONS row ── --}}
        <div class="cm-options-row">
            <label class="cm-check-label">
                <input
                    type="checkbox"
                    class="cm-check-input"
                    x-model="composeUrgent"
                    aria-label="Demander une réponse rapide"
                >
                <span class="cm-check-box">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3.5">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                <span class="cm-check-text">
                    <strong class="cm-check-strong">Demander une réponse rapide</strong>
                    <em class="cm-check-em">Affiche un indicateur d'urgence chez le destinataire (à utiliser avec parcimonie).</em>
                </span>
            </label>
        </div>

    </div>{{-- /cm-body --}}

    {{-- ── FOOTER ── --}}
    <div class="cm-footer">
        <div class="cm-footer-left">
            <span class="cm-footer-recip">
                <span x-text="recipientTokens.length" style="font-family:var(--font-mono);font-weight:600;"></span>
                <span x-text="recipientTokens.length === 1 ? ' destinataire' : ' destinataires'"></span>
            </span>
            <span class="cm-footer-lead" x-show="linkedLead" x-text="linkedLead ? 'Lié à ' + linkedLead.ref : ''"></span>
        </div>
        <div class="cm-footer-right">
            <button type="button" class="cm-btn-ghost-sm" disabled title="Prochainement">
                Programmer l'envoi
            </button>
            <button type="button" class="cm-btn-ghost-sm" @click="closeDrawer()">
                Annuler
            </button>
            <button
                type="button"
                class="cm-btn-send"
                @click="sendCompose()"
                :disabled="!canSend || drawerSending"
                :aria-disabled="!canSend || drawerSending"
            >
                <span x-text="drawerSending ? 'Envoi…' : 'Envoyer le message'"></span>
                <svg x-show="!drawerSending" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>
    </div>

</div>{{-- /cm-drawer --}}
