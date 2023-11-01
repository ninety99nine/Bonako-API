<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OccasionController;

Route::get('/occasions', [OccasionController::class, 'showOccasions'])->name('occasions.show');
