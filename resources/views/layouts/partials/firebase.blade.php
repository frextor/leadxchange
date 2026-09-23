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
        })();
    </script>
    @endif
