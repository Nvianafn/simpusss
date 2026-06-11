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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'simpus' => [
        'auth_url' => env('AUTH_SERVICE_URL', 'http://auth-nginx'),
        'mahasiswa_url' => env('MAHASISWA_SERVICE_URL', 'http://mahasiswa-nginx'),
        'ppl_url' => env('PPL_SERVICE_URL', 'http://ppl-nginx'),
        'klinik_url' => env('KLINIK_SERVICE_URL', 'http://klinik-nginx'),
        'bank_url' => env('BANK_SERVICE_URL', 'http://bank-nginx'),
        'internal_token' => env('INTERNAL_API_TOKEN'),
    ],

];
