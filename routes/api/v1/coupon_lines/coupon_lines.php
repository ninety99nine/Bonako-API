<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CouponLineController;

Route::controller(CouponLineController::class)
    ->prefix('coupon-lines')
    ->group(function () {
        Route::get('/', 'showCouponLines')->name('show.coupon.lines');

        //  Coupon Line
        Route::prefix('{couponLineId}')->group(function () {
            Route::get('/', 'showCouponLine')->name('show.coupon.line');
        });
});
