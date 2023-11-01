<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionPlanController;

Route::get('/subscription-plans', [SubscriptionPlanController::class, 'showSubscriptionPlans'])->name('subscription.plans.show');
