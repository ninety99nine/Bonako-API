<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiAssistantTokenUsageController;

Route::controller(AiAssistantTokenUsageController::class)
    ->prefix('ai/assistant/token-usage')
    ->group(function () {
        Route::get('/', 'showAiAssistantTokenUsages')->name('show.ai.assistant.token.usages');

        //  AI Assistant Token Usage
        Route::prefix('{aiAssistantTokenUsageId}')->group(function () {
            Route::get('/', 'showAiAssistantTokenUsage')->name('show.ai.assistant.token.usage');
        });
});
