<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

/**
 *  Transaction Routes (Public & Super-Admin Facing Routes)
 *
 *  Public users are allowed to view a transaction
 */

 Route::get('/transactions', [TransactionController::class, 'showTransactions'])->name('transactions.show');

Route::controller(TransactionController::class)
    ->prefix('transactions/{transaction}')
    ->name('transaction')->group(function () {

    Route::get('/', 'show')->name('.show')->whereNumber('transaction');
    Route::delete('/', 'delete')->name('.delete')->whereNumber('transaction');
    Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('transaction');
    Route::get('/verify-dpo-payment', 'verify-dpo-payment')->name('.verify.dpo.payment')->whereNumber('transaction');
    Route::post('/renew-payment-link', 'renewPaymentLink')->name('.renew.payment.link')->whereNumber('transaction');

    /// Proof Of Payment
    Route::get('/proof-of-payment-photo', 'showProofOfPaymentPhoto')->name('.proof.of.payment.photo.show')->whereNumber('transaction');
    Route::post('/proof-of-payment-photo', 'updateProofOfPaymentPhoto')->name('.proof.of.payment.photo.update')->whereNumber('transaction');
    Route::delete('/proof-of-payment-photo', 'deleteProofOfPaymentPhoto')->name('.proof.of.payment.photo.delete')->whereNumber('transaction');

});
