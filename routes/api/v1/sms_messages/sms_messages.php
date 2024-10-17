<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsMessageController;

Route::controller(SmsMessageController::class)
    ->prefix('sms-messages')
    ->group(function () {
        Route::get('/', 'showSmsMessages')->name('show.sms.messages');
        Route::post('/', 'createSmsMessage')->name('create.sms.message');
        Route::delete('/', 'deleteSmsMessages')->name('delete.sms.messages');

        //  SMS Message
        Route::prefix('{smsMessageId}')->group(function () {
            Route::get('/', 'showSmsMessage')->name('show.sms.message');
            Route::put('/', 'updateSmsMessage')->name('update.sms.message');
            Route::delete('/', 'deleteSmsMessage')->name('delete.sms.message');
        });
});
