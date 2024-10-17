<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdController;

Route::post('/launch/ussd', [UssdController::class, 'launchUssd'])->name('launch.ussd');
