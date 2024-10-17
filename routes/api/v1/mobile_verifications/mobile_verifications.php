<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MobileVerificationController;

Route::controller(MobileVerificationController::class)
    ->prefix('mobile-verifications')
    ->group(function () {
        Route::get('/', 'showMobileVerifications')->name('show.mobile.verifications');
        Route::post('/', 'createMobileVerification')->name('create.mobile.verification');
        Route::delete('/', 'deleteMobileVerifications')->name('delete.mobile.verifications');

        //  Mobile Verification
        Route::prefix('{mobileVerificationId}')->group(function () {
            Route::get('/', 'showMobileVerification')->name('show.mobile.verification');
            Route::put('/', 'updateMobileVerification')->name('update.mobile.verification');
            Route::delete('/', 'deleteMobileVerification')->name('delete.mobile.verification');
        });
});
