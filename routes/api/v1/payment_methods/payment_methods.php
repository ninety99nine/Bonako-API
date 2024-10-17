<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentMethodController;

Route::controller(PaymentMethodController::class)
    ->prefix('payment-methods')
    ->group(function () {
        Route::get('/', 'showPaymentMethods')->name('show.payment.methods');
        Route::post('/', 'createPaymentMethod')->name('create.payment.method');
        Route::delete('/', 'deletePaymentMethods')->name('delete.payment.methods');

        //  Payment Method
        Route::prefix('{paymentMethodId}')->group(function () {
            Route::get('/', 'showPaymentMethod')->name('show.payment.method');
            Route::put('/', 'updatePaymentMethod')->name('update.payment.method');
            Route::delete('/', 'deletePaymentMethod')->name('delete.payment.method');
        });
});
