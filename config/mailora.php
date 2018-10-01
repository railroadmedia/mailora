<?php

return [

    // REQUIRED
    'safety-recipient' => env('MAILORA_SAFETY_RECIPIENT', ''),

    // required to make "public" route work
    'approved-from-public-recipients' => env( 'MAILORA_APPROVED_FROM_PUBLIC_RECIPIENTS', []),

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
        'sender-address' => env('MAIL_FROM_ADDRESS', null), // REQUIRED
        'sender-name' => env('MAIL_FROM_NAME', null), // REQUIRED
        'recipient-address' => env('MAILORA_DEFAULT_RECIPIENT', null), // REQUIRED

        // 2.1 Optional - application-specific defaults for text shown to users
        'subject' => null,
        'success-message' => null,
        'error-message' => null,

        // 2.2 Advanced, see documentation for details
        'type' => env('MAILORA_DEFAULT_TYPE', null),
        'users-email-set-reply-to' => true,
    ],
];
