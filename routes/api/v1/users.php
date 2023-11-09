<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/**
 *  User Routes (Public & Super-Admin Facing Routes)
 *
 *  The following routes require an authenticated user.
 *  Refer to "api.php" to see the middleware set to enable this restriction.
 */
$options = ['auth', 'users'];

foreach($options as $option) {

    $isRouteForAuth = $option == 'auth';
    $isRouteForUsers = $option == 'users';

    Route::controller(UserController::class)
        /**
         *  Scope bindings will instruct laravel to fetch the child relationship
         *  via the parent relationship e.g "users/{user}/addresses/{address}"
         *  will make sure that the {address} must be a resource related to
         *  the {user} provided.
         *
         *  Refer to: https://laravel.com/docs/9.x/routing#implicit-model-binding-scoping
         */
        ->scopeBindings()
        ->prefix($option)
        ->group(function () use ($isRouteForUsers) {

        // Show additional routes for the "users" prefix
        if($isRouteForUsers) {

            Route::name('users')/* ->middleware('superadmin') */->group(function () {
                Route::get('/', 'index')->name('.show');
                Route::post('/', 'create')->name('.create');
                Route::post('/validate-create', 'validateCreate')->name('.validate.create');
            });

        }

        //  Result: prefix('{user}') and prefix('user')
        Route::prefix($isRouteForUsers ? '{user}' : 'user')
            ->name($isRouteForUsers ? 'user' : 'auth.user')
            ->group(function () {

            //  Enable public access so that team members can see other team member accounts
            Route::get('/', 'show')->name('.show')->whereNumber('user');
            Route::put('/', 'update')->name('.update')->whereNumber('user');
            Route::delete('/', 'delete')->name('.delete')->whereNumber('user');
            Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('user');

            Route::post('/logout', 'logout')->name('.logout')->whereNumber('user');
            Route::get('/tokens', 'showTokens')->name('.tokens.show')->whereNumber('user');
            Route::get('/show-terms-and-conditions', 'showTermsAndConditions')->name('.terms.and.conditions.show')->whereNumber('user');
            Route::post('/accept-terms-and-conditions', 'acceptTermsAndConditions')->name('.terms.and.conditions.accept')->whereNumber('user');
            Route::post('/verify-mobile-verification-code', 'verifyMobileVerificationCode')->name('.verify.mobile.verification.code')->whereNumber('user');
            //  USSD Server Routes: The following route is restricted to USSD requests (See attached middleware)
            Route::post('/show-mobile-verification-code', 'showMobileVerificationCode')->middleware('request.via.ussd')->name('.show.mobile.verification.code')->whereNumber('user');
            Route::post('/generate-mobile-verification-code', 'generateMobileVerificationCode')->middleware('request.via.ussd')->name('.generate.mobile.verification.code')->whereNumber('user');

            //  Notifications
            Route::prefix('notifications')->group(function () {

                Route::get('/filters', 'showNotificationFilters')->name('.notification.filters.show')->whereNumber('user');

                Route::name('.notifications')->group(function () {
                    Route::get('/', 'showNotifications')->name('.show')->whereNumber('user');
                    Route::post('/', 'markNotificationsAsRead')->name('.mark.as.read')->whereNumber('user');
                    Route::get('/count', 'countNotifications')->name('.count')->whereNumber('user');
                });

                Route::prefix('{notification}')->name('.notification')->group(function () {
                    Route::get('/', 'showNotification')->name('.show')->whereNumber(['user', 'notification']);
                    Route::post('/', 'markNotificationAsRead')->name('.mark.as.read')->whereNumber(['user', 'notification']);
                });

            });

            //  Addresses
            Route::prefix('addresses')->group(function () {

                Route::name('.addresses')->group(function () {
                    Route::get('/', 'showAddresses')->name('.show')->whereNumber('user');
                    Route::post('/', 'createAddress')->name('.create')->whereNumber('user');
                });

                Route::prefix('{address}')->name('.address')->group(function () {
                    Route::get('/', 'showAddress')->name('.show')->whereNumber(['user', 'address']);
                    Route::put('/', 'updateAddress')->name('.update')->whereNumber(['user', 'address']);
                    Route::delete('/', 'deleteAddress')->name('.delete')->whereNumber(['user', 'address']);
                });

            });

            //  Friends
            Route::prefix('friends')->group(function () {

                Route::get('/filters', 'showFriendAndFriendGroupFilters')->name('.friend.and.friend.group.filters.show')->whereNumber('user');

                Route::name('.friends')->group(function () {
                    Route::get('/', 'showFriends')->name('.show')->whereNumber('user');
                    Route::post('/', 'createFriends')->name('.create')->whereNumber('user');
                    Route::delete('/', 'removeFriends')->name('.remove')->whereNumber('user');
                    Route::get('/last-selected', 'showLastSelectedFriend')->name('.last.selected.show')->whereNumber('user');
                    Route::put('/last-selected', 'updateLastSelectedFriends')->name('.last.selected.update')->whereNumber('user');
                });

            });

            //  Friends Groups
            Route::prefix('friend-groups')->group(function () {

                Route::get('/filters', 'showFriendGroupFilters')->name('.friend.group.filters.show')->whereNumber('user');

                Route::name('.friend.groups')->group(function () {
                    Route::get('/', 'showFriendGroups')->name('.show')->whereNumber('user');
                    Route::post('/', 'createFriendGroup')->name('.create')->whereNumber('user');
                    Route::delete('/', 'deleteManyFriendGroups')->name('.delete.many')->whereNumber('user');
                    Route::get('/last-selected', 'showLastSelectedFriendGroup')->name('.last.selected.show')->whereNumber('user');
                    Route::put('/last-selected', 'updateLastSelectedFriendGroups')->name('.last.selected.update')->whereNumber('user');
                });

                Route::prefix('{friend_group}')->name('.friend.group')->group(function () {

                    Route::get('/', 'showFriendGroup')->name('.show')->whereNumber(['user', 'friend_group']);
                    Route::put('/', 'updateFriendGroup')->name('.update')->whereNumber(['user', 'friend_group']);
                    Route::delete('/', 'deleteFriendGroup')->name('.delete')->whereNumber(['user', 'friend_group']);
                    Route::get('/members', 'showFriendGroupMembers')->name('.members.show')->whereNumber(['user', 'friend_group']);
                    Route::delete('/members', 'removeFriendGroupMembers')->name('.members.remove')->whereNumber(['user', 'friend_group']);

                    //  Friends Group Stores
                    Route::prefix('stores')->name('.stores')->group(function () {
                        Route::get('/', 'showFriendGroupStores')->name('.show')->whereNumber(['user', 'friend_group']);
                        Route::post('/', 'addFriendGroupStores')->name('.add')->whereNumber(['user', 'friend_group']);
                        Route::delete('/', 'removeFriendGroupStores')->name('.remove')->whereNumber(['user', 'friend_group']);
                    });

                    //  Friends Group Orders
                    Route::prefix('orders')->name('.orders')->group(function () {
                        Route::get('/', 'showFriendGroupOrders')->name('.show')->whereNumber(['user', 'friend_group']);
                    });

                });

            });

            //  Orders
            Route::prefix('orders')->group(function () {
                Route::get('/filters', 'showOrderFilters')->name('.order.filters.show')->whereNumber('user');
                Route::get('/', 'showOrders')->name('.orders.show')->whereNumber('user');
            });

            //  Stores
            Route::prefix('stores')->group(function () {
                Route::get('/first-created-store', 'showFirstCreatedStore')->name('.first.created.store.show')->whereNumber('user');
                Route::get('/filters', 'showStoreFilters')->name('.store.filters.show')->whereNumber('user');
                Route::post('/join', 'joinStore')->name('.stores.join')->whereNumber('user');
                Route::post('/', 'createStore')->name('.stores.create')->whereNumber('user');
                Route::get('/', 'showStores')->name('.stores.show')->whereNumber('user');
            });

            //  AI Assistant
            Route::prefix('ai/assistant')->name('.ai.assistant')->group(function () {

                Route::get('/', 'showAiAssistant')->name('.show')->whereNumber('user');

                //  Shortcodes
                Route::post('/generate-payment-shortcode', 'generateAiAssistantPaymentShortcode')->name('.payment.shortcode.generate')->whereNumber('store');

                //  AI Assistant Subscriptions
                Route::prefix('subscriptions')->name('.subscriptions')->group(function () {

                    Route::get('/', 'showAiAssistantSubscriptions')->name('.show')->whereNumber('user');

                    //  USSD Server Routes: The following route is restricted to USSD requests (See attached middleware)
                    Route::post('/', 'createAiAssistantSubscription')->name('.create')->whereNumber('user')->middleware('request.via.ussd');
                    Route::post('/calculate-amount', 'calculateAiAccessSubscriptionAmount')->name('.calculate.amount')->whereNumber('user');

                });

            });

            //  AI Messages
            Route::prefix('ai/messages')->group(function () {

                Route::name('.ai.messages')->group(function () {
                    Route::get('/', 'showAiMessages')->name('.show')->whereNumber('user');
                    Route::post('/', 'createAiMessage')->name('.create')->whereNumber('user');
                });

                Route::prefix('{ai_message}')->name('.ai.message')->group(function () {
                    Route::get('/', 'showAddress')->name('.show')->whereNumber(['user', 'ai_message']);
                    Route::put('/', 'updateAddress')->name('.update')->whereNumber(['user', 'ai_message']);
                    Route::delete('/', 'deleteAddress')->name('.delete')->whereNumber(['user', 'ai_message']);
                });

            });

            //  Resource Totals
            Route::get('/resource-totals', 'showResourceTotals')->name('.resource.totals.show')->whereNumber('user');

        });

    });

}
