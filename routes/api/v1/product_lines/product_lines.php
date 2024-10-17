<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductLineController;

Route::controller(ProductLineController::class)
    ->prefix('product-lines')
    ->group(function () {
        Route::get('/', 'showProductLines')->name('show.product.lines');

        //  Product Line
        Route::prefix('{productLineId}')->group(function () {
            Route::get('/', 'showProductLine')->name('show.product.line');
        });
});
