<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\TransactionRepository;

class UserResource extends BaseResource
{
    protected $isProfileOwner;
    protected $customExcludeFields = ['password'];

    protected $customIncludeFields = [
        'transactions_as_payer_count', 'paid_transactions_as_payer_count'
    ];

    protected $resourceRelationships = [
        'latestTransactionAsPayer' => TransactionRepository::class,
    ];

    private function viewingPrivately() {
        return $this->isSuperAdmin || request()->routeIs([
            'api.home', 'auth.*', 'user.profile.update'
        ]);
    }

    private function viewingPublicly() {
        return $this->viewingPrivately() == false;
    }

    private function viewingProfileExistence() {
        return request()->routeIs(['auth.account.exists']);
    }

    public function toArray($request)
    {
        /**
         *  Checking account existence
         *
         *  If we are checking the account existence then
         *  limit the information we share.
         */
        if( $this->viewingProfileExistence() ) {

            //  Don't show links
            $this->showLinks = false;

            //  Overide and apply custom fields
            $this->customFields = ['mobile_number'];

            //  Overide and apply custom attributes
            $this->customAttributes = ['requires_password'];

        /**
         *  Viewing as Public User
         *
         *  If we are veiwing as the general public then limit the information
         *  we share. Usually we just want to check if the account exists,
         *  so we only limit to the user name(s) and account status.
         */
        }elseif( $this->viewingPublicly() ) {

            //  Overide and apply custom fields
            $this->customFields = ['id', 'first_name', 'last_name', 'mobile_number'];

            //  Overide and apply custom attributes
            //$this->customAttributes = ['name'];

        }

        /**
         *  If the user is accessed via a store relationship then we can gain access to the store-user
         *  pivot information. This pivot information is accessed via the "user_store_association"
         *  pivot name. If this property is provided then we can include it with our payload as an
         *  attribute
         */
        if( !empty($this->resource->user_store_association) ) {

            //  Include the user and store association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['user_store_association']
            );

        }

