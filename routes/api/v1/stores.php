<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

/**
 *  Store Routes (Public & Super-Admin Facing Routes)
 *
 *  The following routes require an authenticated user.
 *  Refer to "api.php" to see the middleware set to enable this restriction.
 */
Route::controller(StoreController::class)
    ->prefix('stores')
    /**
     *  Scope bindings will instruct laravel to fetch the child relationship
     *  via the parent relationship e.g "stores/{store}/products/{product}"
     *  will make sure that the {product} must be a resource related to
     *  the {store} provided.
     *
     *  Refer to: https://laravel.com/docs/9.x/routing#implicit-model-binding-scoping
     */
    ->scopeBindings()
    ->group(function () {

    Route::get('/filters', 'showStoreFilters')->name('store.filters.show');
    Route::get('/brand-stores', 'showBrandStores')->name('brand.stores.show');
    Route::get('/influencer-stores', 'showInfluencerStores')->name('influencer.stores.show');

    Route::prefix('assigned-stores')->name('assigned.stores')->group(function () {
        Route::post('/arrangement', 'updateAssignedStoreArrangement')->name('.arrangement.update');
    });

    /**
     *  Any authenticated user can view multiple stores
     */
    Route::name('stores')->group(function () {

        Route::get('/', 'index')->name('.show');
        Route::post('/', 'createStore')->name('.create');

        //  Check Invitations To Follow Stores
        Route::get('/check-invitations-to-follow', 'checkInvitationsToFollow')->name('.check.invitations.to.follow');

        //  Accept / Decline Invitations To Follow All Stores
        Route::post('/accept-all-invitations-to-follow', 'acceptAllInvitationsToFollow')->name('.accept.all.invitations.to.follow');
        Route::post('/decline-all-invitations-to-follow', 'declineAllInvitationsToFollow')->name('.decline.all.invitations.to.follow');

        //  Check Invitations To Join Team
        Route::get('/check-invitations-to-join-team', 'checkInvitationsToJoinTeam')->name('.check.invitations.to.join.team');

        //  Accept / Decline Invitations To Join All Teams
        Route::post('/accept-all-invitations-to-join-team', 'acceptAllInvitationsToJoinTeam')->name('.accept.all.invitations.to.join.team');
        Route::post('/decline-all-invitations-to-join-team', 'declineAllInvitationsToJoinTeam')->name('.decline.all.invitations.to.join.team');

    });

    /**
     *  Any authenticated user can view a single store
     */
    Route::prefix('{store}')->name('store')->group(function () {
        Route::get('/', 'show')->name('.show')->whereNumber('store');

        //  You need to be assigned to this store as an admin or creator to do any of the following actions
        Route::middleware('assigned.to.store.as.team.member:admin,creator')->group(function() {
            Route::put('/', 'update')->name('.update')->whereNumber('store');
            Route::get('/logo', 'showLogo')->name('.logo.show')->whereNumber('store');
            Route::post('/logo', 'updateLogo')->name('.logo.update')->whereNumber('store');
            Route::delete('/logo', 'deleteLogo')->name('.logo.delete')->whereNumber('store');
            Route::get('/adverts', 'showAdverts')->name('.adverts.show')->whereNumber('store');
            Route::post('/adverts', 'createAdvert')->name('.adverts.create')->whereNumber('store');
            Route::delete('/adverts', 'deleteAdvert')->name('.adverts.delete')->whereNumber('store');
            Route::post('/adverts/update', 'updateAdvert')->name('.adverts.update')->whereNumber('store');
            Route::get('/cover-photo', 'showCoverPhoto')->name('.cover.photo.show')->whereNumber('store');
            Route::post('/cover-photo', 'updateCoverPhoto')->name('.cover.photo.update')->whereNumber('store');
            Route::delete('/cover-photo', 'deleteCoverPhoto')->name('.cover.photo.delete')->whereNumber('store');
        });

        //  You need to be assigned to this store as a creator to do any of the following actions
        Route::middleware('assigned.to.store.as.team.member:creator')->group(function() {
            Route::delete('/', 'delete')->name('.delete')->whereNumber('store');
            Route::post('/confirm-delete', 'confirmDelete')->name('.confirm.delete')->whereNumber('store');
        });

        //  Products
        Route::prefix('products')->group(function () {
            Route::get('/filters', 'showProductFilters')->name('.product.filters.show')->whereNumber('store');
            Route::get('/', 'showProducts')->name('.products.show')->whereNumber('store');
            Route::post('/', 'createProduct')->name('.products.create')->whereNumber('store');
            Route::post('/visibility', 'updateProductVisibility')->name('.product.visibility.update')->whereNumber('store');
            Route::post('/arrangement', 'updateProductArrangement')->name('.product.arrangement.update')->whereNumber('store');
        });

        //  Coupons
        Route::prefix('coupons')->group(function () {
            Route::get('/filters', 'showCouponFilters')->name('.coupon.filters.show')->whereNumber('store');
            Route::get('/', 'showCoupons')->name('.coupons.show')->whereNumber('store');
            Route::post('/', 'createCoupon')->name('.coupons.create')->whereNumber('store');
        });

        //  Orders
        Route::prefix('orders')->group(function () {
            Route::get('/filters', 'showOrderFilters')->name('.order.filters.show')->whereNumber('store');
            Route::get('/', 'showOrders')->name('.orders.show')->whereNumber('store');
        });

        //  Reviews
        Route::prefix('reviews')->group(function () {
            Route::get('/review-rating-options', 'showReviewRatingOptions')->name('.review.rating.options.show')->whereNumber('store');
            Route::get('/filters', 'showReviewFilters')->name('.review.filters.show')->whereNumber('store');
            Route::get('/', 'showReviews')->name('.reviews.show')->whereNumber('store');
            Route::post('/', 'createReview')->name('.reviews.create')->whereNumber('store');
        });

        //  Followers
        Route::prefix('followers')->group(function () {
            Route::get('/filters', 'showFollowerFilters')->name('.follower.filters.show')->whereNumber('store');
            Route::get('/', 'showFollowers')->name('.followers.show')->whereNumber('store');
            Route::post('/', 'inviteFollowers')->name('.followers.invite')->whereNumber('store');
        });

        //  Following
        Route::get('/following', 'showFollowing')->name('.following.show')->whereNumber('store');
        Route::post('/following', 'updateFollowing')->name('.following.update')->whereNumber('store');

        //  Invitations To Follow
        Route::post('/accept-invitation-to-follow', 'acceptInvitationToFollow')->name('.accept.invitation.to.follow')->whereNumber('store');
        Route::post('/decline-invitation-to-follow', 'declineInvitationToFollow')->name('.decline.invitation.to.follow')->whereNumber('store');

        //  Team Members
        Route::prefix('team-members')->group(function () {

            Route::get('/permissions', 'showAllTeamMemberPermissions')->name('.all.team.member.permissions.show');
            Route::get('/filters', 'showTeamMemberFilters')->name('.team.member.filters.show')->whereNumber('store');
            Route::get('/', 'showTeamMembers')->name('.team.members.show')->whereNumber('store');
            Route::post('/', 'inviteTeamMembers')->name('.team.members.invite')->whereNumber('store');
            Route::delete('/', 'removeTeamMembers')->name('.team.members.remove')->whereNumber('store');

            Route::prefix('{team_member}')->name('.team.member')->group(function () {
                Route::get('/', 'showTeamMember')->name('.show')->whereNumber(['store', 'team_member']);
                Route::get('/permissions', 'showTeamMemberPermissions')->name('.permissions.show')->whereNumber(['store', 'team_member']);
                Route::put('/permissions', 'updateTeamMemberPermissions')->name('.permissions.update')->whereNumber(['store', 'team_member']);
            });

        });

        //  Invitations To Join Team
        Route::post('/accept-invitation-to-join-team', 'acceptInvitationToJoinTeam')->name('.accept.invitation.to.join.team')->whereNumber('store');
        Route::post('/decline-invitation-to-join-team', 'declineInvitationToJoinTeam')->name('.decline.invitation.to.join.team')->whereNumber('store');

        //  My Permissions
        Route::get('/permissions', 'showMyPermissions')->name('.permissions.show')->whereNumber('store');

        //  Customers
        Route::prefix('customers')->group(function () {

            Route::get('/filters', 'showCustomerFilters')->name('.customer.filters.show')->whereNumber('store');
            Route::get('/', 'showCustomers')->name('.customers.show')->whereNumber('store');

            Route::prefix('{customer}')->name('.customer')->group(function () {
                Route::get('/', 'showCustomer')->name('.show')->whereNumber(['store', 'customer']);
            });

        });

        //  Subscriptions
        Route::prefix('subscriptions')
            ->name('.subscriptions')->group(function () {

            Route::get('/', 'showMySubscriptions')->name('.show')->whereNumber('store');

            //  USSD Server Routes: The following route is restricted to USSD requests (See attached middleware)
            Route::post('/store-access', 'createStoreAccessSubscription')->name('.create')->whereNumber('store')->middleware('request.via.ussd');
            Route::post('/store-access/fake', 'createStoreAccessFakeSubscription')->name('.fake.create')->whereNumber('store');
            Route::post('/store-access/calculate-amount', 'calculateStoreAccessSubscriptionAmount')->name('.calculate.amount')->whereNumber('store');

        });

        //  Friend Groups
        Route::post('/add-to-friend-groups', 'addStoreToFriendGroups')->name('.friend.groups.add')->whereNumber('store');
        Route::delete('/remove-from-friend-group', 'removeStoreFromFriendGroup')->name('.friend.groups.remove')->whereNumber('store');

        //  Brand Stores
        Route::post('/add-to-brand-stores', 'addStoreToBrandStores')->name('.brand.add')->whereNumber('store');
        Route::post('/remove-from-brand-stores', 'removeStoreFromBrandStores')->name('.brand.remove')->whereNumber('store');
        Route::post('/add-or-remove-from-brand-stores', 'addOrRemoveStoreFromBrandStores')->name('.brand.add.or.remove')->whereNumber('store');

        //  Influencer Stores
        Route::post('/add-to-influencer-stores', 'addStoreToInfluencerStores')->name('.influencer.add')->whereNumber('store');
        Route::post('/remove-from-influencer-stores', 'removeStoreFromInfluencerStores')->name('.influencer.remove')->whereNumber('store');
        Route::post('/add-or-remove-from-influencer-stores', 'addOrRemoveStoreFromInfluencerStores')->name('.influencer.add.or.remove')->whereNumber('store');

        //  Assigned Stores
        Route::post('/add-to-assigned-stores', 'addStoreToAssignedStores')->name('.assigned.add')->whereNumber('store');
        Route::post('/remove-from-assigned-stores', 'removeStoreFromAssignedStores')->name('.assigned.remove')->whereNumber('store');
        Route::post('/add-or-remove-from-assigned-stores', 'addOrRemoveStoreFromAssignedStores')->name('.assigned.add.or.remove')->whereNumber('store');

        //  Instant Carts
        Route::get('/instant-carts', 'instantCarts')->name('.instant.carts.show')->whereNumber('store');

        //  Shortcodes
        Route::get('/show-visit-shortcode', 'showVisitShortcode')->name('.visit.shortcode.show')->whereNumber('store');
        Route::post('/generate-payment-shortcode', 'generatePaymentShortcode')->name('.payment.shortcode.generate')->whereNumber('store');

        //  Payment methods
        Route::get('/supported-payment-methods', 'showSupportedPaymentMethods')->name('.supported.payment.methods.show')->whereNumber('store');
        Route::get('/available-payment-methods', 'showAvailablePaymentMethods')->name('.available.payment.methods.show')->whereNumber('store');

        //  Sharable Content
        Route::prefix('sharable-content')
            ->name('.sharable.content')->group(function () {

            Route::get('/', 'showSharableContent')->name('.show')->whereNumber('store');
            Route::get('/choices', 'showSharableContentChoices')->name('.choices.show')->whereNumber('store');

        });

        //  Shopping carts
        Route::prefix('shopping-cart')
            ->name('.shopping.cart')
            ->group(function () {

            //  Allow the public access to manage their shopping cart and place an order
            Route::post('/', 'inspectShoppingCart')->name('.inspect')->whereNumber('store');
            Route::post('/convert', 'convertShoppingCart')->name('.convert')->whereNumber('store');
            Route::post('/order-for-users', 'showShoppingCartOrderForUsers')->name('.order.for.users.show')->whereNumber('store');
            Route::get('/order-for-options', 'showShoppingCartOrderForOptions')->name('.order.for.options.show')->whereNumber('store');
            Route::post('/count-order-for-users', 'countShoppingCartOrderForUsers')->name('.order.for.users.count')->whereNumber('store');

        });

    });

});
