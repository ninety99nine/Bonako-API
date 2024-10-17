<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Models\Pivots\UserStoreAssociation;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\SubscriptionRepository;

class StoreResource extends BaseResource
{
    protected $customExcludeFields = ['user_id'];

    protected $resourceRelationships = [
        'authActiveSubscription' => SubscriptionRepository::class,
        'products' => ProductRepository::class,
    ];

    public function toArray($request)
    {
        /**
         *  Viewing as Public User
         *
         *  If we are veiwing as the general public
         *  then limit the information we share.
         */
        if( $this->isPublicUser ) {

            //  Overide and apply custom fields
        //    $this->customFields = ['name', 'call_to_action', 'description', 'verified', 'online', 'offline_message'];

            //  Overide and apply custom attributes
        //    $this->customAttributes = [];

        }

        /**
         *  If the store is accessed via a user relationship then we can gain access to the store-user
         *  pivot information. This pivot information is accessed via the "user_store_association"
         *  pivot name. If this property is provided then we can include it with our payload as
         *  an attribute
         */
        if( !empty($this->resource->user_store_association) ) {

            //  Include the user and store association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['user_store_association']
            );

        }

        /**
         *  If the store is accessed via a friend group relationship then we can gain access to the friend-group-store
         *  pivot information. This pivot information is accessed via the "friend_group_store_association" pivot name.
         *  If this property is provided then we can include it with our payload as an attribute
         */
        if( !empty($this->resource->friend_group_store_association) ) {

            //  Include the friend group and store association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['friend_group_store_association']
            );

        }

        /**
         *  If the store-payment-method relationship exists we can include
         *  it with our payload as an attribute
         */
        if( !empty($this->resource->store_payment_method_association) ) {

            //  Include the user and store association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['store_payment_method_association']
            );

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        $store = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.store', route('show.store', ['storeId' => $store->id])),
            new ResourceLink('update.store', route('update.store', ['storeId' => $store->id])),
            new ResourceLink('delete.store', route('delete.store', ['storeId' => $store->id])),
            new ResourceLink('show.store.logo', route('show.store.logo', ['storeId' => $store->id])),
            new ResourceLink('upload.store.logo', route('upload.store.logo', ['storeId' => $store->id])),
            new ResourceLink('show.store.cover.photo', route('show.store.cover.photo', ['storeId' => $store->id])),
            new ResourceLink('upload.store.cover.photo', route('upload.store.cover.photo', ['storeId' => $store->id])),
            new ResourceLink('show.store.adverts', route('show.store.adverts', ['storeId' => $store->id])),
            new ResourceLink('upload.store.advert', route('upload.store.advert', ['storeId' => $store->id])),
            new ResourceLink('show.store.quick.start.guide', route('show.store.quick.start.guide', ['storeId' => $store->id])),

            new ResourceLink('show.store.followers', route('show.store.followers', ['storeId' => $store->id])),
            new ResourceLink('invite.store.followers', route('invite.store.followers', ['storeId' => $store->id])),
            new ResourceLink('show.store.following', route('show.store.following', ['storeId' => $store->id])),
            new ResourceLink('update.store.following', route('update.store.following', ['storeId' => $store->id])),
            new ResourceLink('accept.invitation.to.follow.store', route('accept.invitation.to.follow.store', ['storeId' => $store->id])),
            new ResourceLink('decline.invitation.to.follow.store', route('decline.invitation.to.follow.store', ['storeId' => $store->id])),

            new ResourceLink('show.store.team.members', route('show.store.team.members', ['storeId' => $store->id])),
            new ResourceLink('invite.store.team.members', route('invite.store.team.members', ['storeId' => $store->id])),
            new ResourceLink('remove.store.team.members', route('remove.store.team.members', ['storeId' => $store->id])),
            new ResourceLink('show.my.store.permissions', route('show.my.store.permissions', ['storeId' => $store->id])),
            new ResourceLink('show.team.member.permission.options', route('show.team.member.permission.options', ['storeId' => $store->id])),
            new ResourceLink('accept.invitation.to.join.store.team', route('accept.invitation.to.join.store.team', ['storeId' => $store->id])),
            new ResourceLink('decline.invitation.to.join.store.team', route('decline.invitation.to.join.store.team', ['storeId' => $store->id])),

            new ResourceLink('show.store.orders', route('show.store.orders', ['storeId' => $store->id])),
            new ResourceLink('show.store.products', route('show.store.products', ['storeId' => $store->id])),
            new ResourceLink('show.store.coupons', route('show.store.coupons', ['storeId' => $store->id])),
            new ResourceLink('show.store.reviews', route('show.store.reviews', ['storeId' => $store->id])),
            new ResourceLink('show.store.customers', route('show.store.customers', ['storeId' => $store->id])),
            new ResourceLink('show.store.subscriptions', route('show.store.subscriptions', ['storeId' => $store->id])),
            new ResourceLink('show.store.transactions', route('show.store.transactions', ['storeId' => $store->id])),
        ];
    }
}
