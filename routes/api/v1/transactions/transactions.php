<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

Route::controller(TransactionController::class)
    ->prefix('transactions')
    ->group(function () {
        Route::get('/', 'showTransactions')->name('show.transactions');
        Route::post('/', 'createTransaction')->name('create.transaction');
        Route::delete('/', 'deleteTransactions')->name('delete.transactions');

        //  Transaction
        Route::prefix('{transactionId}')->group(function () {
            Route::get('/', 'showTransaction')->name('show.transaction');
            Route::put('/', 'updateTransaction')->name('update.transaction');
            Route::delete('/', 'deleteTransaction')->name('delete.transaction');
            Route::post('/renew', 'renewPaymentLink')->name('renew.transaction.payment.link');

            Route::get('/proof-of-payment', 'showTransactionProofOfPaymentPhoto')->name('show.transaction.proof.of.payment.photo');
            Route::post('/proof-of-payment', 'uploadTransactionProofOfPaymentPhoto')->name('upload.transaction.proof.of.payment.photo');
            Route::delete('/proof-of-payment', 'deleteTransactionProofOfPaymentPhoto')->name('delete.transaction.proof.of.payment.photo');
        });
});
