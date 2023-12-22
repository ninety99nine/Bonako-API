<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\ProductRepository;
use App\Repositories\ShortcodeRepository;
use App\Http\Resources\Helpers\ResourceLink;
use App\Models\Pivots\UserStoreAssociation;
use App\Repositories\OrderRepository;
use App\Repositories\SubscriptionRepository;

class StoreResource extends BaseResource
{
    protected $customExcludeFields = ['user_id'];

    protected $resourceRelationships = [
        'authPaymentShortcode' => ShortcodeRepository::class,
        'authActiveSubscription' => SubscriptionRepository::class,
        'visitShortcode' => ShortcodeRepository::class,
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
        //    $this->customFields = ['logo', 'cover_photo', 'adverts', 'name', 'call_to_action', 'description', 'verified', 'online', 'offline_message'];

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
        /**
         *  Check if this request is being performed by the Authourized User
         *  or by the Super Admin on behalf of a user. Use this to determine
         *  the route name prefix to generate the correct links.
         */
        $routeNamePrefix = 'store.';

        $this->resourceLinks = [
            new ResourceLink('self', route($routeNamePrefix.'show', ['store' => $this->resource->id]), 'The users store'),
            new ResourceLink('update.store', route($routeNamePrefix.'update', ['store' => $this->resource->id]), 'Update store'),
            new ResourceLink('delete.store', route($routeNamePrefix.'delete', ['store' => $this->resource->id]), 'Delete store'),
            new ResourceLink('confirm.delete.store', route($routeNamePrefix.'confirm.delete', ['store' => $this->resource->id]), 'Confirm delete store'),
            new ResourceLink('show.logo', route($routeNamePrefix.'logo.show', ['store' => $this->resource->id]), 'Show store logo'),
            new ResourceLink('update.logo', route($routeNamePrefix.'logo.update', ['store' => $this->resource->id]), 'Update store logo'),
            new ResourceLink('delete.logo', route($routeNamePrefix.'logo.delete', ['store' => $this->resource->id]), 'Delete store logo'),
            new ResourceLink('show.cover.photo', route($routeNamePrefix.'cover.photo.show', ['store' => $this->resource->id]), 'Show store cover photo'),
            new ResourceLink('update.cover.photo', route($routeNamePrefix.'cover.photo.update', ['store' => $this->resource->id]), 'Update store cover photo'),
            new ResourceLink('delete.cover.photo', route($routeNamePrefix.'cover.photo.delete', ['store' => $this->resource->id]), 'Delete store cover photo'),
            new ResourceLink('show.adverts', route($routeNamePrefix.'adverts.show', ['store' => $this->resource->id]), 'Show store adverts'),
            new ResourceLink('create.advert', route($routeNamePrefix.'adverts.create', ['store' => $this->resource->id]), 'Create store advert'),
            new ResourceLink('update.advert', route($routeNamePrefix.'adverts.update', ['store' => $this->resource->id]), 'Update store advert'),
            new ResourceLink('delete.advert', route($routeNamePrefix.'adverts.delete', ['store' => $this->resource->id]), 'Delete store advert'),

            //  Products
            new ResourceLink('show.product.filters', route($routeNamePrefix.'product.filters.show', ['store' => $this->resource->id]), 'The store product filters'),
            new ResourceLink('show.products', route($routeNamePrefix.'products.show', ['store' => $this->resource->id]), 'The store products'),
            new ResourceLink('create.products', route($routeNamePrefix.'products.create', ['store' => $this->resource->id]), 'Create store products'),
            new ResourceLink('update.product.visibility', route($routeNamePrefix.'product.visibility.update', ['store' => $this->resource->id]), 'Update the store product visibility'),
            new ResourceLink('update.product.arrangement', route($routeNamePrefix.'product.arrangement.update', ['store' => $this->resource->id]), 'Update the store product arrangement'),

            //  Coupons
            new ResourceLink('show.coupon.filters', route($routeNamePrefix.'coupon.filters.show', ['store' => $this->resource->id]), 'The store coupon filters'),
            new ResourceLink('show.coupons', route($routeNamePrefix.'coupons.show', ['store' => $this->resource->id]), 'The store coupons'),
            new ResourceLink('create.coupons', route($routeNamePrefix.'coupons.create', ['store' => $this->resource->id]), 'Create store coupons'),

            //  Orders
            new ResourceLink('show.orders', route($routeNamePrefix.'orders.show', ['store' => $this->resource->id]), 'The store orders'),
            new ResourceLink('show.order.filters', route($routeNamePrefix.'order.filters.show', ['store' => $this->resource->id]), 'The store order filters'),

            //  Reviews
            new ResourceLink('show.reviews', route($routeNamePrefix.'reviews.show', ['store' => $this->resource->id]), 'The store reviews'),
            new ResourceLink('create.reviews', route($routeNamePrefix.'reviews.create', ['store' => $this->resource->id]), 'The route to add a review'),
            new ResourceLink('show.review.filters', route($routeNamePrefix.'review.filters.show', ['store' => $this->resource->id]), 'The store review filters'),
            new ResourceLink('show.review.rating.options', route($routeNamePrefix.'review.rating.options.show', ['store' => $this->resource->id]), 'The store review rating options'),

            //  Followers
            new ResourceLink('show.follower.filters', route($routeNamePrefix.'follower.filters.show', ['store' => $this->resource->id]), 'The store follower filters'),
            new ResourceLink('show.followers', route($routeNamePrefix.'followers.show', ['store' => $this->resource->id]), 'The store followers'),
            new ResourceLink('invite.followers', route($routeNamePrefix.'followers.invite', ['store' => $this->resource->id]), 'The store route to invite followers'),
            new ResourceLink('show.following', route($routeNamePrefix.'following.show', ['store' => $this->resource->id]), 'The route to show following'),
            new ResourceLink('update.following', route($routeNamePrefix.'following.update', ['store' => $this->resource->id]), 'The route to update following'),
            new ResourceLink('accept.invitation.to.follow', route($routeNamePrefix.'accept.invitation.to.follow', ['store' => $this->resource->id]), 'The route to accept invitation to follow'),
            new ResourceLink('decline.invitation.to.follow', route($routeNamePrefix.'decline.invitation.to.follow', ['store' => $this->resource->id]), 'The route to decline invitation to follow'),

            //  Team Members
            new ResourceLink('show.all.team.member.permissions', route($routeNamePrefix.'all.team.member.permissions.show', ['store' => $this->resource->id]), 'Show store team member permissions'),
            new ResourceLink('show.team.member.filters', route($routeNamePrefix.'team.member.filters.show', ['store' => $this->resource->id]), 'The store team member filters'),
            new ResourceLink('show.team.members', route($routeNamePrefix.'team.members.show', ['store' => $this->resource->id]), 'The store team members'),
            new ResourceLink('invite.team.members', route($routeNamePrefix.'team.members.invite', ['store' => $this->resource->id]), 'The store route to invite team members'),
            new ResourceLink('remove.team.members', route($routeNamePrefix.'team.members.remove', ['store' => $this->resource->id]), 'The store route to remove team members'),
            new ResourceLink('accept.invitation.to.join.team', route($routeNamePrefix.'accept.invitation.to.join.team', ['store' => $this->resource->id]), 'The route to accept invitation to join team'),
            new ResourceLink('decline.invitation.to.join.team', route($routeNamePrefix.'decline.invitation.to.join.team', ['store' => $this->resource->id]), 'The route to decline invitation to join team'),
            new ResourceLink('show.my.permissions', route($routeNamePrefix.'permissions.show', ['store' => $this->resource->id]), 'The route to show my permissions'),

            //  Customers
            new ResourceLink('show.customer.filters', route($routeNamePrefix.'customer.filters.show', ['store' => $this->resource->id]), 'The store customer filters'),
            new ResourceLink('show.customers', route($routeNamePrefix.'customers.show', ['store' => $this->resource->id]), 'The store customers'),

            //  Subscriptions
            new ResourceLink('show.my.subscriptions', route($routeNamePrefix.'subscriptions.show', ['store' => $this->resource->id]), 'Show my subscriptions'),
            new ResourceLink('create.subscriptions', route($routeNamePrefix.'subscriptions.create', ['store' => $this->resource->id]), 'Create a subscription'),
            /*  Remove the route below because it was created for testing purposes - Use the route above for production instead */
            new ResourceLink('create.fake.subscriptions', route($routeNamePrefix.'subscriptions.fake.create', ['store' => $this->resource->id]), 'Create a fake subscription'),
            new ResourceLink('calculate.subscription.amount', route($routeNamePrefix.'subscriptions.calculate.amount', ['store' => $this->resource->id]), 'Calculate subscription amount'),

            //  Friend Groups
            new ResourceLink('add.to.friend.groups', route($routeNamePrefix.'friend.groups.add', ['store' => $this->resource->id]), 'Add store to friend groups'),
            new ResourceLink('remove.from.friend.groups', route($routeNamePrefix.'friend.groups.remove', ['store' => $this->resource->id]), 'Remove store from friend group'),

            //  Brand Stores
            new ResourceLink('add.to.brand.stores', route($routeNamePrefix.'brand.add', ['store' => $this->resource->id]),'Add to brand stores'),
            new ResourceLink('remove.from.brand.stores', route($routeNamePrefix.'brand.remove', ['store' => $this->resource->id]),'Remove from brand stores'),
            new ResourceLink('add.or.remove.from.brand.stores', route($routeNamePrefix.'brand.add.or.remove', ['store' => $this->resource->id]),'Add or remove from brand stores'),

            //  Influencer Stores
            new ResourceLink('add.to.influencer.stores', route($routeNamePrefix.'influencer.add', ['store' => $this->resource->id]),'Add to influencer stores'),
            new ResourceLink('remove.from.influencer.stores', route($routeNamePrefix.'influencer.remove', ['store' => $this->resource->id]),'Remove from influencer stores'),
            new ResourceLink('add.or.remove.from.influencer.stores', route($routeNamePrefix.'influencer.add.or.remove', ['store' => $this->resource->id]),'Add or remove from influencer stores'),

            //  Assigned Stores
            new ResourceLink('add.to.assigned.stores', route($routeNamePrefix.'assigned.add', ['store' => $this->resource->id]),'Add to assigned stores'),
            new ResourceLink('remove.from.assigned.stores', route($routeNamePrefix.'assigned.remove', ['store' => $this->resource->id]),'Remove from assigned stores'),
            new ResourceLink('add.or.remove.from.assigned.stores', route($routeNamePrefix.'assigned.add.or.remove', ['store' => $this->resource->id]),'Add or remove from assigned stores'),

            //  Shortcodes
            new ResourceLink('show.visit.shortcode', route($routeNamePrefix.'visit.shortcode.show', ['store' => $this->resource->id]), 'Request a payment shortcode'),
            new ResourceLink('generate.payment.shortcode', route($routeNamePrefix.'payment.shortcode.generate', ['store' => $this->resource->id]), 'Generate a payment shortcode'),

            //  Payment Methods
            new ResourceLink('show.supported.payment.methods', route($routeNamePrefix.'supported.payment.methods.show', ['store' => $this->resource->id]), 'Show supported payment methods'),
            new ResourceLink('show.available.payment.methods', route($routeNamePrefix.'available.payment.methods.show', ['store' => $this->resource->id]), 'Show available payment methods'),

            //  Sharable Content
            new ResourceLink('show.sharable.content', route($routeNamePrefix.'sharable.content.show', ['store' => $this->resource->id]), 'Show sharable content'),
            new ResourceLink('show.sharable.content.choices', route($routeNamePrefix.'sharable.content.choices.show', ['store' => $this->resource->id]), 'Show sharable content choices'),

            //  Shopping Cart
            new ResourceLink('count.shopping.cart.order.for.users', route($routeNamePrefix.'shopping.cart.order.for.users.count', ['store' => $this->resource->id]), 'The shopping cart order for total users'),
            new ResourceLink('show.shopping.cart.order.for.options', route($routeNamePrefix.'shopping.cart.order.for.options.show', ['store' => $this->resource->id]), 'The shopping cart order for options'),
            new ResourceLink('show.shopping.cart.order.for.users', route($routeNamePrefix.'shopping.cart.order.for.users.show', ['store' => $this->resource->id]), 'The shopping cart order for users'),
            new ResourceLink('inspect.shopping.cart', route($routeNamePrefix.'shopping.cart.inspect', ['store' => $this->resource->id]), 'The route to inspect the shopping cart'),
            new ResourceLink('convert.shopping.cart', route($routeNamePrefix.'shopping.cart.convert', ['store' => $this->resource->id]), 'The route to convert the shopping cart'),

        ];

    }
}
