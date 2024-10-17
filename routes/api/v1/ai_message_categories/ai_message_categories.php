<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiMessageCategoryController;

Route::controller(AiMessageCategoryController::class)
    ->prefix('ai/message-categories')
    ->group(function () {
        Route::get('/', 'showAiMessageCategories')->name('show.ai.message.categories');
        Route::post('/', 'createAiMessageCategory')->name('create.ai.message.category');
        Route::delete('/', 'deleteAiMessageCategories')->name('delete.ai.message.categories');

        // AI Message Category
        Route::prefix('{aiMessageCategoryId}')->group(function () {
            Route::get('/', 'showAiMessageCategory')->name('show.ai.message.category');
            Route::put('/', 'updateAiMessageCategory')->name('update.ai.message.category');
            Route::delete('/', 'deleteAiMessageCategory')->name('delete.ai.message.category');
        });
});
