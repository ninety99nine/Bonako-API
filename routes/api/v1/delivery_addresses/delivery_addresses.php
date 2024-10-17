<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryAddressController;

Route::controller(DeliveryAddressController::class)
    ->prefix('delivery-addresses')
    ->group(function () {
        Route::get('/', 'showDeliveryAddresses')->name('show.delivery.addresses');
        Route::post('/', 'createDeliveryAddress')->name('create.delivery.address');
        Route::delete('/', 'deleteDeliveryAddresses')->name('delete.delivery.addresses');

        //  Delivery Address
        Route::prefix('{deliveryAddressId}')->group(function () {
            Route::get('/', 'showDeliveryAddress')->name('show.delivery.address');
            Route::put('/', 'updateDeliveryAddress')->name('update.delivery.address');
            Route::delete('/', 'deleteDeliveryAddress')->name('delete.delivery.address');
        });
});
