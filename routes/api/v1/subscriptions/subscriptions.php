<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;

Route::controller(SubscriptionController::class)
    ->prefix('subscriptions')
    ->group(function () {
        Route::get('/', 'showSubscriptions')->name('show.subscriptions');
        Route::post('/', 'createSubscription')->name('create.subscription');
        Route::delete('/', 'deleteSubscriptions')->name('delete.subscriptions');

        //  Subscription
        Route::prefix('{subscriptionId}')->group(function () {
            Route::get('/', 'showSubscription')->name('show.subscription');
            Route::put('/', 'updateSubscription')->name('update.subscription');
            Route::delete('/', 'deleteSubscription')->name('delete.subscription');
        });
});
