<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

/**
 *  Transaction Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a transaction
 */
Route::controller(TransactionController::class)
    ->prefix('transactions')
    ->group(function () {

    Route::get('/', 'index')->name('transactions.show');

    Route::prefix('{transaction}')->name('transaction')->group(function () {
        Route::get('/', 'show')->name('.show')->whereNumber('transaction');
        Route::get('/verify-dpo-payment', 'verify-dpo-payment')->name('.verify.dpo.payment')->whereNumber('transaction');
    });

});
