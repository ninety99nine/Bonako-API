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

Route::controller(UserController::class)
    ->scopeBindings()
    ->prefix('users')
    ->group(function () {

        Route::get('/', 'showUsers')->name('show.users');
        Route::post('/', 'createUser')->name('create.user');
        Route::delete('/', 'deleteUsers')->name('delete.users');
        Route::post('/validate-create', 'validateCreateUser')->name('validate.create.user');
        Route::post('/search-user-by-mobile-number', 'searchUserByMobileNumber')->name('search.user.by.mobile.number');

        Route::prefix('{userId}')
            ->group(function () {

                Route::get('/', 'showUser')->name('show.user');
                Route::put('/', 'updateUser')->name('update.user');
                Route::delete('/', 'deleteUser')->name('delete.user');

                Route::post('/generate-mobile-verification-code', 'generateUserMobileVerificationCode')->middleware('request.via.ussd')->name('generate.user.mobile.verification.code');
                Route::post('/verify-mobile-verification-code', 'verifyUserMobileVerificationCode')->name('verify.user.mobile.verification.code');

                Route::get('/tokens', 'showUserTokens')->name('show.user.tokens');
                Route::post('/logout', 'logoutUser')->name('logout.user');

                Route::get('/profile-photo', 'showUserProfilePhoto')->name('show.user.profile.photo');
                Route::post('/profile-photo', 'uploadUserProfilePhoto')->name('upload.user.profile.photo');
                Route::delete('/profile-photo', 'deleteUserProfilePhoto')->name('delete.user.profile.photo');

                Route::get('/ai/assistant', 'showUserAiAssistant')->name('show.user.ai.assistant');

                Route::get('/resource/totals', 'showUserResourceTotals')->name('show.user.resource.totals');

                //  Orders
                Route::controller(OrderController::class)->prefix('orders')->group(function () {
                    Route::get('/', 'showOrders')->name('show.user.orders');
                });

                //  Stores
                Route::controller(StoreController::class)->prefix('stores')->group(function () {
                    Route::get('/', 'showStores')->name('show.user.stores');
                });

                //  Reviews
                Route::controller(ReviewController::class)->prefix('reviews')->group(function () {
                    Route::get('/', 'showReviews')->name('show.user.reviews');
                });

                //  Friends
                Route::controller(FriendController::class)->prefix('friends')->group(function () {
                    Route::get('/', 'showFriends')->name('show.user.friends');
                });

                //  Addresses
                Route::controller(AddressController::class)->prefix('addresses')->group(function () {
                    Route::get('/', 'showAddresses')->name('show.user.addresses');
                });

                //  AI Messages
                Route::controller(AiMessageController::class)->prefix('/ai/messages')->group(function () {
                    Route::get('/', 'showAiMessages')->name('show.user.ai.messages');
                });

                //  Notifications
                Route::controller(NotificationController::class)->prefix('notifications')->group(function () {
                    Route::get('/', 'showNotifications')->name('show.user.notifications');
                });

                //  Friend Groups
                Route::controller(FriendGroupController::class)->prefix('friend-groups')->group(function () {
                    Route::get('/', 'showFriendGroups')->name('show.user.friend.groups');
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
