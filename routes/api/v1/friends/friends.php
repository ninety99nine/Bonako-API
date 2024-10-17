<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendController;

Route::controller(FriendController::class)
    ->prefix('friends')
    ->group(function () {
        Route::get('/', 'showFriends')->name('show.friends');
        Route::post('/', 'addFriend')->name('add.friend');
        Route::delete('/', 'removeFriends')->name('remove.friends');
        Route::get('/last-selected', 'showLastSelectedFriend')->name('show.last.selected.friend');
        Route::put('/last-selected', 'updateLastSelectedFriends')->name('update.last.selected.friends');

        //  Friend
        Route::prefix('{friendId}')->group(function () {
            Route::get('/', 'showFriend')->name('show.friend');
            Route::put('/', 'updateFriend')->name('update.friend');
            Route::delete('/', 'removeFriend')->name('remove.friend');
        });
});
