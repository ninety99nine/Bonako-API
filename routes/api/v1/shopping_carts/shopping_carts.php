<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShoppingCartController;

Route::controller(ShoppingCartController::class)
    ->withoutMiddleware('auth:sanctum')
    ->prefix('shopping-carts')
    ->group(function () {
        Route::post('/', 'inspectShoppingCart')->name('inspect.shopping.cart');
});
