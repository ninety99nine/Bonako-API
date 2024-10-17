<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiMessageController;

Route::controller(AiMessageController::class)
    ->prefix('ai/messages')
    ->group(function () {
        Route::get('/', 'showAiMessages')->name('show.ai.messages');
        Route::post('/', 'createAiMessage')->name('create.ai.message');
        Route::delete('/', 'deleteAiMessages')->name('delete.ai.messages');

        // AI Message
        Route::prefix('{aiMessageId}')->group(function () {
            Route::get('/', 'showAiMessage')->name('show.ai.message');
            Route::put('/', 'updateAiMessage')->name('update.ai.message');
            Route::delete('/', 'deleteAiMessage')->name('delete.ai.message');
        });
});
