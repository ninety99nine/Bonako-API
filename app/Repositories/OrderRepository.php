<?php

namespace App\Repositories;

use App\Enums\UserVerfiedTransaction;
use App\Models\User;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Str;
use App\Models\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\PaymentMethodRepository;
use App\Exceptions\OrderFullyPaidException;
use App\Exceptions\CannotUpdateOrderException;
use App\Exceptions\CartRequiresProductsException;
use App\Exceptions\DPOCompanyTokenNotProvidedException;
use Illuminate\Validation\ValidationException;
use App\Services\ShoppingCart\ShoppingCartService;
use App\Exceptions\OrderHasNoAmountOutstandingException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Exceptions\OrderCollectionCodeInvalid;
use App\Exceptions\OrderCollectionVerificationCodeInvalid;
use App\Exceptions\OrderAlreadyCollectedException;
use App\Exceptions\OrderCannotBeUpdatedAfterBeingCancelledException;
use App\Exceptions\OrderCannotRequestPaymentException;
use App\Exceptions\OrderDoesNotHavePayableAmountException;
use App\Exceptions\OrderHasNoPendingPaymentException;
use App\Exceptions\OrderProhibitsTransactionsWhenCancelledException;
use App\Exceptions\OrderWithPaidTransactionsCannotBeUpdatedException;
use App\Exceptions\OrderProhibitsMultiplePendingPaymentByUserException;
use App\Exceptions\OrderWithPaidTransactionsCannotBeCancelledException;
use App\Exceptions\OrderWithPendingTransactionsCannotBeUpdatedException;
use App\Exceptions\OrderWithPendingTransactionsCannotBeCancelledException;
use App\Models\Address;
use App\Models\DeliveryAddress;
use App\Models\FriendGroup;
use App\Models\MobileVerification;
use App\Models\Pivots\UserOrderCollectionAssociation;
use App\Models\Pivots\UserStoreAssociation;
use App\Models\Product;
use App\Models\Store;
use App\Notifications\Orders\OrderCreated;
use App\Notifications\Orders\OrderSeen;
use App\Notifications\Orders\OrderStatusUpdated;
use App\Notifications\Orders\OrderUpdated;
use App\Services\AWS\AWSService;
use App\Services\CodeGenerator\CodeGeneratorService;
use App\Services\DirectPayOnline\DirectPayOnlineService;
use App\Services\OrangeMoney\OrangeMoneyService;
use App\Services\QrCode\QrCodeService;
use App\Services\Sms\SmsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OrderRepository extends BaseRepository
{
    use BaseTrait;

    protected $requiresConfirmationBeforeDelete = true;

    /**
     *  Return the ShoppingCartService instance
     *
     *  @return ShoppingCartService
     */
    public function shoppingCartService()
    {
        return resolve(ShoppingCartService::class);
    }

    /**
     *  Return the CartRepository instance
     *
     *  @return CartRepository
     */
    public function cartRepository()
    {
        return resolve(CartRepository::class);
    }

    /**
     *  Return the UserRepository instance
     *
     *  @return UserRepository
     */
    public function userRepository()
    {
        return resolve(UserRepository::class);
    }

    /**
     *  Return the StoreRepository instance
     *
     *  @return StoreRepository
     */
    public function storeRepository()
    {
        return resolve(StoreRepository::class);
    }

    /**
     *  Return the OccasionRepository instance
     *
     *  @return OccasionRepository
     */
    public function occasionRepository()
    {
        return resolve(OccasionRepository::class);
    }

    /**
     *  Return the DeliveryAddressRepository instance
     *
     *  @return DeliveryAddressRepository
     */
    public function deliveryAddressRepository()
    {
        return resolve(DeliveryAddressRepository::class);
    }

    /**
     *  Return the TransactionRepository instance
     *
     *  @return TransactionRepository
     */
    public function transactionRepository()
    {
        return resolve(TransactionRepository::class);
    }

    /**
     *  Return the PaymentMethodRepository instance
     *
     *  @return PaymentMethodRepository
     */
    public function paymentMethodRepository()
    {
        return resolve(PaymentMethodRepository::class);
    }


    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return OrderRepository
     */
    public function eagerLoadOrderRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        //  Check if we want to eager load the cart on this order
        if( request()->input('with_cart') ) {

            //  Additionally we can eager load the cart on this order
            array_push($relationships, 'cart');

        }

        //  Check if we want to eager load the customer on this order
        if( request()->input('with_customer') ) {

            //  Additionally we can eager load the customer on this order
            array_push($relationships, 'customer');

        }

        //  Check if we want to eager load the transactions on this order
        if( request()->input('with_transactions') ) {

            //  Additionally we can eager load the transactions on this order
            array_push($relationships, 'transactions');

        }

        //  Check if we want to eager load the total transactions on this order
        if( request()->input('with_count_transactions') ) {

            //  Additionally we can eager load the total transactions on this order
            array_push($countableRelationships, 'transactions');

        }

        //  Check if we want to eager load the store on this order
        if( request()->input('with_store') ) {

            //  Additionally we can eager load the store on this order as well as
            //  eager load the current auth user's user and store association on
            //  that store
            array_push($relationships, 'store.authUserStoreAssociation');

        }

        //  Check if we want to eager load the user and order association
        if( request()->input('with_user_order_collection_association') ) {

            /**
             *  Additionally we can eager load the current auth user's user and order
             *  collection association on that order. Note that this is not necessary
             *  for orders that are retrieved on the user and order relationship. In
             *  such cases the user and order collection association is loaded by
             *  default. This particularly helps in cases when we are not
             *  acquiring orders through the user relationship but we
             *  still need to access the user and order collection
             *  association if it exists.
             */
            $model = $model->with(['authUserOrderCollectionAssociation']);

        }

        //  Check if we want to eager load the delivery address on this order
        if( request()->input('with_delivery_address') ) {

            //  Additionally we can eager load the delivery address on this order
            array_push($relationships, 'deliveryAddress');

        }

        //  Check if we want to eager load the payment method on this order
        if( request()->input('with_payment_method') ) {

            //  Additionally we can eager load the payment method on this order
            array_push($relationships, 'paymentMethod');

        }

        //  Check if we want to eager load the occasion on this order
        if( request()->input('with_occasion') ) {

            //  Additionally we can eager load the occasion on this order
            array_push($relationships, 'occasion');

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof Order)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show the store order filters
     *
     *  @param Store $store
     *  @return array
     */
    public function showStoreOrderFilters(Store $store)
    {
        $filters = collect(Order::STORE_ORDER_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Waiting',
         *          'total' => 4000,
         *          'total_summarized' => '4k'
         *      ],
         *      [
         *          'name' => 'On Its Way',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($store) {

            $total = $this->queryStoreOrders($store, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the store orders
     *
     *  @param Store $store
     *  @return OrderRepository
     */
    public function showStoreOrders(Store $store)
    {
        //  The $filter is used to identify orders that match the specified order status
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the store orders based on the specified filter (if provided)
        $orders = $this->queryStoreOrders($store, $filter);

        //  Eager load the order relationships based on request inputs
        return $this->eagerLoadOrderRelationships($orders)->get();
    }

    /**
     *  Query the orders by the specified filter
     *
     *  @param User $user
     *  @param string $filter - The filter to query the orders
     */
    public function queryStoreOrders($store, $filter)
    {
        //  Normalize the filter
        $filter = $this->separateWordsThenLowercase($filter);

        /**
         *  The $startAtOrderId is used to identify orders that have
         *  been placed after the order specified by this order id
         */
        $startAtOrderId = request()->input('start_at_order_id');

        //  Set the $userOrderAssociation e.g customer, friend or team member
        $userOrderAssociation = $this->separateWordsThenLowercase(request()->input('user_order_association'));

        //  If the user must be associated as a customer
        if($userOrderAssociation == 'customer') {

            //  Query the store orders where the user is associated as a customer
            $orders = $store->orders()->whereHas('users', function ($query) {
                $query->where('user_order_collection_association.role', 'Customer')
                      ->where('user_order_collection_association.user_id', auth()->user()->id);
            });

        //  If the user must be associated as a friend
        }else if($userOrderAssociation == 'friend') {

            //  Query the store orders where the user is associated as a friend
            $orders = $store->orders()->whereHas('users', function ($query) {
                $query->where('user_order_collection_association.role', 'Friend')
                      ->where('user_order_collection_association.user_id', auth()->user()->id);
            });

            //  If the user must be associated as a customer or friend
            }else if($userOrderAssociation == 'customer or friend') {

                //  Query the friend group orders where the user is associated as a friend
                $orders = $store->orders()->whereHas('users', function ($query) {
                    $query->where(function ($subquery) {
                        $subquery->where('user_order_collection_association.role', 'Customer')
                                ->orWhere('user_order_collection_association.role', 'Friend');
                    })->where('user_order_collection_association.user_id', auth()->user()->id);
                });

        //  If the user must be associated as a team member
        }else if($userOrderAssociation == 'team member') {

            //  Query the store orders where the user is associated as a team member
            $orders = Order::whereHas('store', function ($query) use ($store) {
                $query->where('stores.id', $store->id)->whereHas('teamMembers', function ($query2) {
                    $query2->joinedTeam()->matchingUserId(auth()->user()->id);
                });
            });

        }

        //  If we have the filter
        if( !empty($filter) ) {

            //  Get the order statuses
            $statuses = collect(Order::STATUSES)->map(fn($status) => $this->separateWordsThenLowercase($status));

            //  If the filter matches one of the order statuses
            if(collect($statuses)->contains($filter)) {

                //  Query orders matching the given filter
                $orders = $orders->where('status', $filter);

            }

        }

        //  If we have the start at order id
        if( !empty($startAtOrderId) ) {

            //  Query for orders where the first order id matches the given order id
            $orders = $orders->where('orders.id', '>=', $startAtOrderId);

        }

        //  Query the latest orders first
        $orders = $orders->latest();

        //  Return the orders query
        return $orders;
    }

    /**
     *  Show the user order filters
     *
     *  @param User $user
     *  @return array
     */
    public function showUserOrderFilters(User $user)
    {
        //  Get the user order filters
        $filters = collect(Order::USER_ORDER_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Waiting',
         *          'total' => 4000,
         *          'total_summarized' => '4k'
         *      ],
         *      [
         *          'name' => 'On Its Way',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($user) {

            $total = $this->queryUserOrders($user, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the user orders
     *
     *  @param User $user
     *  @return OrderRepository
     */
    public function showUserOrders(User $user)
    {
        /**
         *  Always eager load the store so that methods such as getPayableAmountsAttribute()
         *  and getCanRequestPaymentAttribute() can be properly processed. Currently they
         *  depend on the request store e.g /stores/{store_id} or the eager loaded store
         *  in order to compute their logic.
         */
        request()->merge(['with_store' => '1']);

        //  The $filter is used to identify orders that match the specified order status
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the user orders based on the specified filter (if provided)
        $orders = $this->queryUserOrders($user, $filter);

        //  Eager load the order relationships based on request inputs
        return $this->eagerLoadOrderRelationships($orders)->get();
    }

    /**
     *  Query the orders by the specified filter
     *
     *  @param User $user
     *  @param string $filter - The filter to query the orders
     */
    public function queryUserOrders($user, $filter)
    {
        /**
         *  The $storeId is used to identify orders
         *  matching the specified store
         */
        $storeId = request()->input('store_id');

        //  Normalize the filter
        $filter = $this->separateWordsThenLowercase($filter);

        /**
         *  The $startAtOrderId is used to identify orders that have
         *  been placed after the order specified by this order id
         */
        $startAtOrderId = request()->input('start_at_order_id');

        //  Set the $userOrderAssociation e.g customer, friend or team member
        $userOrderAssociation = $this->separateWordsThenLowercase(request()->input('user_order_association'));

        //  If the user must be associated as a customer
        if($userOrderAssociation == 'customer') {

            //  Query the orders where the user is associated as a customer
            $orders = $user->ordersAsCustomer();

        //  If the user must be associated as a friend
        }else if($userOrderAssociation == 'friend') {

            //  Query the orders where the user is associated as a friend
            $orders = $user->ordersAsFriend();

        //  If the user must be associated as a customer or friend
        }else if($userOrderAssociation == 'customer or friend') {

            //  Query the orders where the user is associated as a customer or friend
            $orders = $user->ordersAsCustomerOrFriend();

        //  If the user must be associated as a team member
        }else if($userOrderAssociation == 'team member') {

            //  Query the orders where the user is associated as a team member
            $orders = Order::whereHas('store', function ($query) use ($user) {
                $query->whereHas('teamMembers', function ($query2) use ($user) {
                    $query2->joinedTeam()->matchingUserId($user->id);
                });
            });

        }

        //  If we have the filter
        if( !empty($filter) ) {

            //  Get the order statuses
            $statuses = collect(Order::STATUSES)->map(fn($status) => $this->separateWordsThenLowercase($status));

            //  If the filter matches one of the order statuses
            if(collect($statuses)->contains($filter)) {

                //  Query orders matching the given filter
                $orders = $orders->where('status', $filter);

            }

        }

        //  If we have the start at order id
        if( !empty($startAtOrderId) ) {

            //  Query for orders where the first order id matches the given order id
            $orders = $orders->where('orders.id', '>=', $startAtOrderId);

        }

        //  If we have the store id
        if( !empty($storeId) ) {

            //  Query for orders matching the specified store id
            $orders = $orders->where('store_id', $storeId);

        }

        //  Query the latest orders first
        $orders = $orders->latest();

        //  Return the orders query
        return $orders;
    }

    /**
     *  Show the friend group order filters
     *
     *  @param FriendGroup $friendGroup
     *  @return array
     */
    public function showFriendGroupOrderFilters(FriendGroup $friendGroup)
    {
        //  Get the friend group order filters
        $filters = collect(Order::FRIEND_GROUP_ORDER_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Waiting',
         *          'total' => 4000,
         *          'total_summarized' => '4k'
         *      ],
         *      [
         *          'name' => 'On Its Way',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($friendGroup) {

            $total = $this->queryFriendGroupOrders($friendGroup, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the store orders
     *
     *  @param FriendGroup $friendGroup
     *  @return OrderRepository
     */
    public function showFriendGroupOrders(FriendGroup $friendGroup)
    {
        /**
         *  Always eager load the store so that methods such as getPayableAmountsAttribute()
         *  and getCanRequestPaymentAttribute() can be properly processed. Currently they
         *  depend on the request store e.g /stores/{store_id} or the eager loaded store
         *  in order to compute their logic.
         */
        //request()->merge(['with_store' => '1']);

        //  The $filter is used to identify orders that match the specified order status
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the user orders based on the specified filter (if provided)
        $orders = $this->queryFriendGroupOrders($friendGroup, $filter);

        //  Eager load the order relationships based on request inputs
        return $this->eagerLoadOrderRelationships($orders)->get();
    }

    /**
     *  Query the orders by the specified filter
     *
     *  @param FriendGroup $friendGroup
     *  @param string $filter - The filter to query the orders
     */
    public function queryFriendGroupOrders($friendGroup, $filter)
    {
        /**
         *  The $storeId is used to identify orders
         *  matching the specified store
         */
        $storeId = request()->input('store_id');

        //  Normalize the filter
        $filter = $this->separateWordsThenLowercase($filter);

        /**
         *  The $startAtOrderId is used to identify orders that have
         *  been placed after the order specified by this order id
         */
        $startAtOrderId = request()->input('start_at_order_id');

        //  Set the $userOrderAssociation e.g customer, friend or team member
        $userOrderAssociation = $this->separateWordsThenLowercase(request()->input('user_order_association'));

        //  If the user must be associated as a customer
        if($userOrderAssociation == 'customer') {

            //  Query the friend group orders where the user is associated as a customer
            $orders = $friendGroup->orders()->whereHas('users', function ($query) {
                $query->where('user_order_collection_association.role', 'Customer')
                      ->where('user_order_collection_association.user_id', auth()->user()->id);
            });

        //  If the user must be associated as a friend
        }else if($userOrderAssociation == 'friend') {

            //  Query the friend group orders where the user is associated as a friend
            $orders = $friendGroup->orders()->whereHas('users', function ($query) {
                $query->where('user_order_collection_association.role', 'Friend')
                      ->where('user_order_collection_association.user_id', auth()->user()->id);
            });

        //  If the user must be associated as a customer or friend
        }else if($userOrderAssociation == 'customer or friend') {

            //  Query the friend group orders where the user is associated as a friend
            $orders = $friendGroup->orders()->whereHas('users', function ($query) {
                $query->where(function ($subquery) {
                    $subquery->where('user_order_collection_association.role', 'Customer')
                            ->orWhere('user_order_collection_association.role', 'Friend');
                })->where('user_order_collection_association.user_id', auth()->user()->id);
            });

        //  If the user must be associated as a team member
        }else if($userOrderAssociation == 'team member') {

            //  Query the store orders where the user is associated as a team member
            $orders = Order::whereHas('friendGroups', function ($query) use ($friendGroup) {
                $query->where('friend_groups.id', $friendGroup->id)->whereHas('store', function ($query) {
                    $query->whereHas('teamMembers', function ($query2) {
                        $query2->joinedTeam()->matchingUserId(auth()->user()->id);
                    });
                });
            });

        }

        //  If we have the filter
        if( !empty($filter) ) {

            //  Get the order statuses
            $statuses = collect(Order::STATUSES)->map(fn($status) => $this->separateWordsThenLowercase($status));

            //  If the filter matches one of the order statuses
            if(collect($statuses)->contains($filter)) {

                //  Query orders matching the given filter
                $orders = $orders->where('status', $filter);

            }

        }

        //  If we have the start at order id
        if( !empty($startAtOrderId) ) {

            //  Query for orders where the first order id matches the given order id
            $orders = $orders->where('orders.id', '>=', $startAtOrderId);

        }

        //  If we have the store id
        if( !empty($storeId) ) {

            //  Query for orders matching the specified store id
            $orders = $orders->where('store_id', $storeId);

        }

        //  Query the latest orders first
        $orders = $orders->latest();

        //  Return the orders query
        return $orders;
    }




    /**
     *  Show the order while eager loading any required relationships
     *
     *  @return OrderRepository
     */
    public function show()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  Eager load the order relationships based on request inputs
        return $this->eagerLoadOrderRelationships($order);
    }

    /**
     *  Inspect and return the current shopping cart instance.
     *  This cart instance is simply a mockup of the current
     *  user's cart before it is saved to the database
     *
     *  @return Cart
     */
    public function getShoppingCart()
    {
        return $this->shoppingCartService()->startInspection();
    }

    /**
     *  Show the order for options
     *
     *  @return array
     */
    public function showOrderForOptions()
    {
        return Order::ORDER_FOR_OPTIONS;
    }

    /**
     *  Count the order for users (customer & friends)
     *
     *  @return array
     */
    public function countOrderForUsers($orderFor, $friendUserIds, $friendGroupIds)
    {
        //  Get the user ids that this order is for
        $userIds = $this->showOrderForUserIds($orderFor, $friendUserIds, $friendGroupIds);

        //  Count the total number of people that this order is for
        $total = count($userIds);

        //  Return the total number of people that this order is for
        return [
            'total' => $total
        ];

    }

    /**
     *  Show the order for users (customer & friends)
     *
     *  @return UserRepository
     */
    public function showOrderForUsers($orderFor, $friendUserIds, $friendGroupIds)
    {
        //  Get the user ids that this order is for
        $userIds = $this->showOrderForUserIds($orderFor, $friendUserIds, $friendGroupIds);

        //  If we have one user id returned
        if(count($userIds) == 1) {

            // Query one user
            $users = User::where('id', $userIds[0]);

        //  If we have multiple user ids returned
        }else{

            // Query multiple users
            $users = User::whereIn('id', $userIds);

        }

        //  Return the user's that this order is for
        return $this->userRepository()->setModel($users)->get();

    }

    /**
     *  Show the shopping cart user ids (customer & friends)
     *
     *  @return array<int>
     */
    public function showOrderForUserIds($orderFor, $friendUserIds, $friendGroupIds)
    {
        /**
         *  @var User $user
         */
        $user = auth()->user();

        //  Check if we are ordering for me
        $orderForMe = $orderFor == 'me';

        if($orderForMe) {

            //  Return the current user id
            return [$user->id];

        }else{

            //  Check if we are ordering for me and friends
            $orderForMeAndFriends = $orderFor == 'me and friends';

            //  Get the matching friend user ids from the friends directory
            $matchingFriendUserIdsFromFriends = empty($friendUserIds) ? [] : DB::table('user_friend_association')->where('user_id', $user->id)->whereIn('friend_user_id', $friendUserIds)->pluck('friend_user_id');

            //  Get the matching friend user ids from the friend groups directory
            $matchingFriendUserIdsFromFriendGroups = empty($friendGroupIds) ? [] : DB::table('user_friend_group_association')->whereIn('friend_group_id', $friendGroupIds)->pluck('user_id');

            //  Get the matching friend user ids without duplicates
            $matchingFriendUserIds = collect($matchingFriendUserIdsFromFriends)->merge($matchingFriendUserIdsFromFriendGroups)->unique()->filter(function($userId) use($user) {

                //  Remove the current user from the list of the friend user ids
                return $userId != $user->id;

            })->toArray();

            //  Get the user ids that this order is for
            $userIds = $matchingFriendUserIds;

            //  If this order is for me and friends
            if($orderForMeAndFriends) {
                array_push($userIds, $user->id);
            }

            //  Return the user ids of the people that this order is for
            return collect($userIds)->values()->toArray();

        }

    }

    /**
     *  Create a new order
     *
     *  @return OrderRepository
     */
    public function createOrder($store)
    {
        return $this->createOrUpdateOrder($store);
    }

    /**
     *  Update an existing order
     *
     *  @return OrderRepository
     */
    public function updateOrder($store)
    {
        return $this->createOrUpdateOrder($store);
    }

    /**
     *  Check if this is an updatable order
     *
     *  @return bool
     */
    public function checkIfUpdatableShoppingCart()
    {
        /**
         *  If the Order model instance is set, then we can continue
         */
        if( $this->model instanceof Order && !empty($this->model->id)) {

            /**
             * @var Order $order
             */
            $order = $this->model;

            //  If this order is refelecting as paid (Customise the exception message)
            if( $order->isPaid() ) throw new CannotUpdateOrderException(
                'This order cannot be updated because it has been paid'
            );

            //  If this order is refelecting as pending payment (Customise the exception message)
            if( $order->isPendingPayment() ) throw new CannotUpdateOrderException(
                'This order cannot be updated because it has a pending payment'
            );

            //  If this order is refelecting as partially paid (Customise the exception message)
            if( $order->isPartiallyPaid() ) throw new CannotUpdateOrderException(
                'This order cannot be updated because it has been partially paid'
            );

            //  If this order is refelecting as collected (Customise the exception message)
            if( $order->isCompleted() ) throw new CannotUpdateOrderException(
                'This order cannot be updated because it has been collected by the customer'
            );

            return true;

        }

        return false;
    }

    public function createOrUpdateOrder(Store $store)
    {
        //  Check if this is an updatable order (If the order has been set)
        $isUpdatableOrder = $this->checkIfUpdatableShoppingCart();

        //  Get the inspected shopping cart
        $inspectedShoppingCart = $this->getShoppingCart();

        /**
         *  Forget everything that has been saved to memory since
         *  it will no longer be used after this cart conversion.
         *  This will help save memory.
         */
        $this->shoppingCartService()->forgetCache();

        //  Make sure that the shopping cart has product lines
        if($inspectedShoppingCart->total_products == 0) throw new CartRequiresProductsException;

        //  Get the customer user id
        $customerUserId = $inspectedShoppingCart->customer_user_id;

        //  Get the specified order for value
        $orderFor = strtolower(request()->input('order_for'));

        //  Get the specified friends can collect value
        $friendsCanCollect = request()->input('friends_can_collect') ?? false;

        //  Get the specified friend user ids
        $friendUserIds = request()->input('friend_user_ids');

        //  Get the specified friend group ids
        $friendGroupIds = request()->input('friend_group_ids');

        //  Get the specified occasion id
        $occasionId = request()->input('occasion_id');

        //  Get the specified special note
        $specialNote = request()->input('special_note');

        //  Get the user ids that this order is for
        $userIds = $this->showOrderForUserIds($orderFor, $friendUserIds, $friendGroupIds);

        //  Get the friend user ids that this order is for
        $friendUserIds = collect($userIds)->reject(fn($userId) => $userId == $customerUserId)->toArray();

        //  Count the total number of people that this order is for (include customer & friends)
        $orderForTotalUsers = count($userIds);

        //  Count the total number of people that this order is for (friends only)
        $orderForTotalFriends = count($friendUserIds);

        //  Get the collection type
        $collectionType = request()->filled('collection_type') ? request()->input('collection_type') : null;

        if(request()->filled('delivery_destination_name')) {

            $destinationName = request()->input('delivery_destination_name');

        }else if(request()->filled('pickup_destination_name')) {

            $destinationName = request()->input('pickup_destination_name');

        }else{

            $destinationName = null;

        }

        // Check if the address id is specified
        if(request()->filled('address_id')) {

            // Get the specified address id
            $addressId = request()->input('address_id');

            // Get the specified address if it exists
            $address = Address::find($addressId);

            //  If the specified address exists
            if($address) {

                // The given datetime
                $addressLastUpdatedAt = $address->updated_at;

                /**
                 *  Get the first delivery address that has a delivery date greater than or equal
                 *  to the address last update at datetime. This will help us determine if the
                 *  address has been updated since the last delivery address was created. This
                 *  way we can use the same delivery address if the address has not changed.
                 */
                $deliveryAddress = DeliveryAddress::where('address_id', $address->id)
                                    ->where('created_at', '>=', $addressLastUpdatedAt)
                                    ->orderBy('created_at', 'asc')
                                    ->first();

                //  Check if the delivery address exists
                if(!$deliveryAddress) {

                    // Get the address as an array and exclude the `created_at` and `updated_at` fields
                    $addressArray = Arr::except($address->toArray(), ['created_at', 'updated_at']);

                    // Merge the address array with an additional `address_id` key
                    $addressArray = array_merge($addressArray, ['address_id' => $address->id]);

                    //  Create a new delivery address for this order
                    $deliveryAddressRepository = $this->deliveryAddressRepository()->create($addressArray);
                    $deliveryAddress = $deliveryAddressRepository->model;

                }

            }

        }

        //  Find or add the customer to this store
        $userAsCustomer = $this->storeRepository()->setModel($store)->findOrAddCustomerByUserId($customerUserId);

        //  Set the order payload for creating a new order or updating an existing order
        $orderPayload = [
            'delivery_address_id' => isset($deliveryAddress) ? $deliveryAddress->id : null,
            'amount_outstanding' => $inspectedShoppingCart->grand_total,
            'grand_total' => $inspectedShoppingCart->grand_total,
            'customer_first_name' => $userAsCustomer->first_name,
            'customer_last_name' => $userAsCustomer->last_name,
            'order_for_total_friends' => $orderForTotalFriends,
            'order_for_total_users' => $orderForTotalUsers,
            'store_id' => $inspectedShoppingCart->store_id,
            'destination_name' => $destinationName,
            'customer_user_id' => $customerUserId,
            'collection_type' => $collectionType,
            'order_for' => ucwords($orderFor),
            'special_note' => $specialNote,
            'currency' => $store->currency,
            'occasion_id' => $occasionId,
        ];

        if($isUpdatableOrder) {

            //  Update an existing order for the inspected shopping cart
            $orderRepository = parent::update($orderPayload);

        }else{

            //  Create a new order for the inspected shopping cart
            $orderRepository = parent::create($orderPayload);

        }

        /**
         * @var Order $order
         */
        $order = $orderRepository->model;

        if($isUpdatableOrder) {

            /**
             *  Get the order cart
             *
             *  @var Cart $oldCart
             */
            $oldCart = $order->cart;

            $cartRepository = $this->cartRepository()->setModel($oldCart)->update(

                /**
                 *  Update the order shopping cart using the inspected shopping cart.
                 *  This should update the pricing totals correctly as well as
                 *  other important details such as item quantities e.t.c
                 */
                $inspectedShoppingCart->toArray()

            /**
             *  Attempt to update the associated product lines and coupon lines of this
             *  shopping cart if they exist. In either case return the cart repository
             *  instance.
             */
            )->updateProductAndCouponLines();

        }else{

            //  Create a new Cart Model instance associated to the created Order
            $cartRepository = $this->cartRepository()->create(

                /**
                 *  Convert the shopping cart to array to expose the
                 *  raw attribute values without any casting applied
                 */
                array_merge(
                    $inspectedShoppingCart->toArray(),
                    [
                        'order_id' => $order->id
                    ]
                )

            );

        }

        /**
         *  @var Cart $cart
         */
        $newCart = $cartRepository->model;

        if($isUpdatableOrder) {

            //  Remove previously associated users
            $this->removeUsersFromOrder();

            //  Remove previously associated friend groups
            $this->removeFriendGroupsFromOrder();

        }

        //  Add the customer to this order
        $orderRepository->addCustomerToOrder($customerUserId);

        //  Add friends to this order
        $orderRepository->addFriendsToOrder($friendUserIds, $friendsCanCollect);

        /**
         *  Get the specified friend group ids. Make sure that the specified friend group ids
         *  are in array format since the request supports JSON encoded data i.e string data
         */
        $friendGroupIds = is_string($friendGroupIds = (request()->input('friend_group_ids') ?? [])) ? json_decode($friendGroupIds) : $friendGroupIds;

        //  Add friend groups to this order
        $orderRepository->addFriendGroupsToOrder($friendGroupIds);

        //  Generate the order summary (Must be done after adding friends to this order)
        $order->generateSummary()->save();

        if($isUpdatableOrder) {

            //  Update the customer requested order totals (Reduce previous cart totals)
            $this->updateCustomerRequestedOrderTotals($userAsCustomer->user_store_association, $oldCart, false);

        }

        //  Update the customer requested order totals (Increase current cart totals)
        $this->updateCustomerRequestedOrderTotals($userAsCustomer->user_store_association, $newCart, true);

        //  Extend the visibility of the store products
        $this->extendVisibilityOfProducts($store, $newCart);

        /**
         *  Get the friends (if any)
         *
         *  @var Collection<User> $teamMembers
         */
        $friends = count($friendUserIds) ? User::whereIn('id', $friendUserIds)->get() : collect([]);

        /**
         *  Get the store team members (exclude the customer and friends as a team members)
         *
         *  @var Collection<User> $teamMembers
         */
        $teamMembers = $store->teamMembers()->whereNotIn('users.id', [$userAsCustomer->id, ...$friends->pluck('id')])->joinedTeam()->get();

        /**
         *  Get the notifiable users (customer, friends, and team members who have joined this store)
         *
         *  @var Collection<User> $notifiableUsers
         */
        $notifiableUsers = collect([$userAsCustomer])->concat(...[$friends, $teamMembers]);

        if($isUpdatableOrder) {

            //  Send order updated notification to the customer, friends, and team members who have joined this store
            //  change to Notification::send() instead of Notification::sendNow() so that this is queued
            Notification::sendNow($notifiableUsers, new OrderUpdated($order, auth()->user()));

        }else{

            //  Send order created notification to the customer, friends, and team members who have joined this store
            //  change to Notification::send() instead of Notification::sendNow() so that this is queued
            Notification::sendNow($notifiableUsers, new OrderCreated($order, auth()->user()));

            /// Send sms to customer placing this order
            SmsService::sendOrangeSms(
                $order->craftNewOrderSmsMessageForCustomer($store),
                $userAsCustomer->mobile_number->withExtension,
                $store, null, null
            );

            foreach($teamMembers as $teamMember) {

                /// Send sms to team member who has joined this store
                SmsService::sendOrangeSms(
                    $order->craftNewOrderSmsMessageForSeller($store, $userAsCustomer),
                    $teamMember->mobile_number->withExtension,
                    $store, null, null
                );

            }

            foreach($friends as $friend) {

                /// Send sms to friend tagged on this order
                SmsService::sendOrangeSms(
                    $order->craftNewOrderSmsMessageForFriend($store, $userAsCustomer, $friend, $friends),
                    $friend->mobile_number->withExtension,
                    $store, null, null
                );

            }

        }

        //  Load the auth user and order collection association
        $this->setModel($order->load('authUserOrderCollectionAssociation'));

        //  Eager load the order relationships based on request inputs
        return $this->eagerLoadOrderRelationships($order);
    }

    /**
     *  Update the customer order totals after requesting an new order
     *
     * @param UserStoreAssociation $userStoreAssociation The user and store pivot relationship
     * @param Order|Cart $model This can be an Order or Cart Model instance
     *
     * @return void
     */
    public function updateCustomerRequestedOrderTotals(UserStoreAssociation $userStoreAssociation, Model $model, $increment = true)
    {
        //  Modify the current totals (Increment requested order totals)
        $this->updateCustomerOrderTotals($userStoreAssociation, $model, 'requested', $increment);
    }

    /**
     *  Update the customer order totals after delivering an existing order
     *
     * @param UserStoreAssociation $userStoreAssociation The user and store pivot relationship
     * @param Order|Cart $model This can be an Order or Cart Model instance
     *
     * @return void
     */
    public function updateCustomerReceivedOrderTotals(UserStoreAssociation $userStoreAssociation, Model $model, $increment = true)
    {
        //  Modify the current totals (Increment received order totals)
        $this->updateCustomerOrderTotals($userStoreAssociation, $model, 'received', $increment);
    }

    /**
     *  Update the customer order totals after cancelling an existing order
     *
     * @param UserStoreAssociation $userStoreAssociation The user and store pivot relationship
     * @param Order|Cart $model This can be an Order or Cart Model instance
     *
     * @return void
     */
    public function updateCustomerCancelledOrderTotals(UserStoreAssociation $userStoreAssociation, Model $model, $increment = true)
    {
        //  Modify the current totals (Increment cancelled order totals)
        $this->updateCustomerOrderTotals($userStoreAssociation, $model, 'cancelled', $increment);
    }

    /**
     *  Update the customer order totals from the given order or cart
     *
     * @param UserStoreAssociation $userStoreAssociation The user and store pivot relationship
     * @param Order|Cart $model This can be an Order or Cart Model instance
     * @param string $type e.g The type of total e.g requested, received or cancelled
     * @param bool $increment e.g True or false whether to increment or decrement totals
     *
     * @return Cart
     */
    public function updateCustomerOrderTotals(UserStoreAssociation $userStoreAssociation, Model $model, $type, $increment = true)
    {
        //  Incase the model is an Order model instance
        if( $model instanceof Order ) {

            //  Incase the cart is not loaded, please load it
            $model->loadMissing('cart');

            //  Extract the order cart as the model to target
            $cart = $model->cart;

        //  Incase the model is a Cart model instance
        }else if( $model instanceof Cart ) {

            //  Set the cart
            $cart = $model;

        }else{

            //  The cart was not found
            throw new ModelNotFoundException;

        }

        /**
         *  Get the Model fields matching the given type
         *  that must be modified e.g
         *
         *   ----------------------------
         *
         *   $type = 'requested';
         *
         *   $fields = [
         *      "total_orders_requested",
         *      "grand_total_requested",
         *      "sub_total_requested",
         *      ... e.t.c
         *   ]
         *
         *   ----------------------------
         *
         *   $type = 'received';
         *
         *   $fields = [
         *      "total_orders_received",
         *      "grand_total_received",
         *      "sub_total_received",
         *      ... e.t.c
         *   ]
         *
         *   ----------------------------
         *
         *   $type = 'cancelled';
         *
         *   $fields = [
         *      "total_orders_cancelled",
         *      "grand_total_cancelled",
         *      "sub_total_cancelled",
         *      ... e.t.c
         *   ]
         */
        $fields = collect( $userStoreAssociation->getAttributes() )->keys()->filter(function($field) use ($type) {

            return Str::endsWith($field, $type);

        })->all();

        foreach($fields as $field) {

            /**
             *  The following returns everything in the field before
             *  the given type e.g
             *
             *  From "total_orders_requested" to "total_orders" or
             *  From "grand_total_requested" to "grand_total" or
             *  From "sub_total_requested" to "sub_total" or
             *  ... e.t.c
             */
            $matchingField = Str::beforeLast($field, '_'.$type);

            /**
             *  The following will modify the model field as follows
             *
             *  $userStoreAssociation['total_orders_requested'] += 1;
             *  $userStoreAssociation['grand_total_requested'] += $model['grand_total'];
             *  $userStoreAssociation['sub_total_requested'] += $model['sub_total'];
             *  ... e.t.c
             */

            if( $increment ) {

                //  Check if this is a Money object
                if(is_object($userStoreAssociation[$field]) && isset($cart[$matchingField])) {

                    $userStoreAssociation[$field]->amount += $cart[$matchingField]->amount;

                }else if($matchingField == 'total_orders') {

                    $userStoreAssociation[$field] += 1;

                }

            }else{

                //  Check if this is a Money object
                if(is_object($userStoreAssociation[$field]) && isset($cart[$matchingField])) {

                    $userStoreAssociation[$field]->amount -= $cart[$matchingField]->amount;

                }else if($matchingField == 'total_orders') {

                    $userStoreAssociation[$field] -= 1;

                }

            }

            /**
             *  The following appends "avg_" to the begining of
             *  the field name to initialise $avgField value
             *
             *  from "total_orders_requested" to "avg_total_orders_requested"
             *  from "grand_total_requested" to "avg_grand_total_requested"
             *  from "sub_total_requested" to "avg_sub_total_requested"
             */
            $avgField = 'avg_'.$field;

            /**
             *  If this model has an average (avg) matching field i.e
             *  if the "avg_total_orders_requested" does not exist as
             *  a field on the Model, then trying to access its
             *  value will return null. We need to allow fields
             *  that are not returning null to calculate their
             *  average values
             */
            if( $userStoreAssociation[$avgField] !== null ) {

                /**
                 *  The following will modify the model averages as follows
                 *
                 *  $userStoreAssociation['avg_grand_total_requested'] = $userStoreAssociation['grand_total_requested'] / $model['total_orders_requested'];
                 *  $userStoreAssociation['avg_sub_total_requested'] = $userStoreAssociation['sub_total_requested'] / $model['total_orders_requested'];
                 *  ... e.t.c
                 */

                //  Check if this is a Money object
                if(is_object($userStoreAssociation[$avgField])) {

                    $userStoreAssociation[$avgField]->amount = $userStoreAssociation[$field]->amount / $userStoreAssociation['total_orders_'.$type];

                }else {

                    $userStoreAssociation[$avgField] = $userStoreAssociation[$field] / $userStoreAssociation['total_orders_'.$type];

                }

            }

        }

        //  Save the changes on the customer order totals
        $userStoreAssociation->save();
    }

    /**
     *  Extend the product visibility of the cart products
     *
     *  return $this
     */
    public function extendVisibilityOfProducts(Store $store, Cart $cart)
    {
        //  Find the products matching the cart product lines
        $products = $store->products()->whereIn('id', collect($cart->productLines)->pluck('product_id'));

        //  Extend the product visibility
        $products->update([
            'visible' => 1,
            'visibility_expires_at' => (new Product)->getExtendVisibilityDateTime()
        ]);

        return $this;
    }















    /**
     *  Add customer to this order
     *
     *  @param array<int> $userId The customer user id
     */
    public function addCustomerToOrder($userId)
    {
        /**
         * @var Order $order
         */
        $order = $this->model;

        $order->friends()->attach($userId, [
            'role' => 'Customer',
            'can_collect' => true
        ]);
    }

    /**
     *  Add friends to this order
     *
     *  @param array<int> $friendUserIds List of friend user ids
     *  @param bool $friendsCanCollect Indication of whether friends can collect this order
     */
    public function addFriendsToOrder($friendUserIds, $friendsCanCollect = true)
    {
        if(count($friendUserIds)) {

            /**
             * @var Order $order
             */
            $order = $this->model;

            $order->friends()->attach($friendUserIds, [
                'role' => 'Friend',
                'can_collect' => $friendsCanCollect
            ]);

        }
    }

    /**
     *  Remove users from this order
     *
     *  @return $this
     */
    public function removeUsersFromOrder()
    {
        /**
         * @var Order $order
         */
        $order = $this->model;

        $order->users()->detach();

        return $this;
    }

    /**
     *  Remove friends from this order
     *
     *  @return $this
     */
    public function removeFriendsFromOrder()
    {
        /**
         * @var Order $order
         */
        $order = $this->model;

        $order->friends()->detach();

        return $this;
    }

    /**
     *  Add friend groups to this order
     *
     *  @param array<int> $friendGroupIds
     */
    public function addFriendGroupsToOrder($friendGroupIds)
    {
        if(count($friendGroupIds)) {

            /**
             * @var Order $order
             */
            $order = $this->model;

            $order->friendGroups()->attach($friendGroupIds);

        }
    }

    /**
     *  Remove friend groups from this order
     *
     *  @return $this
     */
    public function removeFriendGroupsFromOrder()
    {
        /**
         * @var Order $order
         */
        $order = $this->model;

        $order->friendGroups()->detach();

        return $this;
    }

    /**
     *  Show the order cart
     *
     *  @return OrderRepository
     */
    public function showOrderCart()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        if($order->cart) {

            return $this->cartRepository()->setModel($order->cart);

        }else{

            throw new ModelNotFoundException();

        }
    }

    /**
     *  Show the order customer
     *
     *  @return UserRepository
     */
    public function showOrderCustomer()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        if($order->customer) {

            return $this->userRepository()->setModel($order->customer);

        }else{

            throw new ModelNotFoundException();

        }
    }

    /**
     *  Show the order occasion
     *
     *  @return OrderRepository
     */
    public function showOccasion()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        if($order->occasion) {

            return $this->occasionRepository()->setModel($order->occasion)->showOccasion();

        }else{

            throw new ModelNotFoundException();

        }
    }

    /**
     *  Show the order delivery address
     *
     *  @return OrderRepository
     */
    public function showDeliveryAddress()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        if($order->deliveryAddress) {

            return $this->deliveryAddressRepository()->setModel($order->deliveryAddress);

        }else{

            throw new ModelNotFoundException();

        }
    }

    /**
     *  Cancel the order
     *
     *  @return OrderRepository
     */
    public function cancelOrder(Request $request)
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  This order cannot be cancelled because it has received payments
        if($order->amount_paid->amount > 0) throw new OrderWithPaidTransactionsCannotBeCancelledException;

        //  This order cannot be cancelled because it has pending payments
        if($order->amount_pending->amount > 0) throw new OrderWithPendingTransactionsCannotBeCancelledException;

        //  Cancel the repository model instance
        return parent::update(['status' => 'Cancelled', 'cancellation_reason' => $request->input('cancellation_reason')]);
    }

    /**
     *  Uncancel the order
     *
     *  @return OrderRepository
     */
    public function uncancelOrder(Request $request)
    {
        //  Cancel the repository model instance
        return parent::update(['status' => 'Waiting', 'cancellation_reason' => null]);
    }

    /**
     *  Show the payment methods available to Request payment for this order
     *
     *  @return PaymentMethodRepository
     */
    public function showRequestPaymentPaymentMethods(Store $store)
    {
        $methods = [];

        if($store->perfect_pay_enabled || $store->dpo_payment_enabled) {
            array_push($methods, 'DPO Card');
        }

        if($store->perfect_pay_enabled || $store->orange_money_payment_enabled) {
            array_push($methods, 'Orange Money');
        }

        $paymentMethods = PaymentMethod::whereIn('method', $methods)->orderBy('position', 'asc');

        return $this->paymentMethodRepository()->setModel($paymentMethods)->get();
    }

    /**
     *  Request payment for this order
     *
     *  @return OrderRepository
     */
    public function requestPayment(Request $request)
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        if($order->grand_total->amount == 0) {
            throw new OrderDoesNotHavePayableAmountException();
        }

        //  Check if we can request payment for this order
        if($this->model->can_request_payment) {

            //  Set the selected payment method id
            $paymentMethodId = $request->input('payment_method_id');

            //  Get the selected payment method
            $paymentMethod = PaymentMethod::find($paymentMethodId);

            if($paymentMethod) {

                //  Create a new pending payment transaction
                $transactionRepository = $this->createTransaction($request, UserVerfiedTransaction::NO);

                /**
                 *  @var Transaction $transaction
                 */
                $transaction = $transactionRepository->model;

                //  Check if this is a DPO Card payment method or an Orange Money payment method
                if($paymentMethod->isDpoCard() || $paymentMethod->isOrangeMoney()) {

                    //  Check if this is a card payment method
                    if($paymentMethod->isDpoCard()) {

                        //  Create a new order payment link and attach it to this transaction
                        $transaction = DirectPayOnlineService::createOrderPaymentLink($transaction);

                    //  Check if this is a mobile wallet method
                    }else if($paymentMethod->isOrangeMoney()) {

                        //  Create a new order payment link and attach it to this transaction
                        $transaction = OrangeMoneyService::createOrderPaymentLink($transaction);

                    }

                    /**
                     *  @var Store $store
                     */
                    $store = $order->store;

                    /**
                     *  @var User $payingUser
                     */
                    $payingUser = $transaction->payingUser;

                    /**
                     *  @var User $requestingUser
                     */
                    $requestingUser = $transaction->requestingUser;

                    /// Send order payment request sms to the paying user
                    SmsService::sendOrangeSms(
                        $order->craftOrderPaymentRequestSmsMessage($store, $transaction, $requestingUser, $paymentMethod),
                        $payingUser->mobile_number->withExtension,
                        $store, null, null
                    );

                    //  Return this transaction repository
                    return $transactionRepository->setModel($transaction);

                }else{

                    //  Request a payment shortcode for this pending transaction
                    $transactionRepository = $transactionRepository->generatePaymentShortcode();

                    //  Return this order repository
                    return $transactionRepository;

                }

            }

        }else{

            throw new OrderCannotRequestPaymentException();

        }
    }

    /**
     *  Show the payment methods available to mark an unverified payment for this order
     *
     *  @return PaymentMethodRepository
     */
    public function showMarkAsUnverifiedPaymentPaymentMethods(Store $store)
    {
        $paymentMethods = PaymentMethod::availableOnStores()->orderBy('position', 'asc');

        return $this->paymentMethodRepository()->setModel($paymentMethods)->get();
    }

    /**
     *  Mark the order as paid on the requested payment.
     *  This method can be used to mark an order as
     *  paid if the order was paid via USSD. Such
     *  transactions are verified by the system.
     *
     *  @return OrderRepository
     */
    public function markAsVerifiedPayment()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        if($order->grand_total->amount == 0) {
            throw new OrderDoesNotHavePayableAmountException();
        }

        /**
         *  Get the current authenticated user's latest transaction
         *  that is pending payment for this order
         *
         *  @var Transaction $authTransactionPendingPayment
         */
        $authTransactionPendingPayment = $order->authTransactionPendingPayment;

        //  If the transaction does not exist
        if( !$authTransactionPendingPayment ) throw new OrderHasNoPendingPaymentException;

        //  Mark this transaction as paid
        $transactionRepository = $this->transactionRepository()->setModel($authTransactionPendingPayment)->update([

            'payment_status' => 'Paid',

            /**
             *  Update description:
             *
             *  Before: Partial payment for order #00001 requested by John Doe
             *  After:  Partial payment for order #00001 requested by John Doe and paid by Mark Winters
             */
            'description' => $authTransactionPendingPayment->description . ' and paid by ' . auth()->user()->name
        ]);

        //  Update the order amount balance
        $this->updateOrderAmountBalance();

        //  Return the transaction repository
        return $transactionRepository;
    }

    /**
     *  Mark the order as paid. This method can be
     *  used to manually mark an order as paid if
     *  the order was paid using other payment
     *  methods such as Cash or Credit Card
     *  among other payment methods.
     *
     *  @return OrderRepository
     */
    public function markAsUnverifiedPayment(Request $request)
    {
        //  Create a new paid transaction
        $transactionRepository = $this->createTransaction($request, UserVerfiedTransaction::YES);

        /**
         *  @var Order $order
         */
        $order = $this->model;

        /**
         *  @var Store $store
         */
        $store = $order->store;

        /**
         *  @var Transaction $transaction
         */
        $transaction = $transactionRepository->model;

        /**
         *  Get the users associated with this order as a customer or friend
         *
         *  @var Collection<User> $users
         */
        $users = $order->users()->get();

        /**
         *  Get the store team members (exclude the users associated with this order as a customer or friend)
         *
         *  @var Collection<User> $teamMembers
         */
        $teamMembers = $store->teamMembers()->whereNotIn('users.id', $users->pluck('id'))->joinedTeam()->get();

        /**
         *  Get the user that verified this transaction
         *
         *  @var User $verifiedByUser
         */
        $verifiedByUser = auth()->user();

        foreach($users->concat($teamMembers) as $user) {

            /// Send order mark as verified payment sms to user
            SmsService::sendOrangeSms(
                $order->craftOrderMarkAsUnVerifiedPaymentSmsMessage($store, $transaction, $verifiedByUser),
                $user->mobile_number->withExtension,
                $store, null, null
            );

        }

        //  Return the transaction repository
        return $transactionRepository;
    }

    /**
     *  Create a transaction for this order.
     *
     *  @return TransactionRepository
     */
    public function createTransaction(Request $request, UserVerfiedTransaction $userVerifiedTransaction)
    {
        //  Avoid transactions on a cancelled order
        $this->avoidInitiatingTransactionsOnCancelledOrder(
            $userVerifiedTransaction == UserVerfiedTransaction::YES
                ? 'This order cannot be marked as paid because it has been cancelled'
                : 'This order cannot request payment because it has been cancelled'
        );

        /**
         *  Load the cart on this order
         *
         *  @var Order $order
         */
        $order = $this->setModel($this->model->loadMissing(['cart']))->model;

        /**
         *  @var Cart $cart
         */
        $cart = $order->cart;

        //  If the order has already been paid in full
        if( $order->isPaid() ) throw new OrderFullyPaidException;

        //  If the order cannot be paid due to no amount left to pay (possibly a free order)
        if( $order->amount_outstanding_percentage === 0 ) throw new OrderHasNoAmountOutstandingException;

        //  Check if the amount is provided
        if( $request->filled('amount') ) {

            //  Get the amount
            $amount = $request->input('amount');

            //  Check if the amount exceeds the remaining outstanding amount after deducting the pending amount
            if( $amount > ($outstandingAmountRemaining = $order->amount_outstanding->amount - $order->amount_pending->amount) ) {

                //  Convert to money format
                $amountSpecified = $order->convertToMoneyFormat($amount, $cart->currency);

                //  Convert to money format
                $outstandingAmountRemaining = $order->convertToMoneyFormat($outstandingAmountRemaining, $order->currency);

                //  Throw an Exception - Amount exceeded
                throw ValidationException::withMessages(['amount' => 'The amount specified '.$amountSpecified->amountWithCurrency.' is more than the remaining payable amount '.$outstandingAmountRemaining->amountWithCurrency.' for this order']);

            }

            //  Determine if this is a full payment (non-partial payment)
            $fullPayment = $amount == $order->amount_outstanding->amount;

            //  Calculate the percentage paid of the total cart grand total
            $percentage = $fullPayment ? $order->amount_outstanding_percentage : ($amount / $cart->grand_total->amount * 100);

        //  Check if the percentage is provided
        }elseif( $request->filled('percentage') ) {

            //  Get the percentage
            $percentage = $request->input('percentage');

            //  Check if the amount exceeds the remaining outstanding percentage after deducting the pending percentage
            if( $percentage > ($outstandingPercentageRemaining = $order->amount_outstanding_percentage - $order->amount_pending_percentage) ) {

                //  Throw an Exception - Percentage exceeded
                throw ValidationException::withMessages(['percentage' => 'The percentage specified '.$percentage.'% is more than the remaining payable percentage '.$outstandingPercentageRemaining.'% for this order']);

            }

            //  Determine if this is a full payment (non-partial payment)
            $fullPayment = $percentage == $order->amount_outstanding_percentage;

            //  Calculate the amount paid of the total cart grand total
            $amount = $fullPayment ? $order->amount_outstanding : ($percentage / 100 * $cart->grand_total->amount);

        }

        //  Set the transaction description
        $description = ($fullPayment ? 'Full' : 'Partial') . ' payment for order #'.$order->number . ($userVerifiedTransaction == UserVerfiedTransaction::YES ? ' confirmed by ' : ' requested by ') . auth()->user()->name;

        //  Determine the payer of this amount (If the mobile number is provided then this payer is not the customer)
        if( $mobileNumber = $request->input('mobile_number') ) {

            //  Get the user matching the given mobile number (This user is the payer)
            $payerUserId = User::searchMobileNumber($mobileNumber)->first()->id;

        }else{

            //  The payer is the customer
            $payerUserId = $order->customer_user_id;

        }

        //  Check if this transaction is a system verified transaction
        if($userVerifiedTransaction == UserVerfiedTransaction::NO) {

            //  Avoid requesting multiple pending payment for the same payer
            $this->avoidRequestingMultiplePendingPaymentsPerUser($payerUserId);

        }

        /**
         *  If the transaction is a system verified transaction, then this is a requested transaction
         *  that will be later confirmed after the payment is successful e.g Paying online using a
         *  Credit/Debit card. Requested transactions are verified by the system after the payer
         *  makes payment using a generated payment link or shortcode.
         */
        $requestedByUserId = ($userVerifiedTransaction == UserVerfiedTransaction::NO) ? auth()->user()->id : null;

        /**
         *  If the transaction is a user verified transaction, then this is verified transaction that
         *  was not verified by the system. Verified transactions are verified by the store management
         *  after the payer makes payment using other payment methods such as cash, cheque or any
         *  other payment that cannot be verified by the system.
         */
        $verifiedByUserId = ($userVerifiedTransaction == UserVerfiedTransaction::YES) ? auth()->user()->id : null;

        //  If verified by the user
        if($userVerifiedTransaction == UserVerfiedTransaction::YES) {

            //  Then this transaction is paid
            $paymentStatus = 'Paid';

        //  If verified by the system
        }else{

            //  Then this transaction is pending payment to be later verified as paid
            $paymentStatus = 'Pending Payment';

        }

        //  Set the payment method (if provided)
        $paymentMethodId = $request->input('payment_method_id');

        //  Create a new transaction
        $transactionRepository = $this->transactionRepository()->create([
            'payment_status' => $paymentStatus,
            'description' => $description,

            'amount' => $amount,
            'percentage' => $percentage,
            'currency' => $cart->currency,
            'payment_method_id' => isset($paymentMethodId) ? $paymentMethodId : null,

            'payer_user_id' => $payerUserId,

            /**
             *  If the verified_by_user_id is set, then the transaction is verified by
             *  the store management. If the requested_by_user_id is set then the
             *  transaction is verified by the system. They cannot have a valu at
             *  the same time. One must have a value while the other is NULL.
             *
             *  If both the requested_by_user_id and the verified_by_user_id are
             *  set, then it causes confusion as to who verified this transaction
             */
            'requested_by_user_id' => $requestedByUserId,
            'verified_by_user_id' => $verifiedByUserId,

            'owner_id' => $order->id,
            'owner_type' => $order->getResourceName()
        ]);

        //  Update the order amount balance
        $this->updateOrderAmountBalance();

        //  Return this transaction repository
        return $transactionRepository;

    }

    /**
     *  Avoid transactions on a cancelled order
     */
    public function avoidInitiatingTransactionsOnCancelledOrder($exceptionMessage = null)
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  If the order is cancelled
        if( $order->isCancelled() ) {

            /**
             *  Note that the Exception class does not accept NULL values,
             *  therefore we must implement custom conditional checks to
             *  determine whether to include a custom exception message
             *  or fallback to the default message.
             */
            if($exceptionMessage) {
                throw new OrderProhibitsTransactionsWhenCancelledException($exceptionMessage);
            }else{
                throw new OrderProhibitsTransactionsWhenCancelledException();
            }

        }

    }

    /**
     *  Avoid requesting multiple pending payment for the same payer
     */
    public function avoidRequestingMultiplePendingPaymentsPerUser($payerUserId, $exceptionMessage = null)
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  Avoid requesting payment multiple times for the same payer
        if( $order->transactions()->notCancelled()->where(['payment_status' => 'Pending Payment', 'payer_user_id' => $payerUserId])->exists() ) {

            /**
             *  Note that the Exception class does not accept NULL values,
             *  therefore we must implement custom conditional checks to
             *  determine whether to include a custom exception message
             *  or fallback to the default message.
             */
            if($exceptionMessage) {
                throw new OrderProhibitsMultiplePendingPaymentByUserException($exceptionMessage);
            }else{
                throw new OrderProhibitsMultiplePendingPaymentByUserException();
            }

        }

    }

    public function updateOrderAmountBalance()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  Get the order transactions
        $transactions = $order->transactions()->get();

        //  Get the cart grand total
        $grandTotal = $order->cart->grand_total->amount;

        //  Calculate the order balance paid
        $amountPaid = collect($transactions)->filter(fn(Transaction $transaction) => $transaction->isPaid() && $transaction->isCancelled() == false)->map(fn($transaction) => $transaction->amount->amount)->sum();
        $percentagePaid = (int) ($grandTotal > 0 ? ($amountPaid / $grandTotal * 100) : 0);

        //  Calculate the order balance pending payment
        $amountPending = collect($transactions)->filter(fn(Transaction $transaction) => $transaction->isPendingPayment() && $transaction->isCancelled() == false)->map(fn($transaction) => $transaction->amount->amount)->sum();
        $percentagePending = (int) ($grandTotal > 0 ? ($amountPending / $grandTotal * 100) : 0);

        //  Calculate the order balance outstanding payment
        $amountOutstanding = $grandTotal - $amountPaid;
        $percentageOutstanding = (int) ($grandTotal > 0 ? ($amountOutstanding / $grandTotal * 100) : 0);

        //  If we have pending payments
        if( $percentagePending != 0 ) {

            $paymentStatus = 'Pending Payment';

        //  If we have no payment
        }elseif( $percentagePaid == 0 ) {

            $paymentStatus = 'Unpaid';

        //  If we have full payment
        }elseif( $percentagePaid == 100 ) {

            $paymentStatus = 'Paid';

        //  If we have partial payment
        }else {

            $paymentStatus = 'Partially Paid';

        }

        //  Set and update the order attributes
        $order->fill([
            'grand_total' => $grandTotal,
            'payment_status' => $paymentStatus,

            'amount_paid' => $amountPaid,
            'amount_paid_percentage' => $percentagePaid,

            'amount_pending' => $amountPending,
            'amount_pending_percentage' => $percentagePending,

            'amount_outstanding' => $amountOutstanding,
            'amount_outstanding_percentage' => $percentageOutstanding,
        ])->save();

    }

    /**
     *  Generate the 6 digit collection code required
     *  to mark this order as completed
     *
     *  @return array
     */
    public function generateCollectionCode()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  If the order collection has already been verified
        if( $order->collection_verified ) throw new OrderAlreadyCollectedException;

        //  Get the user if assigned to this order
        $userOrderAssociation = DB::table('user_order_collection_association')
                                    ->where('user_id', auth()->user()->id)
                                    ->where('order_id', $order->id)
                                    ->where('can_collect', '1')
                                    ->first();

        if($userOrderAssociation) {

            //  Get the existing generated order collection codes (These must be excluded from the generated set)
            $excludeExistingCodes = DB::table('user_order_collection_association')->where('order_id', $order->id)->pluck('collection_code');

            //  Generate a random 6 digit order collection code
            $collectionCode = CodeGeneratorService::generateRandomSixDigitNumber($excludeExistingCodes);

            //  Generate the update order status to completed url
            $updateStatusToCompletedUrl = route('order.status.update', ['store' => $order->store_id, 'order' => $order->id, 'status' => 'completed', 'collection_code' => $collectionCode]);

            /**
             *  Generate a QR code to capture two details:
             *
             *  1) The url to update the status of this order to completed with the collection code embedded
             *  2) The collection code
             */
            $qrCodeImageUrl = QrCodeService::generate(
                $updateStatusToCompletedUrl.'|'.$collectionCode
            );

            //  Create a collection record
            $collectionRecord = [
                'collection_code' => $collectionCode,
                'collection_qr_code' => $qrCodeImageUrl,
                'collection_code_expires_at' => now()->addSeconds(120)
            ];

            /**
             *  Save the collection record on the user and order association.
             *  Set to expire exactly after 120 seconds (2 minutes)
             */
            DB::table('user_order_collection_association')->where('order_id', $order->id)->where('user_id', auth()->user()->id)->update($collectionRecord);

            //  Collection record was created
            return array_merge(
                $collectionRecord,
                ['collection_qr_code' => $qrCodeImageUrl],
                [
                    'message' => 'The collection code was created',
                    'created' => true
                ]
        );

        }else{

            throw new AccessDeniedHttpException;

        }

    }

    /**
     *  Revoke the 6 digit collection code required
     *  to mark this order as completed
     *
     *  @return array
     */
    public function revokeCollectionCode()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  If the order collection has already been verified
        if( $order->collection_verified ) throw new OrderAlreadyCollectedException;

        //  Get the user if assigned to this order
        $userOrderAssociation = DB::table('user_order_collection_association')
                                    ->where('user_id', auth()->user()->id)
                                    ->where('order_id', $order->id)
                                    ->where('can_collect', '1')
                                    ->first();

        if($userOrderAssociation) {

            //  Revoke the collection code and collection qr code
            DB::table('user_order_collection_association')->where('order_id', $order->id)->update([
                'collection_code' => null,
                'collection_qr_code' => null
            ]);

            if(!empty($userOrderAssociation->collection_qr_code)) {

                //  Delete the qr code file
                AWSService::delete($userOrderAssociation->collection_qr_code);

            }

            //  Collection code was revoked
            return [
                'message' => 'The collection code was revoked'
            ];

        }else{

            throw new AccessDeniedHttpException;

        }

    }

    /**
     *  Update the order status
     *
     *  @return OrderRepository
     */
    public function updateStatus()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        /**
         *  Get the user that updated this order status
         *
         *  @var User $updatedByUser
         */
        $updatedByUser = auth()->user();

        // Get the order status
        $status = $this->separateWordsThenLowercase(request()->input('status'));

        if( $status == 'completed' ) {

            //  Mark this order as completed
            $this->updateStatusToCompleted();

        }else{

            //  Update the status of this order e.g On Its Way, Ready For Pickup
            parent::update(['status' => ucwords($status)]);

        }

        //  Send order status changed notification to customer and friends (if any)
        //  change to Notification::send() instead of Notification::sendNow() so that this is queued
        Notification::sendNow(
            /**
             *  Send notifications to the customer
             *
             *  Since the customer is acquired via a belongsTo relationship,
             *  order->customer returns a collection with only one user.
             */
            collect($order->customer)->merge(
                //  As well as the friends (if any) who were tagged on this order
                $order->order_for_total_friends == 0 ? [] : $order->friends
            ),
            new OrderStatusUpdated($order, $updatedByUser)
        );

        /**
         *  @var Store $store
         */
        $store = $order->store;

        /**
         *  Get the users associated with this order as a customer or friend
         *
         *  @var Collection<User> $users
         */
        $users = $order->users()->get();

        /**
         *  Get the store team members (exclude the users associated with this order as a customer or friend)
         *
         *  @var Collection<User> $teamMembers
         */
        $teamMembers = $store->teamMembers()->whereNotIn('users.id', $users->pluck('id'))->joinedTeam()->get();

        foreach($users->concat($teamMembers) as $user) {

            /// Send order collected sms to user
            SmsService::sendOrangeSms(
                $order->craftOrderStatusUpdatedMessage($store, $updatedByUser),
                $user->mobile_number->withExtension,
                $store, null, null
            );

        }

        //  Return the order with any relationships required
        return $this->show($this->model);
    }

    /**
     *  Mark the order as completed using the collection
     *  code or the customer verification code
     *
     *  @return OrderRepository
     */
    public function updateStatusToCompleted()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  If the order collection has already been verified
        if( $order->collection_verified ) throw new OrderAlreadyCollectedException;

        //  Get the order collection code was provided
        $collectionCode = request()->input('collection_code');

        //  Assume that the collection code is invalid by default
        $isValid = false;

        //  Get the user and order association
        $userOrderAssociation = UserOrderCollectionAssociation::where('order_id', $order->id)
            ->where('collection_code', $collectionCode)
            ->with('user')
            ->first();

        //  If the user and order association exists
        if($userOrderAssociation) {

            //  If the collection code expires at a datetime that is in the future then this code has not yet expired
            if( Carbon::parse($userOrderAssociation->collection_code_expires_at)->isFuture() ) {

                //  Indicate that this collection code is valid
                $isValid = true;

            }else{

                //  Throw an error that this collection code has expired
                throw new AccessDeniedHttpException('This collection code has expired');

            }

        }else{

            /**
             *  Get the matching mobile verification mobile numbers.
             *
             *  Since its possible to generate the same mobile verification code for different mobile numbers,
             *  we can simply capture all the mobile numbers sharing the same code.
             */
            $mobileVerificationMobileNumbers = MobileVerification::where('code', $collectionCode)->latest()->pluck('mobile_number');

            if(count($mobileVerificationMobileNumbers)) {

                //  Get the user and order association that consists of a user matching any one of these mobile numbers
                $userOrderAssociations = UserOrderCollectionAssociation::where('order_id', $order->id)
                    ->whereHas('user', function ($query) use ($mobileVerificationMobileNumbers) {
                        $query->whereIn('mobile_number', $mobileVerificationMobileNumbers);
                    })->with('user')->get();

                foreach($userOrderAssociations as $userOrderAssociation) {

                    //  Check if this user can collect this order
                    if($userOrderAssociation->can_collect) {

                        //  Revoke this user's mobile verification code
                        AuthRepository::revokeUserMobileVerificationCode($userOrderAssociation->user);

                        //  Indicate that this collection code is valid
                        $isValid = true;

                        break;

                    }else{

                        //  Throw an error that this person does not have permission to collect this order
                        throw new AccessDeniedHttpException('This person does not have permission to collect this order');

                    }

                }

            }

            if($isValid == false) {

                //  Throw an error that this collection code is incorrect
                throw new AccessDeniedHttpException('This collection code is incorrect');

            }

        }

        if($isValid) {

            /**
             *  Get the user that verified this order collection
             *
             *  @var User $verifiedByUser
             */
            $verifiedByUser = auth()->user();

            /**
             *  Get the user that collected this order
             *
             *  @var User $updatedByUser
             */
            $collectedByUser = $userOrderAssociation->user;

            /**
             *  Mark this order as completed
             *  Set the order as collected
             *
             *  @return OrderRepository
             */
            parent::update([
                'status' => 'Completed',
                'collection_verified' => true,
                'collection_verified_at' => now(),

                //  The user that verified this order collection
                'collection_verified_by_user_id' => $verifiedByUser->id,
                'collection_verified_by_user_last_name' => $verifiedByUser->last_name,
                'collection_verified_by_user_first_name' => $verifiedByUser->first_name,

                //  The user that collected this order
                'collection_by_user_id' => $collectedByUser->id,
                'collection_by_user_last_name' => $collectedByUser->last_name,
                'collection_by_user_first_name' => $collectedByUser->first_name,
            ]);

            //  Revoke the collection code and collection qr code
            DB::table('user_order_collection_association')->where('order_id', $order->id)->update([
                'collection_code' => null,
                'collection_qr_code' => null,
                'collection_code_expires_at' => null
            ]);

            if(!empty($userOrderAssociation->collection_qr_code)) {

                //  Delete the qr code file
                AWSService::delete($userOrderAssociation->collection_qr_code);

            }

            /**
             *  @var Store $store
             */
            $store = $order->store;

            /**
             *  Get the users associated with this order as a customer or friend
             *
             *  @var Collection<User> $users
             */
            $users = $order->users()->get();

            /**
             *  Get the store team members (exclude the users associated with this order as a customer or friend)
             *
             *  @var Collection<User> $teamMembers
             */
            $teamMembers = $store->teamMembers()->whereNotIn('users.id', $users->pluck('id'))->joinedTeam()->get();

            foreach($users->concat($teamMembers) as $user) {

                /// Send order collected sms to user
                SmsService::sendOrangeSms(
                    $order->craftOrderCollectedSmsMessage($store, $collectedByUser, $verifiedByUser),
                    $user->mobile_number->withExtension,
                    $store, null, null
                );

            }

        }else{

            //  Throw an error that this collection code is invalid
            throw new AccessDeniedHttpException('This collection code is invalid');

        }
    }







    /**
     *  Show the order transaction filters
     *
     *  @return array
     */
    public function showOrderTransactionFilters()
    {
        return $this->transactionRepository()->showOrderTransactionFilters($this->model);
    }

    /**
     *  Show the order transactions
     *
     *  @return TransactionRepository
     */
    public function showOrderTransactions()
    {
        return $this->transactionRepository()->showOrderTransactions($this->model)->get();
    }

    /**
     *  Show the order transactions
     *
     *  @return array
     */
    public function showOrderTransactionsCount()
    {
        $total = $this->transactionRepository()->showOrderTransactions($this->model)->count();

        return [
            'total' => $total
        ];
    }







    /**
     *  Show the users that viewed this order
     *
     *  @return OrderRepository
     */
    public function showViewers()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        //  Initialize the UserRepository instance and return the users that viewed this order
        return $this->userRepository()->setModel($order->usersThatViewed()->orderByPivot('last_seen_at', 'DESC'))->get();
    }

    /**
     *  Mark the viewership of this order
     *
     *  @return OrderRepository
     */
    public function markAsSeen()
    {
        /**
         *  @var Order $order
         */
        $order = $this->model;

        // Get the user id
        $userId = auth()->user()->id;

        // Get the viewer if exists
        $viewer = $order->usersThatViewed()->where('user_id', $userId)->first();

        if( $viewer ) {

            //  Update existing viewership
            $order->usersThatViewed()->updateExistingPivot($userId, [
                'views' => $viewer->user_order_view_association->views + 1,
                'last_seen_at' => now()
            ]);

        }else{

            //  Create new viewership
            $order->usersThatViewed()->attach($userId, [
                'views' => 1,
                'last_seen_at' => now()
            ]);

        }

        //  Calculate the total views on this order by the team
        $totalViews = $order->total_views_by_team + 1;

        //  If the order's first view datetime was already captured
        if($order->first_viewed_by_team_at) {

            //  Update the order's last view datetime
            parent::update([
                'last_viewed_by_team_at' => now(),
                'total_views_by_team' => $totalViews
            ]);

        //  If the order's first view datetime was not captured
        }else{

            //  Update the order's first and last view datetime
            parent::update([
                'first_viewed_by_team_at' => now(),
                'last_viewed_by_team_at' => now(),
                'total_views_by_team' => $totalViews
            ]);

        }

        //  If this user has never viewed this order before
        if( !$viewer ) {

            //  Send order seen notification to customer and friends (if any)
            //  change to Notification::send() instead of Notification::sendNow() so that this is queued
            Notification::sendNow(
                /**
                 *  Send notifications to the customer
                 *
                 *  Since the customer is acquired via a belongsTo relationship,
                 *  order->customer returns a collection with only one user.
                 */
                collect($order->customer)->merge(
                    //  As well as the friends (if any) who were tagged on this order
                    $order->order_for_total_friends == 0 ? [] : $order->friends
                ),
                new OrderSeen($order, auth()->user())
            );

        }

    }

}
