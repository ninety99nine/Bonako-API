<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CouponController;

Route::controller(CouponController::class)
    ->prefix('coupons')
    ->group(function () {
        Route::get('/', 'showCoupons')->name('show.coupons');
        Route::post('/', 'createCoupon')->name('create.coupon');
        Route::delete('/', 'deleteCoupons')->name('delete.coupons');

        //  Coupon
        Route::prefix('{couponId}')->group(function () {
            Route::get('/', 'showCoupon')->name('show.coupon');
            Route::put('/', 'updateCoupon')->name('update.coupon');
            Route::delete('/', 'deleteCoupon')->name('delete.coupon');
        });
});
