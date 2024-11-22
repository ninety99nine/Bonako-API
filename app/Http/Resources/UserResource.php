<?php

namespace App\Http\Resources;

use App\Traits\AuthTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class UserResource extends BaseResource
{
    use AuthTrait;

    protected $isProfileOwner;
    protected $customExcludeFields = ['password'];

    protected $customIncludeFields = [
        'transactions_as_payer_count', 'paid_transactions_as_payer_count'
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
         *  pivot information. This pivot information is accessed via the "friend_group_user_association" pivot name.
         *  If this property is provided then we can include it with our payload as an attribute
         */
        if( !empty($this->resource->friend_group_user_association) ) {

            //  Include the user and friend group association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['friend_group_user_association']
            );

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        $user = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.user', route("show.user", ['userId' => $user->id])),
            new ResourceLink('update.user', route("update.user", ['userId' => $user->id])),
            new ResourceLink('delete.user', route("delete.user", ['userId' => $user->id])),

            new ResourceLink('generate.mobile.verification.code', route("generate.user.mobile.verification.code", ['userId' => $user->id])),
            new ResourceLink('verify.mobile.verification.code', route("verify.user.mobile.verification.code", ['userId' => $user->id])),

            new ResourceLink('show.tokens', route("show.user.tokens", ['userId' => $user->id])),
            new ResourceLink('logout.user', route("logout.user", ['userId' => $user->id])),

            new ResourceLink('show.profile.photo', route("show.user.profile.photo", ['userId' => $user->id])),
            new ResourceLink('upload.profile.photo', route("upload.user.profile.photo", ['userId' => $user->id])),
            new ResourceLink('delete.profile.photo', route("delete.user.profile.photo", ['userId' => $user->id])),

            new ResourceLink('show.ai.assistant', route("show.user.ai.assistant", ['userId' => $user->id])),
            new ResourceLink('show.resource.totals', route("show.user.resource.totals", ['userId' => $user->id])),

            new ResourceLink('show.orders', route("show.user.orders", ['userId' => $user->id])),
            new ResourceLink('show.stores', route("show.user.stores", ['userId' => $user->id])),
            new ResourceLink('show.reviews', route("show.user.reviews", ['userId' => $user->id])),
            new ResourceLink('show.friends', route("show.user.friends", ['userId' => $user->id])),
            new ResourceLink('show.addresses', route("show.user.addresses", ['userId' => $user->id])),
            new ResourceLink('show.ai.messages', route("show.user.ai.messages", ['userId' => $user->id])),
            new ResourceLink('show.notifications', route("show.user.notifications", ['userId' => $user->id])),
            new ResourceLink('show.friend.groups', route("show.user.friend.groups", ['userId' => $user->id])),
        ];

        if( $storeId = request()->storeId ) {

            array_push($this->resourceLinks,

                //  Store Team Member Routes
                new ResourceLink('show.store.team.member', route('show.store.team.member', ['storeId' => $storeId, 'teamMemberId' => $user->id])),
                new ResourceLink('show.store.team.member.permissions', route('show.store.team.member.permissions', ['storeId' => $storeId, 'teamMemberId' => $user->id])),
                new ResourceLink('update.store.team.member.permissions', route('update.store.team.member.permissions', ['storeId' => $storeId, 'teamMemberId' => $user->id])),

            );

        }

    }
}
