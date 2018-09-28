<?php

return [
    // 0. REQUIRED For authentication-protected route - See documentation for details
    'auth_middleware' => [],

    // 1. REQUIRED For views other than the "general" one native to this package
    'views-directory' => '/laravel/resources/views/',
    'mailables-namespace' => '', // only required if differs from Laravel's standard "App\Mail\"

    // 2. Some required, some optional...
    'defaults' => [

        // 2.0 REQUIRED (either hardcoded here, or provided by environmental variables)
        'safety-recipient' =>   env('MAILORA_SAFETY_RECIPIENT',    ''), // REQUIRED
        'sender-address' =>     env('MAIL_FROM_ADDRESS',        ''), // REQUIRED
        'sender-name' =>        env('MAIL_FROM_NAME',           ''), // REQUIRED
        'recipient-address' =>  env('MAILORA_DEFAULT_RECIPIENT',   ''), // REQUIRED

        // 2.1 REQUIRED... To make "public" route work
        'approved-from-public-recipients' => env( 'MAILORA_APPROVED_FROM_PUBLIC_RECIPIENTS', []),

        // 2.2 Optional - receive emails of errors
        'admin' => env('MAILORA_DEFAULT_ADMIN', null),

        // 2.3 Optional - application-specific defaults for text shown to users
        'subject' => env('MAILORA_DEFAULT_TYPE', null),
        'success-message' => null,
        'error-message' => null,

        // 2.4. Advanced, see documentation for details
        'name-of-production-env' => env( 'MAILORA_NAME_OF_PROD_ENV', 'production'), // REQUIRED
        'type' => env('MAILORA_DEFAULT_TYPE', 'general'), // REQUIRED
        'public-free-for-all' => env('MAILORA_PUBLIC_FREE_FOR_ALL', false), // optional
        'users-email-set-reply-to' => true, // optional

        // ----------------------------------------- leave out for now
        // ----------------------------------------- leave out for now
        // not necessary and unnecessarily complex - leave out for now
        // ----------------------------------------- leave out for now
        // ----------------------------------------- leave out for now
        // 2.5 add "+from-public" tag to end of default FROM and TWO email.
        // 'recipient-address-public' => env('MAIL_DEFAULT_RECIPIENT_PUBLIC', null),
        // 'sender-public' => env('MAIL_FROM_ADDRESS_PUBLIC', null),
    ],
];
