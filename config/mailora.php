<?php

return [
    // 0. REQUIRED For authentication-protected route - See documentation for details
    'auth_middleware' => [],

    // 1. Some required, some optional...
    'defaults' => [

        // 1.0 REQUIRED (from either here, or provided by environmental variable)
        'safety-recipient' =>   env('MAILORA_SAFETY_RECIPIENT',    ''), // REQUIRED
        'sender-address' =>     env('MAIL_FROM_ADDRESS',        ''), // REQUIRED
        'sender-name' =>        env('MAIL_FROM_NAME',           ''), // REQUIRED
        'recipient-address' =>  env('MAILORA_DEFAULT_RECIPIENT',   ''), // REQUIRED

        // 1.1 REQUIRED... To make "public" route work
        'approved-from-public-recipients' => env( 'MAILORA_APPROVED_FROM_PUBLIC_RECIPIENTS', []),

        // 1.2 Optional - receive emails of errors
        'admin' => env('MAILORA_DEFAULT_ADMIN', null),

        // 1.3 Optional - application-specific defaults for text shown to users
        'subject' => env('MAILORA_DEFAULT_TYPE', null),
        'success-message' => null,
        'error-message' => null,

        // 1.4. Advanced, see documentation for details
        'name-of-production-env' => env( 'MAILORA_NAME_OF_PROD_ENV', 'production'), // REQUIRED
        'type' => env('MAILORA_DEFAULT_TYPE', 'general'), // REQUIRED
        'public-free-for-all' => env('MAILORA_PUBLIC_FREE_FOR_ALL', false), // optional
        'users-email-set-reply-to' => true, // optional

        // ----------------------------------------- leave out for now
        // ----------------------------------------- leave out for now
        // not necessary and unnecessarily complex - leave out for now
        // ----------------------------------------- leave out for now
        // ----------------------------------------- leave out for now
        // 1.5 add "+from-public" tag to end of default FROM and TWO email.
        // 'recipient-address-public' => env('MAIL_DEFAULT_RECIPIENT_PUBLIC', null),
        // 'sender-public' => env('MAIL_FROM_ADDRESS_PUBLIC', null),
    ],
];
