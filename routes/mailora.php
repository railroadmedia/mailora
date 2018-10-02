<?php

use Railroad\Mailora\Controllers\MailController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['prefix' => 'mailora',],
    function(){
        Route::post(
            '/send',
            MailController::class . '@send'
        )->name('mailora.send');
    }
);