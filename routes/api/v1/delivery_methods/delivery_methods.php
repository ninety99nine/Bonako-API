<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryMethodController;

Route::controller(DeliveryMethodController::class)
    ->prefix('delivery-methods')
    ->group(function () {
        Route::get('/', 'showDeliveryMethods')->name('show.delivery.methods');
        Route::post('/', 'createDeliveryMethod')->name('create.delivery.method');
        Route::delete('/', 'deleteDeliveryMethods')->name('delete.delivery.methods');
        Route::post('/arrangement', 'updateDeliveryMethodArrangement')->name('update.delivery.method.arrangement');

        //  Delivery Method
        Route::prefix('{deliveryMethodId}')->group(function () {
            Route::get('/', 'showDeliveryMethod')->name('show.delivery.method');
            Route::put('/', 'updateDeliveryMethod')->name('update.delivery.method');
            Route::delete('/', 'deleteDeliveryMethod')->name('delete.delivery.method');
        });
});
