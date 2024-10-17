<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AiMessageController;
use App\Http\Controllers\FriendGroupController;
use App\Http\Controllers\NotificationController;

$prefixes = ['auth', 'users'];

foreach ($prefixes as $prefix) {

    $isUsersPrefix = ($prefix == 'users');

    Route::controller(UserController::class)
        ->scopeBindings()
        ->prefix($prefix)
        ->group(function () use ($isUsersPrefix) {

            if ($isUsersPrefix) {

                Route::get('/', 'showUsers')->name('show.users');
                Route::post('/', 'createUser')->name('create.user');
                Route::delete('/', 'deleteUsers')->name('delete.users');
                Route::post('/validate-create', 'validateCreateUser')->name('validate.create.user');
                Route::post('/search-user-by-mobile-number', 'searchUserByMobileNumber')->name('search.user.by.mobile.number');

            }

            Route::prefix($isUsersPrefix ? '{user}' : 'user')
                ->group(function () use ($isUsersPrefix) {

                    $prefix = $isUsersPrefix ? 'user' : 'auth.user';

                    Route::get('/', 'showUser')->name('show.'.$prefix);
                    Route::put('/', 'updateUser')->name('update.'.$prefix);
                    Route::delete('/', 'deleteUser')->name('delete.'.$prefix);

                    Route::post('/generate-mobile-verification-code', 'generateUserMobileVerificationCode')->middleware('request.via.ussd')->name('generate.'.$prefix.'.mobile.verification.code');
                    Route::post('/verify-mobile-verification-code', 'verifyUserMobileVerificationCode')->name('verify.'.$prefix.'.mobile.verification.code');

                    Route::get('/tokens', 'showUserTokens')->name('show.'.$prefix.'.tokens');
                    Route::post('/logout', 'logoutUser')->name('logout.'.$prefix);

                    Route::get('/profile-photo', 'showUserProfilePhoto')->name('show.'.$prefix.'.profile.photo');
                    Route::post('/profile-photo', 'uploadUserProfilePhoto')->name('upload.'.$prefix.'.profile.photo');
                    Route::delete('/profile-photo', 'deleteUserProfilePhoto')->name('delete.'.$prefix.'.profile.photo');

                    Route::get('/ai/assistant', 'showUserAiAssistant')->name('show.'.$prefix.'.ai.assistant');

                    Route::get('/resource/totals', 'showUserResourceTotals')->name('show.'.$prefix.'.resource.totals');

                    //  Orders
                    Route::controller(OrderController::class)->prefix('orders')->group(function () use ($prefix) {
                        Route::get('/', 'showOrders')->name('show.'.$prefix.'.orders');
                    });

                    //  Stores
                    Route::controller(StoreController::class)->prefix('stores')->group(function () use ($prefix) {
                        Route::get('/', 'showStores')->name('show.'.$prefix.'.stores');
                    });

                    //  Reviews
                    Route::controller(ReviewController::class)->prefix('reviews')->group(function () use ($prefix) {
                        Route::get('/', 'showReviews')->name('show.'.$prefix.'.reviews');
                    });

                    //  Friends
                    Route::controller(FriendController::class)->prefix('friends')->group(function () use ($prefix) {
                        Route::get('/', 'showFriends')->name('show.'.$prefix.'.friends');
                    });

                    //  Addresses
                    Route::controller(AddressController::class)->prefix('addresses')->group(function () use ($prefix) {
                        Route::get('/', 'showAddresses')->name('show.'.$prefix.'.addresses');
                    });

                    //  AI Messages
                    Route::controller(AiMessageController::class)->prefix('/ai/messages')->group(function () use ($prefix) {
                        Route::get('/', 'showAiMessages')->name('show.'.$prefix.'.ai.messages');
                    });

                    //  Notifications
                    Route::controller(NotificationController::class)->prefix('notifications')->group(function () use ($prefix) {
                        Route::get('/', 'showNotifications')->name('show.'.$prefix.'.notifications');
                    });

                    //  Friend Groups
                    Route::controller(FriendGroupController::class)->prefix('friend-groups')->group(function () use ($prefix) {
                        Route::get('/', 'showFriendGroups')->name('show.'.$prefix.'.friend.groups');
                    });




                    /*

                    Route::prefix('sms-alert')->name('.sms.alert')->group(function () {
                        Route::get('/', 'showSmsAlert')->name('.show');

                        Route::prefix('transactions')->name('.transactions')->group(function () {
                            Route::get('/', 'showSmsAlertTransactions')->name('.show');
                            Route::post('/', 'createSmsAlertTransaction')->name('.create')->middleware('request.via.ussd');
                            Route::post('/calculate-amount', 'calculateSmsAlertTransactionAmount')->name('.calculate.amount');
                        });

                        Route::prefix('sms-alert-activity-association')->group(function () {
                            Route::prefix('{sms_alert_activity_association}')->group(function () {
                                Route::put('/', 'updateSmsAlertActivityAssociation')->name('.update');
                            });
                        });
                    });

                    Route::get('/resource-totals', 'showResourceTotals')->name('.resource.totals.show');

                    */
                });
        });
}
