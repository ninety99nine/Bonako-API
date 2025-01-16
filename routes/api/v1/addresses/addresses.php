<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;

Route::controller(AddressController::class)
    ->prefix('addresses')
    ->group(function () {
        Route::get('/', 'showAddresses')->name('show.addresses');
        Route::post('/', 'createAddress')->name('create.address');
        Route::delete('/', 'deleteAddresses')->name('delete.addresses');
        Route::post('/validate', 'validateAddAddress')->withoutMiddleware('auth:sanctum')->name('validate.add.address');

        //  Address
        Route::prefix('{addressId}')->group(function () {
            Route::get('/', 'showAddress')->name('show.address');
            Route::put('/', 'updateAddress')->name('update.address');
            Route::delete('/', 'deleteAddress')->name('delete.address');
        });
});
