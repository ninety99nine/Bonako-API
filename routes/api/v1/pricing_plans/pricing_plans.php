<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PricingPlanController;

Route::controller(PricingPlanController::class)
    ->prefix('pricing-plans')
    ->group(function () {
        Route::get('/', 'showPricingPlans')->name('show.pricing.plans');
        Route::post('/', 'createPricingPlan')->name('create.pricing.plan');
        Route::delete('/', 'deletePricingPlans')->name('delete.pricing.plans');

        //  Pricing Plan
        Route::prefix('{pricingPlanId}')->group(function () {
            Route::get('/', 'showPricingPlan')->name('show.pricing.plan');
            Route::put('/', 'updatePricingPlan')->name('update.pricing.plan');
            Route::delete('/', 'deletePricingPlan')->name('delete.pricing.plan');
            Route::get('/payment-methods', 'showPricingPlanPaymentMethods')->name('show.pricing.plan.payment.methods');

            Route::post('/pay', 'payPricingPlan')->name('pay.pricing.plan');
            Route::get('/verify-payment/{transactionId}', 'verifyPricingPlanPayment')->name('verify.pricing.plan.payment')->withoutMiddleware(['auth:sanctum', 'format.request.payload']);
        });
});
