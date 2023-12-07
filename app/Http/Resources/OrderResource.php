<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Repositories\UserRepository;
use App\Repositories\CartRepository;
use App\Http\Resources\BaseResource;
use App\Repositories\StoreRepository;
use App\Repositories\TransactionRepository;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\DeliveryAddressRepository;
use App\Repositories\OccasionRepository;
use App\Repositories\PaymentMethodRepository;

class OrderResource extends BaseResource
{
    protected $customExcludeFields = ['store_id'];

    protected $resourceRelationships = [
        'cart' => CartRepository::class,
        'store' => StoreRepository::class,
        'customer' => UserRepository::class,
        'occasion' => OccasionRepository::class,
        'transactions' => TransactionRepository::class,
        'paymentMethod' => PaymentMethodRepository::class,
        'deliveryAddress' => DeliveryAddressRepository::class
    ];

    public function __construct($resource)
    {
        parent::__construct($resource);

        /**
         *  When eager loading the authUserOrderCollectionAssociation relationship which is the
         *  user_order_collection_association of the current authenticated user with this order.
         *  This ensures that we can access the user_order_collection_association pivot
         *  information.
         *
         *  This relationship needs to be renamed from "authUserOrderCollectionAssociation" to
         *  "user_order_collection_association" so that we can transform the relationship
         *  based on the "user_store_association" name.
         */
        if($this->resource->relationLoaded('authUserOrderCollectionAssociation')) {

            //  Rename the "authUserOrderCollectionAssociation" to "user_order_collection_association"
            $this->resource->setAttribute('user_order_collection_association', $this->resource->authUserOrderCollectionAssociation);

            //  Exclude the authUserOrderCollectionAssociation relationship from being included with fields
            $this->customExcludeFields = array_merge(
                ($this->customExcludeFields ?? []), ['user_order_collection_association']
            );

        }
    }

    /**
     *  Check if this order is being requested by a team member
     *  who has the permissions to manage orders
     *
     *  Note that an order can be retrieved from a store, in
     *  which case the "user_store_association" will exist,
     *  but also an order can be retrieved from a user, in
     *  which case the "user_store_association" will not
     *  exist.
     *
     *  @return bool
     */
    private function canManageOrders() {
        if(!empty(request()->store)) {
            return request()->store->user_store_association->can_manage_orders;
        }
        return false;
    }

    /**
     *  Check if this order is being requested by the customer
     *
     *  @return bool
     */
    private function isCustomer() {
        if(!empty($this->resource->user_order_collection_association)) {
            return $this->resource->user_order_collection_association->is_associated_as_customer;
        }
        return false;
    }

    /**
     *  Check if this order is being requested by the friend
     *
     *  @return bool
     */
    private function isFriend() {
        if(!empty($this->resource->user_order_collection_association)) {
            return $this->resource->user_order_collection_association->is_associated_as_friend;
        }
        return false;
    }

    /**
     *  Check if this order is being requested by a user that is allowed
     *  to see more sensitive information regarding this order.
     *
     *  @return bool
     */
    private function viewingPrivately() {

        $isFriend = $this->isFriend();
        $isCustomer = $this->isCustomer();
        $isSuperAdmin = $this->isSuperAdmin;
        $canManageOrders = $this->canManageOrders();

        return $isFriend || $isCustomer || $isSuperAdmin || $canManageOrders;
    }

    /**
     *  Check if this order is being requested by a user that is not allowed
     *  to see more sensitive information regarding this order.
     *
     *  @return bool
     */
    private function viewingPublicly() {
        return $this->viewingPrivately() == false;
    }

    public function toArray($request)
    {
        /**
         *  Viewing as Public User
         *
         *  If we are veiwing as the general public then limit the information we share.
         *  Usually we just want the basic order details, nothing that would expose
         *  sensitive order information such as customer information, amounts paid,
         *  amounts pending or amounts outstanding. Only the store staff and
         *  customer can see those details.
         */
        if( $this->viewingPublicly() ) {

            //  Make this order anonymous
            $this->resource = $this->resource->makeAnonymous();

            //  Exclude the following relationships
            $this->customExcludeRelationships = ['transactions'];

        }else{

            //  If the authenticated user is a customer on this order
            if( $this->isCustomer() ) {

                $this->resource->customer_display_name = 'Me';

            }

        }

        /**
         *  If the order is accessed via a user relationship then we can gain access to the user-order
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

        if( !empty($this->resource->user_association_as_order_viewer) ) {

            //  Include the user and order viewership payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['user_association_as_order_viewer']
            );

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        $routeNamePrefix = 'order.';
        $orderId = $this->resource->id;
        $storeId = $this->resource->store_id;

        $this->resourceLinks = [
            new ResourceLink('self', route($routeNamePrefix.'show', ['store' => $storeId, 'order' => $orderId]), 'The store order'),
            new ResourceLink('generate.collection.code', route($routeNamePrefix.'generate.collection.code', ['store' => $storeId, 'order' => $orderId]), 'The route to generate the collection code'),
            new ResourceLink('revoke.collection.code', route($routeNamePrefix.'revoke.collection.code', ['store' => $storeId, 'order' => $orderId]), 'The route to revoke the collection code'),
            new ResourceLink('show.request.payment.payment.methods', route($routeNamePrefix.'request.payment.payment.methods.show', ['store' => $storeId, 'order' => $orderId]), 'The route to show the payment methods available to request payment this order'),
            new ResourceLink('request.payment', route($routeNamePrefix.'request.payment', ['store' => $storeId, 'order' => $orderId]), 'The route to request payment this order'),
            new ResourceLink('show.mark.as.unverified.payment.payment.methods', route($routeNamePrefix.'mark.as.unverified.payment.payment.methods.show', ['store' => $storeId, 'order' => $orderId]), 'The route to show the payment methods available to mark this order as an unverfied payment'),
            new ResourceLink('mark.as.unverified.payment', route($routeNamePrefix.'mark.as.unverified.payment', ['store' => $storeId, 'order' => $orderId]), 'The route to mark this order as an unverfied payment'),
            new ResourceLink('mark.as.verified.payment', route($routeNamePrefix.'mark.as.verified.payment', ['store' => $storeId, 'order' => $orderId]), 'The route to mark this order as an verfied payment'),
            new ResourceLink('update.status', route($routeNamePrefix.'status.update', ['store' => $storeId, 'order' => $orderId]), 'The route to update the order status'),
            new ResourceLink('show.viewers', route($routeNamePrefix.'viewers.show', ['store' => $storeId, 'order' => $orderId]), 'The route to update the order status'),

            //  Paying User
            new ResourceLink('show.order.paying.users', route($routeNamePrefix.'paying.users.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order transactions'),

            new ResourceLink('show.transaction.filters', route($routeNamePrefix.'transaction.filters.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order transaction filters'),
            new ResourceLink('show.transactions', route($routeNamePrefix.'transactions.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order transactions'),
            new ResourceLink('show.cart', route($routeNamePrefix.'cart.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order cart'),
            new ResourceLink('show.occasion', route($routeNamePrefix.'occasion.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order occasion'),
            new ResourceLink('show.customer', route($routeNamePrefix.'customer.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order customer'),
            new ResourceLink('show.users', route($routeNamePrefix.'users.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order users'),
            new ResourceLink('show.delivery.address', route($routeNamePrefix.'delivery.address.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order delivery address'),
            new ResourceLink('show.transactions.count', route($routeNamePrefix.'transactions.count.show', ['store' => $storeId, 'order' => $orderId]), 'Show the order transactions count'),
        ];
    }
}
