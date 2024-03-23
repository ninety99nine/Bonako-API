<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;

Route::get('/subscriptions', [SubscriptionController::class, 'showSubscriptions'])->name('subscriptions.show');
