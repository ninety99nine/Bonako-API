<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Notify other devices on successful login
Broadcast::channel('login.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Push user notification
Broadcast::channel('user.notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('testing.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

