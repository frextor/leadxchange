/* ================================================================
   InboxApp — Alpine.js v3 component
   ================================================================ */

function inboxApp() {
    return {

        /* ---- Data ---- */
        items:       window.__inboxData        || [],
        connections: window.__inboxConnections || [],
        leads:       window.__inboxLeads       || [],

        /* ---- List state ---- */
        selectedId: null,
        filter:     'all',
        search:     '',

        /* ---- Detail state ---- */
        replyBody:     '',
        replySending:  false,
        acceptSending: false,
        rejectSending: false,

        /* ---- Drawer state ---- */
        drawerOpen:    false,
        drawerDirty:   false,
        drawerSending: false,

        /* ---- Compose form ---- */
        composeSubject: '',
        composeBody:    '',
        composeUrgent:  false,

        /* ---- Combobox ---- */
        recipientTokens:      [],
        recipientQuery:       '',
        recipientDropdownOpen: false,
        _recipientBlurTimer:  null,

        /* ---- Lead context ---- */
        linkedLead:       null,
        leadDropdownOpen: false,

        /* ---- Toasts ---- */
        toasts:  [],
        _toastId: 0,

        /* ================================================================
           ICON MAP (SVG strings by kind)
           ================================================================ */
        iconMap: {
            'lead-received':  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="7 10 12 15 17 10"/><line x1="12" y1="3" x2="12" y2="15"/><path d="M5 19h14"/></svg>`,
            'lead-accepted':  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`,
            'lead-rejected':  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
            'lead-converted': `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/></svg>`,
            'lead-reminder':  `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
            'message':        `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`,
            'network':        `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>`,
            'connection':     `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
            'deadline':       `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
            'rating':         `<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>`,
        },

        /* ================================================================
           TAG MAP (label + css class) by kind
           ================================================================ */
        tagMap: {
            'lead-received':  { label: 'Lead reçu',   cls: 'ix-tag-lead'  },
            'lead-accepted':  { label: 'Accepté',     cls: 'ix-tag-pos'   },
            'lead-rejected':  { label: 'Refusé',      cls: 'ix-tag-neg'   },
            'lead-converted': { label: 'Converti',    cls: 'ix-tag-pos'   },
            'lead-reminder':  { label: 'Rappel',      cls: 'ix-tag-neg'   },
            'message':        { label: 'Message',     cls: 'ix-tag-msg'   },
            'network':        { label: 'Réseau',      cls: 'ix-tag-net'   },
            'connection':     { label: 'Connexion',   cls: 'ix-tag-net'   },
            'deadline':       { label: 'Échéance',    cls: 'ix-tag-neg'   },
            'rating':         { label: 'Évaluation',  cls: 'ix-tag-bonus' },
        },

        /* ================================================================
           COMPUTED PROPERTIES
           ================================================================ */

        get selectedItem() {
            return this.items.find(i => i.id === this.selectedId) || null;
        },

        get unreadCount() {
            return this.items.filter(i => !i.read && !i.archived).length;
        },

        get filterCounts() {
            const na = this.items.filter(i => !i.archived);
            return {
                all:      na.length,
                unread:   na.filter(i => !i.read).length,
                mentions: 0,
                leads:    na.filter(i => i.kind && i.kind.startsWith('lead')).length,
                system:   na.filter(i => !i.actor_id || i.kind === 'deadline' || i.kind === 'lead-reminder').length,
                network:  na.filter(i => i.kind === 'network' || i.kind === 'connection').length,
                archived: this.items.filter(i => i.archived).length,
            };
        },

        get filteredItems() {
            let list;
            if (this.filter === 'archived') {
                list = this.items.filter(i => i.archived);
            } else {
                list = this.items.filter(i => !i.archived);
                if      (this.filter === 'unread')   list = list.filter(i => !i.read);
                else if (this.filter === 'leads')    list = list.filter(i => i.kind && i.kind.startsWith('lead'));
                else if (this.filter === 'system')   list = list.filter(i => !i.actor_id || i.kind === 'deadline' || i.kind === 'lead-reminder');
                else if (this.filter === 'network')  list = list.filter(i => i.kind === 'network' || i.kind === 'connection');
                /* mentions: show all for now */
            }

            const q = this.search.trim().toLowerCase();
            if (q) {
                list = list.filter(i => {
                    const hay = [i.title, i.preview, i.actor_name, i.body].filter(Boolean).join(' ').toLowerCase();
                    return hay.includes(q);
                });
            }

            return list;
        },

        get groupedItems() {
            const now     = new Date();
            const today   = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const weekAgo = new Date(today); weekAgo.setDate(weekAgo.getDate() - 7);

            const buckets = { today: [], week: [], older: [] };
            for (const item of this.filteredItems) {
                const d = new Date(item.ts);
                if      (d >= today)    buckets.today.push(item);
                else if (d >= weekAgo)  buckets.week.push(item);
                else                    buckets.older.push(item);
            }

            return [
                buckets.today.length ? { key: 'today', label: "Aujourd'hui",   items: buckets.today } : null,
                buckets.week.length  ? { key: 'week',  label: 'Cette semaine', items: buckets.week  } : null,
                buckets.older.length ? { key: 'older', label: 'Plus ancien',   items: buckets.older } : null,
            ].filter(Boolean);
        },

        get filteredConnections() {
            const q     = this.recipientQuery.trim().toLowerCase();
            const taken = new Set(this.recipientTokens.map(t => t.id));
            return this.connections.filter(c => {
                if (taken.has(c.id)) return false;
                if (!q) return true;
                return [c.name, c.company, c.position].filter(Boolean).join(' ').toLowerCase().includes(q);
            }).slice(0, 8);
        },

        get wordCount() {
            return this.composeBody.trim() ? this.composeBody.trim().split(/\s+/).length : 0;
        },

        get charCount() {
            return this.composeBody.length;
        },

        get canSend() {
            return this.recipientTokens.length > 0 && this.composeBody.trim().length > 0;
        },

        /* ================================================================
           INIT
           ================================================================ */
        init() {
            /* Auto-select first item */
            if (this.filteredItems.length > 0) {
                this.selectedId = this.filteredItems[0].id;
                /* Don't auto-mark as read on init — only on explicit select */
            }

            /* Global keydown */
            document.addEventListener('keydown', (e) => this.handleKey(e));

            /* Dirty-watch for compose form */
            this.$watch('composeSubject', () => { if (this.drawerOpen) this.drawerDirty = true; });
            this.$watch('composeBody',    () => { if (this.drawerOpen) this.drawerDirty = true; });
            this.$watch('recipientTokens', () => { if (this.drawerOpen) this.drawerDirty = true; });
        },

        /* ================================================================
           LIST ACTIONS
           ================================================================ */
        select(id) {
            this.selectedId = id;
            this._markRead(id);
            /* On mobile: open detail overlay */
            const detail = document.querySelector('.ix-detail-col');
            if (detail) detail.classList.add('ix-detail-open');
        },

        backToList() {
            const detail = document.querySelector('.ix-detail-col');
            if (detail) detail.classList.remove('ix-detail-open');
            this.selectedId = null;
        },

        _markRead(id) {
            const item = this.items.find(i => i.id === id);
            if (!item || item.read) return;
            item.read = true;
            this._post(`/inbox/${id}/read`)
                .catch(() => { if (item) item.read = false; });
        },

        toggleRead(id) {
            const item = this.items.find(i => i.id === id);
            if (!item) return;
            const prev = item.read;
            item.read = !prev;
            this._post(`/inbox/${id}/read`)
                .catch(() => { if (item) item.read = prev; });
        },

        archive(id) {
            const item = this.items.find(i => i.id === id);
            if (!item) return;
            const prevArchived = item.archived;
            item.archived = true;
            if (this.selectedId === id) {
                this.selectedId = null;
                document.querySelector('.ix-detail-col')?.classList.remove('ix-detail-open');
            }
            this.toast('Archivé.', 'info');
            this._post(`/inbox/${id}/archive`)
                .catch(() => {
                    if (item) item.archived = prevArchived;
                    this.toast('Erreur lors de l\'archivage.', 'error');
                });
        },

        /* ================================================================
           LEAD ACTIONS (from detail action panel)
           ================================================================ */
        acceptLead() {
            const item = this.selectedItem;
            if (!item || !item.lead_id || this.acceptSending) return;
            this.acceptSending = true;
            this._post(`/leads/${item.lead_id}/accept`)
                .then(() => {
                    item.kind = 'lead-accepted';
                    this.toast('Lead accepté !', 'success');
                })
                .catch(() => this.toast('Erreur lors de l\'acceptation.', 'error'))
                .finally(() => { this.acceptSending = false; });
        },

        rejectLead() {
            const item = this.selectedItem;
            if (!item || !item.lead_id || this.rejectSending) return;
            this.rejectSending = true;
            this._post(`/leads/${item.lead_id}/reject`)
                .then(() => {
                    item.kind = 'lead-rejected';
                    this.toast('Lead refusé.', 'info');
                })
                .catch(() => this.toast('Erreur.', 'error'))
                .finally(() => { this.rejectSending = false; });
        },

        /* ================================================================
           INLINE REPLY
           ================================================================ */
        sendReply() {
            const item = this.selectedItem;
            if (!item || !this.replyBody.trim() || this.replySending) return;
            this.replySending = true;
            this._postJSON(`/inbox/${item.id}/reply`, { body: this.replyBody })
                .then(() => {
                    this.toast('Réponse envoyée.', 'success');
                    this.replyBody = '';
                })
                .catch(() => this.toast('Erreur lors de l\'envoi.', 'error'))
                .finally(() => { this.replySending = false; });
        },

        /* ================================================================
           DRAWER
           ================================================================ */
        openDrawer() {
            this.drawerOpen  = true;
            this.drawerDirty = false;
        },

        closeDrawer() {
            this.drawerOpen = false;
            setTimeout(() => this._resetCompose(), 280);
        },

        _resetCompose() {
            this.composeSubject      = '';
            this.composeBody         = '';
            this.composeUrgent       = false;
            this.recipientTokens     = [];
            this.recipientQuery      = '';
            this.recipientDropdownOpen = false;
            this.linkedLead          = null;
            this.leadDropdownOpen    = false;
            this.drawerDirty         = false;
        },

        sendCompose() {
            if (!this.canSend || this.drawerSending) return;
            this.drawerSending = true;
            this._postJSON('/inbox/compose', {
                recipient_ids: this.recipientTokens.map(t => t.id),
                subject:       this.composeSubject,
                body:          this.composeBody,
                lead_id:       this.linkedLead?.id || null,
                urgent:        this.composeUrgent,
            })
            .then(() => {
                const n = this.recipientTokens.length;
                this.toast(`Message envoyé à ${n} destinataire${n > 1 ? 's' : ''}.`, 'success');
                this.closeDrawer();
            })
            .catch(() => this.toast('Erreur lors de l\'envoi.', 'error'))
            .finally(() => { this.drawerSending = false; });
        },

        /* ================================================================
           COMBOBOX
           ================================================================ */
        addRecipient(c) {
            if (this.recipientTokens.find(t => t.id === c.id)) return;
            this.recipientTokens = [...this.recipientTokens, c];
            this.recipientQuery  = '';
        },

        removeRecipient(id) {
            this.recipientTokens = this.recipientTokens.filter(t => t.id !== id);
        },

        onComboFocus() {
            this.recipientDropdownOpen = true;
        },

        onComboBlur() {
            this._recipientBlurTimer = setTimeout(() => {
                this.recipientDropdownOpen = false;
            }, 150);
        },

        onComboInput() {
            this.recipientDropdownOpen = true;
        },

        onComboKeydown(e) {
            if (e.key === 'Backspace' && !this.recipientQuery && this.recipientTokens.length > 0) {
                this.recipientTokens = this.recipientTokens.slice(0, -1);
            } else if (e.key === 'Enter' && this.filteredConnections.length > 0) {
                e.preventDefault();
                this.addRecipient(this.filteredConnections[0]);
            }
        },

        pickConnection(c) {
            clearTimeout(this._recipientBlurTimer);
            this.addRecipient(c);
            this.$nextTick(() => {
                this.$el.querySelector('.cm-combo-input')?.focus();
            });
        },

        /* ================================================================
           LEAD CONTEXT PICKER
           ================================================================ */
        selectLead(lead) {
            this.linkedLead       = lead;
            this.leadDropdownOpen = false;
            this.drawerDirty      = true;
        },

        removeLead() {
            this.linkedLead = null;
        },

        /* ================================================================
           KEYBOARD HANDLING
           ================================================================ */
        handleKey(e) {
            const tag = e.target.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;

            /* ⌘K / Ctrl+K — focus search */
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                this.$refs.searchInput?.focus();
                return;
            }

            if (this.drawerOpen) return;

            const filtered = this.filteredItems;
            const idx      = filtered.findIndex(i => i.id === this.selectedId);

            if (e.key === 'j' || e.key === 'ArrowDown') {
                e.preventDefault();
                const next = filtered[idx + 1];
                if (next) this.select(next.id);
            } else if (e.key === 'k' || e.key === 'ArrowUp') {
                e.preventDefault();
                const prev = idx > 0 ? filtered[idx - 1] : null;
                if (prev) this.select(prev.id);
            } else if (e.key === 'e' || e.key === 'E') {
                if (this.selectedId) this.archive(this.selectedId);
            } else if (e.key === 'r' || e.key === 'R') {
                if (this.selectedId) this.toggleRead(this.selectedId);
            } else if (e.key === 'Escape' && this.drawerOpen) {
                this.closeDrawer();
            }
        },

        handleDrawerKey(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
                e.preventDefault();
                this.sendCompose();
            }
            if (e.key === 'Escape') {
                this.closeDrawer();
            }
        },

        handleReplyKey(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'Enter') {
                e.preventDefault();
                this.sendReply();
            }
        },

        /* ================================================================
           TOAST
           ================================================================ */
        toast(msg, type = 'info') {
            const id = ++this._toastId;
            this.toasts = [...this.toasts, { id, msg, type }];
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3200);
        },

        /* ================================================================
           FORMAT HELPERS
           ================================================================ */
        formatTime(ts) {
            const diff = Math.floor((Date.now() - new Date(ts)) / 1000);
            if (diff < 60)     return 'à l\'instant';
            if (diff < 3600)   return `il y a ${Math.floor(diff / 60)} min`;
            if (diff < 86400)  return `il y a ${Math.floor(diff / 3600)}h`;
            if (diff < 604800) return `il y a ${Math.floor(diff / 86400)}j`;
            return new Date(ts).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
        },

        formatDateTime(ts) {
            return new Date(ts).toLocaleDateString('fr-FR', {
                weekday: 'short', day: 'numeric', month: 'long',
                hour: '2-digit', minute: '2-digit',
            });
        },

        initials(name) {
            if (!name) return '?';
            const p = name.trim().split(/\s+/);
            return ((p[0]?.[0] || '') + (p[1]?.[0] || '')).toUpperCase() || '?';
        },

        renderBody(body) {
            if (!body) return '';
            return body
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');
        },

        heatLabel(heat) {
            return { hot: 'Chaud', warm: 'Tiède', cold: 'Froid' }[heat] || heat;
        },

        heatClass(heat) {
            return { hot: 'cm-heat-hot', warm: 'cm-heat-warm', cold: 'cm-heat-cold' }[heat] || 'cm-heat-cold';
        },

        /* ================================================================
           FETCH HELPERS
           ================================================================ */
        _csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },

        _post(url) {
            return fetch(url, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': this._csrf() },
                credentials: 'same-origin',
            });
        },

        _postJSON(url, data) {
            return fetch(url, {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'Accept':        'application/json',
                    'X-CSRF-TOKEN':  this._csrf(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(data),
            }).then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            });
        },
    };
}
