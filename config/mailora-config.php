<?php

return [
    'defaults' => [
        'recipient-safety'                  => env('MAIL_SAFETY_RECIPIENT',                 null),
        'recipient-address'                 => env('MAIL_DEFAULT_RECIPIENT',                null),
        'admin'                             => env('MAIL_DEFAULT_ADMIN',                    null),
        'recipient-address-public'          => env('MAIL_DEFAULT_RECIPIENT_PUBLIC',         null),
        'subject'                           => env('MAIL_DEFAULT_TYPE',                     null),
        'sender-address'                    => env('MAIL_DEFAULT_SENDER_ADDRESS',           null),
        'sender-name'                       => env('MAIL_DEFAULT_SENDER_NAME',              null),
        'sender-public'                     => env('MAIL_DEFAULT_SENDER_ADDRESS_PUBLIC',    null),
        'success-message'                   => null,
        'error-message'                     => null,
        'production'                        => env( 'MAIL_NAME_TO_TREAT_LIKE_PROD',         'production'),
        'public-free-for-all'               => env( 'MAIL_PUBLIC_FREE_FOR_ALL',             false),
        'approved-from-public-recipients'   => env( 'MAIL_APPROVED_FROM_PUBLIC_RECIPIENTS', []),
    ],
    'auth_middleware' => []
];