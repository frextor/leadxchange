{{-- Fenêtres globales du design lx2 (maquette : dialog « Inviter un ami », détail de notification) --}}

<div class="overlay hidden" id="lx2InviteDialog" onclick="if(event.target===this) lx2CloseInvite()">
    <form class="dialog" role="dialog" aria-modal="true" aria-labelledby="lx2InviteTitle" onsubmit="lx2SendInvite(event)">
        <div class="dh">
            <div>
                <h3 id="lx2InviteTitle">Inviter un ami</h3>
                <p>Il recevra un lien personnalisé pour rejoindre LeadXchange.</p>
            </div>
            <button type="button" class="x" onclick="lx2CloseInvite()" aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db">
            <label class="label" for="friendMail">Adresse e-mail de votre ami</label>
            <input class="input" id="friendMail" type="email" required placeholder="Adresse e-mail de votre ami">
            <p class="help" id="friendMailErr" style="color:var(--destructive)" hidden></p>
        </div>
        <div class="df">
            <button type="button" class="btn btn-outline" onclick="lx2CloseInvite()">Annuler</button>
            <button type="submit" class="btn btn-primary" id="friendMailBtn">Envoyer l'invitation</button>
        </div>
    </form>
</div>

<div class="overlay hidden" id="notifDetailModal" onclick="if(event.target===this) closeNotifDetail()">
    <div class="dialog" role="dialog" aria-modal="true" style="max-width:400px">
        <div class="dh">
            <div><h3 id="notifDetailTitle"></h3></div>
            <button type="button" class="x" onclick="closeNotifDetail()" aria-label="Fermer"><x-lx2-icon name="x" /></button>
        </div>
        <div class="db">
            <p id="notifDetailBody" style="margin:0;color:var(--fg-2);line-height:1.6"></p>
            <p id="notifDetailDate" class="help"></p>
        </div>
        <div class="df" id="notifDetailActions"></div>
    </div>
</div>

<div class="toast" id="lx2Toast" role="status" hidden></div>

<script>
/* Notifications : rendu des lignes au style de la maquette (.notif) */
window.lxNotifRow = function (n) {
    const icons = {!! json_encode([
        'connection' => \App\Support\Lx2Icons::svg('user-plus'),
        'lead'       => \App\Support\Lx2Icons::svg('inbox'),
        'event'      => \App\Support\Lx2Icons::svg('calendar-days'),
        'group'      => \App\Support\Lx2Icons::svg('users-round'),
        'payment'    => \App\Support\Lx2Icons::svg('credit-card'),
        'rating'     => \App\Support\Lx2Icons::svg('star'),
        'default'    => \App\Support\Lx2Icons::svg('bell'),
    ]) !!};
    const type = String(n.type || '');
    const key = /connect|invit/.test(type) ? 'connection' : /lead/.test(type) ? 'lead' : /event/.test(type) ? 'event'
              : /group|pole/.test(type) ? 'group' : /pay|subscr|plan/.test(type) ? 'payment' : /rat|note/.test(type) ? 'rating' : 'default';
    return `<div class="notif" id="notif-${n.id}" role="button" tabindex="0" style="cursor:pointer" onclick="openNotifDetail(${n.id})">
        <span class="ic">${icons[key]}</span>
        <div class="tx"><b>${escapeHtml(n.title)}</b><span>${escapeHtml(n.body)}</span></div>
        <span class="tm">${timeAgo(n.created_at)}</span>
        ${n.is_read ? '' : '<span class="unread" style="margin-top:6px"></span>'}
    </div>`;
};

/* Toast au style lx2 (remplace celui de nav-scripts sur ce layout) */
window.toast = function (msg, type) {
    const t = document.getElementById('lx2Toast');
    t.textContent = msg;
    t.style.background = type === 'error' ? 'var(--destructive)' : 'var(--fg)';
    t.hidden = false;
    clearTimeout(t._h);
    t._h = setTimeout(() => { t.hidden = true; }, 3000);
};

/* Inviter un ami → API parrainage existante (POST /api/referrals) */
function lx2OpenInvite() {
    document.getElementById('lx2InviteDialog').classList.remove('hidden');
    document.getElementById('userPanel')?.classList.add('hidden');
    document.getElementById('notifPanel')?.classList.add('hidden');
    setTimeout(() => document.getElementById('friendMail').focus(), 50);
}
function lx2CloseInvite() {
    document.getElementById('lx2InviteDialog').classList.add('hidden');
    document.getElementById('friendMailErr').hidden = true;
}
async function lx2SendInvite(e) {
    e.preventDefault();
    const input = document.getElementById('friendMail');
    const err = document.getElementById('friendMailErr');
    const btn = document.getElementById('friendMailBtn');
    btn.disabled = true; err.hidden = true;
    try {
        const res = await fetch('/api/referrals', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                       'Authorization': 'Bearer ' + (window.API_TOKEN || '') },
            credentials: 'same-origin',
            body: JSON.stringify({ email: input.value.trim() }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || (data.errors && Object.values(data.errors)[0][0]) || 'Envoi impossible. Réessayez.');
        input.value = '';
        lx2CloseInvite();
        toast('Invitation envoyée');
    } catch (ex) {
        err.textContent = ex.message; err.hidden = false;
    } finally {
        btn.disabled = false;
    }
}
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    lx2CloseInvite();
    if (typeof closeNotifDetail === 'function') closeNotifDetail();
    document.getElementById('userPanel')?.classList.add('hidden');
    document.getElementById('notifPanel')?.classList.add('hidden');
});
</script>
