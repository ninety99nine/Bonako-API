<?php

use App\Jobs\SendSms;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebController;

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

//  Remove this when running on production
Route::get('/php-info', function () {
    return phpinfo();
});

Route::controller(WebController::class)->group(function(){
    Route::get('/', 'welcome')->name('welcome.page');
});

//  Redirect to terms and conditions
Route::redirect('/terms-and-conditions', config('app.TERMS_AND_CONDITIONS_REDIRECT_URL'), 301)->name('show.terms.and.conditions');

//  Incase we don't match any route
Route::fallback(function() {

    //  Return our 404 Not Found page
    return View('errors.404');

});
