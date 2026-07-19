<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'siren' => [
        'key'      => env('SIREN_API_KEY'),
        'base_url' => env('SIREN_API_URL', 'https://data.siren-api.fr'),
    ],

    'linkedin' => [
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect_uri'  => env('LINKEDIN_REDIRECT_URI', env('APP_URL') . '/auth/linkedin/callback'),
    ],

    'stripe' => [
        'secret'           => env('STRIPE_SECRET_KEY'),
        'publishable'      => env('STRIPE_PUBLISHABLE_KEY'),
        'webhook_secret'   => env('STRIPE_WEBHOOK_SECRET'),
        'currency'         => env('STRIPE_CURRENCY', 'eur'),
        'premium_price_id'            => env('STRIPE_PREMIUM_PRICE_ID'),
        'test_subscription_minutes'   => env('STRIPE_TEST_SUBSCRIPTION_MINUTES', 0),
    ],

];
