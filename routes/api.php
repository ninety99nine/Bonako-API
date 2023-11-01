<?php

use App\Helpers\Routes\RouteHelper;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')->group(function() {

    //  Routes for authentication of broadcasting events
    Broadcast::routes();

});

//  Api version 1 routes
Route::prefix('v1')->middleware(['require.api.headers'])->group(function() {

    Route::middleware([
        'auth:sanctum', 'last.seen', 'last.seen.at.store', 'mark.order.as.seen.by.team.member', 'format.request.and.response.payloads'
    ])->group(function() {

        //  Include Api version 1 route files
        RouteHelper::includeRouteFiles(__DIR__ . '/api/v1/');

    });

});

//  Incase we don't match any route
Route::fallback(function() {

    //  Throw a route not found exception
    throw new RouteNotFoundException();

});
