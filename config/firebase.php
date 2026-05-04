<?php

return [
    'database'            => ['url' => env('FIREBASE_DATABASE_URL')],
    'api_key'             => env('FIREBASE_API_KEY'),
    'auth_domain'         => env('FIREBASE_AUTH_DOMAIN'),
    'project_id'          => env('FIREBASE_PROJECT_ID'),
    'storage_bucket'      => env('FIREBASE_STORAGE_BUCKET'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id'              => env('FIREBASE_APP_ID'),
];
