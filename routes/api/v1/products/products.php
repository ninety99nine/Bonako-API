<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::controller(ProductController::class)
    ->prefix('products')
    ->group(function () {
        Route::get('/', 'showProducts')->name('show.products');
        Route::post('/', 'createProduct')->name('create.product');
        Route::delete('/', 'deleteProducts')->name('delete.products');
        Route::post('/visibility', 'updateProductVisibility')->name('update.product.visibility');
        Route::post('/arrangement', 'updateProductArrangement')->name('update.product.arrangement');

        //  Product
        Route::prefix('{productId}')->group(function () {
            Route::get('/', 'showProduct')->name('show.product');
            Route::put('/', 'updateProduct')->name('update.product');
            Route::delete('/', 'deleteProduct')->name('delete.product');

            //  Product Photos
            Route::prefix('photos')->group(function () {
                Route::get('/', 'showProductPhotos')->name('show.product.photos');
                Route::post('/', 'createProductPhoto')->name('create.product.photo');
                Route::prefix('{photoId}')->group(function () {
                    Route::get('/', 'showProductPhoto')->name('show.product.photo');
                    Route::post('/', 'updateProductPhoto')->name('update.product.photo');
                    Route::delete('/', 'deleteProductPhoto')->name('delete.product.photo');
                });
            });

            //  Product Variations
            Route::prefix('variations')->group(function () {
                Route::get('/', 'showProductVariations')->name('show.product.variations');
                Route::post('/', 'createProductVariations')->name('create.product.variations');
            });
        });
});

