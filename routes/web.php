<?php

use App\Jobs\SendSms;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;
use App\Http\Controllers\Auth\SocialAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//  Social Sign-in
Route::controller(SocialAuthController::class)
->prefix('auth')
->group(function () {
    Route::get('google', 'redirectToGoogle')->name('social-auth-google');
    Route::get('google/callback', 'handleGoogleCallback');

    Route::get('facebook', 'redirectToFacebook')->name('social-auth-facebook');
    Route::get('facebook/callback', 'handleFacebookCallback');

    Route::get('linkedin', 'redirectToLinkedIn')->name('social-auth-linkedin');
    Route::get('linkedin/callback', 'handleLinkedInCallback');
});

Route::controller(WebController::class)->group(function () {
    Route::get('/', 'welcome')->name('welcome.page');
    Route::get('/privacy-policy', 'privacyPolicy')->name('privacy.policy');
    Route::get('/terms-of-service', 'termsOfService')->name('terms.of.service');
    Route::get('/data-deletion-instructions', 'dataDeletionInstructions')->name('data.deletion.instructions');
});

//  Incase we don't match any route
Route::fallback(function() {

    //  Return our 404 Not Found page
    return View('errors.404');

});
