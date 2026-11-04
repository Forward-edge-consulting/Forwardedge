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

    'resend' => [
        'key' => env('RESEND_KEY'),
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


    // Optional Cloudflare Turnstile for public forms. Leave empty to rely on the honeypot and rate limits.
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret'   => env('TURNSTILE_SECRET_KEY'),
    ],

    'mailchimp' => [
        'key'                => env('MAILCHIMP_KEY'),
        'server_prefix'      => env('MAILCHIMP_SERVER_PREFIX'),
        'list_id'            => env('MAILCHIMP_LIST_ID'),
        'transactional_key'  => env('MAILCHIMP_TRANSACTIONAL_KEY'),
        'double_opt_in'      => env('MAILCHIMP_DOUBLE_OPT_IN', false),
    ],

    'recaptcha' => [
        'key'    => env('RECAPTCHA_SITE_KEY'),
        'secret' => env('RECAPTCHA_SECRET_KEY'),
        'score'  => env('RECAPTCHA_MIN_SCORE', 0.5),
        'allowed_hostnames' => array_filter(array_map('trim', explode(',', (string) env('RECAPTCHA_ALLOWED_HOSTS', '')))),
    ],

    'meta_pixel' => [
        'id' => env('META_PIXEL_ID'),
    ],


];
