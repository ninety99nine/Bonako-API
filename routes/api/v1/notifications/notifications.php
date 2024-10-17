<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

Route::controller(NotificationController::class)
    ->prefix('notifications')
    ->group(function () {
        Route::get('/', 'showNotifications')->name('show.notifications');
        Route::put('/mark-as-read', 'markNotificationsAsRead')->name('mark.notifications.as.read');

        //  Notification
        Route::prefix('{notificationId}')->group(function () {
            Route::get('/', 'showNotification')->name('show.notification');
            Route::put('/mark-as-read', 'markNotificationAsRead')->name('mark.notification.as.read');
        });
});
