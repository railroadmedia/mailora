<?php

use Railroad\Mailora\Controllers\MailController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['prefix' => 'mailora'],
    function() {

        Route::post(
            '/send',
            MailController::class . '@sendPublic'
        )->name('mailora.send');

        Route::group(
            [
                'prefix' => 'secure',
                'middleware' => 'is-logged-in',
            ],
            function(){
                Route::post(
                    '/send',
                    MailController::class . '@sendSecure'
                )->name('mailora.secure.send');
            }
        );
    }
);
