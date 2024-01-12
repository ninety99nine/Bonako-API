<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Store;
use App\Models\Order;
use App\Models\Review;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Address;
use App\Models\Shortcode;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Enums\CanSaveChanges;
use App\Enums\InvitationResponse;
use App\Events\Testing;
use App\Traits\Base\BaseTrait;
use App\Models\DeliveryAddress;
use App\Services\AWS\AWSService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pivots\UserStoreAssociation;
use App\Repositories\PaymentMethodRepository;
use Illuminate\Validation\ValidationException;
use App\Exceptions\CartRequiresProductsException;
use App\Exceptions\StoreRoleDoesNotExistException;
use App\Services\ShoppingCart\ShoppingCartService;
use App\Exceptions\StoreHasTooManyProductsException;
use App\Exceptions\InvitationAlreadyAcceptedException;
use App\Exceptions\InvitationAlreadyDeclinedException;
use App\Exceptions\CannotModifyOwnPermissionsException;
use App\Exceptions\CannotRemoveYourselfAsStoreCreatorException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Notifications\Users\InvitationToFollowStoreAccepted;
use App\Exceptions\CannotRemoveYourselfAsTeamMemberException;
use App\Exceptions\InvalidInvitationException;
use App\Exceptions\StoreHasTooManyCouponsException;
use App\Models\FriendGroup;
use App\Models\PaymentMethod;
use App\Models\Pivots\StorePaymentMethodAssociation;
use App\Models\SubscriptionPlan;
use App\Notifications\Orders\OrderCreated;
use App\Notifications\Users\FollowingStore;
use App\Notifications\FriendGroups\FriendGroupStoreAdded;
use App\Notifications\FriendGroups\FriendGroupStoreRemoved;
use App\Notifications\Users\InvitationToFollowStoreCreated;
use App\Notifications\Users\InvitationToFollowStoreDeclined;
use App\Notifications\Users\InvitationToJoinStoreTeamAccepted;
use App\Notifications\Users\InvitationToJoinStoreTeamCreated;
use App\Notifications\Users\InvitationToJoinStoreTeamDeclined;
use App\Notifications\Users\RemoveStoreTeamMember;
use App\Notifications\Users\UnfollowedStore;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Collection;

class StoreRepository extends BaseRepository
{
    use BaseTrait;

    protected $requiresConfirmationBeforeDelete = true;

    protected $createIgnoreFields = ['verified'];
    protected $updateIgnoreFields = ['verified', 'user_id'];

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
     *  Return the ProductRepository instance
     *
     *  @return ProductRepository
     */
    public function productRepository()
    {
        return resolve(ProductRepository::class);
    }

