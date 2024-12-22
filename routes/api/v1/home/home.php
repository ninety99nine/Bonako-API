<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::controller(HomeController::class)
    ->withoutMiddleware('auth:sanctum')
    ->group(function () {
        Route::get('/', 'showApiHome')->name('api.home');
        Route::get('/languages', 'showLanguages')->name('show.languages');
        Route::get('/countries', 'showCountries')->name('show.countries');
        Route::get('/currencies', 'showCurrencies')->name('show.currencies');
        Route::get('/social-media-icons', 'showSocialMediaIcons')->name('show.social.media.icons');
});
