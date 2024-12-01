<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreRollingNumberController;

Route::controller(StoreRollingNumberController::class)
    ->prefix('store-rolling-numbers')
    ->group(function () {
        Route::get('/', 'showStoreRollingNumbers')->name('show.store.rolling.numbers');
        Route::post('/', 'createStoreRollingNumber')->name('create.store.rolling.number');
        Route::delete('/', 'deleteStoreRollingNumbers')->name('delete.store.rolling.numbers');

        //  Store Rolling Number
        Route::prefix('{storeRollingNumberId}')->group(function () {
            Route::get('/', 'showStoreRollingNumber')->name('show.store.rolling.number');
            Route::put('/', 'updateStoreRollingNumber')->name('update.store.rolling.number');
            Route::delete('/', 'deleteStoreRollingNumber')->name('delete.store.rolling.number');
        });
});