    /**
     *  Return the ReviewRepository instance
     *
     *  @return ReviewRepository
     */
    public function reviewRepository()
    {
        return resolve(ReviewRepository::class);
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
     *  Return the OrderRepository instance
     *
     *  @return OrderRepository
     */
    public function orderRepository()
    {
        return resolve(OrderRepository::class);
    }

    /**
     *  Return the CouponRepository instance
     *
     *  @return CouponRepository
     */
    public function couponRepository()
    {
        return resolve(CouponRepository::class);
    }

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
     *  Return the ShortcodeRepository instance
     *
     *  @return ShortcodeRepository
     */
    public function shortcodeRepository()
    {
        return resolve(ShortcodeRepository::class);
    }

    /**
     *  Return the SubscriptionRepository instance
     *
     *  @return SubscriptionRepository
     */
    public function subscriptionRepository()
    {
        return resolve(SubscriptionRepository::class);
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
     *  Return the SubscriptionPlanRepository instance
     *
     *  @return SubscriptionPlanRepository
     */
    public function subscriptionPlanRepository()
    {
        return resolve(SubscriptionPlanRepository::class);
    }

    /**
     *  Show the store filters
     *
     *  @return array
     */
    public function showStoreFilters()
    {
        //  Get the store filters
        $filters = collect(Store::STORE_FILTERS)->map(fn($filter) => $this->separateWordsThenLowercase($filter));

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Popular',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Not Team Member',
         *          'total' => 1000k,
         *          'total_summarized' => '6k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) {

            //  Query the stores by the filter
            $total = $this->queryStoresByFilter($filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the stores
     *
     *  @return StoreRepository
     */
    public function showStores()
    {
        //  Get the request filter
        $filter = request()->input('filter');

        //  Get the request mobile number
        $mobileNumber = request()->input('mobile_number');

        //  Query the stores by the filter (If provided)
        $stores = $this->queryStoresByFilter($filter);

        //  If the mobile number is provided
        if($mobileNumber) {

            //  Query stores that match the provided mobile number
            $stores = $stores->where('mobile_number', $mobileNumber);

        }

        return $this->eagerLoadRelationships($stores)->get();
    }

    /**
     *  Show the brand stores
     *
     *  @return StoreRepository
     */
    public function showBrandStores()
    {
        //  Get the request filter
        $filter = request()->input('filter');

        //  Query the brand stores by the filter (If provided)
        $stores = $this->queryStoresByFilter($filter)->brandStores();

        /**
         *  Eager load the user and store association on each store.
         *  This is important because these stores are not retrieved
         *  from a user and store relationship since we want to also
         *  return the brand stores that a user might not have a
         *  relationship with. In this case we find the stores
         *  that are brand stores and then we eager load the
         *  user and store association on each store if at
         *  all the relationship exists
         */
        request()->merge(['with_user_store_association' => true]);

        return $this->eagerLoadRelationships($stores)->get();
    }

    /**
     *  Show the influencer stores
     *
     *  @return StoreRepository
     */
    public function showInfluencerStores()
    {
        //  Get the request filter
        $filter = request()->input('filter');

        /**
         *  Eager load the user and store association on each store.
         *  This is important because these stores are not retrieved
         *  from a user and store relationship since we want to also
         *  return the influencer stores that a user might not have
         *  a relationship with. In this case we find the stores
         *  that are influencer stores and then we eager load
         *  the user and store association on each store if
         *  at all the relationship exists
         */
        $stores = $this->queryStoresByFilter($filter)->influencerStores();

        //  Eager load the user and store association on each store
        request()->merge(['with_user_store_association' => true]);

        return $this->eagerLoadRelationships($stores)->get();
    }

    /**
     *  Query the stores by the specified filter
     *
     *  @param string $filter - The filter to query the stores
     */
    public function queryStoresByFilter($filter)
    {
        //  Normalize the filter
        $filter = $this->separateWordsThenLowercase($filter);

        if($filter == 'popular today') {

            //  Query stores by order ranking since the start of day
            $stores = $this->queryStoresByOrderRanking(Carbon::now()->startOfDay());

        }else if($filter == 'popular this week') {

            //  Query stores by order ranking since the start of week
            $stores = $this->queryStoresByOrderRanking(Carbon::now()->startOfWeek());

        }else if($filter == 'popular this month') {

            //  Query stores by order ranking since the start of month
            $stores = $this->queryStoresByOrderRanking(Carbon::now()->startOfMonth());

        }else if($filter == 'popular this year') {

            //  Query stores by order ranking since the start of year
            $stores = $this->queryStoresByOrderRanking(Carbon::now()->startOfYear());

        }else{

            $stores = new Store;

        }

        //  Return the stores query
        return $stores;
    }

    /**
     *  Query stores by popularity - A store is popular if it has
     *  received more than 100 orders since the start of day. We
     *  then rank those stores from the store with the highest
     *  orders to the store with the lowest orders.
     *
     *  @param Carbon $startingDate
     */
    private function queryStoresByOrderRanking($startingDate) {

        $minimumOrdersToQualify = 100;

        return Store::whereHas('orders', function ($query) use ($startingDate) {
            $query->where('created_at', '>', $startingDate);
        })->withCount(['orders' => function ($query) use ($startingDate) {
            $query->where('created_at', '>', $startingDate);
        }])->having('orders_count', '>=', $minimumOrdersToQualify)
           ->orderBy('orders_count', 'desc');
    }










    /**
     *  Show the user store filters
     *
     *  @param User $user
     *  @return array
     */
    public function showUserStoreFilters(User $user)
    {
        //  Get the user store filters
        $filters = collect(Store::USER_STORE_FILTERS)->map(fn($filter) => $this->separateWordsThenLowercase($filter));

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Team Member',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Not Team Member',
         *          'total' => 1000k,
         *          'total_summarized' => '6k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($user) {

            //  Query the stores by the filter
            $total = $this->queryUserStoresByFilter($user, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the user's first created store
     *
     *  @param User $user
     *  @return array
     */
    public function showUserFirstCreatedStore(User $user)
    {
        //  Query the first store ever created by this user
        $firstStoreCreated = $user->storesAsTeamMember()->joinedTeamAsCreator()->oldest();

        //  Eager load the store relationships based on request inputs
        $firstStoreCreated = $this->eagerLoadRelationships($firstStoreCreated);

        //  Get the first store ever created by this user
        $firstStoreCreated = $firstStoreCreated->model->first();

        //  Return the store
        return [
            'exists' => !is_null($firstStoreCreated),
            'store' => $firstStoreCreated ? $this->setModel($firstStoreCreated)->transform() : null
        ];
    }

    /**
     *  Show the user stores
     *
     *  @param User $user
     *  @return StoreRepository
     */
    public function showUserStores(User $user)
    {
        //  Get the request filter
        $filter = request()->input('filter');

        //  Query the stores by the filter (If provided)
        $stores = $this->queryUserStoresByFilter($user, $filter);

        return $this->eagerLoadRelationships($stores)->get();
    }

    /**
     *  Query the stores by the specified filter
     *
     *  @param User $user
     *  @param string $filter - The filter to query the stores
     */
    public function queryUserStoresByFilter($user, $filter)
    {
        //  Normalize the filter
        $filter = $this->separateWordsThenLowercase($filter);

        //  Check if the user wants stores listed as team member
        if( in_array($filter, ['team member', 'team member left', 'team member joined', 'team member joined as creator', 'team member joined as non creator', 'team member invited', 'team member declined']) ) {

            //  Query stores where this user is associated as a team member
            $stores = $user->storesAsTeamMember()->orderByPivot('last_seen_at', 'DESC');

            if($filter == 'team member left') {

                //  Left store team
                $stores = $stores->leftTeam();

            }elseif(in_array($filter, ['team member joined', 'team member joined as creator', 'team member joined as non creator'])) {

                if($filter == 'team member joined') {

                    $stores = $stores->joinedTeam();

                }elseif($filter == 'team member joined as creator') {

                    $stores = $stores->joinedTeamAsCreator();

                }elseif($filter == 'team member joined as non creator') {

                    $stores = $stores->joinedTeamAsNonCreator();

                }

            }elseif($filter == 'team member invited') {

                //  Invited to join store team
                $stores = $stores->invitedToJoinTeam();

            }elseif($filter == 'team member declined') {

                //  Declined invitation to join store team
                $stores = $stores->declinedToJoinTeam();

            }

        //  Check if the user wants stores listed as follower
        }else if( in_array($filter, ['follower', 'unfollower', 'invited to follow']) ) {

            //  Query stores where this user is associated as a follower
            $stores = $user->storesAsFollower()->orderByPivot('last_seen_at', 'DESC');

            if($filter == 'follower') {

                //  Following store
                $stores = $stores->following();

            }elseif($filter == 'unfollower') {

                //  Unfollowed store
                $stores = $stores->unfollowed();

            }elseif($filter == 'invited to follow') {

                //  Invited to follow store
                $stores = $stores->invitedToFollow();

            }

        //  Check if the user wants stores assigned
        }else if( in_array($filter, ['assigned']) ) {

            //  Query stores that have been assigned to this user
            $stores = $user->storesAsAssigned()->orderByPivot('assigned_position', 'ASC');

        //  Check if the user wants stores listed as customer
        }elseif( $filter === 'customer' ) {

            //  Query stores where this user is associated as a customer
            $stores = $user->storesAsCustomer()->orderByPivot('last_seen_at', 'DESC');


        //  Check if the user wants stores listed as recent visitor
        }elseif( $filter === 'recent visitor' ) {

            //  Query stores where this user is associated as a recent visitor
            $stores = $user->storesAsRecentVisitor()->orderByPivot('last_seen_at', 'DESC');

        //  Check if the user wants stores listed as friend group member
        }elseif( $filter === 'friend group member' ) {

            //  Query stores where this user has been assigned as a friend group member
            $stores = $user->stores()->whereHas('friendGroups', function (Builder $query) use ($user) {

                //  If we want stores of a specific friend group
                if( $id = request()->input('friend_group_id') ) {

                    //  Query the store ids that match the given friend group id
                    $query->where('friend_group_id', $id);

                }

                $query->whereHas('users', function (Builder $query) use ($user) {

                    //  Query friend groups where this user has been added as a member
                    $query->where('users.id', $user->id);

                });

            })->orderByPivot('last_seen_at', 'DESC');

        }else{

            //  Query stores where this user has been assigned as anything
            $stores = $user->stores()->orderByPivot('last_seen_at', 'DESC');

        }

        //  Return the stores query
        return $stores;
    }

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return StoreRepository
     */
    public function eagerLoadRelationships($model) {

        //  Check if we want to eager load the visit shortcode on each store
        if( request()->input('with_visit_shortcode') ) {

            //  Additionally we can eager load the active visit shortcode
            $model = $model->with(['visitShortcode']);

        }

        //  Check if we want to eager load the auth active subscription on each store
        if( request()->input('with_auth_active_subscription') ) {

            //  Additionally we can eager load the auth active subscription
            $model = $model->with(['authActiveSubscription']);

        }

        //  Check if we want to eager load the total active subscriptions on each store
        if( request()->input('with_count_active_subscriptions') ) {

            //  Additionally we can eager load the total active subscriptions
            $model = $model->withCount(['activeSubscriptions']);

        }

        //  Check if we want to eager load the orders on each store
        if( request()->input('with_orders') ) {

            //  Additionally we can eager load the orders on these stores as well
            $model = $model->with(['orders']);

        }

        //  Check if we want to eager load the products on each store
        if( request()->input('with_visible_products') ) {

            //  Additionally we can eager load the visible products on these stores as well
            $model->with(['products' => function ($query) {
                $query->isNotVariation()->visible()->orderBy('position', 'asc');
            }]);

        }

        //  Check if we want to eager load the total products on each store
        if( request()->input('with_count_products') ) {

            //  Additionally we can eager load the total products still following on these stores as well
            $model = $model->withCount(['products']);

        }

        //  Check if we want to eager load the total orders on each store
        if( request()->input('with_count_orders') ) {

            //  Additionally we can eager load the total orders on these stores as well
            $model = $model->withCount(['orders']);

        }

        //  Check if we want to eager load the total collected orders on each store
        if( request()->input('with_count_collected_orders') ) {

            //  Additionally we can eager load the total collected orders on these stores as well
            $model = $model->withCount(['orders as collected_orders_count' => function ($query) {
                $query->where('collection_verified', '1');
            }]);

        }

        //  Check if we want to eager load the total orders on each store
        if( request()->input('with_count_my_orders') ) {

            //  Additionally we can eager load the total orders on these stores as well
            $model = $model->withCount(['orders as my_orders_count' => function ($query) {
                $query->whereHas('users', function ($subQuery) {
                    $subQuery->where('users.id', auth()->user()->id);
                });
            }]);

        }

        //  Check if we want to eager load the total orders on each store
        if( request()->input('with_count_my_orders_as_customer') ) {

            //  Additionally we can eager load the total orders on these stores as well
            $model = $model->withCount(['orders as my_orders_as_customer_count' => function ($query) {
                $query->whereHas('users', function ($subQuery) {
                    $subQuery->where('users.id', auth()->user()->id)
                        ->where('user_order_collection_association.role', 'Customer');
                });
            }]);

        }

        //  Check if we want to eager load the total orders on each store
        if( request()->input('with_count_my_orders_as_friend') ) {

            //  Additionally we can eager load the total orders on these stores as well
            $model = $model->withCount(['orders as my_orders_as_friend_count' => function ($query) {
                $query->whereHas('users', function ($subQuery) {
                    $subQuery->where('users.id', auth()->user()->id)
                        ->where('user_order_collection_association.role', 'Friend');
                });
            }]);

        }

        //  Check if we want to eager load the total on each store
        if( request()->input('with_count_coupons') ) {

            //  Additionally we can eager load the total on these stores as well
            $model = $model->withCount(['coupons']);

        }

        //  Check if we want to eager load the total team members on each store
        if( request()->input('with_count_team_members') ) {

            //  Additionally we can eager load the total team members still joined on these stores as well
            $model = $model->withCount(['teamMembers' => function (Builder $query) {
                $query->joinedTeam();
            }]);

        }

        //  Check if we want to eager load the total followers on each store
        if( request()->input('with_count_followers') ) {

            //  Additionally we can eager load the total followers still following on these stores as well
            $model = $model->withCount(['followers' => function (Builder $query) {
                $query->following();
            }]);

        }

        //  Check if we want to eager load the total reviews on each store
        if( request()->input('with_count_reviews') ) {

            //  Additionally we can eager load the total reviews on these stores as well
            $model = $model->withCount(['reviews']);

        }

        //  Check if we want to eager load the average review rating on each store
        if( request()->input('with_rating') ) {

            //  Additionally we can eager load the average review rating on these stores as well.
            $model = $model->withAvg('reviews as rating', 'rating');

        }

        //  Check if we want to eager load the user and store association
        if( request()->input('with_user_store_association') ) {

            /**
             *  Additionally we can eager load the store on this order as well as
             *  eager load the current auth user's user and store association on
             *  that store. Note that this is not necessary for stores that are
             *  retrieved on the user and store relationship. In such cases the
             *  user and store association is loaded by default. This helps in
             *  cases when we are not acquiring stores through the user
             *  relationship but we still need to access the user and
             *  store association if it exists.
             */
            $model = $model->with(['authUserStoreAssociation']);

        }

        //  Check if we want to eager load the friend group and store association
        if( request()->input('with_friend_group_store_association') ) {

            /**
             *  Additionally we can eager load the current request's friend group id and store association on
             *  each store. Note that this is not necessary for stores that are retrieved on the friend group
             *  and store relationship. In such cases the friend group and store association is loaded by
             *  default. This helps in cases when we are not acquiring stores through the friend group
             *  relationship but we still need to access the friend group and store association if it
             *  exists e.g When acquiring stores of a particular user, we can specify the friend if
             *  group id so that we can eager load the friend group and store association to know
             *  that friend group is associated with that store that is also associated with that
             *  user
             */
            $model = $model->with(['friendGroupStoreAssociation' => function($query) {

                if(request()->filled('friend_group_id')) {

                    return $query->where('friend_group_id', request()->input('friend_group_id'));

                }else{

                    throw ValidationException::withMessages(['friend_group_id' => 'The friend group id is required to eager load the friend group store association']);

                }

            }]);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show all the store team member permissions
     */
    public function showAllTeamMemberPermissions()
    {
        return $this->extractPermissions(['*']);
    }

    /**
     *  Get store with the necessary relationships
     *
     *  @return StoreRepository
     */
    public function show(Store $store)
    {
        /**
         * @var User $user
         */
        $user = auth()->user();

        //  Get the store through the user so that we can capture the user and store association
        $store = $user->stores()->where('stores.id', $store->id);

        //  Eager load the store relationships based on request inputs
        $store = $this->eagerLoadRelationships($store)->model;

        //  Return the current store repository
        return $this->setModel($store->first());
    }

    /**
     *  Create new store
     *
     *  @param Request $request
     *  @return StoreRepository
     */
    public function createStore(Request $request)
    {
        //  Create store normally
        $storeRepository = parent::create($request);

        /**
         * @var User $user
         */
        $user = auth()->user();

        /**
         *  Since we need the user and store association, we must query the
         *  store through the user to retrieve this pivot information
         */
        return $storeRepository->setModel($user->stores()->where('stores.id', $storeRepository->model->id)->first());
    }

    /**
     *  Update existing store
     *
     *  @param Request $request
     *  @return StoreRepository
     */
    public function updateStore(Request $request)
    {
        //  If we are not registered with a bank
        if($request->filled('registered_with_bank') && $request->input('registered_with_bank') == false) {

            //  Remove the bank entry if previously specified
            $request->merge(['banking_with' => null]);

        }

        //  If we are not registered with CIPA
        if($request->filled('registered_with_cipa') && $request->input('registered_with_cipa') == false) {

            //  Remove the CIPA registration type entry if previously specified
            $request->merge(['registered_with_cipa_as' => null]);

        }

        //  If we have the supported payment methods
        if($request->filled('supported_payment_methods') && !empty($request->input('supported_payment_methods'))) {

            $supportedPaymentMethods = collect($request->input('supported_payment_methods'));
            $storePaymentMethodAssociations = StorePaymentMethodAssociation::where('store_id', $this->model->id)->get();

            //  If we have existing store payment method associations
            if(count($storePaymentMethodAssociations)) {

                //  Foreach existing store payment method association
                foreach($storePaymentMethodAssociations as $storePaymentMethodAssociation) {

                    $supportedPaymentMethod = $supportedPaymentMethods->firstWhere('id', $storePaymentMethodAssociation->payment_method_id);

                    if($supportedPaymentMethod) {

                        $totalDisabled = $storePaymentMethodAssociation->total_disabled;
                        $totalEnabled = $storePaymentMethodAssociation->total_enabled;
                        $instruction = $supportedPaymentMethod['instruction'];
                        $active = $supportedPaymentMethod['active'];

                        $activePropertyChanged = $active != $storePaymentMethodAssociation->active;

                        //  Update the existing store payment method association since it was provided
                        $storePaymentMethodAssociation->update([
                            'active' => $active,
                            'instruction' => $instruction ?? null,
                            'total_enabled' => ($activePropertyChanged && $active) ? ($totalEnabled + 1) : $totalEnabled,
                            'total_disabled' => ($activePropertyChanged && !$active) ? ($totalDisabled + 1) : $totalDisabled,
                        ]);

                        //  Remove this store payment method association from the supported payment methods since we have updated
                        $supportedPaymentMethods = $supportedPaymentMethods->reject(function($currSupportedPaymentMethod) use ($supportedPaymentMethod) {
                            return $currSupportedPaymentMethod['id'] == $supportedPaymentMethod['id'];
                        });

                    }else{

                        //  Update the existing store payment method association since it was not provided
                        $storePaymentMethodAssociation->update([
                            'active' => false,
                            'total_disabled' => $storePaymentMethodAssociation->total_disabled + 1,
                        ]);

                    }

                }

            }

            //  If we still have supported payment methods, then these are non-existing records that must be created
            if($supportedPaymentMethods->count()) {

                $data = $supportedPaymentMethods->map(function($supportedPaymentMethod) {
                    return [
                        'total_enabled' => 1,
                        'total_disabled' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'store_id' => $this->model->id,
                        'active' => $supportedPaymentMethod['active'],
                        'payment_method_id' => $supportedPaymentMethod['id'],
                        'instruction' => $supportedPaymentMethod['instruction'] ?? null,
                    ];
                })->toArray();

                DB::table('store_payment_method_association')->insert($data);

            }

        }

        //  Update store normally
        $storeRepository = parent::update($request);

        //  Return the updated store
        return $this->show($storeRepository->model);

    }

    /**
     *  Return the store visit shortcode
     *
     *  This will allow the user to dial the shortcode and visit via USSD
     *
     *  @return ShortcodeRepository
     */
    public function showVisitShortcode()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        if( $visitShortcode = $store->visitShortcode ) {

            //  Get the store visit shortcode repository
            return $this->shortcodeRepository()->setModel($visitShortcode);

        }

        //  The visit shortcode was not found
        throw new ModelNotFoundException;
    }

    /**
     *  Request the store payment shortcode
     *
     *  This will allow the user to dial the shortcode and pay via USSD
     *
     *  @return StoreRepository
     */
    public function generatePaymentShortcode(Request $request)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Get the User ID that this shortcode is reserved for
        $reservedForUserId = auth()->user()->id;

        //  Request a payment shortcode for this store
        $shortcodeRepository = $this->shortcodeRepository()->generatePaymentShortcode($store, $reservedForUserId);

        //  If we want to return the store with the payment shortcode embedded
        if( $request->input('embed') ) {

            //  Set the store as the repository model with the payment shortcode
            return $this->setModel(

                //  Load the payment shortcode on this store
                $store->load('authPaymentShortcode')

            );

        //  If we want to return the payment shortcode alone
        }else{

            //  Return the shortcode repository
            return $shortcodeRepository;

        }
    }

    /**
     *  Request a visit shortcode for this store
     *
     *  This will allow the user to dial the shortcode visit the store via USSD
     *
     *  @return StoreRepository
     */
    public function requestVisitShortcode($expiresAt, $eagerLoadRelationship = true)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Request a visit shortcode for this store
        $this->shortcodeRepository()->requestVisitShortcode($store, $expiresAt);

        //  Set the store as the repository model with the visit shortcode (If permitted)
        if($eagerLoadRelationship) $this->setModel(

            //  Load the visit shortcode on this store
            $store->load('visitShortcode')

        );

        return $this;
    }

    /**
     *  Expire the payment shortcode from this store
     *
     *  @return StoreRepository
     */
    public function expirePaymentShortcode()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Get the current auth user active store payment shortcode
        $authPaymentShortcode = $store->authPaymentShortcode;

        //  If the current auth user active store payment shortcode exists
        if( $authPaymentShortcode ) {

            //  Expire the store payment shortcode.
            $this->shortcodeRepository()->setModel($authPaymentShortcode)->expireShortcode();

        }

        return $this;
    }

    /**
     *  Calculate store access subscription amount
     *
     *  @param Request $request
     *  @return array
     */
    public function calculateStoreAccessSubscriptionAmount(Request $request)
    {
        //  Get the Subscription Plan ID
        $subscriptionPlanId = $request->input('subscription_plan_id');

        //  Get the Subscription Plan
        $subscriptionPlan = SubscriptionPlan::find($subscriptionPlanId);

        //  Calculate the subscription plan amount
        $amount = $this->subscriptionPlanRepository()->setModel($subscriptionPlan)->calculateSubscriptionPlanAmountAgainstSubscriptionDuration($request);

        return [
            'calculation' => $this->convertToMoneyFormat($amount, $this->model->currency)
        ];
    }

    /**
     *  Create store access subscription
     *
     *  A subscription enables the team member access to the store.
     *  It also generates the store visit shortcode if the
     *  store does not already have one.
     *
     *  @return StoreRepository
     */
    public function createStoreAccessSubscription(Request $request)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        /**
         *  @var User $user
         */
        $user = auth()->user();

        //  Get the latest subscription matching the given authenticated user to this store
        $latestSubscription = $store->subscriptions()->where('user_id', $user->id)->latest()->first();

        //  Create a subscription
        $subscriptionRepository = $this->subscriptionRepository()->createSubscription($store, $request, $latestSubscription);

        //  Get the subscription
        $subscription = $subscriptionRepository->model;

        //  Get the subscription end datetime
        $subscriptionExpiresAt = $subscription->end_at;

        //  Update the last subscription end at on the store
        $store->update([
            'last_subscription_end_at' => $subscriptionExpiresAt
        ]);

        //  Update the last subscription end at on the user and store association
        DB::table('user_store_association')->where('user_id', $user->id)->where('store_id', $store->id)->update([
            'last_subscription_end_at' => $subscriptionExpiresAt
        ]);

        /**
         *  Set the visit shortcode to expire 30 days after the subscription end datetime.
         *  This is so that we can give the team member a 30 day grace period to subscribe
         *  before the shortcode is recycled.
         */
        $visitShortcodeExpiresAt = Carbon::parse($subscriptionExpiresAt)->addDays(Shortcode::GRACE_PERIOD);

        //  Request a visit shortcode for this store (avoid eager loading any relationships)
        $shortcodeRepository = $this->shortcodeRepository()->requestVisitShortcode($store, $visitShortcodeExpiresAt, false);

        //  Expire the payment shortcode
        $this->expirePaymentShortcode();

        // Send sms to user that their subscription was paid successfully
        SmsService::sendOrangeSms(
            $subscription->craftSubscriptionSuccessfulSmsMessageForUser($user, $store),
            $user->mobile_number->withExtension,
            null, null, null
        );

        //  If we want to return the store with the subscription and visit shortcode embedded
        if( $request->input('embed') ) {

            /**
             *  Set the store as the repository model with the visit shortcode
             *  and the current authenticated user's active subscription
             */
            return $this->setModel(

                //  Load the visit shortcode and subscription on this store
                $store->load(['visitShortcode', 'authActiveSubscription'])

            );

        //  If we want to return the subscription alone
        }else{

            //  Return the subscription repository model
            return $subscriptionRepository;

        }
    }

    /**
     *  Show the current authenticated user's subscriptions
     *
     *  @return StoreRepository
     */
    public function showMySubscriptions(Request $request)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the active subscriptions
        if(strtolower($filter) == 'active') {

            $subscriptions = $store->authActiveSubscriptions();

        //  Query the inactive subscriptions
        }else if(strtolower($filter) == 'inactive') {

            $subscriptions = $store->authInactiveSubscriptions();

        //  Query any subscription
        }else{

            $subscriptions = $store->authSubscriptions();

        }

        return $this->subscriptionRepository()->setModel($subscriptions)->get();
    }

    /**
     *  Return the store supported payment methods
     *
     *  @return PaymentMethodRepository
     */
    public function showSupportedPaymentMethods()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        $supportedPaymentMethods = $store->supportedPaymentMethods();
        return $this->paymentMethodRepository()->setModel($supportedPaymentMethods)->get();
    }

    /**
     *  Return the store available payment methods
     *
     *  @return PaymentMethodRepository
     */
    public function showAvailablePaymentMethods()
    {
        $availablePaymentMethods = PaymentMethod::availableOnStores()->orderBy('position', 'asc');
        return $this->paymentMethodRepository()->setModel($availablePaymentMethods)->get();
    }

    /**
     *  Return the store sharable content
     *
     *  @return array
     */
    public function showSharableContent()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model->load(['visitShortcode', 'products' => function($query) {
            return $query->isNotVariation()->visible();
        }]);

        $products = $store->products;
        $hasProducts = count($products);
        $visitShortcode = $store->visitShortcode;
        $hasVisitShortcode = !is_null($visitShortcode);

        $message = $store->name . " here 👋,\n\Check us out on Perfect Order 😉\n\n";

        if ($hasProducts) {
            $message .= collect($products)->map(function ($product, $index) {
                $number = $index + 1;
                return $number . ') ' . $product->name . '   ' . $product->unit_price->amountWithCurrency;
            })->join("\n");
        }

        if ($hasVisitShortcode) {
            $message .= "\n\nPlace your order on " . $visitShortcode->dial['code'] . "\n\n";
            $message .= "Or download the Perfect Order App: https://play.google.com/store/apps/details?id=bw.co.bonakoonline";
        } else {
            $message .= "Download the Perfect Order App: https://play.google.com/store/apps/details?id=bw.co.bonakoonline";
        }

        return [
            'message' => $message
        ];
    }

