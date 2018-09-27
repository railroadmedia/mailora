<?php

use Railroad\Mailora\Controllers\MailController;
use Illuminate\Support\Facades\Route;

Route::group(
    [
        'prefix' => 'mailora',
    ],
    function(){
        Route::group(
            [
                'middleware' => config('mailora.auth_middleware')
            ],
            function(){
                Route::post(
                    '/',
                    MailController::class . '@send'
                )->name('mailora');
            }
        );
        Route::group(
            [
                'prefix' => 'public',
            ],
            function(){
                Route::post(
//                Route::get(
                    '/',
                    MailController::class . '@sendFromPublic'
                )->name('mailora.public');
            }
        );
    }
);