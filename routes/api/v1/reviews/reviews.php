<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReviewController;

Route::controller(ReviewController::class)
    ->prefix('reviews')
    ->group(function () {
        Route::get('/', 'showReviews')->name('show.reviews');
        Route::post('/', 'createReview')->name('create.review');
        Route::delete('/', 'deleteReviews')->name('delete.reviews');
        Route::get('/rating-options', 'showReviewRatingOptions')->name('show.review.rating.options');

        //  Review
        Route::prefix('{reviewId}')->group(function () {
            Route::get('/', 'showReview')->name('show.review');
            Route::put('/', 'updateReview')->name('update.review');
            Route::delete('/', 'deleteReview')->name('delete.review');
        });
});
