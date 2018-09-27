<?php

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
                    \App\Http\Controllers\MailController::class . '@send'
                )->name('mailora');
            }
        );
        Route::group(
            [
                'prefix' => 'public',
            ],
            function(){
                Route::post(
                    '/',
                    \App\Http\Controllers\MailController::class . '@sendFromPublic'
                )->name('mailora.public');
            }
        );
    }
);