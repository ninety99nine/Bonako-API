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
        $order = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.order', route('show.order', ['orderId' => $order->id])),
            new ResourceLink('update.order', route('update.order', ['orderId' => $order->id])),
            new ResourceLink('delete.order', route('delete.order', ['orderId' => $order->id])),
            new ResourceLink('cancel.order', route('cancel.order', ['orderId' => $order->id])),
            new ResourceLink('uncancel.order', route('uncancel.order', ['orderId' => $order->id])),
            new ResourceLink('show.order.cancellation.reasons', route('show.order.cancellation.reasons', ['orderId' => $order->id])),
            new ResourceLink('generate.order.collection.code', route('generate.order.collection.code', ['orderId' => $order->id])),
            new ResourceLink('revoke.order.collection.code', route('revoke.order.collection.code', ['orderId' => $order->id])),
            new ResourceLink('update.order.status', route('update.order.status', ['orderId' => $order->id])),
            new ResourceLink('request.order.payment', route('request.order.payment', ['orderId' => $order->id])),
            new ResourceLink('show.payment.methods.for.requesting.order.payment', route('show.payment.methods.for.requesting.order.payment', ['orderId' => $order->id])),
            new ResourceLink('mark.order.as.paid', route('mark.order.as.paid', ['orderId' => $order->id])),
            new ResourceLink('show.payment.methods.for.marking.as.paid', route('show.payment.methods.for.marking.as.paid', ['orderId' => $order->id])),
            new ResourceLink('show.order.cart', route('show.order.cart', ['orderId' => $order->id])),
            new ResourceLink('show.order.store', route('show.order.store', ['orderId' => $order->id])),
            new ResourceLink('show.order.customer', route('show.order.customer', ['orderId' => $order->id])),
            new ResourceLink('show.order.occasion', route('show.order.occasion', ['orderId' => $order->id])),
            new ResourceLink('show.order.placed.by.user', route('show.order.placed.by.user', ['orderId' => $order->id])),
            new ResourceLink('show.order.created.by.user', route('show.order.created.by.user', ['orderId' => $order->id])),
            new ResourceLink('show.order.collection.verified.by.user', route('show.order.collection.verified.by.user', ['orderId' => $order->id])),
            new ResourceLink('show.order.delivery.address', route('show.order.delivery.address', ['orderId' => $order->id])),
            new ResourceLink('show.order.friend.group', route('show.order.friend.group', ['orderId' => $order->id])),
            new ResourceLink('add.order.friend.group', route('add.order.friend.group', ['orderId' => $order->id])),
            new ResourceLink('remove.order.friend.group', route('remove.order.friend.group', ['orderId' => $order->id])),
            new ResourceLink('show.order.viewers', route('show.order.viewers', ['orderId' => $order->id])),
            new ResourceLink('show.order.transactions', route('show.order.transactions', ['orderId' => $order->id])),
        ];
    }
}
