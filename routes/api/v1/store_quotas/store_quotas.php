<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreQuotaController;

Route::controller(StoreQuotaController::class)
    ->prefix('store-quotas')
    ->group(function () {
        Route::get('/', 'showStoreQuotas')->name('show.store.quotas');
        Route::post('/', 'createStoreQuota')->name('create.store.quota');
        Route::delete('/', 'deleteStoreQuotas')->name('delete.store.quotas');

        //  Store Quota
        Route::prefix('{storeQuotaId}')->group(function () {
            Route::get('/', 'showStoreQuota')->name('show.store.quota');
            Route::put('/', 'updateStoreQuota')->name('update.store.quota');
            Route::delete('/', 'deleteStoreQuota')->name('delete.store.quota');
        });
});
