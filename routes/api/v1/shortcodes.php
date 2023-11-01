<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortcodeController;

/**
 *  Product Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a shortcode
 */
Route::controller(ShortcodeController::class)
    ->prefix('shortcode')
    ->group(function () {

    Route::post('/owner', 'showOwner')->name('shortcode.owner.show');

});
