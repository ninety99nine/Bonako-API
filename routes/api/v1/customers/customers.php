<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;

Route::controller(CustomerController::class)
    ->prefix('customers')
    ->group(function () {
        Route::get('/', 'showCustomers')->name('show.customers');
        Route::post('/', 'createCustomer')->name('create.customer');
        Route::delete('/', 'deleteCustomers')->name('delete.customers');

        //  Customer
        Route::prefix('{customerId}')->group(function () {
            Route::get('/', 'showCustomer')->name('show.customer');
            Route::put('/', 'updateCustomer')->name('update.customer');
            Route::delete('/', 'deleteCustomer')->name('delete.customer');
            Route::get('/orders', 'showCustomerOrders')->name('show.customer.orders');
            Route::get('/transactions', 'showCustomerTransactions')->name('show.customer.transactions');
        });
});