    /**
     *  Return the store sharable content choices
     *
     *  @return array
     */
    public function showSharableContentChoices()
    {
        return [
            'choices' => [
                $this->showSharableContent()
            ]
        ];
    }




















    /**
     *  Show the store coupon filters
     *
     *  @return array
     */
    public function showCouponFilters()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        $filters = collect(Coupon::FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Active',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Inactive',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($store) {

            $coupons = $store->coupons();

            if(strtolower($filter) == 'active') {

                $total = $coupons->active()->count();

            }else if(strtolower($filter) == 'inactive') {

                $total = $coupons->inactive()->count();

            }elseif(strtolower($filter) == 'all') {

                $total = $coupons->count();

            }

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the store coupons
     *
     *  @return CouponRepository
     */
    public function showCoupons()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        $coupons = $store->coupons()->orderBy('updated_at', 'desc');
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  If we have the filter
        if( !empty($filter) ) {

            if(strtolower($filter) == 'active') {

                $coupons = $coupons->active();

            }else if(strtolower($filter) == 'inactive') {

                $coupons = $coupons->inactive();

            }

        }

        return $this->couponRepository()->setModel($coupons)->get();
    }

    /**
     *  Create the store coupon
     *
     *  @param \Illuminate\Http\Request $request
     *  @return CouponRepository
     */
    public function createCoupon(Request $request)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Check if this store has reached the maximum number of coupons
        $hasReachedMaximumCoupons = $store->coupons()->count() >= $store::MAXIMUM_COUPONS;

        //  If this store has reached the maximum number of coupons
        if($hasReachedMaximumCoupons) throw new StoreHasTooManyCouponsException;

        $request->merge([
            'currency' => $this->model->currency,
            'user_id' => auth()->user()->id,
            'store_id' => $this->model->id
        ]);

        // Create a new coupon
        $couponRepository = $this->couponRepository()->create($request);

        //  Return the coupon repository
        return $couponRepository;
    }

    /**
     *  Show the store product filters
     *
     *  @return array
     */
    public function showProductFilters()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        $filters = collect(Product::FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Visible',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Hidden',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($store) {

            $products = $store->products()->isNotVariation();

            if(strtolower($filter) == 'visible') {

                $total = $products->visible()->count();

            }else if(strtolower($filter) == 'hidden') {

                $total = $products->hidden()->count();

            }elseif(strtolower($filter) == 'all') {

                $total = $products->count();

            }

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the store products
     *
     *  @return ProductRepository
     */
    public function showProducts()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));
        $products = $store->products()->isNotVariation()->orderBy('position', 'asc');

        //  If we have the filter
        if( !empty($filter) ) {

            if(strtolower($filter) == 'visible') {

                $products = $products->visible();

            }else if(strtolower($filter) == 'hidden') {

                $products = $products->hidden();

            }

        }

