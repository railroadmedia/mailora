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
        // 3.0 - we are setting the defaults directly from the config for each brand; we might not need these parameters later; to be checked and deleted if not needed
        'sender-address' => env('MAILORA_DEFAULT_SENDER_ADDRESS', null), // REQUIRED
        'sender-name' => env('MAILORA_DEFAULT_SENDER_NAME', null), // REQUIRED
        'recipient-address' => env('MAILORA_DEFAULT_RECIPIENT_ADDRESS', null), // REQUIRED

        // 2.1 Optional
        'recipient-name' => env('MAILORA_DEFAULT_RECIPIENT_NAME', null),
        'subject' => null,
        'message' => null,
        // todo: ability to set multiple recipients.

        // 2.3 Advanced, see documentation for details
        'type' => env('MAILORA_DEFAULT_TYPE', null),
        'users-email-set-reply-to' => true,
    ],


    'drumeo' => [
        'support-email-address' => 'support@drumeo.com',
        'support-sender-name' => 'Drumeo',
        'submit-student-focus-recipient' => 'support@drumeo.com',
        'logo-link' => 'https://dmmior4id2ysr.cloudfront.net/logos/drumeo-logo.png'
    ],

    'pianote' => [
        'support-email-address' => 'support@pianote.com',
        'support-sender-name' => 'Pianote System',
        'submit-student-focus-recipient' => 'team+studentreviews@pianote.com',
        'logo-link' => 'https://dmmior4id2ysr.cloudfront.net/logos/pianote-logo-red.png'
    ],

    'guitareo' => [
        'support-email-address' => 'support@guitareo.com',
        'support-sender-name' => 'Guitareo System',
        'submit-student-focus-recipient' => 'support@guitareo.com',
        'logo-link' => 'https://dmmior4id2ysr.cloudfront.net/logos/guitareo-logo.png'
    ],

    'singeo' => [
        'support-email-address' => 'support@singeo.com',
        'support-sender-name' => 'Singeo System',
        'submit-student-focus-recipient' => 'support@singeo.com',
        'logo-link' => 'https://dmmior4id2ysr.cloudfront.net/logos/singeo-logo-purple'
    ]
];
