<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;

Route::controller(AddressController::class)
    ->prefix('addresses')
    ->group(function () {
        Route::get('/', 'showAddresses')->name('show.addresses');
        Route::post('/', 'addAddress')->name('add.address');
        Route::delete('/', 'removeAddresses')->name('remove.addresses');

        //  Address
        Route::prefix('{addressId}')->group(function () {
            Route::get('/', 'showAddress')->name('show.address');
            Route::put('/', 'updateAddress')->name('update.address');
            Route::delete('/', 'removeAddress')->name('remove.address');
        });
});
