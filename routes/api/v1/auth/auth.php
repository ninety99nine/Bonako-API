<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {

        Route::withoutMiddleware('auth:sanctum')
            ->group(function () {

            Route::post('/login', 'login')->name('login');
            Route::post('/register', 'register')->name('register');
            Route::post('/account-exists', 'accountExists')->name('account.exists');
            Route::post('/reset-password', 'resetPassword')->name('reset.password');
            Route::post('/validate-register', 'validateRegistration')->name('validate.register');
            Route::post('/validate-reset-password', 'validateResetPassword')->name('validate.reset.password');
            Route::post('/verify-mobile-verification-code', 'verifyMobileVerificationCode')->name('verify.mobile.verification.code');
            Route::get('/terms-and-conditions', 'showTermsAndConditions')->name('show.terms.and.conditions');
            Route::get('/terms-and-conditions/takeaways', 'showTermsAndConditionsTakeaways')->name('show.terms.and.conditions.takeaways');
            Route::get('/social-login-links', 'showSocialLoginLinks')->name('show.social.login.links');

            /**
             *  The following route is restricted to USSD requests (See applied middleware)
             */
            Route::post('/generate-mobile-verification-code', 'generateMobileVerificationCode')->middleware('request.via.ussd')->name('generate.mobile.verification.code');
        });

        Route::get('/user', 'showAuthUser')->name('show.auth.user');

});
