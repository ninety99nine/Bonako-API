<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiMessageCategoryController;

Route::get('/ai/message/categories', [AiMessageCategoryController::class, 'showAiMessageCategories'])->name('ai.message.categories.show');
