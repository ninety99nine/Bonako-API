<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/**
 *  Order Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a order
 */
Route::controller(OrderController::class)
    ->prefix('stores/{store}/orders/{order}')
    ->name('order')->group(function () {

    Route::get('/', 'show')->name('.show')->whereNumber(['store', 'order']);
    Route::put('/', 'update')->name('.update')->whereNumber(['store', 'order']);
    Route::delete('/', 'delete')->name('.delete')->whereNumber(['store', 'order']);
    Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber(['store', 'order']);

    Route::put('/cancel', 'cancel')->name('.cancel')->whereNumber(['store', 'order']);
    Route::put('/uncancel', 'uncancel')->name('.uncancel')->whereNumber(['store', 'order']);
    Route::get('/cancellation-reasons', 'showCancellationReasons')->name('.show.cancellation.reasons')->whereNumber(['store', 'order']);

    Route::get('/viewers', 'showViewers')->name('.viewers.show')->whereNumber(['store', 'order']);
    Route::post('/generate-collection-code', 'generateCollectionCode')->name('.generate.collection.code')->whereNumber(['store', 'order']);
    Route::post('/revoke-collection-code', 'revokeCollectionCode')->name('.revoke.collection.code')->whereNumber(['store', 'order']);
    Route::put('/update-status', 'updateStatus')->name('.status.update')->whereNumber(['store', 'order']);

    Route::prefix('request-payment')->group(function () {
        Route::post('/', 'requestPayment')->name('.request.payment')->whereNumber(['store', 'order']);
        Route::get('/payment-methods', 'showRequestPaymentPaymentMethods')->name('.request.payment.payment.methods.show')->whereNumber(['store', 'order']);
    });

    Route::prefix('mark-as-unverified-payment')->group(function () {
        Route::post('/', 'markAsUnverifiedPayment')->name('.mark.as.unverified.payment')->whereNumber(['store', 'order']);
        Route::get('/payment-methods', 'showMarkAsUnverifiedPaymentPaymentMethods')->name('.mark.as.unverified.payment.payment.methods.show')->whereNumber(['store', 'order']);
    });

    //  USSD Server Routes: The following route is restricted to USSD requests (See attached middleware)
    Route::middleware('request.via.ussd')->group(function () {
        Route::put('/mark-as-verified-payment', 'markAsVerifiedPayment')->name('.mark.as.verified.payment')->whereNumber(['store', 'order']);
    });


    //  Cart
    Route::get('/cart', 'showCart')->name('.cart.show')->whereNumber(['store', 'order']);

    //  Customer
    Route::get('/customer', 'showCustomer')->name('.customer.show')->whereNumber(['store', 'order']);

    //  Occasion
    Route::get('/occasion', 'showOccasion')->name('.occasion.show')->whereNumber(['store', 'order']);

    //  Delivery Address
    Route::get('/delivery-address', 'showDeliveryAddress')->name('.delivery.address.show')->whereNumber(['store', 'order']);

    //  Transactions
    Route::prefix('transactions')->group(function () {

        Route::get('/filters', 'showOrderTransactionFilters')->name('.transaction.filters.show')->whereNumber('order');
        Route::get('/count', 'showOrderTransactionsCount')->name('.transactions.count.show')->whereNumber('order');
        Route::get('/', 'showOrderTransactions')->name('.transactions.show')->whereNumber('order');

    });

});
