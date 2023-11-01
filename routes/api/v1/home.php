<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'apiHome'])->withoutMiddleware('auth:sanctum')->name('api.home');
