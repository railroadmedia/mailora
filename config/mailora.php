<?php

return [
    // REQUIRED
    'safety-recipient' => env('MAILORA_SAFETY_RECIPIENT', ''),

    // required to make "public" route work
    'approved-recipients' => '',
    'approved-recipient-domains' => '',

    // required to make "authentication-protected" route work
    'route_middleware_public_groups' => ['mailora_public'],
    'route_middleware_logged_in_groups' => ['mailora_logged_in'],

    // 1. Advanced, see documentation for details
    'views-root-directory' => 'resources/views',
    'views-email-directory' => 'emails',
    'mailables-namespace' => 'App\Mail\\',
    'name-of-production-env' => env('MAILORA_NAME_OF_PROD_ENV', 'production'),
    'public-free-for-all' => env('MAILORA_PUBLIC_FREE_FOR_ALL', false),
    'admin' => env('MAILORA_DEFAULT_ADMIN', null),

    // 2. Some required, some optional...
    'defaults' => [

        // 2.0 REQUIRED (either hardcoded here, or provided by environmental variables)
        'sender-address' => env('MAILORA_DEFAULT_SENDER_ADDRESS', null), // REQUIRED
        'sender-name' => env('MAILORA_DEFAULT_SENDER_NAME', null), // REQUIRED
        'recipient-address' => env('MAILORA_DEFAULT_RECIPIENT_ADDRESS', null), // REQUIRED

        'support-email-address' => [
            'drumeo' => 'support@drumeo.com',
            'pianote' => 'support@pianote.com',
            'guitareo' => 'support@guitareo.com',
            'singeo' => 'support@singeo.com'
        ],

        'support-sender-name' => [
            'drumeo' => 'Drumeo',
            'pianote' => 'Pianote System',
            'guitareo' => 'Guitareo Support',
            'singeo' => 'Singeo System'
        ],

        // 2.1 Optional
        'recipient-name' => env('MAILORA_DEFAULT_RECIPIENT_NAME', null),
        'subject' => null,
        'message' => null,
        // todo: ability to set multiple recipients.

        // 2.3 Advanced, see documentation for details
        'type' => env('MAILORA_DEFAULT_TYPE', null),
        'users-email-set-reply-to' => true,
    ],

    'submit_student_focus_recipient' => [
        'drumeo' => 'support@drumeo.com',
        'pianote' => 'team+studentreviews@pianote.com',
        'guitareo' => 'support@guitareo.com',
        'singeo' => 'support.singeo.com'
    ],


    'logo_server' => 'https://dmmior4id2ysr.cloudfront.net/logos/',
    'logo_name' => [
        'drumeo' => 'drumeo-logo.png',
        'pianote' => 'pianote-logo-red.png',
        'guitareo' => 'guitareo-logo.png',
        'singeo' => 'singeo-logo-purple'
    ],
];
