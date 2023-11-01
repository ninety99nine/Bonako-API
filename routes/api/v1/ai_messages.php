<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiMessageController;

Route::controller(AiMessageController::class)
    ->prefix('ai/messages')
    ->group(function () {

    Route::get('/', 'index')->name('messages.show');
    Route::post('/', 'create')->name('messages.create');

    Route::prefix('{message}')->name('message')->group(function () {
        Route::get('/', 'show')->name('.show')->whereNumber('message');
        Route::put('/', 'update')->name('.update')->whereNumber('message');
        Route::delete('/', 'delete')->name('.delete')->whereNumber('message');
    });

});
