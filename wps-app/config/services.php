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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        'currency' => env('PAYSTACK_CURRENCY', 'GHS'),
        /** Mobile-money recipient bank code for Paystack Transfers (Ghana: e.g. MTN, VOD, etc. — confirm in Paystack dashboard). */
        'momo_bank_code' => env('PAYSTACK_MOMO_BANK_CODE', 'MTN'),
    ],

    /*
    | iGate (or compatible) — orders are queued when an admin moves status to "processing".
    */
    'igate' => [
        'enabled' => (bool) env('IGATE_ENABLED', false),
        'base_url' => rtrim((string) env('IGATE_BASE_URL', ''), '/'),
        'token' => env('IGATE_TOKEN'),
        'timeout' => (int) env('IGATE_TIMEOUT', 15),
        'orders_endpoint' => env('IGATE_ORDERS_ENDPOINT', 'api/orders'),
        'webhook_secret' => env('IGATE_WEBHOOK_SECRET'),
        /*
        | Map partner "status" strings (lowercased) to our lifecycle: sent | failed
        */
        'webhook_status_map' => [
            'success' => 'sent',
            'sent' => 'sent',
            'completed' => 'sent',
            'complete' => 'sent',
            'delivered' => 'sent',
            'done' => 'sent',
            'failed' => 'failed',
            'error' => 'failed',
            'rejected' => 'failed',
        ],
    ],

];
