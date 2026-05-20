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

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        // Platform fee in basis points (100 = 1%). 0 = FeedAI takes nothing.
        'platform_fee_bps' => (int) env('STRIPE_PLATFORM_FEE_BPS', 0),
        // Demo mode: instantly mark a vendor as Stripe-active without any real
        // API call. Auto-enabled when STRIPE_SECRET is empty OR when APP_URL
        // points at localhost — Stripe rejects localhost in business_profile,
        // so a real Connect onboarding can never succeed in local dev anyway.
        'demo_mode' => env('STRIPE_DEMO_MODE', empty(env('STRIPE_SECRET'))
            || str_contains((string) env('APP_URL', ''), 'localhost')
            || str_contains((string) env('APP_URL', ''), '127.0.0.1')),
    ],

];