        return $this->productRepository()->setModel($products)->get();
    }

    /**
     *  Create the store product
     *
     *  @param \Illuminate\Http\Request $request
     *  @return ProductRepository
     */
    public function createProduct(Request $request)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Count the total products on this store
        $totalProducts =  $store->products()->count();

        //  Check if this store has reached the maximum number of products
        $hasReachedMaximumProducts = $totalProducts >= $store::MAXIMUM_PRODUCTS;

        //  If this store has reached the maximum number of products
        if($hasReachedMaximumProducts) throw new StoreHasTooManyProductsException;

        $request->merge([
            'currency' => $this->model->currency,
            'store_id' => $this->model->id,
            'user_id' => auth()->user()->id
        ]);

        // Create a new product
        $productRepository = $this->productRepository()->create($request);

        //  Position this product at the top of the stack
        $this->updateProductArrangement([$productRepository->model->id]);

        //  Return the product repository
        return $productRepository;
    }

    /**
     *  Update the store product arrangement
     *
     *  @param \Illuminate\Http\Request|array $data
     *  @return array
     */
    public function updateProductArrangement($data)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        // Retrieve all the store products
        $products = $store->products()->orderBy('position', 'asc');

        /**
         *  Create a map of product IDs to their original positions.
         *  The keys represent the product IDs while the values
         *  represent the original positions of the products.
         *
         *  e.g $originalProductPositions = [
         *      4 => 1,
         *      3 => 2,
         *      2 => 3,
         *      1 => 4
         *  ];
         */
        $originalProductPositions = $products->pluck('position', 'id');

        /**
         *  Get the product arrangement from the request or array of data that
         *  has been provided on the method parameter. This represents the new
         *  arrangement of products based on their product ids.
         *
         *  $arrangement = ["2", "3"];
         *
         *  This means that the product with id "2" must be in position 1 then
         *  followed by the product with id "3" in position 2. Other products
         *  must be in their original positions but after these two products.
         */
        if(($request = $data) instanceof Request) {
            $arrangement = $request->input('arrangement');
        }else{
            $arrangement = $data;
        }

        //  Make sure that these are products that belong to this store.
        $arrangement = collect($arrangement)->filter(function ($productId) use ($originalProductPositions) {
            return collect($originalProductPositions)->keys()->contains($productId);
        })->toArray();

        /**
         *  Use $arrangement to create an array of updated positions for each product
         *  that has been moved. Basically return the product id as the key and the
         *  new position as the value.
         *
         *  e.g $movedProductPositions = [
         *     2 => 1,
         *     3 => 2
         *  ];
         */
        $movedProductPositions = collect($arrangement)->mapWithKeys(function ($productId, $newPosition) use ($originalProductPositions) {
            return [$productId => ($newPosition + 1)];
        })->toArray();

        /**
         *  Create an array of products that have not been moved. Set their
         *  positions to the original positions that they had before but
         *  after the products that have been moved. Return the same
         *  product id as the key and the updated position as the
         *  value.
         *
         *  e.g $adjustedOriginalProductPositions = [
         *      4 => 3,
         *      1 => 4
         *  ];
         */
        $adjustedOriginalProductPositions = $originalProductPositions->except(collect($movedProductPositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedProductPositions) {
            return [$id => count($movedProductPositions) + $index + 1];
        })->toArray();

        /**
         *  Combine the two arrays of updated positions for each product that has been
         *  moved and the array of products that have not been moved. Return the same
         *  product id as the key and the updated position as the value.
         *
         *  combine:
         *
         *  $movedProductPositions = [
         *     2 => 1,
         *     3 => 2
         *  ];
         *
         *  and:
         *
         *  $adjustedOriginalProductPositions = [
         *      4 => 3,
         *      1 => 4
         *  ];
         *
         *  to get:
         *
         *  $productPositions = [
         *      2 => 1,
         *      3 => 2,
         *      4 => 3,
         *      1 => 4
         *  ];
         */
        $productPositions = $movedProductPositions + $adjustedOriginalProductPositions;

        // Update the positions of all products in the database using one query
        DB::table('products')
            ->where('store_id', $this->model->id)
            ->whereIn('id', array_keys($productPositions))
            ->update(['position' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $position) {
                return 'WHEN ' . $id . ' THEN ' . $position . ' ';
            }, array_keys($productPositions), $productPositions)) . 'END')]);

        return ['message' => 'Products have been updated'];
    }

    /**
     *  Update the store product visibility
     *
     *  @param \Illuminate\Http\Request|array $data
     *  @return array
     */
    public function updateProductVisibility($data)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        // Retrieve all the store products
        $products = $store->products()->orderBy('position', 'asc')->get();

        // Extract the 'id' and 'visible' properties from each product
        $existingProductIdsAndVisibility = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'visible' => $product->visible,
            ];
        });

        /**
         *  Get the product ids and visibility from the request or array of data that
         *  has been provided on the method parameter. This represents the new
         *  product visibility based on their product ids.
         *
         *  $request->all() = [
         *      ['id' => 1, 'visible' => true],
         *      ['id' => 2, 'visible' => false],
         *      ['id' => 3, 'visible' => false]
         *  ];
         *
         *  This means that the product with id "1" must be visible while
         *  the products with id "2" and "3" are must be hidden.
         */
        if(($request = $data) instanceof Request) {
            $productIdsAndVisibility = $request->input('visibility');
        }else{
            $productIdsAndVisibility = $data;
        }

        //  Set the total number of visible products
        $numberOfVisibleProducts = 0;

        /**
         *  Create an array of products that have been changed.
         *  Basically return the product id as the key and the visibility as the value.
         *
         *  e.g $newProductIdsAndVisibility = [
         *     1 => true,
         *     2 => true
         *  ];
         */
        $newProductIdsAndVisibility = collect($productIdsAndVisibility)->filter(function ($item) use ($existingProductIdsAndVisibility) {
            return $existingProductIdsAndVisibility->contains('id', $item['id']);
        })->mapWithKeys(function ($item, $key) use (&$numberOfVisibleProducts) {

            $visible = $item['visible'] ? ($numberOfVisibleProducts < Store::MAXIMUM_VISIBLE_PRODUCTS ? true : false) : false;
            $numberOfVisibleProducts += ($item['visible'] ? 1 : 0);
            return [$item['id'] => $visible];

        })->toArray();

        /**
         *  Create an array of products that have not been changed.
         *  Basically return the product id as the key and the visibility as the value.
         *
         *  e.g $oldProductIdsAndVisibility = [
         *      3 => true,
         *      4 => false,
         *      5 => false
         *  ];
         */
        $oldProductIdsAndVisibility = collect($existingProductIdsAndVisibility)->filter(function ($item) use ($productIdsAndVisibility) {
            return collect($productIdsAndVisibility)->doesntContain('id', $item['id']);
        })->mapWithKeys(function ($item, $key) use (&$numberOfVisibleProducts) {

            $visible = $item['visible'] ? ($numberOfVisibleProducts < Store::MAXIMUM_VISIBLE_PRODUCTS ? true : false) : false;
            $numberOfVisibleProducts += ($item['visible'] ? 1 : 0);
            return [$item['id'] => $visible];

        })->toArray();

        /**
         *  Combine the two arrays of updated positions for each product that has been
         *  moved and the array of products that have not been moved. Return the same
         *  product id as the key and the updated position as the value.
         *
         *  combine:
         *
         *  $newProductIdsAndVisibility = [
         *     1 => true,
         *     2 => true
         *  ];
         *
         *  and:
         *
         *  $oldProductIdsAndVisibility = [
         *      3 => true,
         *      4 => false,
         *      5 => false
         *  ];
         *
         *  to get:
         *
         *  $finalProductIdsAndVisibility = [
         *     1 => true,
         *     2 => true,
         *     3 => true,
         *     4 => false,
         *     5 => false
         *  ];
         */
        $finalProductIdsAndVisibility = $newProductIdsAndVisibility + $oldProductIdsAndVisibility;

        /**
         *  Convert the booleans into integers
         *
         *  From:
         *
         *  $finalProductIdsAndVisibility = [
         *     1 => true,
         *     2 => true,
         *     3 => true,
         *     4 => false,
         *     5 => false
         *  ];
         *
         *  To:
         *
         *  $finalProductIdsAndVisibility = [
         *     1 => 1,
         *     2 => 1,
         *     3 => 1,
         *     4 => 0,
         *     5 => 0
         *  ];
         */
        $finalProductIdsAndVisibility = collect($finalProductIdsAndVisibility)->map(fn($item) => $item ? 1 : 0)->toArray();

        // Update the visibility of all products in the database using one query
        DB::table('products')
            ->where('store_id', $this->model->id)
            ->whereIn('id', array_keys($finalProductIdsAndVisibility))
            ->update(['visible' => DB::raw('CASE id ' . implode(' ', array_map(function ($id, $visibility) {
                return 'WHEN ' . $id . ' THEN ' . $visibility . ' ';
            }, array_keys($finalProductIdsAndVisibility), $finalProductIdsAndVisibility)) . 'END')]);

        return ['message' => 'Products have been updated'];
    }

    /**
     *  Show the store order filters
     *
     *  @return array
     */
    public function showOrderFilters()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        return $this->orderRepository()->showStoreOrderFilters($store);
    }

    /**
     *  Show the store orders
     *
     *  @return OrderRepository
     */
    public function showOrders()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        return $this->orderRepository()->showStoreOrders($store);
    }

    /**
     *  Show the store logo
     *
     *  @return array
     */
    public function showLogo() {
        return [
            'logo' => $this->model->logo
        ];
    }

    /**
     *  Update the store logo
     *
     *  @param \Illuminate\Http\Request $request
     *
     *  @return StoreRepository
     */
    public function updateLogo(Request $request) {

        //  Remove the exiting logo (if any) and save the new logo (if any)
        return $this->removeExistingLogo(CanSaveChanges::NO)->storeLogo($request);

    }

    /**
     *  Remove the existing store logo
     *
     *  @param CanSaveChanges $canSaveChanges - Whether to save the store changes after deleting the logo
     *  @return array | StoreRepository
     */
    public function removeExistingLogo($canSaveChanges = CanSaveChanges::YES) {

        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Check if we have an existing logo stored
        $hasExistingLogo = !empty($store->logo);

        //  If the store has an existing logo stored
        if( $hasExistingLogo ) {

            //  Delete the logo file
            AWSService::delete($store->logo);

        }

        //  If we should save these changes on the database
        if($canSaveChanges == CanSaveChanges::YES) {

            //  Save the store changes
            $this->update(['logo' => null]);

            return [
                'message' => 'Logo deleted successfully'
            ];

        //  If we should not save these changes on the database
        }else{

            //  Remove the logo url reference from the store
            $store->logo = null;

            //  Set the modified store
            $this->setModel($store);

        }

        return $this;

    }

    /**
     *  Store the store logo
     *
     *  @param \Illuminate\Http\Request $request
     *
     *  @return StoreRepository|array
     */
    public function storeLogo(Request $request) {

        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Check if we have a new logo provided
        $hasNewLogo = $request->hasFile('logo');

        /**
         *  Save the new logo when the following condition is satisfied:
         *
         *  1) The logo is provided when we are updating the logo only
         *
         *  If the logo is provided while creating or updating the store as
         *  a whole, then the logo will be updated with the rest of the
         *  store details as a single query.
         *
         *  Refer to the saving() method of the StoreObserver::class
         */
        $updatingTheStoreLogoOnly = $request->routeIs('store.logo.update');

        //  If we have a new logo provided
        if( $hasNewLogo ) {

            //  Save the logo on AWS and update the store with the logo url
            $store->logo = AWSService::store('logos', $request->logo);

            //  Set the modified store
            $this->setModel($store);

            if( $updatingTheStoreLogoOnly ) {

                //  Save the store changes
                $store->save();

            }

        }

        if( $updatingTheStoreLogoOnly ) {

            //  Return the logo image url
            return ['logo' => $store->logo];

        }

        return $this;

    }

    /**
     *  Show the store cover photo
     *
     *  @return array
     */
    public function showCoverPhoto() {

        return [
            'cover_photo' => $this->model->cover_photo
        ];

    }

    /**
     *  Update the store cover photo
     *
     *  @param \Illuminate\Http\Request $request
     *
     *  @return array | StoreRepository
     */
    public function updateCoverPhoto(Request $request) {

        //  Remove the exiting cover photo (if any) and save the new cover photo (if any)
        return $this->removeExistingCoverPhoto(CanSaveChanges::NO)->storeCoverPhoto($request);

    }

    /**
     *  Remove the existing store cover photo
     *
     *  @param CanSaveChanges $canSaveChanges - Whether to save the store changes after deleting the logo
     *  @return array | StoreRepository
     */
    public function removeExistingCoverPhoto($canSaveChanges = CanSaveChanges::YES) {

        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Check if we have an existing cover photo stored
        $hasExistingLogo = !empty($store->cover_photo);

        //  If the store has an existing cover photo stored
        if( $hasExistingLogo ) {

            //  Delete the cover photo file
            AWSService::delete($store->cover_photo);

        }

        //  If we should save these changes on the database
        if($canSaveChanges == CanSaveChanges::YES) {

            //  Save the store changes
            $this->update(['cover_photo' => null]);

            return [
                'message' => 'Cover photo deleted successfully'
            ];

        //  If we should not save these changes on the database
        }else{

            //  Remove the cover photo url reference from the store
            $store->cover_photo = null;

            //  Set the modified store
            $this->setModel($store);

        }

        return $this;

    }

    /**
     *  Store the store cover photo
     *
     *  @param \Illuminate\Http\Request $request
     *  @return array | StoreRepository
     */
    public function storeCoverPhoto(Request $request) {

        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Check if we have a new cover photo provided
        $hasNewCoverPhoto = $request->hasFile('cover_photo');

        /**
         *  Save the new cover photo when the following condition is satisfied:
         *
         *  1) The cover photo is provided when we are updating the cover photo only
         *
         *  If the cover photo is provided while creating or updating the store as
         *  a whole, then the cover photo will be updated with the rest of the
         *  store details as a single query.
         *
         *  Refer to the saving() method of the StoreObserver::class
         */
        $updatingTheStoreCoverPhotoOnly = $request->routeIs('store.cover.photo.update');

        //  If we have a new cover photo provided
        if( $hasNewCoverPhoto ) {

            //  Save the cover photo on AWS and update the store with the cover photo url
            $store->cover_photo = AWSService::store('cover_photos', $request->cover_photo);

            //  Set the modified store
            $this->setModel($store);

            if( $updatingTheStoreCoverPhotoOnly ) {

                //  Save the store changes
                $store->save();

            }

        }

        if( $updatingTheStoreCoverPhotoOnly ) {

            //  Return the cover photo image url
            return ['cover_photo' => $store->cover_photo];

        }

        return $this;

    }








    /**
     *  Show the store adverts
     *
     *  @return array
     */
    public function showAdverts() {

        return [
            'adverts' => $this->model->adverts
        ];

    }

    /**
     *  Create the store advert
     *
     *  @param \Illuminate\Http\Request $request
     *  @return StoreRepository
     */
    public function createAdvert(Request $request)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Make sure we don't have empty adverts if non exist
        $store->adverts = $store->adverts ?? [];

        //  Get the advert position as an integer (This is provided when replacing an existing advert)
        $position = $request->filled('position') ? (int) $request->position : null;

        //  Check if the advert can be stored based on the limits set
        if( count($store->adverts) < Store::MAXIMUM_ADVERTS ) {

            //  Save the advert on AWS and capture the advert url
            $url = AWSService::store('adverts', $request->advert);

            //  Update the store with the advert url
            if(is_null($position)) {

                //  Add a new advert
                $store->adverts = array_merge($store->adverts, [$url]);

            }else{

                //  Calculate the index from the position
                $index = $position - 1;

                //  Temporarily capture the existing store adverts
                $adverts = collect($store->adverts)->toArray();

                //  Replace an advert at a specific position as specified by the index
                array_splice($adverts, $index, 0, [$url]);

                //  Apply changes to the store adverts
                $store->adverts = $adverts;

            }

            //  Save the changes
            $store->save();

            return [
                'message' => 'Advert added successfully',
                'advert' => $url
            ];

        }else{

            //  Throw an Exception - Password does not match
            throw ValidationException::withMessages(['advert' => 'You can\'t upload more than '.Store::MAXIMUM_ADVERTS.' adverts']);

        }
    }

    /**
     *  Update the store advert
     *
     *  @param \Illuminate\Http\Request $request
     *  @return StoreRepository
     */
    public function updateAdvert(Request $request)
    {
        $this->deleteAdvert($request);
        $response = $this->createAdvert($request);
        $response['message'] = 'Advert updated successfully';

        return $response;
    }

    /**
     *  Delete the store advert
     *
     *  @param \Illuminate\Http\Request $request
     *  @return array
     */
    public function deleteAdvert(Request $request)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Get the advert position as an integer
        $position = (int) $request->position;

        //  Calculate the index from the position
        $index = $position - 1;

        //  If the index is equal to or greater than zero
        if( $index >= 0 ) {

            //  Capture the adverts
            $adverts = $store->adverts;

            //  If the index points to an existing image name
            if( isset( $adverts[$index] ) ) {

                //  Delete the advert file
                AWSService::delete($adverts[$index]);

                //  Remove the deleted advert
                unset($adverts[$index]);

                //  Update the adverts
                $store->adverts = collect($adverts)->values();

                //  Save the changes
                $store->save();

                //  Return a message indicating advert removed
                return ['message' => 'Advert removed successfully'];

            }

        }

        //  Return a message indicating no advert removed
        return ['message' => 'No advert was removed'];

    }

    /**
     *  Show the store review filters
     *
     *  @return array
     */
    public function showReviewFilters()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        return $this->reviewRepository()->showStoreReviewFilters($store);
    }

    /**
     *  Show the store reviews
     *
     *  @return ReviewRepository
     */
    public function showReviews()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        return $this->reviewRepository()->showStoreReviews($store);
    }

    /**
     *  Show the store review rating options
     *
     *  @return array
     */
    public function showReviewRatingOptions()
    {
        $ratingOptions = ['very bad', 'bad', 'ok', 'good', 'very good'];

        $ratingOptions = collect($ratingOptions)->map(function($name, $index) {
            return [
                'name' => ucwords($name),
                'rating' => $index + 1
            ];
        });

        return [
            'rating_subjects' => Review::SUBJECTS,
            'rating_options' => $ratingOptions
        ];
    }

    /**
     *  Create the store review
     *
     *  @param \Illuminate\Http\Request $request
     */
    public function createReview(Request $request)
    {
        $request->merge([
            'store_id' => $this->model->id,
            'user_id' => auth()->user()->id
        ]);

        return $this->reviewRepository()->create($request);
    }

    /**
     *  Provide the permission grant names in exchange
     *  of detailed permission details.
     *
     *  @param array $permissions
     *  @return array
     */
    public function extractPermissions($permissions = [])
    {
        return collect($permissions)->contains('*')
            //  Get every permission available except the "*" permission
            ? collect(Store::PERMISSIONS)->filter(fn($permission) => $permission['grant'] !== '*')->values()
            //  Get only the specified permissions
            : collect($permissions)->map(function($permission) {
                return collect(Store::PERMISSIONS)->filter(
                    fn($storePermission) => $storePermission['grant'] == $permission
                )->first();
            })->filter();
    }

    /**
     *  Show the store follower filters
     *
     *  @return array
     */
    public function showFollowerFilters()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        $filters = collect(UserStoreAssociation::FOLLOWER_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'Following',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Unfollowed',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Invited',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($store) {

            if(strtolower($filter) == 'following') {

                $total = $store->followers()->following()->count();

            }else if(strtolower($filter) == 'unfollowed') {

                $total = $store->followers()->unfollowed()->count();

            }else if(strtolower($filter) == 'invited') {

                $total = $store->followers()->invitedToFollow()->count();

            }else if(strtolower($filter) == 'declined') {

                $total = $store->followers()->declinedToFollow()->count();

            }elseif(strtolower($filter) == 'all') {

                $total = $store->followers()->count();

            }

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the store followers
     *
     *  @return UserRepository
     */
    public function showFollowers()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        $users = $store->followers();
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Order by the last seen date and time
        $users = $users->orderByPivot('user_store_association.last_seen_at', 'DESC');

        //  If we have the filter
        if( !empty($filter) ) {

            if(strtolower($filter) == 'following') {

                $users = $users->following();

            }else if(strtolower($filter) == 'unfollowed') {

                $users = $users->unfollowed();

            }else if(strtolower($filter) == 'invited') {

                $users = $users->invitedToFollow();

            }else if(strtolower($filter) == 'declined') {

                $users = $users->declinedToFollow();

            }

        }

        return $this->userRepository()->setModel($users)->get();
    }

    /**
     *  Accept all invitations to join stores
     */
    public function acceptAllInvitationsToFollow()
    {
        //  Get the stores that the user has been invited to follow
        $stores = Store::with(['teamMembers' => function($query) {

            //  Get each store with the team members who have joined that store
            $query->joinedTeam();

        }])->whereHas('followers', function($query) {

            //  Check if the user has been invited to follow this store
            $query->where([
                'user_id' => auth()->user()->id,
                'follower_status' => 'Invited',
            ]);

        })->get();

        //  Accept the invitations
        DB::table('user_store_association')
            ->where('follower_status', 'Invited')
            ->where('user_id', auth()->user()->id)
            ->update([
                'follower_status' => 'Following'
            ]);

        //  Notify the team members of each store on the user's decision to accept the invitation
        $this->notifyTeamMembersOnUserResponseToFollowInvitation(InvitationResponse::Accepted, $stores);

        return ['message' => 'Invitations accepted successfully'];
    }

    /**
     *  Decline all invitations to join stores
     */
    public function declineAllInvitationsToFollow()
    {
        //  Get the stores that the user has been invited to follow
        $stores = Store::with(['teamMembers' => function($query) {

            //  Get each store with the team members who have joined that store
            $query->joinedTeam();

        }])->whereHas('followers', function($query) {

            //  Check if the user has been invited to follow this store
            $query->where([
                'user_id' => auth()->user()->id,
                'follower_status' => 'Invited',
            ]);

        })->get();

        //  Decline the invitations
        DB::table('user_store_association')
            ->where('follower_status', 'Invited')
            ->where('user_id', auth()->user()->id)
            ->update([
                'follower_status' => 'Declined'
            ]);

        //  Notify the team members of each store on the user's decision to declined the invitation
        $this->notifyTeamMembersOnUserResponseToFollowInvitation(InvitationResponse::Declined, $stores);

        return ['message' => 'Invitations declined successfully'];
    }

    /**
     *  Accept invitation to join store
     */
    public function acceptInvitationToFollow()
    {
        /**
         *  @var User $user
         */
        $user = auth()->user();

        $userStoreAssociation = $this->getUserStoreAssociation($user);

        if($userStoreAssociation->is_follower_who_is_invited) {

            //  Accept invitation
            $this->updateInvitationToFollowStatus('Following');

            //  Notify the team members of this store on the user's decision to accept the invitation
            $this->notifyTeamMembersOnUserResponseToFollowInvitation(InvitationResponse::Accepted);

            return ['message' => 'Invitation accepted successfully'];

        }else{

            if($userStoreAssociation->is_follower) {

                throw new InvitationAlreadyAcceptedException;

            }else if($userStoreAssociation->is_follower_who_has_declined) {

                throw new InvitationAlreadyDeclinedException('This invitation has already been declined and cannot be accepted');

            }

        }
    }

    /**
     *  Decline invitation to follow store
     */
    public function declineInvitationToFollow()
    {
        /**
         *  @var User $user
         */
        $user = auth()->user();

        $userStoreAssociation = $this->getUserStoreAssociation($user);

        if($userStoreAssociation) {

            if($userStoreAssociation->is_follower_who_is_invited) {

                //  Decline invitation
                $this->updateInvitationToFollowStatus('Declined');

                //  Notify the team members of this store on the user's decision to decline the invitation
                $this->notifyTeamMembersOnUserResponseToFollowInvitation(InvitationResponse::Declined);

                return ['message' => 'Invitation declined successfully'];

            }else{

                if($userStoreAssociation->is_follower) {

                    throw new InvitationAlreadyAcceptedException('This invitation has already been accepted and cannot be declined.');

                }else if($userStoreAssociation->is_follower_who_has_declined) {

                    throw new InvitationAlreadyDeclinedException;

                }

            }

        }else{

            throw new InvalidInvitationException('You have not been invited to this store');

        }
    }

    /**
     *  Notify the team members on the user's decision to accept or decline the invitation to follow
     *
     *  @param InvitationResponse $invitationResponse - Indication of whether the user has accepted or declined the invitation
     *  @param Collection|\App\Models\Store[] $storesInvitedToFollow
     */
    public function notifyTeamMembersOnUserResponseToFollowInvitation(InvitationResponse $invitationResponse, $storesInvitedToFollow = [])
    {
        /**
         *  @var User $user
         */
        $user = auth()->user();

        //  Method to send the notifications
        $sendNotifications = function($storeInvitedToFollow, $user) use ($invitationResponse) {
            if($invitationResponse == InvitationResponse::Accepted) {

                //  Notify the team members that this user has accepted the invitation to join this store
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $storeInvitedToFollow->teamMembers,
                    new InvitationToFollowStoreAccepted($storeInvitedToFollow, $user)
                );

            }else{

                //  Notify the team members that this user has declined the invitation to follow this store
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $storeInvitedToFollow->teamMembers,
                    new InvitationToFollowStoreDeclined($storeInvitedToFollow, $user)
                );

            }
        };

        $teamMembers = ['teamMembers' => function ($query) use ($user) {

            /**
             *  Eager load the team members who have joined each store.
             *
             *  Exclude the current user since we join them to the store before sending the notification
             *  if they have accepted the invitation. This avoids sending them a notification as well.
             */
            $query->joinedTeam()->where('user_id', '!=', $user->id);

        }];

        //  Check if we are accepting on declining invitations on multiple stores
        if(count($storesInvitedToFollow)) {

            //  Foreach store
            foreach($storesInvitedToFollow as $storeInvitedToFollow) {

                //  Send notifications to team members of this store
                $sendNotifications($storeInvitedToFollow, $user);

            }

        //  Check if are accepting on declining invitations on this store
        }else{

            /**
             *  @var Store $store
             */
            $storeInvitedToFollow = $this->model->load($teamMembers);

            //  Send notifications to team members of this store
            $sendNotifications($storeInvitedToFollow, $user);

        }

    }

    /**
     * Get the user and store association
     */
    public function getUserStoreAssociation($user)
    {
        return UserStoreAssociation::where('store_id', $this->model->id)
                ->where('user_id', $user->id)
                ->first();
    }

    /**
     * Update the user's invitation state
     */
    public function updateInvitationToFollowStatus($state)
    {
        return DB::table('user_store_association')
            ->where('store_id', $this->model->id)
            ->where('user_id', auth()->user()->id)
            ->update([
                'follower_status' => $state
            ]);
    }

    /**
     *  Invite followers to this store
     *
     *  @return array
     */
    public function inviteFollowers()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        /**
         *  Get the specified mobile numbers. Make sure that the specified mobile numbers are
         *  in array format since the request supports JSON encoded data i.e string data
         */
        $mobileNumbers = is_string($mobileNumbers = request()->input('mobile_numbers')) ? json_decode($mobileNumbers) : $mobileNumbers;

        //  Get the users that are assigned to this store as followers that match the specified mobile numbers
        $assignedUsers = $store->followers()->whereIn('users.mobile_number', $mobileNumbers)->get();

        //  Get the users that are not assigned to this store as followers that match the specified mobile numbers
        $notAssignedUsers = User::whereIn('mobile_number', $mobileNumbers)->whereDoesntHave('storesAsFollower', function (Builder $query) {

            //  Query for users that are not followers on this specific store
            $query->where('user_store_association.store_id', $this->model->id);

        })->get();

        /**
         *  Get the guest users that are assigned to this store that match the specified mobile numbers.
         *  These guest users are non-existing users that are yet still to create their user accounts.
         */
        $mobileNumbersThatDontMatchAnyUserButInvited = DB::table('user_store_association')
            ->whereIn('mobile_number', $mobileNumbers)
            ->where('follower_status', 'Invited')
            ->where('store_id', $this->model->id)
            ->get();

        //  Merge the users, whether assigned as a follower or not
        $users = collect($assignedUsers)->merge($notAssignedUsers);

        /**
         *  Get the mobile numbers that don't match any user that was retrieved.
         *  These are mobile numbers of people who have not been invited whether
         *  as an existing user or guest (non-existing user)
         */
        $mobileNumbersThatDontMatchAnyUser = array_diff($mobileNumbers, array_merge(
            collect($mobileNumbersThatDontMatchAnyUserButInvited)->pluck('mobile_number')->toArray(),
            $users->pluck('mobile_number')->map(fn($mobileNumber) => $mobileNumber->withExtension)->toArray()
        ));

        /**
         *  If we supplied one or more numbers and retrieved users
         *  that have not yet been assigned as followers, then
         *  we can invite these people by their user accounts.
         */
        if( $notAssignedUsers->count() > 0 ) {

            //  Invite existing users to this store
            $this->addFollowers($notAssignedUsers);

        }

        /**
         *  If we supplied one or more numbers that did not retrieve any
         *  users, then we can invite these people by using their mobile
         *  numbers.
         */
        if($mobileNumbersThatDontMatchAnyUser) {

            //  Invite non-existent users to this store
            $this->addFollowersByMobileNumbers($mobileNumbersThatDontMatchAnyUser);

        }

        /**
         *  If the mobile numbers specified match the assigned users,
         *  then this means that every user has already been invited.
         */
        if(count($mobileNumbers) === ($totalAssignedUsers = $assignedUsers->count())) {

            $message = $assignedUsers->pluck('first_name')->join(', ', ' and ');

            if($totalAssignedUsers == 1) {

                $message .= ' has';

            }elseif($totalAssignedUsers <= 3) {

                $message .= ' have';

            }else{

                $message = 'These people have';

            }

            $message =  "$message already been invited";

        /**
         *  If the mobile numbers specified are more than the users
         *  that have not been invited, then this means that some
         *  users were either invited or don't have user accounts
         */
        }else {

            $message = 'Invitations sent successfully';

        }

        //  Function to transform data of existing user
        $transformExistingUser = function(User $user) {
            return [
                'name' => $user->name,
                'mobile_number' => $user->mobile_number,
                'follower_status' => $user->user_store_association->follower_status ?? 'Invited',
            ];
        };

        //  Function to transform data of non-existing user
        $transformNonExistingUser = function($mobileNumber) {
            return [
                'mobile_number' => $this->convertToMobileNumberFormat($mobileNumber),
                'follower_status' => 'Invited'
            ];
        };

        $invitations = [

            //  Total invited
            'total_invited' => ($notAssignedUsers->count() + count($mobileNumbersThatDontMatchAnyUser)),
            'total_already_invited' => ($assignedUsers->count() + count($mobileNumbersThatDontMatchAnyUserButInvited)),

            //  Information about existing users who are invited
            'existing_users_invited' => [
                'total' => $notAssignedUsers->count(),
                'existing_users' => collect($notAssignedUsers)->map(function($user) use ($transformExistingUser) {
                    return $transformExistingUser($user);
                })->toArray()
            ],

            //  Information about existing users who were already invited
            'existing_users_already_invited' => [
                'total' => $assignedUsers->count(),
                'existing_users' => collect($assignedUsers)->map(function($user) use ($transformExistingUser) {
                    return $transformExistingUser($user);
                })->toArray()
            ],

            //  Information about non-existing users who are invited
            'non_existing_users_invited' => [
                'total' => count($mobileNumbersThatDontMatchAnyUser),
                'non_existing_users' => collect($mobileNumbersThatDontMatchAnyUser)->map(function($mobileNumber) use ($transformNonExistingUser) {
                    return $transformNonExistingUser($mobileNumber);
                })->values()->toArray()
            ],

            //  Information about non-existing users who are invited
            'non_existing_users_already_invited' => [
                'total' => count($mobileNumbersThatDontMatchAnyUserButInvited),
                'non_existing_users' => collect($mobileNumbersThatDontMatchAnyUserButInvited)->map(function($record) use ($transformNonExistingUser) {
                    return $transformNonExistingUser($record->mobile_number);
                })->values()->toArray()
            ],

        ];

        return [
            'message' => $message,
            'invitations' => $invitations
        ];
    }

    /**
     *  Get the user with their user and store association information
     *
     *  @param User|int $user
     *  @return User|null
     */
    public function getStoreUser($user)
    {
        if($user instanceof Model) {

            $userId = $user->id;

        }elseif(is_int($user)) {

            $userId = $user;

        }

        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Return the user with their user and store association information
        return $store->users()->wherePivot('user_id', $userId)->first();
    }

    /**
     *  Show user following status
     *
     *  @return array
     */
    public function showFollowing(User $user)
    {
        return [
            'following' => $this->checkIfUserIsFollowing($user)
        ];
    }

    /**
     *  Update user following status
     *
     *  @return array
     */
    public function updateFollowing(Request $request, User $user)
    {
        //  Get the follower status (if provided)
        $followerStatus = $request->input('status');

        //  Update the user's following to this store
        $this->updateFollowers($user, $followerStatus);

        //  If the user is following on this store
        if( $this->checkIfUserIsFollowing($user) ) {

            return [
                'message' => 'You are following',
                'follower_status' => 'Following',
                'following' => true
            ];

        //  If the user is not following on this store
        }else{

            return [
                'message' => 'You are not following',
                'follower_status' => 'Unfollowed',
                'following' => false
            ];

        }
    }

    /**
     *  Check if the given user is following this store
     *
     *  @param User $user
     *  @return bool
     */
    public function checkIfUserIsFollowing(User $user)
    {
        //  Check if the user is currently listed as a follower or unfollower of this store
        if( $storeUser = $this->getStoreUser($user) ) {

            //  Return true if the user is following this store
            return $storeUser->user_store_association->follower_status == 'Following';

        }

        //  Return false that the user is not following this store
        return false;
    }

    /**
     *  Check invitations to follow this store
     *
     *  @return array
     */
    public function checkInvitationsToFollow()
    {
        $invitations = DB::table('user_store_association')
                        ->where('user_id', auth()->user()->id)
                        ->where('follower_status', 'Invited')
                        ->get();

        $totalInvitations = count($invitations);
        $hasInvitations = $totalInvitations > 0;

        return [
            'has_invitations' => $hasInvitations,
            'total_invitations' => $totalInvitations,
        ];
    }

    /**
     *  Add a single or multiple users as a follower to this store
     *
     *  @param Collection|\App\Models\User[] $users
     *  @param string|null $followerStatus e.g Following, Unfollowed, Invited
     *  @return void
     */
    public function addFollowers($users, $followerStatus = null)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        if(($user = $users) instanceof User) {

            //  Convert Model to collection
            $users = collect([$user]);

        }elseif(is_array($users)) {

            //  Convert array to collection
            $users = collect($users);

        }

        //  Get the user ids from the collection
        $userIds = $users->pluck('id');

        //  If we have user ids
        if($userIds->count()) {

            //  Set the follower status to "Invited" if no value is indicated
            if( empty($followerStatus) ) $followerStatus = 'Invited';

            //  Invite the specified users
            $store->followers()->attach($userIds, [
                'invited_to_follow_by_user_id' => auth()->user()->id,
                'follower_status' => $followerStatus,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            //  Notify the users that they have been invited to follow this store
            //  change to Notification::send() instead of Notification::sendNow() so that this is queued
            Notification::sendNow(
                $users,
                new InvitationToFollowStoreCreated($store, auth()->user())
            );

        }
    }

    /**
     *  Add a non-existent user on this store as a follower
     *  by using their mobile number. This allows us to
     *  invite people to be followers by using their
     *  mobile number even while yet they do not
     *  have user accounts.
     *
     *  @param int | array<int> $mobileNumbers
     *  @return void
     */
    public function addFollowersByMobileNumbers($mobileNumbers = [])
    {
        if(is_int($mobileNumber = $mobileNumbers)) {

            $mobileNumbers = [$mobileNumber];

        }

        /**
         *  @var Store $store
         */
        $store = $this->model;

        $data = collect($mobileNumbers)->map(function($mobileNumber) use($store) {
            return [
                'invited_to_follow_by_user_id' => auth()->user()->id,
                'mobile_number' => $mobileNumber,
                'follower_status' => 'Invited',
                'store_id' => $store->id,
                'created_at' => now(),
                'updated_at' => now(),

                //  Set the user id equal to the guest user id because the user does not yet exist.
                'user_id' => $this->userRepository()->getGuestUserId(),
            ];
        })->toArray();

        //  Invite the specified users
        DB::table('user_store_association')->insert($data);
    }

    /**
     *  Update a single or multiple users as a follower to this store
     *
     *  @param Collection|\App\Models\User[] $users
     *  @param string|null $followerStatus e.g Following, Unfollowed, Invited
     *  @return void
     */
    public function updateFollowers($users, $followerStatus = null)
    {
        if(($user = $users) instanceof User) {

            //  Convert Model to collection
            $users = collect([$user]);

        }elseif(is_array($users)) {

            //  Convert array to collection
            $users = collect($users);

        }

        if( $users->count() ) {

            /**
             *  @var Store $store
             */
            $store = $this->model;

            $store->load(['teamMembers' => function($query) {

                //  Load the team members who have joined that store
                $query->joinedTeam();

            }]);

            /**
             *  Foreach user (Super Admin should be able to specify multiple users to immediately follow a store),
             *  however if this is a specific user that wants to follow or unfollow, then we expect only one user
             *  in this list of user ids.
             */
            foreach($users as $user) {

                //  Check if the follower status is provided
                if( $followerStatus == null ) {

                    //  Check if the user is currently a listed follower or unfollower of this store
                    $storeUser = $this->getStoreUser($user->id);

                    //  Otherwise toggle the existing user following status
                    $followerStatus = $storeUser->user_store_association->follower_status;
                    $followerStatus = $followerStatus == 'Following' ? 'Unfollowed' : 'Following';

                }

                $store->followers()->updateExistingPivot($user->id, [
                    'follower_status' => $followerStatus,
                    'updated_at' => now(),
                ]);

                if($followerStatus == 'Following') {

                    //  Notify the team members that this user is following this store
                    //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                    Notification::sendNow(
                        $store->teamMembers,
                        new FollowingStore($store, $user)
                    );

                }else{

                    //  Notify the team members that this user has unfollowed this store
                    //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                    Notification::sendNow(
                        $store->teamMembers,
                        new UnfollowedStore($store, $user)
                    );

                }

            }

        }
    }















    /**
     *  Show the store team member filters
     *
     *  @return UserRepository
     */
    public function showTeamMemberFilters()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        $filters = collect(UserStoreAssociation::TEAM_MEMBER_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'all',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Joined',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Left',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($store) {

            if(strtolower($filter) == 'all') {

                $total = $store->teamMembers()->count();

            }else if(strtolower($filter) == 'joined') {

                $total = $store->teamMembers()->joinedTeam()->count();

            }else if(strtolower($filter) == 'left') {

                $total = $store->teamMembers()->leftTeam()->count();

            }else if(strtolower($filter) == 'invited') {

                $total = $store->teamMembers()->invitedToJoinTeam()->count();

            }else if(strtolower($filter) == 'declined') {

                $total = $store->teamMembers()->declinedToJoinTeam()->count();

            }

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the store team members. Either
     *  show all the team members or those
     *  that are invited.
     *
     * @return UserRepository
     */
    public function showTeamMembers()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        $users = $store->teamMembers();
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Order by the last seen date and time
        $users = $users->orderByPivot('user_store_association.last_seen_at', 'DESC');

        //  If we have the filter
        if( !empty($filter) ) {

            if(strtolower($filter) == 'joined') {

                $users = $users->joinedTeam();

            }else if(strtolower($filter) == 'left') {

                $users = $users->leftTeam();

            }else if(strtolower($filter) == 'invited') {

                $users = $users->invitedToJoinTeam();

            }else if(strtolower($filter) == 'declined') {

                $users = $users->declinedToJoinTeam();

            }

        }

        return $this->userRepository()->setModel($users)->get();
    }

    /**
     *  Show the store team member
     *
     *  @return UserResource
     */
    public function showTeamMember(User $user)
    {
        return $this->userRepository()->setModel($user);
    }

    /**
     *  Get user permissions for this store model instance
     *
     *  @return array
     */
    public function showTeamMemberPermissions(User $user)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        /**
         *  @var User $user
         */
        $user = $store->teamMembers()->where('user_store_association.user_id', $user->id)->first();

        if( $user ) {

            $teamMemberRole = $user->user_store_association->team_member_role;
            $teamMemberPermissions = $user->user_store_association->team_member_permissions;

            return [
                'team_member_role' => $teamMemberRole,
                'team_member_permissions' => $this->extractPermissions($teamMemberPermissions),
                'message' => 'You are a team member of this store'
            ];

        }else{

            return [
                'team_member_role' => null,
                'team_member_permissions' => [],
                'message' => 'You are not a team member of this store'
            ];

        }
    }

    /**
     *  Update user permissions for this store model instance
     *
     *  @return array
     */
    public function updateTeamMemberPermissions(User $user)
    {
        //  Deny the action of modifying your own permissions
        if( $user->id === auth()->user()->id ) throw new CannotModifyOwnPermissionsException;

        //  Add user's permissions to this store
        $this->updateTeamMembersByUserIds($user);

        return ['message' => 'Permissions updated successfully'];
    }

    /**
     *  Check invitations to join store team members
     *
     *  @return array
     */
    public function checkInvitationsToJoinTeam()
    {
        $invitations = DB::table('user_store_association')->where('user_id', auth()->user()->id)
                        ->where('team_member_status', 'Invited')
                        ->get();

        $totalInvitations = count($invitations);
        $hasInvitations = $totalInvitations > 0;

        return [
            'has_invitations' => $hasInvitations,
            'total_invitations' => $totalInvitations,
        ];
    }

    /**
     *  Accept all invitations to join store teams
     */
    public function acceptAllInvitationsToJoinTeam()
    {
        //  Get the stores that the user has been invited to join
        $stores = Store::with(['teamMembers' => function($query) {

            //  Get each store with the team members who have joined that store
            $query->joinedTeam();

        }])->whereHas('teamMembers', function($query) {
            $query->where([
                'user_id' => auth()->user()->id,
                'team_member_status' => 'Invited',
            ]);
        })->get();

        //  Accept the invitations
        DB::table('user_store_association')
            ->where('team_member_status', 'Invited')
            ->where('user_id', auth()->user()->id)
            ->update([
                'team_member_status' => 'Joined',
                'team_member_join_code' => null
            ]);

        //  Notify the team members of each store on the user's decision to accept the invitation
        $this->notifyTeamMembersOnUserResponseToJoinTeamInvitation(InvitationResponse::Accepted, $stores);

        return ['message' => 'Invitations accepted successfully'];
    }

    /**
     *  Decline all invitations to join store teams
     */
    public function declineAllInvitationsToJoinTeam()
    {
        //  Get the stores that the user has been invited to join
        $stores = Store::with(['teamMembers' => function($query) {

            //  Get each store with the team members who have joined that store
            $query->joinedTeam();

        }])->whereHas('teamMembers', function($query) {

            //  Check if the user has been invited to join this store team
            $query->where([
                'user_id' => auth()->user()->id,
                'team_member_status' => 'Invited',
            ]);

        })->get();

        //  Decline the invitations
        DB::table('user_store_association')
            ->where('team_member_status', 'Invited')
            ->where('user_id', auth()->user()->id)
            ->update([
                'team_member_status' => 'Declined',
                'team_member_join_code' => null
            ]);

        //  Notify the team members of each store on the user's decision to decline the invitation
        $this->notifyTeamMembersOnUserResponseToJoinTeamInvitation(InvitationResponse::Declined, $stores);

        return ['message' => 'Invitations declined successfully'];
    }

    /**
     *  Accept invitation to join store
     */
    public function acceptInvitationToJoinTeam()
    {
        /**
         *  @var User $user
         */
        $user = auth()->user();

        $userStoreAssociation = $this->getUserStoreAssociation($user);

        if($userStoreAssociation->is_team_member_who_is_invited) {

            //  Accept invitation
            $this->updateInvitationToJoinTeamStatus('Joined');

            //  Notify the team members of each store on the user's decision to accept the invitation
            $this->notifyTeamMembersOnUserResponseToJoinTeamInvitation(InvitationResponse::Accepted);

            return ['message' => 'Invitation accepted successfully'];

        }else{

            if($userStoreAssociation->is_team_member_who_has_joined) {

                throw new InvitationAlreadyAcceptedException;

            }else if($userStoreAssociation->is_team_member_who_has_declined) {

                throw new InvitationAlreadyDeclinedException('This invitation has already been declined and cannot be accepted. Request the store manager to resend the invitation again.');

            }

        }
    }

    /**
     *  Decline invitation to join store
     */
    public function declineInvitationToJoinTeam()
    {
        /**
         *  @var User $user
         */
        $user = auth()->user();

        $userStoreAssociation = $this->getUserStoreAssociation($user);

        if($userStoreAssociation) {

            if($userStoreAssociation->is_team_member_who_is_invited) {

                //  Decline invitation
                $this->updateInvitationToJoinTeamStatus('Declined');

                //  Notify the team members of each store on the user's decision to decline the invitation
                $this->notifyTeamMembersOnUserResponseToJoinTeamInvitation(InvitationResponse::Declined);

                return ['message' => 'Invitation declined successfully'];

            }else{

                if($userStoreAssociation->is_team_member_who_has_joined) {

                    throw new InvitationAlreadyAcceptedException('This invitation has already been accepted and cannot be declined.');

                }else if($userStoreAssociation->is_team_member_who_has_declined) {

                    throw new InvitationAlreadyDeclinedException;

                }

            }

        }else{

            throw new InvalidInvitationException('You have not been invited to this store');

        }
    }

    /**
     *  Notify the team members on the user's decision to accept or decline the invitation to join team
     *
     *  @param InvitationResponse $invitationResponse - Indication of whether the user has accepted or declined the invitation
     *  @param Collection|\App\Models\Store[] $storesInvitedToJoinTeam
     */
    public function notifyTeamMembersOnUserResponseToJoinTeamInvitation(InvitationResponse $invitationResponse, $storesInvitedToJoinTeam = [])
    {
        /**
         *  @var User $user
         */
        $user = auth()->user();

        //  Method to send the notifications
        $sendNotifications = function($storeInvitedToJoinTeam, $user) use ($invitationResponse) {

            if($invitationResponse == InvitationResponse::Accepted) {

                //  Notify the team members that this user has accepted the invitation to join this store team
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $storeInvitedToJoinTeam->teamMembers,
                    new InvitationToJoinStoreTeamAccepted($storeInvitedToJoinTeam, $user)
                );

            }else{

                //  Notify the team members that this user has declined the invitation to join this store team
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $storeInvitedToJoinTeam->teamMembers,
                    new InvitationToJoinStoreTeamDeclined($storeInvitedToJoinTeam, $user)
                );

            }
        };

        $teamMembers = ['teamMembers' => function ($query) use ($user) {

            /**
             *  Eager load the team members who have joined each store.
             *
             *  Exclude the current user since we join them to the store before sending the notification
             *  if they have accepted the invitation. This avoids sending them a notification as well.
             */
            $query->joinedTeam()->where('user_id', '!=', $user->id);

        }];

        //  Check if we are accepting on declining invitations on multiple stores
        if(count($storesInvitedToJoinTeam)) {

            //  Foreach store
            foreach($storesInvitedToJoinTeam as $storeInvitedToJoinTeam) {

                //  Send notifications to team members of this store
                $sendNotifications($storeInvitedToJoinTeam, $user);

            }

        //  Check if are accepting on declining invitations on this store
        }else{

            /**
             *  @var Store $store
             */
            $storeInvitedToJoinTeam = $this->model->load($teamMembers);

            //  Send notifications to team members of this store
            $sendNotifications($storeInvitedToJoinTeam, $user);

        }

    }

    /**
     * Update the user's invitation state
     */
    public function updateInvitationToJoinTeamStatus($state)
    {
        return DB::table('user_store_association')
            ->where('store_id', $this->model->id)
            ->where('user_id', auth()->user()->id)->update([
                'team_member_status' => $state
            ]);
    }








    /**
     *  Show the store customer filters
     *
     *  @return UserRepository
     */
    public function showCustomerFilters()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        $filters = collect(UserStoreAssociation::CUSTOMER_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'all',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Joined',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Left',
         *          'total' => 1000,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($store) {

            if(strtolower($filter) == 'all') {

                $total = $store->customers()->count();

            }else if(strtolower($filter) == 'loyal') {

                $total = $store->customers()->where('total_orders_requested', '>=', '5')->count();

            }

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the store customers. Either
     *  show all the customers or those
     *  that are invited.
     *
     * @return UserRepository
     */
    public function showCustomers()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        $users = $store->customers();
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Order by the last seen date and time
        $users = $users->orderByPivot('user_store_association.last_seen_at', 'DESC');

        //  If we have the filter
        if( !empty($filter) ) {

            if(strtolower($filter) == 'loyal') {

                $users = $users->customers()->where('total_orders_requested', '>=', '5');

            }

        }

        return $this->userRepository()->setModel($users)->get();
    }

    /**
     *  Show the store customer
     *
     *  @return UserResource
     */
    public function showCustomer(User $user)
    {
        return $this->userRepository()->setModel($user);
    }


    /**
     *  Add store to friend groups
     *
     *  @return array
     */
     public function addStoreToFriendGroups()
     {
        /**
         *  @var Store $store
         */
        $store = $this->model;

         /**
          *  @var User $user
          */
         $user = auth()->user();

        /**
         *  Get the specified friend group ids. Make sure that the specified friend group ids
         *  are in array format since the request supports JSON encoded data i.e string data
         */
        $friendGroupIds = is_string($friendGroupIds = request()->input('friend_group_ids')) ? json_decode($friendGroupIds) : $friendGroupIds;

        //  Get the matching friend groups with their associated users
        $friendGroups = $user->friendGroups()->whereDoesntHave('stores', function (Builder $query) use ($store) {

            /**
             *  Query the friend group stores that do not match this store.
             *  Must be a friend group where this store has not been added.
             */
            $query->where('stores.id', $store->id);

        })->with(['users' => function($query) use ($user) {

            //  Exclude the current authenticated user
            return $query->where('users.id', '!=', $user->id);

        }])->whereIn('friend_groups.id', $friendGroupIds)->get();

        //  Get the matching friend group ids
        $friendGroupIds = $friendGroups->pluck('id');

        /**
         *  Note: sync() and syncWithoutDetaching() both don't have a parameter for additional
         *  values, you have to pass the additional values as an array with the ids e.g
         *
         *  [
         *      "1" => ['added_by_user_id' => "1"],
         *      "2" => ['added_by_user_id' => "1"],
         *      "3" => ['added_by_user_id' => "1"]
         *      ... e.t.c
         *  ]
         *
         *  ['added_by_user_id' => "1"] in this case represents the pivot data
         *
         *  Reference: https://stackoverflow.com/questions/54944227/laravels-syncwithoutdetaching-and-additional-data
         */
        $records = collect($friendGroupIds)->mapWithKeys(function($friendGroupId) {
            return [$friendGroupId => ['added_by_user_id' => auth()->user()->id]];
        })->toArray();

        if( count($records) ) {

            //  Add the friend groups to this store
            $store->friendGroups()->syncWithoutDetaching($records);

            /**
             *  Add the friend group users to this store
             *
             *  This is important since it allows us to query stores related to friend groups while being able to
             *  access the user and store association pivot so that we can acquire more information
             *  about the user's relationship with each store e.g whether they are a team member
             *  and have a subscription on those stores.
             *
             *  When adding user's to friend groups, we make sure that when the user and store association
             *  pivot relationship does not exist, we create it. This ensures that we can always access
             *  the pivot information whenever we run the "$user->stores()->whereHas('friendGroups')"
             *  method.
             */
            $friendGroupUserIds = User::whereHas('friendGroups', function (Builder $query) use ($friendGroupIds) {

                //  Query the friend groups that match the given friend group id
                $query->whereIn('friend_group_id', $friendGroupIds);

            })->pluck('users.id');

            $store->users()->syncWithoutDetaching($friendGroupUserIds);

            //  Count the total friend groups
            $totalFriendGroups = count($records);

            //dd($friendGroups->pluck('name'));

            //  Foreach friend group
            foreach($friendGroups as $friendGroup) {

                //  Notify the friend group users that the store has been added
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $friendGroup->users,
                    new FriendGroupStoreAdded($friendGroup, $store, $user)
                );

            }

            return [
                'message' => 'Store added to '. $totalFriendGroups . ($totalFriendGroups == 1 ? ' group': ' groups')
            ];

        }else{

            return [
                'message' => 'Store was not added to any group'
            ];

        }
    }


    /**
     *  Remove store from friend group
     *
     *  @return array
     */

     public function removeStoreFromFriendGroup()
     {
        /**
         *  @var Store $store
         */
        $store = $this->model;

         /**
          *  @var User $user
          */
         $user = auth()->user();

         /**
          *  Get the specified friend group ids. Make sure that the specified friend group ids
          *  are in array format since the request supports JSON encoded data i.e string data
          */
        $friendGroupIds = is_string($friendGroupIds = request()->input('friend_group_ids')) ? json_decode($friendGroupIds) : $friendGroupIds;

        //  Get the matching friend groups with their associated users
        $friendGroups = $user->friendGroups()->whereHas('stores', function (Builder $query) use ($store) {

            /**
             *  Query the friend group stores that match this store.
             *  Must be a friend group where this store has been added.
             */
            $query->where('stores.id', $store->id);

        })->with(['users' => function($query) use ($user) {

            //  Exclude the current authenticated user
            return $query->where('users.id', '!=', $user->id);

        }])->whereIn('friend_groups.id', $friendGroupIds)->get();

        //  Remove the store from the friends group
        DB::table('friend_group_store_association')
            ->whereIn('friend_group_id', $friendGroups->pluck('id'))
            ->where('store_id', $store->id)
            ->delete();

        //  Foreach friend group
        foreach($friendGroups as $friendGroup) {

            //  Notify the friend group users that the store has been removed
            //  change to Notification::send() instead of Notification::sendNow() so that this is queued
            Notification::sendNow(
                $friendGroup->users,
                new FriendGroupStoreRemoved($friendGroup, $store, $user)
            );

        }

        return [
            'message' => 'Store removed from '. (count($friendGroupIds) == 1 ? 'group' : 'groups')
        ];
    }

    /**
     *  Add store to brand stores
     *
     *  @return array
     */
    public function addStoreToBrandStores()
    {
        $this->update([
            'is_brand_store' => '1',
            'verified' => '1'
        ]);

        return [
            'message' => 'Store added to brand stores',
            'is_brand_store' => $this->model->is_brand_store
        ];
    }

    /**
     *  Remove store from brand stores
     *
     *  @return array
     */
    public function removeStoreFromBrandStores()
    {
        $this->update([
            /**
             *  Decide whether to disable the "verified" flag based on whether this store
             *  also happens to be an influencer store or not. If this store is still an
             *  influencer store then leave it still as verified even if its no longer
             *  recognised as a brand store
             */
            'verified' => $this->model->is_influencer_store,
            'is_brand_store' => '0',
        ]);

        return [
            'message' => 'Store removed from brand stores',
            'is_brand_store' => $this->model->is_brand_store
        ];
    }

    /**
     *  Add or remove store from brand stores
     *
     *  @return array
     */
    public function addOrRemoveStoreFromBrandStores()
    {
        if($this->model->is_brand_store) {

            return $this->removeStoreFromBrandStores();

        }else{

            return $this->addStoreToBrandStores();
        }
    }

    /**
     *  Add store to brand stores
     *
     *  @return array
     */
    public function addStoreToInfluencerStores()
    {
        $this->update([
            'is_influencer_store' => '1',
            'verified' => '1'
        ]);

        return [
            'message' => 'Store added to influencer stores',
            'is_influencer_store' => $this->model->is_influencer_store
        ];
    }

    /**
     *  Remove store from influencer stores
     *
     *  @return array
     */
    public function removeStoreFromInfluencerStores()
    {
        $this->update([
            /**
             *  Decide whether to disable the "verified" flag based on whether this store
             *  also happens to be an influencer store or not. If this store is still an
             *  influencer store then leave it still as verified even if its no longer
             *  recognised as a brand store
             */
            'verified' => $this->model->is_brand_store,
            'is_influencer_store' => '0',
        ]);

        return [
            'message' => 'Store removed from influencer stores',
            'is_influencer_store' => $this->model->is_influencer_store
        ];
    }

    /**
     *  Add or remove store from influencer stores
     *
     *  @return array
     */
    public function addOrRemoveStoreFromInfluencerStores()
    {
        if($this->model->is_influencer_store) {

            return $this->removeStoreFromInfluencerStores();

        }else{

            return $this->addStoreToInfluencerStores();
        }
    }


    /**
     *  Add store to assigned stores
     *
     *  @return array
     */
    public function addStoreToAssignedStores()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        /**
         *  @var UserStoreAssociation|null $store
         */
        $userStoreAssociation = $store->authUserStoreAssociation;

        if($userStoreAssociation) {

            $userStoreAssociation->update([
                'is_assigned' => true
            ]);

        }else{

            UserStoreAssociation::create([
                'is_assigned' => true,
                'store_id' => $store->id,
                'user_id' => auth()->user()->id,
            ]);

        }

        $this->resetAssignedStorePositions();

        return [
            'message' => 'Store added'
        ];
    }

    /**
     *  Remove store from assigned stores
     *
     *  @return array
     */
    public function removeStoreFromAssignedStores()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        /**
         *  @var UserStoreAssociation|null $store
         */
        $userStoreAssociation = $store->authUserStoreAssociation;

        if($userStoreAssociation) {

            $userStoreAssociation->update([
                'is_assigned' => false
            ]);

        }else{

            UserStoreAssociation::create([
                'is_assigned' => false,
                'store_id' => $store->id,
                'user_id' => auth()->user()->id,
            ]);

        }

        $this->resetAssignedStorePositions();

        return [
            'message' => 'Store removed'
        ];
    }

    /**
     *  Remove store from assigned stores
     *
     *  @return array
     */
    public function addOrRemoveStoreFromAssignedStores()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        $message = 'Store added';

        /**
         *  @var UserStoreAssociation|null $store
         */
        $userStoreAssociation = $store->authUserStoreAssociation;

        if($userStoreAssociation) {

            $userStoreAssociation->update([
                'is_assigned' => $userStoreAssociation->is_assigned ? false : true
            ]);

            if($userStoreAssociation->is_assigned) {
                $message = 'Store removed';
            }

        }else{

            UserStoreAssociation::create([
                'is_assigned' => true,
                'store_id' => $store->id,
                'user_id' => auth()->user()->id,
            ]);

        }

        $this->resetAssignedStorePositions();

        return [
            'message' => $message
        ];
    }

    public function resetAssignedStorePositions()
    {
        /**
         *  @var User $user
         */
        $user = auth()->user();

        $storeIds = $user->storesAsAssigned()->orderByPivot('assigned_position', 'ASC')->pluck('store_id');

        if(count($storeIds)) {

            // Re-arrange the stores
            $this->updateAssignedStoreArrangement($storeIds);

        }
    }

    /**
     *  Update the auth assigned store arrangement
     *
     *  @param \Illuminate\Http\Request|array $data
     *  @return array
     */
    public function updateAssignedStoreArrangement($data)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        /**
         *  @var User $user
         */
        $user = auth()->user();

        // Retrieve all the user assigned store
        $stores = $user->storesAsAssigned()->orderByPivot('assigned_position', 'ASC');

        /**
         *  Create a map of product IDs to their original positions.
         *  The keys represent the product IDs while the values
         *  represent the original positions of the products.
         *
         *  e.g $originalProductPositions = [
         *      4 => 1,
         *      3 => 2,
         *      2 => 3,
         *      1 => 4
         *  ];
         */
        $originalStorePositions = $stores->pluck('assigned_position', 'store_id');

        /**
         *  Get the store arrangement from the request or array of data that
         *  has been provided on the method parameter. This represents the new
         *  arrangement of stores based on their store ids.
         *
         *  $arrangement = ["2", "3"];
         *
         *  This means that the store with id "2" must be in position 1 then
         *  followed by the store with id "3" in position 2. Other stores
         *  must be in their original positions but after these two stores.
         */
        if(($request = $data) instanceof Request) {
            $arrangement = $request->input('arrangement');
        }else{
            $arrangement = $data;
        }

        //  Make sure that these are stores that belong to this store.
        $arrangement = collect($arrangement)->filter(function ($storeId) use ($originalStorePositions) {
            return collect($originalStorePositions)->keys()->contains($storeId);
        })->toArray();

        /**
         *  Use $arrangement to create an array of updated positions for each store
         *  that has been moved. Basically return the store id as the key and the
         *  new position as the value.
         *
         *  e.g $movedProductPositions = [
         *     2 => 1,
         *     3 => 2
         *  ];
         */
        $movedStorePositions = collect($arrangement)->mapWithKeys(function ($storeId, $newPosition) use ($originalStorePositions) {
            return [$storeId => ($newPosition + 1)];
        })->toArray();

        /**
         *  Create an array of stores that have not been moved. Set their
         *  positions to the original positions that they had before but
         *  after the stores that have been moved. Return the same
         *  store id as the key and the updated position as the
         *  value.
         *
         *  e.g $adjustedOriginalStorePositions = [
         *      4 => 3,
         *      1 => 4
         *  ];
         */
        $adjustedOriginalStorePositions = $originalStorePositions->except(collect($movedStorePositions)->keys())->keys()->mapWithKeys(function ($id, $index) use ($movedStorePositions) {
            return [$id => count($movedStorePositions) + $index + 1];
        })->toArray();

        /**
         *  Combine the two arrays of updated positions for each store that has been
         *  moved and the array of stores that have not been moved. Return the same
         *  store id as the key and the updated position as the value.
         *
         *  combine:
         *
         *  $movedStorePositions = [
         *     2 => 1,
         *     3 => 2
         *  ];
         *
         *  and:
         *
         *  $adjustedOriginalStorePositions = [
         *      4 => 3,
         *      1 => 4
         *  ];
         *
         *  to get:
         *
         *  $storePositions = [
         *      2 => 1,
         *      3 => 2,
         *      4 => 3,
         *      1 => 4
         *  ];
         */
        $storePositions = $movedStorePositions + $adjustedOriginalStorePositions;

        // Update the positions of all stores in the database using one query
        DB::table('user_store_association')
            ->where('user_id', auth()->user()->id)
            ->whereIn('store_id', array_keys($storePositions))
            ->update(['assigned_position' => DB::raw('CASE store_id ' . implode(' ', array_map(function ($storeId, $position) {
                return 'WHEN ' . $storeId . ' THEN ' . $position . ' ';
            }, array_keys($storePositions), $storePositions)) . 'END')]);


        return ['message' => 'Stores have been updated'];
    }



    /**
     *  Invite team members to this store
     *
     *  @return array
     */

    public function inviteTeamMembers()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        /**
         *  Get the specified mobile numbers. Make sure that the specified mobile numbers are
         *  in array format since the request supports JSON encoded data i.e string data
         */
        $mobileNumbers = is_string($mobileNumbers = request()->input('mobile_numbers')) ? json_decode($mobileNumbers) : $mobileNumbers;

        //  Get the users that are assigned to this store as team members that match the specified mobile numbers
        $assignedUsers = $store->teamMembers()->whereIn('users.mobile_number', $mobileNumbers)->get();

        //  Get the users that are not assigned to this store as team members that match the specified mobile numbers
        $notAssignedUsers = User::whereIn('mobile_number', $mobileNumbers)->whereDoesntHave('storesAsTeamMember', function (Builder $query) {

            //  Query for users that are not team members on this specific store
            $query->where('user_store_association.store_id', $this->model->id);

        })->get();

        /**
         *  Get the guest users that are assigned to this store that match the specified mobile numbers.
         *  These guest users are non-existing users that are yet still to create their user accounts.
         */
        $mobileNumbersThatDontMatchAnyUserButInvited = DB::table('user_store_association')
            ->whereIn('mobile_number', $mobileNumbers)
            ->where('team_member_status', 'Invited')
            ->where('store_id', $this->model->id)
            ->get();

        //  Merge the users, whether assigned as a team member or not
        $users = collect($assignedUsers)->merge($notAssignedUsers);

        /**
         *  Get the mobile numbers that don't match any user that was retrieved.
         *  These are mobile numbers of people who have not been invited whether
         *  as an existing user or guest (non-existing user)
         */
        $mobileNumbersThatDontMatchAnyUser = array_diff($mobileNumbers, array_merge(
            collect($mobileNumbersThatDontMatchAnyUserButInvited)->pluck('mobile_number')->toArray(),
            $users->pluck('mobile_number')->map(fn($mobileNumber) => $mobileNumber->withExtension)->toArray()
        ));

        /**
         *  If we supplied one or more numbers and retrieved users
         *  that have not yet been assigned as team members, then
         *  we can invite these people by their user accounts.
         */
        if( $notAssignedUsers->count() > 0 ) {

            //  Invite existing users to this store
            $this->addTeamMembers($notAssignedUsers);

        }

        /**
         *  If we supplied one or more numbers that did not retrieve any
         *  users, then we can invite these people by using their mobile
         *  numbers.
         */
        if($mobileNumbersThatDontMatchAnyUser) {

            //  Invite non-existent users to this store
            $this->addTeamMembersByMobileNumbers($mobileNumbersThatDontMatchAnyUser);

        }

        /**
         *  If the mobile numbers specified match the assigned users,
         *  then this means that every user has already been invited.
         */
        if(count($mobileNumbers) === ($totalAssignedUsers = $assignedUsers->count())) {

            $message = $assignedUsers->pluck('first_name')->join(', ', ' and ');

            if($totalAssignedUsers == 1) {

                $message .= ' has';

            }elseif($totalAssignedUsers <= 3) {

                $message .= ' have';

            }else{

                $message = 'These people have';

            }

            $message =  "$message already been invited";

        /**
         *  If the mobile numbers specified are more than the users
         *  that have not been invited, then this means that some
         *  users were either invited or don't have user accounts
         */
        }else {

            $message = 'Invitations sent successfully';

        }

        //  Function to transform data of existing user
        $transformExistingUser = function(User $user) {
            return [
                'name' => $user->name,
                'mobile_number' => $user->mobile_number,
                'team_member_status' => $user->user_store_association->team_member_status ?? 'Invited',
            ];
        };

        //  Function to transform data of non-existing user
        $transformNonExistingUser = function($mobileNumber) {
            return [
                'mobile_number' => $this->convertToMobileNumberFormat($mobileNumber),
                'team_member_status' => 'Invited'
            ];
        };

        $invitations = [

            //  Total invited
            'total_invited' => ($notAssignedUsers->count() + count($mobileNumbersThatDontMatchAnyUser)),
            'total_already_invited' => ($assignedUsers->count() + count($mobileNumbersThatDontMatchAnyUserButInvited)),

            //  Information about existing users who are invited
            'existing_users_invited' => [
                'total' => $notAssignedUsers->count(),
                'existing_users' => collect($notAssignedUsers)->map(function($user) use ($transformExistingUser) {
                    return $transformExistingUser($user);
                })->toArray()
            ],

            //  Information about existing users who were already invited
            'existing_users_already_invited' => [
                'total' => $assignedUsers->count(),
                'existing_users' => collect($assignedUsers)->map(function($user) use ($transformExistingUser) {
                    return $transformExistingUser($user);
                })->toArray()
            ],

            //  Information about non-existing users who are invited
            'non_existing_users_invited' => [
                'total' => count($mobileNumbersThatDontMatchAnyUser),
                'non_existing_users' => collect($mobileNumbersThatDontMatchAnyUser)->map(function($mobileNumber) use ($transformNonExistingUser) {
                    return $transformNonExistingUser($mobileNumber);
                })->values()->toArray()
            ],

            //  Information about non-existing users who are invited
            'non_existing_users_already_invited' => [
                'total' => count($mobileNumbersThatDontMatchAnyUserButInvited),
                'non_existing_users' => collect($mobileNumbersThatDontMatchAnyUserButInvited)->map(function($record) use ($transformNonExistingUser) {
                    return $transformNonExistingUser($record->mobile_number);
                })->values()->toArray()
            ],

        ];

        return [
            'message' => $message,
            'invitations' => $invitations
        ];
    }

    /**
     *  Add a single user as creator of this store
     *
     *  @param \App\Models\User $user
     *  @return void
     */
    public function addCreator($user)
    {
        $this->addTeamMembers($user, 'Joined', ['*'], 'Creator');
    }

    /**
     *  Add a single user or multiple users as admins to this store
     *
     *  @param Collection|\App\Models\User[] $users
     *  @return void
     */
    public function addAdmins($users = [])
    {
        $this->addTeamMembers($users, null, ['*'], 'Admin');
    }

    /**
     *  Add a single or multiple users on this store.
     *  This allows us to assign new users as team members to this store with a given role and permissions
     *
     *  @param Collection|\App\Models\User[] $users
     *  @param string|null $teamMemberStatus e.g Joined, Left, Invited
     *  @param array | null $teamMemberPermissions e.g ['*'] or ['manage orders', 'manage customers', e.t.c]
     *  @param string|null $teamMemberRole e.g 'Admin'
     *  @return void
     */
    public function addTeamMembers($users, $teamMemberStatus = null, $teamMemberPermissions = [], $teamMemberRole = null)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        if(($user = $users) instanceof User) {

            //  Convert Model to collection
            $users = collect([$user]);

        }elseif(is_array($users)) {

            //  Convert array to collection
            $users = collect($users);

        }

        //  Get the user ids from the collection
        $userIds = $users->pluck('id');

        //  If we have user ids
        if( $userIds->count() ) {

            //  Transform the request permissions
            $teamMemberPermissions = $this->resolvePermissions($teamMemberPermissions);

            //  Determine the team member role
            $teamMemberRole = $this->resolveRole($teamMemberRole, $teamMemberPermissions);

            //  Set the team member status to "Invited" if no value is indicated
            if( empty($teamMemberStatus) ) $teamMemberStatus = 'Invited';

            $records = $userIds->map(function($userId) use($teamMemberStatus, $teamMemberPermissions, $teamMemberRole) {

                $teamMemberJoinCode = $teamMemberStatus == 'Invited' ? $this->generateRandomSixDigitCode() : null;

                $isAssigned = $teamMemberRole == 'Creator';

                $record = [
                    'team_member_permissions' => json_encode($teamMemberPermissions),
                    'invited_to_join_team_by_user_id' => auth()->user()->id,
                    'team_member_join_code' => $teamMemberJoinCode,
                    'team_member_status' => $teamMemberStatus,
                    'team_member_role' => $teamMemberRole,
                    'store_id' => $this->model->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'user_id' => $userId,

                    //  Automatically assign store to user if the user is a creator
                    'is_assigned' => $isAssigned,

                    //  Automatically follow by default
                    'follower_status' => 'Following',

                    /**
                     *  If this user the the current authenticated user.
                     *  This is usually the case when we are adding the
                     *  creator to the store and need to capture their
                     *  last_seen_at activity on this store
                     */
                    'last_seen_at' => $userId == auth()->user()->id ? now() : null
                ];

                return $record;

            })->toArray();

            //  Insert the specified user and store associations
            DB::table('user_store_association')->insert($records);

            //  If these users are not creators
            if(strtolower($teamMemberRole) !== 'creator') {

                //  Notify the users that they have been invited to join this store
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $users,
                    new InvitationToJoinStoreTeamCreated($store, auth()->user())
                );

            }

        }
    }

    /**
     *  Add a non-existent user on this store as a team member
     *  by using their mobile number. This allows us to invite
     *  people to be team members by using their mobile number
     *  even while yet they do not have user accounts.
     *
     *  @param int | array<int> $mobileNumbers
     *  @param array | null $teamMemberPermissions e.g ['*'] or ['manage orders', 'manage customers', e.t.c]
     *  @param string|null $teamMemberRole e.g 'Admin'
     *  @return void
     */
    public function addTeamMembersByMobileNumbers($mobileNumbers = [], $teamMemberPermissions = [], $teamMemberRole = null)
    {
        if(is_int($mobileNumber = $mobileNumbers)) {

            $mobileNumbers = [$mobileNumber];

        }

        if( !empty($mobileNumbers) ) {

            //  Transform the request permissions
            $teamMemberPermissions = $this->resolvePermissions($teamMemberPermissions);

            //  Determine the team member role
            $teamMemberRole = $this->resolveRole($teamMemberRole, $teamMemberPermissions);

            $records = collect($mobileNumbers)->map(function($mobileNumber) use($teamMemberRole, $teamMemberPermissions) {

                //  Generate the team member's join code
                $teamMemberJoinCode = $this->generateRandomSixDigitCode();

                return [
                    'team_member_permissions' => json_encode($teamMemberPermissions),
                    'invited_to_join_team_by_user_id' => auth()->user()->id,
                    'team_member_join_code' => $teamMemberJoinCode,
                    'team_member_role' => $teamMemberRole,
                    'mobile_number' => $mobileNumber,
                    'team_member_status' => 'Invited',
                    'store_id' => $this->model->id,
                    'created_at' => now(),
                    'updated_at' => now(),

                    //  Set the user id equal to the guest user id because the user does not yet exist.
                    'user_id' => $this->userRepository()->getGuestUserId(),

                    //  Automatically follow by default
                    'follower_status' => 'Following',
                ];
            })->toArray();

            //  Invite the specified users
            DB::table('user_store_association')->insert($records);

        }
    }

    /**
     *  Update a single or multiple users on this store.
     *  This allows us to update existing users as team members to this store with a given role and permissions
     *
     *  @param int | array<int> | \App\Models\User $userIds
     *  @param array | null $teamMemberPermissions e.g ['*'] or ['manage orders', 'manage customers', e.t.c]
     *  @param string|null $teamMemberRole e.g 'Admin'
     *  @return void
     */
    public function updateTeamMembersByUserIds($userIds = [], $teamMemberPermissions = [], $teamMemberRole = null)
    {
        if(($user = $userIds) instanceof Model) {

            $userIds = [$user->id];

        }elseif(is_int($id = $userIds)) {

            $userIds = [$id];

        }

        if( is_array($userIds) && !empty($userIds) ) {

            //  Transform the request permissions
            $teamMemberPermissions = $this->resolvePermissions($teamMemberPermissions);

            //  Determine the team member role
            $teamMemberRole = $this->resolveRole($teamMemberRole, $teamMemberPermissions);

            $data = [
                'team_member_permissions' => json_encode($teamMemberPermissions),
                'team_member_role' => $teamMemberRole,
                'updated_at' => now()
            ];

            //  Update the specified user and store associations
            DB::table('user_store_association')
                ->where('store_id', $this->model->id)
                ->whereIn('user_id', $userIds)
                ->update($data);
        }
    }

    /**
     *  Get the permissions passed as a parameter or
     *  the permissions provided by the request
     *
     *  @param array<string> $teamMemberPermissions
     *  @return array<string>
     */
    public function resolvePermissions($teamMemberPermissions) {

        //  Capture the permission to be set (Prefer the parameter shared permissions over the request permissions)
        $teamMemberPermissions = count($teamMemberPermissions) ? $teamMemberPermissions : (request()->filled('permissions') ? request()->input('permissions') : []);

        //  Make sure that the specified permissions are in array format (since the request supports JSON encoded data i.e string data)
        $teamMemberPermissions = is_string($teamMemberPermissions) ? json_decode($teamMemberPermissions) : $teamMemberPermissions;

        //  Check if this store permissions exist (capture any that do not exist)
        $nonExistingPermissions = collect($teamMemberPermissions)->filter(function($currPermission) {

            //  Return permissions that are not granted by our store permissions
            return collect(Store::PERMISSIONS)->contains('grant', $currPermission) == false;

        })->join(', ', ' and ');

        if( $nonExistingPermissions ) throw new StoreRoleDoesNotExistException("The specified permission ($nonExistingPermissions".(Str::contains($nonExistingPermissions, 'and') ? ') do not': ') does not')." exist");

        //  If we have granted the users the ability to manage everything
        if( count($teamMemberPermissions) > 1 && collect($teamMemberPermissions)->contains('*') ) {

            $teamMemberPermissions = ["*"];

        }

        //  Return the transformed permissions
        return $teamMemberPermissions;

    }

    /**
     *  Get the role specified or set the appropriate
     *  role based on the permissions provided
     *
     *  @param string $teamMemberRole
     *  @param array<string>
     *  @return string
     */
    public function resolveRole($teamMemberRole, $teamMemberPermissions) {

        //  Set the default team member role name
        $defaulfRole = 'Team Member';

        //  Set the team member role to the default role if no value is indicated
        if( empty($teamMemberRole) ) $teamMemberRole = $defaulfRole;

        //  Check if this team member role exists
        $roleDoesNotExist = collect(UserStoreAssociation::TEAM_MEMBER_ROLES)->contains($teamMemberRole) == false;

        if( $roleDoesNotExist ) throw new StoreRoleDoesNotExistException("The specified team member role of $teamMemberRole does not exist");

        //  If we have granted the users the ability to manage everything and the user is not a creator
        if( collect($teamMemberPermissions)->contains('*') && $teamMemberRole != 'Creator') {

            $teamMemberRole = 'Admin';

        }

        return $teamMemberRole;
    }

    /**
     *  Remove a single or multiple users on this store.
     *
     *  @return void
     */
    public function removeTeamMembers()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        /**
         *  Get the specified mobile numbers. Make sure that the specified mobile numbers are
         *  in array format since the request supports JSON encoded data i.e string data
         */
        $mobileNumbers = is_string($mobileNumbers = request()->input('mobile_numbers')) ? json_decode($mobileNumbers) : $mobileNumbers;

        //  Get the users that are assigned to this store that match the specified mobile numbers
        $assignedUsers = $store->teamMembers()
            //  Matches non-existing user by mobile number
            ->whereIn('user_store_association.mobile_number', $mobileNumbers)
            //  Matches existing user by mobile number
            ->orWhereIn('users.mobile_number', $mobileNumbers)
            ->get();

        //  If we have one or more users to remove
        if( !empty($assignedUsers) ) {

            //  If we have only one user to remove
            if( count($assignedUsers) == 1 ) {

                //  Get this user
                $assignedUser = $assignedUsers[0];

                //  If this user's id is the same as the current auth user
                if($assignedUser->id === auth()->user()->id) {

                    //  Deny the action of removing yourself
                    throw new CannotRemoveYourselfAsTeamMemberException();

                }

                //  If this user is a creator
                if($assignedUser->user_store_association->is_team_member_as_creator) {

                    //  Deny the action of removing yourself as a store creator
                    throw new CannotRemoveYourselfAsStoreCreatorException();

                }

            //  Otherwise if we have more than one user to remove
            }else{

                //  Lets check each user before proceeding
                foreach($assignedUsers as $index => $assignedUser) {

                    //  If this user's id is the same as the current auth user
                    if($assignedUser->id === auth()->user()->id) {

                        /**
                         *  Deny the action of removing yourself by unsetting this user
                         *  instead of throwing an exception so that we can proceed to
                         *  remove other users
                         */
                        unset($assignedUsers[$index]);

                    }

                    //  If this user is a creator
                    if($assignedUser->user_store_association->is_team_member_as_creator) {

                        /**
                         *  Deny the action of removing creator by unsetting this user
                         */
                        unset($assignedUsers[$index]);

                    }

                }
            }

            //  Get the store permissions
            $permissions = collect(Store::PERMISSIONS)->map(fn($permission) => $permission['grant'])->values();

            //  Get the pivot ids of the user associations as team members
            $userStoreAssociationIds = $assignedUsers->map(function(User $assignedUser) use ($store, $permissions) {

                //  Foreach of the available store permissions
                foreach($permissions as $permission) {

                    //  Revoke this permission if saved to the cache memory
                    $assignedUser->removeHasStorePermissionFromCache($store->id, $permission);

                }

                return $assignedUser->user_store_association->id;

            })->toArray();

            if(count($userStoreAssociationIds) == 0) {

                return ['message' => 'No team members removed'];

            }else{

                //  Remove the user associations as team members
                DB::table('user_store_association')->whereIn('id', $userStoreAssociationIds)->update([
                    'invited_to_join_team_by_user_id' => null,
                    'last_subscription_end_at' => null,
                    'team_member_permissions' => null,
                    'team_member_join_code' => null,
                    'team_member_status' => null,
                    'team_member_role' => null
                ]);

                /**
                 *  @var User $user
                 */
                $removedByUser = auth()->user();

                //  Get the team members who joined
                $teamMembers = $store->teamMembers()->joinedTeam()->get();

                //  Foreach assigned user that has been removed
                foreach($assignedUsers as $removedUser) {

                    //  Notify the team members that a team member has been removed
                    //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                    Notification::sendNow(
                        //  Send notifications to the team members who joined
                        $teamMembers,
                        new RemoveStoreTeamMember($store, $removedUser, $removedByUser)
                    );

                }

                //  Return a message indicating the total members removed
                return ['message' => count($userStoreAssociationIds).' team '.(count($userStoreAssociationIds) === 1 ? 'member': 'members').' removed'];

            }


        }

        //  Return a message indicating no members removed
        return ['message' => 'No members removed'];
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
        return $this->shoppingCartService()->startInspection();;
    }

    /**
     *  Show the shopping cart order for options
     *
     *  @return array
     */
    public function showShoppingCartOrderForOptions()
    {
        return $this->orderRepository()->showOrderForOptions();
    }

    /**
     *  Show the shopping cart order for users (customer & friends)
     *
     *  @return UserRepository
     */
    public function showShoppingCartOrderForUsers(Request $request)
    {
        //  Get the specified order for
        $orderFor = strtolower($request->input('order_for'));

        //  Get the specified friend user ids
        $friendUserIds = $request->input('friend_user_ids');

        //  Get the specified friend group ids
        $friendGroupIds = $request->input('friend_group_ids');

        return $this->orderRepository()->showOrderForUsers($orderFor, $friendUserIds, $friendGroupIds);
    }

    /**
     *  Count the shopping cart order for users (customer & friends)
     *
     *  @return array
     */
    public function countShoppingCartOrderForUsers(Request $request)
    {
        //  Get the specified order for
        $orderFor = strtolower($request->input('order_for'));

        //  Get the specified friend user ids
        $friendUserIds = $request->input('friend_user_ids');

        //  Get the specified friend group ids
        $friendGroupIds = $request->input('friend_group_ids');

        return $this->orderRepository()->countOrderForUsers($orderFor, $friendUserIds, $friendGroupIds);
    }

    /**
     *  Inspect the user's store shopping cart.
     *
     *  @return CartRepository
     */
    public function inspectShoppingCart()
    {
        //  Get the inspected shopping cart
        $inspectedShoppingCart = $this->getShoppingCart();

        //  Lets return the shopping cart as part of the cart respository instance
        return $this->cartRepository()->setModel($inspectedShoppingCart);
    }

    /**
     *  Convert the user's store shopping cart to an order
     *
     *  @return OrderRepository
     */
    public function convertShoppingCart()
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;
        return $this->orderRepository()->createOrder($store);
    }

    /**
     *  Add a customer to this store
     *
     *  @param int $userId
     *
     *  @return User
     */
    public function findOrAddCustomerByUserId(int $userId)
    {
        //  Get the matching user as a customer on this store
        $userAsCustomer = $this->findCustomerByUserId($userId);

        //  Return the customer if found
        if($userAsCustomer) return $userAsCustomer;

        //  Add user as a customer on this store
        $this->addCustomer($userId);

        //  Return the newly created customer
        return $this->findCustomerByUserId($userId);
    }

    /**
     *  Find the customer to this store matching
     *  the specified customer user id
     *
     *  @param int $userId
     *
     *  @return User|null
     */
    public function findCustomerByUserId(int $userId)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        //  Return the matching user as a customer on this store
        return $store->customers()->where('user_store_association.user_id', $userId)->first();
    }

    /**
     *  Add a customer on this store using
     *  the customer's user id
     *
     *  @param int $userId
     *
     *  @return void
     */
    public function addCustomer(int $userId)
    {
        /**
         *  @var Store $store
         */
        $store = $this->model;

        // Check if the user is already associated with this store in any way (team member, visitor, e.t.c)
        if( $user = $store->users()->where('user_store_association.user_id', $userId)->first() ) {

            //  Update the existing user and store association
            $user->user_store_association->is_associated_as_customer = true;
            $user->user_store_association->save();

        }else{

            //  Assign the user as a customer to this store
            $store->customers()->attach($userId, [
                'is_associated_as_customer' => true
            ]);

        }
    }
}
