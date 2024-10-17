<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FriendGroupController;

Route::controller(FriendGroupController::class)
    ->prefix('friend-groups')
    ->group(function () {
        Route::get('/', 'showFriendGroups')->name('show.friend.groups');
        Route::post('/', 'createFriendGroup')->name('create.friend.group');
        Route::delete('/', 'removeFriendGroups')->name('remove.friend.groups');
        Route::get('/first-created', 'showFirstCreatedFriendGroup')->name('show.first.created.friend.group');
        Route::get('/last-selected', 'showLastSelectedFriendGroup')->name('show.last.selected.friend.group');
        Route::put('/last-selected', 'updateLastSelectedFriendGroups')->name('update.last.selected.friend.groups');
        Route::get('/check-invitations', 'checkInvitationsToJoinFriendGroups')->name('check.invitations.to.join.friend.groups');
        Route::put('/accept-all-invitations-to-join', 'acceptAllInvitationsToJoinFriendGroups')->name('accept.all.invitations.to.join.friend.groups');
        Route::put('/decline-all-invitations-to-join', 'declineAllInvitationsToJoinFriendGroups')->name('decline.all.invitations.to.join.friend.groups');

        //  Friend Group
        Route::prefix('{friendGroupId}')->group(function () {
            Route::get('/', 'showFriendGroup')->name('show.friend.group');
            Route::put('/', 'updateFriendGroup')->name('update.friend.group');
            Route::delete('/', 'removeFriendGroup')->name('remove.friend.group');

            //  Friend Group Members
            Route::prefix('members')->group(function () {
                Route::get('/', 'showFriendGroupMembers')->name('show.friend.group.members');
                Route::post('/', 'inviteFriendGroupMembers')->name('invite.friend.group.members');
                Route::delete('/', 'removeFriendGroupMembers')->name('remove.friend.group.members');
                Route::put('/leave', 'leaveFriendGroup')->name('leave.friend.group');
                Route::put('/accept-invitation-to-join', 'acceptInvitationToJoinFriendGroup')->name('accept.invitation.to.join.friend.group');
                Route::put('/decline-invitation-to-join', 'declineInvitationToJoinFriendGroup')->name('decline.invitation.to.join.friend.group');
            });

            //  Friend Group Stores
            Route::prefix('stores')->group(function () {
                Route::get('/', 'showFriendGroupStores')->name('show.friend.group.stores');
                Route::post('/', 'addFriendGroupStores')->name('add.friend.group.stores');
                Route::delete('/', 'removeFriendGroupStores')->name('remove.friend.group.stores');
            });

            //  Friend Group Orders
            Route::prefix('orders')->group(function () {
                Route::get('/', 'showFriendGroupOrders')->name('show.friend.group.orders');
            });

        });
});
