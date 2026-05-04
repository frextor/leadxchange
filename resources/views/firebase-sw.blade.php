importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey:            '{!! $config['api_key'] !!}',
    authDomain:        '{!! $config['auth_domain'] !!}',
    projectId:         '{!! $config['project_id'] !!}',
    storageBucket:     '{!! $config['storage_bucket'] !!}',
    messagingSenderId: '{!! $config['messaging_sender_id'] !!}',
    appId:             '{!! $config['app_id'] !!}',
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const data  = payload.data  || {};
    const notif = payload.notification || {};

    self.registration.showNotification(notif.title || 'LeadXchange', {
        body:    notif.body || '',
        icon:    '/favicon.ico',
        data:    data,
        actions: [
            { action: 'view', title: 'View' },
        ],
    });
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(clients.openWindow('/connections'));
});
