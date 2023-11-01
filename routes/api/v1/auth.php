<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::controller(AuthController::class)
    ->prefix('auth')
    ->name('auth')
    ->group(function () {

    /**
     *  Guest Routes
     *
     *  The following routes do not require an authenticated user.
     *  The middleware set via "api.php" to enable this restriction is rejected.
     */
    Route::withoutMiddleware('auth:sanctum')->group(function () {

        Route::post('/login', 'login')->name('.login');
        Route::post('/register', 'register')->name('.register');
        Route::post('/account-exists', 'accountExists')->name('.account.exists');
        Route::post('/reset-password', 'resetPassword')->name('.reset.password');
        Route::post('/validate-register', 'validateRegister')->name('.validate.register');
        Route::post('/validate-reset-password', 'validateResetPassword')->name('.validate.reset.password');
        Route::post('/verify-mobile-verification-code', 'verifyMobileVerificationCode')->name('.verify.mobile.verification.code');
        //  USSD Server Routes: The following route is restricted to USSD requests (See attached middleware)
        Route::post('/show-mobile-verification-code', 'showMobileVerificationCode')->middleware('request.via.ussd')->name('.show.mobile.verification.code');
        Route::post('/generate-mobile-verification-code', 'generateMobileVerificationCode')->middleware('request.via.ussd')->name('.generate.mobile.verification.code');

    });

});
