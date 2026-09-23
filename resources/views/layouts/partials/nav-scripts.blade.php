    <!-- Toast -->
    <div id="toastContainer" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;
        let notifLoaded = false;

        function toggleMembers() {
            const panel = document.getElementById('notificationPanel');
            document.getElementById('userPanel').classList.add('hidden');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                if (!notifLoaded) { loadRequests(); notifLoaded = true; }
            } else { panel.classList.add('hidden'); }
        }

        function toggleUserMenu() {
            document.getElementById('notificationPanel').classList.add('hidden');
            document.getElementById('userPanel').classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const mi  = document.getElementById('membersNavItem');
            const ud  = document.getElementById('userDropdown');
            const nb  = document.getElementById('notifBellWrap');
            if (mi && !mi.contains(e.target)) document.getElementById('notificationPanel').classList.add('hidden');
            if (ud && !ud.contains(e.target)) document.getElementById('userPanel').classList.add('hidden');
            if (nb && !nb.contains(e.target)) document.getElementById('notifPanel').classList.add('hidden');
        });

        async function loadRequests() {
            const loading = document.getElementById('loadingState');
            const list    = document.getElementById('requestsList');
            const empty   = document.getElementById('emptyState');
            try {
                loading.style.display = 'block'; list.style.display = 'none'; empty.style.display = 'none';
                const res  = await fetch('/api/connections?type=received&status=pending', { headers: {'Accept':'application/json','Authorization':'Bearer '+window.API_TOKEN}, credentials: 'same-origin' });
                if (!res.ok) throw new Error();
                const data = await res.json();
                displayRequests(data.data || []);
            } catch { loading.style.display='none'; empty.style.display='block'; }
        }

        function displayRequests(reqs) {
            const loading = document.getElementById('loadingState');
            const list    = document.getElementById('requestsList');
            const empty   = document.getElementById('emptyState');
            const badge   = document.getElementById('membersBadge');
            const count   = document.getElementById('requestCountText');
            const n = reqs.length;
            loading.style.display = 'none';
            if (n > 0) { badge.textContent=n; badge.style.display='flex'; count.textContent=`${n} demande${n>1?'s':''} en attente`; }
            else { badge.style.display='none'; count.textContent='Aucune demande en attente'; }
            if (n === 0) { list.style.display='none'; empty.style.display='block'; return; }
            empty.style.display = 'none'; list.style.display = 'block';
            list.innerHTML = reqs.map(r => `
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition" id="req-${r.id}">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0" style="background:var(--lx-accent-grad,linear-gradient(135deg,#34d4bf,#1E8F88));">
                            ${(r.user.first_name||'?').charAt(0)}${(r.user.last_name||'?').charAt(0)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">${r.user.first_name} ${r.user.last_name}</p>
                            <p class="text-xs text-gray-400 mt-0.5">${timeAgo(r.created_at)}</p>
                            <div class="flex gap-2 mt-2">
                                <button onclick="accept(${r.id})" class="flex-1 text-xs font-semibold py-1.5 rounded-lg text-white" style="background:var(--lx-accent,#1E8F88);">Accepter</button>
                                <button onclick="reject(${r.id})" class="flex-1 text-xs font-semibold py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">Refuser</button>
                            </div>
                        </div>
                    </div>
                </div>`).join('');
        }

        async function accept(id) {
            const el = document.getElementById(`req-${id}`);
            el.style.opacity='0.5'; el.style.pointerEvents='none';
            try {
                await fetch(`/api/connections/${id}/accept`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                el.remove(); loadRequests(); toast('Demande acceptée 🎉','success');
            } catch { el.style.opacity='1'; el.style.pointerEvents='auto'; }
        }

        async function reject(id) {
            const el = document.getElementById(`req-${id}`);
            el.style.opacity='0.5'; el.style.pointerEvents='none';
            try {
                await fetch(`/api/connections/${id}/reject`, { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                el.remove(); loadRequests(); toast('Demande refusée','info');
            } catch { el.style.opacity='1'; el.style.pointerEvents='auto'; }
        }

        function timeAgo(d) {
            const diff=new Date()-new Date(d),m=Math.floor(diff/60000),h=Math.floor(diff/3600000),days=Math.floor(diff/86400000);
            if(m<1)return'À l\'instant'; if(m<60)return`il y a ${m} min`; if(h<24)return`il y a ${h} h`; if(days<7)return`il y a ${days} j`;
            return new Date(d).toLocaleDateString('fr-FR');
        }

        function incrementBadge() {
            const b=document.getElementById('membersBadge');
            b.textContent=parseInt(b.textContent||0)+1; b.style.display='flex';
        }

        function toast(msg, type='success') {
            const c={success:'bg-green-500',error:'bg-red-500',info:'bg-blue-500'};
            const t=document.createElement('div');
            t.className=`${c[type]} text-white px-6 py-3 rounded-lg shadow-2xl flex items-center space-x-3 transform transition-all`;
            t.style.transform='translateX(400px)';
            t.innerHTML=`<i class="fas fa-check-circle"></i><span class="font-medium">${msg}</span>`;
            document.getElementById('toastContainer').appendChild(t);
            setTimeout(()=>t.style.transform='translateX(0)',10);
            setTimeout(()=>t.style.transform='translateX(400px)',3000);
            setTimeout(()=>t.remove(),3300);
        }

        // ── Notifications panel ──────────────────────────────────────
        let notifPanelLoaded = false;

        window.toggleNotifPanel = function() {
            const panel = document.getElementById('notifPanel');
            document.getElementById('userPanel').classList.add('hidden');
            document.getElementById('notificationPanel').classList.add('hidden');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                if (!notifPanelLoaded) { loadNotifications(); notifPanelLoaded = true; }
            } else {
                panel.classList.add('hidden');
            }
        };

        async function loadNotifications() {
            try {
                const res  = await fetch('/api/notifications', { headers:{'Accept':'application/json','Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                const data = await res.json();
                renderNotifications(data.notifications || []);
                updateNotifBadge(data.unread_count || 0);
            } catch {
                document.getElementById('notifLoadingState').style.display = 'none';
                document.getElementById('notifEmpty').style.display = 'block';
            }
        }

        function renderNotifications(items) {
            const loading = document.getElementById('notifLoadingState');
            const list    = document.getElementById('notifList');
            const empty   = document.getElementById('notifEmpty');
            const countEl = document.getElementById('notifCountText');
            loading.style.display = 'none';
            const unread = items.filter(n => !n.is_read).length;
            countEl.textContent = unread > 0 ? `${unread} non lue${unread > 1 ? 's' : ''}` : 'Tout lu';
            if (!items.length) { empty.style.display = 'block'; return; }
            empty.style.display = 'none';
            list.style.display  = 'block';
            window._notifCache = window._notifCache || {};
            items.forEach(n => window._notifCache[n.id] = n);

            list.innerHTML = items.map(n => `
                <div class="flex items-start gap-3 px-5 py-3.5 border-b border-gray-50 hover:bg-gray-50 transition cursor-pointer ${n.is_read ? 'opacity-70' : ''}"
                     id="notif-${n.id}" onclick="openNotifDetail(${n.id})">
                    <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0 ${n.is_read ? 'bg-gray-200' : 'bg-teal-500'}"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 leading-tight">${escapeHtml(n.title)}</p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">${escapeHtml(n.body)}</p>
                        <p class="text-[10px] text-gray-300 mt-1">${timeAgo(n.created_at)}</p>
                    </div>
                    <button onclick="event.stopPropagation(); deleteNotif(${n.id})" class="text-gray-200 hover:text-red-400 transition flex-shrink-0 mt-0.5">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>`).join('');
        }

        function updateNotifBadge(count) {
            const badge = document.getElementById('notifBadge');
            if (!badge) return;
            if (count > 0) {
                badge.textContent = count > 9 ? '9+' : count;
                badge.classList.remove('hidden');
                badge.style.display = 'flex';
            } else {
                badge.classList.add('hidden');
                badge.style.display = 'none';
            }
        }

        window.markAllNotifRead = async function() {
            try {
                await fetch('/api/notifications/read-all', { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                notifPanelLoaded = false;
                loadNotifications();
                updateNotifBadge(0);
            } catch {}
        };

        // URL is only offered as a link if it does NOT point to the admin subdomain —
        // a regular user must never be bounced to admin.* and hit a login/403 wall.
        function isSafeNotifUrl(url) {
            if (!url) return false;
            try {
                const u = new URL(url, window.location.origin);
                return !/^admin\./i.test(u.hostname);
            } catch {
                return !/^\/?admin\b/i.test(url);
            }
        }

        window.openNotifDetail = async function(id) {
            const n = (window._notifCache || {})[id];
            if (!n) return;

            document.getElementById('notifDetailTitle').textContent = n.title;
            document.getElementById('notifDetailBody').textContent  = n.body;
            document.getElementById('notifDetailDate').textContent  = timeAgo(n.created_at);

            const url = n.data && n.data.url ? n.data.url : null;
            const actions = document.getElementById('notifDetailActions');
            actions.innerHTML = '';
            if (url && isSafeNotifUrl(url)) {
                const a = document.createElement('a');
                a.href = url;
                a.className = 'flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90';
                a.style.background = 'linear-gradient(135deg,#2BB6A3,#1E8F88)';
                a.textContent = 'Voir';
                actions.appendChild(a);
            }
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.onclick = closeNotifDetail;
            closeBtn.className = (url && isSafeNotifUrl(url) ? '' : 'flex-1 ') + 'px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 hover:bg-gray-50 transition';
            closeBtn.textContent = 'Fermer';
            actions.appendChild(closeBtn);

            document.getElementById('notifDetailModal').classList.remove('hidden');

            if (!n.is_read) {
                n.is_read = true;
                const el = document.getElementById('notif-' + id);
                if (el) { el.classList.add('opacity-70'); el.querySelector('.bg-teal-500')?.classList.replace('bg-teal-500','bg-gray-200'); }
                try {
                    await fetch('/api/notifications/' + id + '/read', { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                } catch {}
            }
        };

        window.closeNotifDetail = function() {
            document.getElementById('notifDetailModal').classList.add('hidden');
        };

        window.deleteNotif = async function(id) {
            const el = document.getElementById('notif-' + id);
            if (el) el.style.opacity = '0.3';
            try {
                await fetch('/api/notifications/' + id, { method:'DELETE', headers:{'Accept':'application/json','X-CSRF-TOKEN':CSRF,'Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                if (el) el.remove();
            } catch { if (el) el.style.opacity = '1'; }
        };

        function escapeHtml(s) {
            return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // ── Refresh badge every 60s ──────────────────────────────────
        window.addEventListener('DOMContentLoaded', () => {
            fetch('/api/connections?type=received&status=pending', { headers:{'Accept':'application/json','Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' })
                .then(r=>r.json()).then(d=>{ const n=(d.data||[]).length; if(n>0){const b=document.getElementById('membersBadge'); b.textContent=n; b.style.display='flex';} }).catch(()=>{});
            setInterval(()=>{ if(notifLoaded) loadRequests(); }, 30000);
            setInterval(async()=>{
                try {
                    const r = await fetch('/api/notifications', { headers:{'Accept':'application/json','Authorization':'Bearer '+window.API_TOKEN}, credentials:'same-origin' });
                    const d = await r.json();
                    updateNotifBadge(d.unread_count || 0);
                    if (notifPanelLoaded) { notifPanelLoaded = false; loadNotifications(); notifPanelLoaded = true; }
                } catch {}
            }, 60000);
        });
    </script>
