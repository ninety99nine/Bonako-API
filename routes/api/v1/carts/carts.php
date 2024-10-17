<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::controller(CartController::class)
    ->prefix('carts')
    ->group(function () {
        Route::get('/', 'showCarts')->name('show.carts');

        //  Cart
        Route::prefix('{cartId}')->group(function () {
            Route::get('/', 'showCart')->name('show.cart');
        });
});
