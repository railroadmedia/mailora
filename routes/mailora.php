<?php

use Illuminate\Support\Facades\Route;
use Railroad\Mailora\Controllers\MailController;

Route::group(
    [
        'prefix' => 'mailora',
        'middleware' => config('mailora.route_middleware_public_groups'),
    ],
    function () {

        Route::group(
            ['prefix' => 'public'],
            function () {
                Route::post('/send', MailController::class . '@sendPublic')
                    ->name('mailora.public.send');
            }
        );
    }
);

Route::group(
    [
        'prefix' => 'mailora',
        'middleware' => config('mailora.route_middleware_logged_in_groups'),
    ],
    function () {

        Route::group(
            ['prefix' => 'secure'],
            function () {
                Route::post('/send', MailController::class . '@sendSecure')
                    ->name('mailora.secure.send');
            }
        );
    }
);
