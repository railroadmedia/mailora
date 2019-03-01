<?php

use Railroad\Mailora\Controllers\MailController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['prefix' => 'mailora'],
    function () {

        Route::group(
            ['prefix' => 'public'],
            function () {
                Route::post('/send', MailController::class . '@sendPublic')->name('mailora.public.send');
            }
        );

        Route::group(
            ['prefix' => 'secure', 'middleware' => config('mailora.auth_middleware')],
            function () {
                Route::post('/send', MailController::class . '@sendSecure')->name('mailora.secure.send');
            }
        );
    }
);
