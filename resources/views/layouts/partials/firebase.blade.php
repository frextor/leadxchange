    @if(config('firebase.api_key'))
    <script>
        (function() {
            firebase.initializeApp({ apiKey:'{{ config("firebase.api_key") }}', authDomain:'{{ config("firebase.auth_domain") }}', projectId:'{{ config("firebase.project_id") }}', storageBucket:'{{ config("firebase.storage_bucket") }}', messagingSenderId:'{{ config("firebase.messaging_sender_id") }}', appId:'{{ config("firebase.app_id") }}' });
            const messaging=firebase.messaging(), vapidKey='{{ config("firebase.vapid_key") }}';
            async function initFcm() {
                try {
                    if(await Notification.requestPermission()!=='granted') return;
                    const swReg=await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                    const token=await messaging.getToken({vapidKey,serviceWorkerRegistration:swReg});
                    if(token) await fetch('/api/device-token',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':CSRF},credentials:'same-origin',body:JSON.stringify({token,platform:'web'})});
                } catch(e){console.warn('FCM:',e.message);}
            }
            messaging.onMessage(payload=>{ const d=payload.data||{}; incrementBadge(); toast(`${d.sender_first_name} ${d.sender_last_name} vous a envoyé une demande`,'success'); });
            initFcm();

            // ── Messages : temps réel via Firebase Realtime Database (chat_inbox/{uid}) ──
            // Même chemin déjà synchronisé côté serveur pour l'app mobile — le web s'y branche aussi
            // pour une mise à jour instantanée du badge, au lieu d'attendre le prochain sondage.
            async function initChatRealtime() {
                const uid = window.AUTH_USER_ID;
                if (!uid) return;
                try {
                    const res = await fetch('/api/chat/firebase/token', {
                        headers: { 'Accept': 'application/json', 'Authorization': 'Bearer ' + (window.API_TOKEN || '') },
                        credentials: 'same-origin',
                    });
                    const { firebase_token } = await res.json();
                    if (!firebase_token) return;

                    await firebase.auth().signInWithCustomToken(firebase_token);

                    let lastTotal = null;
                    firebase.database().ref('chat_inbox/' + uid).on('value', (snapshot) => {
                        const inbox = snapshot.val() || {};
                        const total = Object.values(inbox).reduce((sum, c) => sum + (c.unread_count || 0), 0);

                        const dot = document.getElementById('lx2ChatDot');
                        if (dot) dot.classList.toggle('hidden', total === 0);

                        const navCount = document.querySelector('[data-nav="chat.index"] .count');
                        if (navCount) { navCount.textContent = total > 9 ? '9+' : total; navCount.hidden = total === 0; }

                        if (lastTotal !== null && total > lastTotal && typeof toast === 'function') {
                            toast('💬 Nouveau message reçu');
                        }
                        lastTotal = total;
                    });
                } catch (e) { console.warn('Chat realtime:', e.message); }
            }
            initChatRealtime();
        })();
    </script>
    @endif
