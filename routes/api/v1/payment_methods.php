<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentMethodController;

Route::get('/payment-method-filters', [PaymentMethodController::class, 'showPaymentMethodFilters'])->name('payment.method.filters.show');
Route::get('/payment-methods', [PaymentMethodController::class, 'showPaymentMethods'])->name('payment.methods.show');
