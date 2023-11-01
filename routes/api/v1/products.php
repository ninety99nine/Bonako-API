<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/**
 *  Product Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a product and its variations
 */
Route::controller(ProductController::class)
    ->prefix('stores/{store}/products/{product}')
    ->name('product')->group(function () {

    Route::get('/', 'show')->name('.show')->whereNumber(['store', 'product']);
    Route::put('/', 'update')->name('.update')->whereNumber(['store', 'product']);
    Route::delete('/', 'delete')->name('.delete')->whereNumber(['store', 'product']);
    Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber(['store', 'product']);

    //  Photo
    Route::get('/photo', 'showPhoto')->name('.photo.show')->whereNumber('store');
    Route::post('/photo', 'updatePhoto')->name('.photo.update')->whereNumber('store');
    Route::delete('/photo', 'deletePhoto')->name('.photo.delete')->whereNumber('store');

    //  Variations
    Route::prefix('variations')->name('.variations')->group(function () {
        Route::get('/', 'showVariations')->name('.show')->whereNumber(['store', 'product']);
        Route::post('/', 'createVariations')->name('.create')->whereNumber(['store', 'product']);
    });

});