        /**
         *  If the user is accessed via an order relationship then we can gain access to the user-order-view
         *  pivot information. This pivot information is accessed via the "user_order_view_association"
         *  pivot name. If this property is provided then we can include it with our payload as an
         *  attribute
         */
        if( !empty($this->resource->user_order_view_association) ) {

            //  Include the user and order view association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['user_order_view_association']
            );

        }

        /**
         *  If the user is accessed via an order relationship then we can gain access to the user-order-collection
         *  pivot information. This pivot information is accessed via the "user_order_collection_association"
         *  pivot name. If this property is provided then we can include it with our payload as an
         *  attribute
         */
        if( !empty($this->resource->user_order_collection_association) ) {

            //  Include the user and order collection association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['user_order_collection_association']
            );

        }

        /**
         *  If the user is accessed via an friend group relationship then we can gain access to the user-friend-group
         *  pivot information. This pivot information is accessed via the "user_friend_group_association" pivot name.
         *  If this property is provided then we can include it with our payload as an attribute
         */
        if( !empty($this->resource->user_friend_group_association) ) {

            //  Include the user and friend group association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['user_friend_group_association']
            );

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        //  Check if this resource belongs to the authenticated
        $isAuthUser = $this->resource->id == request()->auth_user->id;

        //  Auth user route name prefix
        $authUserPrefix = 'auth.user.';

        //  User route name prefix
        $userPrefix = 'user.';

        //  Set the route name prefix
        $prefix = $isAuthUser ? $authUserPrefix : $userPrefix;

        //  User route parameters
        $userParams = ['user' => $this->resource->id];

        //  Set the route parameters
        $params = $isAuthUser ? [] : $userParams;

        $this->resourceLinks = [

            //  User
            new ResourceLink('self', route($prefix.'show', $params),'Show user'),
            new ResourceLink('update.user', route($prefix.'update', $params),'Update user'),
            new ResourceLink('delete.user', route($prefix.'delete', $params),'Delete user'),
            new ResourceLink('confirm.delete.user', route($prefix.'confirm.delete', $params),'Confirm delete user'),
            new ResourceLink('show.profile.photo', route($prefix.'profile.photo.show', $params), 'Show user profile photo'),
            new ResourceLink('update.profile.photo', route($prefix.'profile.photo.update', $params), 'Update user profile photo'),
            new ResourceLink('delete.profile.photo', route($prefix.'profile.photo.delete', $params), 'Delete user profile photo'),

            //  Access Tokens
            new ResourceLink('logout', route($prefix.'logout', $params),'Logout'),
            new ResourceLink('show.tokens', route($prefix.'tokens.show', $params),'Show tokens'),

            //  Terms And Conditions
            new ResourceLink('show.terms.and.conditions', route($prefix.'terms.and.conditions.show', $params),'Show terms and conditions'),
            new ResourceLink('accept.terms.and.conditions', route($prefix.'terms.and.conditions.accept', $params),'Accept terms and conditions'),

            //  Mobile Verification
            new ResourceLink('show.mobile.verification.code', route($prefix.'show.mobile.verification.code', $params),'Show mobile verification code'),
            new ResourceLink('verify.mobile.verification.code', route($prefix.'verify.mobile.verification.code', $params),'Verify mobile verification code'),
            new ResourceLink('generate.mobile.verification.code', route($prefix.'generate.mobile.verification.code', $params),'Generate mobile verification code'),

            //  Addresses
            new ResourceLink('show.addresses', route($prefix.'addresses.show', $params),'Show addresses'),
            new ResourceLink('create.addresses', route($prefix.'addresses.create', $params),'Create address'),

            //  Notifications
            new ResourceLink('show.notification.filters', route($prefix.'notification.filters.show', $params),'Show notification filters'),
            new ResourceLink('show.notifications', route($prefix.'notifications.show', $params),'Show notifications'),
            new ResourceLink('count.notifications', route($prefix.'notifications.count', $params),'Count notifications'),
            new ResourceLink('mark.notifications.as.read', route($prefix.'notifications.mark.as.read', $params),'Mark notifications as read'),

            //  Friends
            new ResourceLink('show.friends', route($prefix.'friends.show', $params),'Show friends'),
            new ResourceLink('create.friends', route($prefix.'friends.create', $params),'Create friends'),
            new ResourceLink('remove.friends', route($prefix.'friends.remove', $params),'Remove friends'),
            new ResourceLink('show.last.selected.friend', route($prefix.'friends.last.selected.show', $params),'Show last selected friend'),
            new ResourceLink('update.last.selected.friends', route($prefix.'friends.last.selected.update', $params),'Update last selected friends'),
            new ResourceLink('show.friend.and.friend.group.filters', route($prefix.'friend.and.friend.group.filters.show', $params),'Show friend and friend group filters'),

            //  Friend Groups
            new ResourceLink('show.first.created.friend.group', route($prefix.'first.created.friend.group.show', $params),'Show first created friend group'),
            new ResourceLink('show.last.selected.friend.group', route($prefix.'last.selected.friend.group.show', $params),'Show last selected friend group'),
            new ResourceLink('update.last.selected.friend.groups', route($prefix.'last.selected.friend.groups.update', $params),'Update last selected friend group'),
            new ResourceLink('delete.many.friend.groups', route($prefix.'delete.many', $params),'Delete many friend groups'),
            new ResourceLink('check.invitations.to.join.friend.groups', route($prefix.'friend.groups.check.invitations.to.join.groups', $params),'Check invitations to join groups'),
            new ResourceLink('accept.all.invitations.to.join.friend.groups', route($prefix.'friend.groups.accept.all.invitations.to.join.groups', $params),'Accept all invitations to join groups'),
            new ResourceLink('decline.all.invitations.to.join.friend.groups', route($prefix.'friend.groups.decline.all.invitations.to.join.groups', $params),'Decline all invitations to join groups'),
            new ResourceLink('show.friend.group.filters', route($prefix.'friend.group.filters.show', $params),'Show friend group filters'),
            new ResourceLink('show.friend.groups', route($prefix.'friend.groups.show', $params),'Show friend groups'),
            new ResourceLink('create.friend.groups', route($prefix.'friend.groups.create', $params),'Create friend groups'),

            //  AI Assistant
            new ResourceLink('show.ai.assistant', route($prefix.'ai.assistant.show', $params),'Show AI Assistant'),
            new ResourceLink('show.ai.assistant.subscriptions', route($prefix.'ai.assistant.subscriptions.show', $params),'Show AI Assistant subscriptions'),
            new ResourceLink('create.ai.assistant.subscriptions', route($prefix.'ai.assistant.subscriptions.create', $params),'Create AI Assistant subscription'),
            new ResourceLink('calculate.ai.assistant.subscription.amount', route($prefix.'ai.assistant.subscriptions.calculate.amount', $params),'Calculate AI Assistant subscription amount'),
            new ResourceLink('generate.ai.assistant.payment.shortcode', route($prefix.'ai.assistant.payment.shortcode.generate', $params), 'Generate a payment shortcode for AI Assistant'),

            //  SMS Alert
            new ResourceLink('show.sms.alert', route($prefix.'sms.alert.show', $params),'Show SMS alert'),
            new ResourceLink('show.sms.alert.transactions', route($prefix.'sms.alert.transactions.show', $params),'Show SMS alert transactions'),
            new ResourceLink('create.sms.alert.transaction', route($prefix.'sms.alert.transactions.create', $params),'Create SMS alert transaction'),
            new ResourceLink('calculate.sms.alert.transaction.amount', route($prefix.'sms.alert.transactions.calculate.amount', $params),'Calculate SMS alert transaction amount'),
            new ResourceLink('generate.sms.alert.payment.shortcode', route($prefix.'sms.alert.payment.shortcode.generate', $params), 'Generate a payment shortcode for SMS alert'),

            //  AI Messages
            new ResourceLink('show.ai.messages', route($prefix.'ai.messages.show', $params),'Show AI messages'),
            new ResourceLink('create.ai.messages', route($prefix.'ai.messages.create', $params),'Create AI messages'),
            new ResourceLink('create.ai.messages.while.streaming', route($prefix.'ai.messages.create.while.streaming', $params),'Create AI messages while streaming'),

            //  Orders
            new ResourceLink('show.order.filters', route($prefix.'order.filters.show', $params),'Show order filters'),
            new ResourceLink('show.orders', route($prefix.'orders.show', $params),'Show orders'),

            //  reviews
            new ResourceLink('show.review.filters', route($prefix.'review.filters.show', $params),'Show review filters'),
            new ResourceLink('show.reviews', route($prefix.'reviews.show', $params),'Show reviews'),

            //  Stores
            new ResourceLink('show.first.created.store', route($prefix.'first.created.store.show', $params),'Show first store created'),
            new ResourceLink('show.store.filters', route($prefix.'store.filters.show', $params),'Show store filters'),
            new ResourceLink('join.stores', route($prefix.'stores.join', $params),'Join store'),
            new ResourceLink('create.stores', route($prefix.'stores.create', $params),'Create store'),
            new ResourceLink('show.stores', route($prefix.'stores.show', $params),'Show stores'),

            //  Resource Totals
            new ResourceLink('show.resource.totals', route($prefix.'resource.totals.show', $params),'Show resource totals'),

        ];

        //  If a store exists on this request
        if( request()->store ) {

            array_push($this->resourceLinks,

                //  Store Team Member Routes
                new ResourceLink('show.store.team.member', route('store.team.member.show', ['store' => request()->store->id, 'team_member' => $this->resource->id]), 'Show the team member'),
                new ResourceLink('show.store.team.member.permissions', route('store.team.member.permissions.show', ['store' => request()->store->id, 'team_member' => $this->resource->id]), 'Show the team member permissions'),
                new ResourceLink('update.store.team.member.permissions', route('store.team.member.permissions.update', ['store' => request()->store->id, 'team_member' => $this->resource->id]), 'Update the team member permissions'),

                //  Store Customer Routes
                new ResourceLink('show.store.customer', route('store.customer.show', ['store' => request()->store->id, 'customer' => $this->resource->id]), 'Show the customer'),

            );

        }
    }
}
