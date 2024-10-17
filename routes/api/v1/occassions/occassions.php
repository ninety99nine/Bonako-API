<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OccasionController;

Route::controller(OccasionController::class)
    ->prefix('occasions')
    ->group(function () {
        Route::get('/', 'showOccasions')->name('show.occasions');
        Route::post('/', 'createOccasion')->name('create.occasion');
        Route::delete('/', 'deleteOccasions')->name('delete.occasions');

        //  Occasion
        Route::prefix('{occasionId}')->group(function () {
            Route::get('/', 'showOccasion')->name('show.occasion');
            Route::put('/', 'updateOccasion')->name('update.occasion');
            Route::delete('/', 'deleteOccasion')->name('delete.occasion');
        });
});
