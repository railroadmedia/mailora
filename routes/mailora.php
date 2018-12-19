<?php

use Railroad\Mailora\Controllers\MailController;
use Illuminate\Support\Facades\Route;

Route::group(
    ['prefix' => 'members'],
    function() {
        Route::group(
            [],
            function(){
                Route::post('/mail', MailController::class . '@sendPublic')->name('mailora.public.send');
            }
        );
    }
);
