<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VariableController;

Route::controller(VariableController::class)
    ->prefix('variables')
    ->group(function () {
        Route::get('/', 'showVariables')->name('show.variables');

        //  Variable
        Route::prefix('{variableId}')->group(function () {
            Route::get('/', 'showVariable')->name('show.variable');
        });
});
