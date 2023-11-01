<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CouponController;

/**
 *  Coupon Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a coupon
 */
Route::controller(CouponController::class)
    ->prefix('stores/{store}/coupons/{coupon}')
    ->name('coupon')->group(function () {

    Route::get('/', 'show')->name('.show')->whereNumber('coupon');
    Route::put('/', 'update')->name('.update')->whereNumber('coupon');
    Route::delete('/', 'delete')->name('.delete')->whereNumber('coupon');
    Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('coupon');

});
