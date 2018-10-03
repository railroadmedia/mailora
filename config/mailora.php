<?php

return [
    // REQUIRED
    'safety-recipient' => env('MAILORA_SAFETY_RECIPIENT', ''),

    // required to make "public" route work
    'approved-recipients' => env( 'MAILORA_APPROVED_RECIPIENTS', []),
    'approved-recipient-domains' => env('MAILORA_APPROVED_RECIPIENT_DOMAINS', []),

    // required to make "authentication-protected" route work
    'auth_middleware' => [],

    // 1. Advanced, see documentation for details
    'views-directory' => '/laravel/resources/views/emails',
    'mailables-namespace' => 'App\Mail\\',
    'name-of-production-env' => env( 'MAILORA_NAME_OF_PROD_ENV', 'production'),
    'public-free-for-all' => env('MAILORA_PUBLIC_FREE_FOR_ALL', false),
    'admin' => env('MAILORA_DEFAULT_ADMIN', null),

    // 2. Some required, some optional...
    'defaults' => [

        // 2.0 REQUIRED (either hardcoded here, or provided by environmental variables)
        'sender-address' => env('MAILORA_DEFAULT_SENDER_ADDRESS', null), // REQUIRED
        'sender-name' => env('MAILORA_DEFAULT_SENDER_NAME', null), // REQUIRED
        'recipient-address' => env('MAILORA_DEFAULT_RECIPIENT_ADDRESS', null), // REQUIRED

        // 2.1 Optional
        'recipient-name' => env('MAILORA_DEFAULT_RECIPIENT_NAME', null),
        'subject' => null,
        'message' => null,

        // 2.3 Advanced, see documentation for details
        'type' => env('MAILORA_DEFAULT_TYPE', null),
        'users-email-set-reply-to' => true,
    ],
];
