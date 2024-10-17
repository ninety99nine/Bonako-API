<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiLessonController;

Route::controller(AiLessonController::class)
    ->prefix('ai/lessons')
    ->group(function () {
        Route::get('/', 'showAiLessons')->name('show.ai.lessons');
        Route::post('/', 'createAiLesson')->name('create.ai.lesson');
        Route::delete('/', 'deleteAiLessons')->name('delete.ai.lessons');

        // AI Lesson
        Route::prefix('{aiLessonId}')->group(function () {
            Route::get('/', 'showAiLesson')->name('show.ai.lesson');
            Route::put('/', 'updateAiLesson')->name('update.ai.lesson');
            Route::delete('/', 'deleteAiLesson')->name('delete.ai.lesson');
        });
});
