<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;

Route::controller(SearchController::class)
    ->prefix('search')
    ->name('search')
    ->group(function () {

    Route::get('/stores', 'searchStores')->name('.stores.show');
    Route::get('/friends', 'searchFriends')->name('.friends.show');
    Route::get('/friend-groups', 'searchFriendGroups')->name('.friend.groups.show');

    Route::get('/filters', 'showSearchFilters')->name('.filters.show');

});

