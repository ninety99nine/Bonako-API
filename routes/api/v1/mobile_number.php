<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileNumberController;

/**
 *  Product Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a shortcode
 */
Route::controller(MobileNumberController::class)
    ->prefix('mobile-number')
    ->group(function () {

    Route::get('/profile', 'showUserAccount')->name('mobile.number.user.account.show');

});
