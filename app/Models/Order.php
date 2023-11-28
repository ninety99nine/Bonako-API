<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Status;
use App\Models\Store;
use App\Casts\Currency;
use App\Casts\Percentage;
use App\Traits\OrderTrait;
use App\Casts\OrderStatus;
use App\Casts\MobileNumber;
use App\Models\Base\BaseModel;
use App\Casts\OrderPaymentStatus;
use App\Casts\OrderCollectionType;
use App\Services\Ussd\UssdService;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Pivots\UserStoreAssociation;
use App\Models\Pivots\UserOrderViewAssociation;
use App\Models\Pivots\FriendGroupOrderAssociation;
use App\Models\Pivots\UserOrderCollectionAssociation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends BaseModel
{
    use HasFactory, OrderTrait;

    const PAYMENT_STATUSES = Transaction::STATUSES;
    const COLLECTION_TYPES = ['Delivery', 'Pickup'];
    const USER_ORDER_FILTERS = ['All', ...self::STATUSES];
    const STORE_ORDER_FILTERS = ['All', ...self::STATUSES];
    const FRIEND_GROUP_ORDER_FILTERS = ['All', ...self::STATUSES];
    const ORDER_FOR_OPTIONS = ['Me', 'Me And Friends', 'Friends Only' /*, 'Business'*/];
    const STATUSES = ['Waiting', 'On Its Way', 'Ready For Pickup', 'Cancelled', 'Completed'];
    const CANCELLATION_REASONS = [
        'Not Available', 'No stock', 'No payment', 'No delivery', 'No pickup', 'Changes in order',
        'Cancelled by customer', 'Unrealistic order', 'Fake order', 'Other'
    ];

    const USER_ORDER_ASSOCIATIONS = [
        'Customer', 'Friend', 'Customer Or Friend', 'Team Member'
    ];

    /**
     *  Magic Numbers
     */
    const SPECIAL_NOTE_MIN_CHARACTERS = 3;
    const SPECIAL_NOTE_MAX_CHARACTERS = 400;

    protected $casts = [
        'grand_total' => Money::class,
        'amount_paid' => Money::class,
        'amount_pending' => Money::class,
        'collection_verified' => 'boolean',
        'amount_outstanding' => Money::class,
        'collection_verified_at' => 'datetime',
        'last_viewed_by_team_at' => 'datetime',
        'first_viewed_by_team_at' => 'datetime',
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class,
        'status' => OrderStatus::class,
        'collection_verified' => Status::class,
        'amount_paid_percentage' => Percentage::class,
        'payment_status' => OrderPaymentStatus::class,
        'collection_type' => OrderCollectionType::class,
        'amount_pending_percentage' => Percentage::class,
        'amount_outstanding_percentage' => Percentage::class,
    ];

    protected $fillable = [

        /*  Basic Information  */
        'summary',

        /*  Balance Information  */
        'currency',
        'grand_total',
        'amount_paid', 'amount_paid_percentage',
        'amount_pending', 'amount_pending_percentage',
        'amount_outstanding', 'amount_outstanding_percentage',

        /*  Status Information  */
        'status', 'payment_status',

        /*  Special Note Information  */
        'special_note',

        /*  Cancellation Information  */
        'cancellation_reason',

        /*  Customer Information  */
        'customer_first_name', 'customer_last_name', 'customer_user_id',

        /*  Order For Information  */
        'order_for', 'order_for_total_users', 'order_for_total_friends',

        /*  Collection Information  */
        'collection_verified', 'collection_verified_at',
        'collection_verified_by_user_id', 'collection_verified_by_user_first_name',
        'collection_verified_by_user_last_name', 'collection_by_user_id',
        'collection_by_user_first_name', 'collection_by_user_last_name',
        'collection_type', 'destination_name', 'delivery_address_id',

        /*  Occasion Information  */
        'occasion_id',

        /*  Payment Information  */
        'payment_method_id',

        /*  Ownership Information  */
        'store_id',

        /*  Team Views  */
        'total_views_by_team', 'first_viewed_by_team_at', 'last_viewed_by_team_at'

    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /*
     *  Scope: Return orders that are being searched
     */
    public function scopeSearch($query, $searchWord)
    {
        // Remove the leading zeros to retrive the order id
        $searchWordWithoutLeadingZeros = ltrim($searchWord);

        /**
         *  Search: Order number, associated user's first name, last name or mobile number
         */
        return $query->where('id', $searchWordWithoutLeadingZeros)
                     ->orWhereHas('users', function($query) use ($searchWord) {
                        $query->search($searchWord);
                     });
    }

    /**
     *  Scope orders where the current authenticated user is the customer
     */
    public function scopeAuthAsCustomer($query)
    {
        //  Query orders where the current authenticated has been added as a customer
        return $query->userAsCustomer(auth()->user()->id);
    }

    /**
     *  Scope orders where the specified user is the customer
     */
    public function scopeUserAsCustomer($query, $userId)
    {
        return $query->whereHas('customer', function (Builder $query) use ($userId) {

            //  Query orders where the specified user has been added as a customer
            $query->where('user_order_collection_association.user_id', $userId);

        });
    }

    /**
     *  Scope orders where the specified user is the customer
     */
    public function scopeUserAsFriend($query, $userId)
    {
        return $query->whereHas('friends', function (Builder $query) use ($userId) {

            //  Query orders where the specified user has been added as a friend
            $query->where('user_order_collection_association.user_id', $userId);

        });
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Get the Cart assigned to this Order
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasOne
     */
    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Get the transactions
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'owner');
    }

    /**
     * Get the latest transaction
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'owner')->latest();
    }

    /**
     *  Returns the current authenticated user's latest transaction
     *  that is pending payment for this order
     */
    public function authTransactionPendingPayment()
    {
        return $this->transaction()->pendingPayment()->belongsToAuth();
    }

    /**
     * Get the Delivery Address for this Order
     */
    public function deliveryAddress()
    {
        return $this->belongsTo(DeliveryAddress::class, 'delivery_address_id');
    }

    /**
     * Get the Occasion for this Order
     */
    public function occasion()
    {
        return $this->belongsTo(Occasion::class, 'occasion_id');
    }

    /**
     * Get the Payment Method for this Order
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Get the User that collected this Order
     */
    public function userThatCollected()
    {
        return $this->belongsTo(User::class, 'collection_by_user_id');
    }

    /**
     * Get the User that verified the Order collection
     */
    public function userThatVerifiedCollection()
    {
        return $this->belongsTo(User::class, 'collection_verified_by_user_id');
    }

    /**
     * Get the Store that owns the Order
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     *  Get the Users of this Order
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_order_collection_association', 'order_id', 'user_id')
                    ->withPivot(UserOrderCollectionAssociation::VISIBLE_COLUMNS)
                    ->using(UserOrderCollectionAssociation::class)
                    ->as('user_order_collection_association');
    }

    /**
     *  Get the Customer that owns the Order
     */
    public function customer()
    {
        return $this->users()->where('user_order_collection_association.role', 'Customer');
    }

    /**
     *  Get the friends (Users) of this Order
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function friends()
    {
        return $this->users()->where('user_order_collection_association.role', 'Friend');
    }

    /**
     *  Returns the current authenticated user and order collection association
     *
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function authUserOrderCollectionAssociation()
    {
        return $this->hasOne(UserOrderCollectionAssociation::class, 'order_id')
                    ->where('user_id', auth()->user()->id);
    }

    /**
     *  Get the Friend Groups of this Order
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function friendGroups()
    {
        return $this->belongsToMany(FriendGroup::class, 'friend_group_order_association', 'order_id', 'friend_group_id')
                    ->withPivot(FriendGroupOrderAssociation::VISIBLE_COLUMNS)
                    ->using(FriendGroupOrderAssociation::class)
                    ->as('friend_group_order_association');
    }

    /**
     *  Get the Users (team members) that viewed the Order.
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function usersThatViewed()
    {
        return $this->belongsToMany(User::class, 'user_order_view_association', 'order_id', 'user_id')
                    ->withPivot(UserOrderViewAssociation::VISIBLE_COLUMNS)
                    ->using(UserOrderViewAssociation::class)
                    ->as('user_order_view_association');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'customer_name', 'customer_display_name', 'collection_by_user_name', 'collection_verified_by_user_name', 'number', 'dial_to_show_collection_code', 'follow_up_statuses',
        'can_mark_as_paid', 'can_request_payment', 'payable_amounts', 'is_paid', 'is_unpaid', 'is_partially_paid', 'is_pending_payment', 'is_waiting', 'is_on_its_way', 'is_ready_for_pickup', 'is_cancelled',
        'is_completed', 'is_ordering_for_me', 'is_ordering_for_me_and_friends', 'is_ordering_for_friends_only', 'is_ordering_for_business',
        'other_associated_friends'
    ];

    /**
     *  Get the user and order collection association pivot model is provided
     *
     *  @return UserOrderCollectionAssociation|null $userOrderCollectionAssociation
     */
    public function getUserOrderCollectionAssociation() {

        $userOrderCollectionAssociation = null;

        /**
         *  Check if the user and order collection association pivot model is loaded so that
         *  we can determine how this user is associated with this order. If the relationship
         *  "user_order_collection_association" exists then this order was acquired directly
         *  from a user and order relationship e.g
         *
         *  $user->orders()->first();
         */
        if( $this->relationLoaded('user_order_collection_association') ) {

            /**
             *  @var UserOrderCollectionAssociation $userOrderCollectionAssociation
             */
            $userOrderCollectionAssociation = $this->user_order_collection_association;

        /**
         *  Check if the user and order collection association pivot model is loaded so that
         *  we can determine how this user is associated with this order. If the relationship
         *  "authUserOrderCollectionAssociation" exists then this order was acquired without
         *  a user and order relationship but the UserOrderCollectionAssociation was then
         *  eager loaded on the order e.g
         *
         *  Order::with('authUserOrderCollectionAssociation')->first();
         */
        }elseif( $this->relationLoaded('authUserOrderCollectionAssociation') ) {

            /**
             *  @var UserOrderCollectionAssociation $userOrderCollectionAssociation
             */
            $userOrderCollectionAssociation = $this->authUserOrderCollectionAssociation;

        }

        return $userOrderCollectionAssociation;
    }

    public function getOtherAssociatedFriendsAttribute()
    {
        $otherFriendsText = '';
        $orderForTotalFriendsLeft = $this->order_for_total_friends;

        if($this->is_ordering_for_me_and_friends || $this->is_ordering_for_friends_only) {

            /**
             *  @var UserOrderCollectionAssociation $userOrderCollectionAssociation
             */
            $userOrderCollectionAssociation = $this->getUserOrderCollectionAssociation();

            /**
             *  If the user and order collection association is provided and the current authenticated
             *  user is associated with this order as a friend
             */
            if(!is_null($userOrderCollectionAssociation) && $userOrderCollectionAssociation->is_associated_as_friend) {

                // Will end as 'John Doe + Me'
                if($this->is_ordering_for_me_and_friends) $otherFriendsText .= '+ Me';

                // Will end as 'John Doe for Me'
                if($this->is_ordering_for_friends_only) $otherFriendsText .= 'for Me';

                // Reduce the total friends left by 1
                $orderForTotalFriendsLeft = $orderForTotalFriendsLeft - 1;

                if($orderForTotalFriendsLeft > 0) $otherFriendsText .= ' and ';

            }else{

                // Will end as 'John Doe + 2 friends'
                if($this->is_ordering_for_me_and_friends) $otherFriendsText .= '+ ';

                // Will end as 'John Doe for 2 friends'
                if($this->is_ordering_for_friends_only) $otherFriendsText .= 'for ';

            }

            if($orderForTotalFriendsLeft > 0) {

                /**
                 * Will End As:
                 *
                 * (1) 'John Doe + 1 friend'
                 * (2) 'John Doe + 2 friends'
                 * (3) 'John Doe + You and 1 friend'
                 * (4) 'John Doe + You and 2 friends'
                 *
                 * (5) 'John Doe for 1 friend'
                 * (6) 'John Doe for 2 friends'
                 * (7) 'John Doe for You and 1 friend'
                 * (8) 'John Doe for You and 2 friends'
                 */
                $otherFriendsText .= $orderForTotalFriendsLeft.($orderForTotalFriendsLeft == 1 ? ' friend' : ' friends');

            }

        }

        return empty($otherFriendsText) ? null : $otherFriendsText;
    }

    public function getCustomerAttribute()
    {
        if($this->relationLoaded('customer')) {

            /**
             *  Since the customer is a belongsToMany, the relationship returns
             *  a collection of the User Model. We need to always return the
             *  first result otherwise null.
             */
            $customerCollection = $this->getRelation('customer');

            if($customerCollection->count()) {
                return $customerCollection->first();
            }

        }

        return null;
    }

    public function getCustomerNameAttribute()
    {
        return trim($this->customer_first_name.' '.$this->customer_last_name);
    }

    public function setCustomerNameAttribute($value)
    {
        /**
         *  This allows us to modify the "customer_name" e.g setting the value to "Null"
         *  when transforming the order and need to make the "customer_name" anonymous
         *  when running the makeAnonymous() method.
         */
        $this->customer_name = $value;
    }

    public function getCustomerDisplayNameAttribute()
    {
        /**
         *  This is later changed to "Me" when transforming the order depending
         *  on whether or not the authenticated user placed this order. We use
         *  this to determine how the customer, friend, store team members and
         *  general public see this order.
         */
        return $this->customer_name;
    }

    public function setCustomerDisplayNameAttribute($value)
    {
        /**
         *  This allows us to modify the "customer_display_name" e.gsetting the value to "Me"
         *  when transforming the order depending on whether or not the authenticated user
         *  placed this order. We use this to determine how the customer, friend, store
         *  team members and general public see this order.
         */
        $this->customer_display_name = $value;
    }

    public function getCollectionByUserNameAttribute()
    {
        return trim($this->collection_by_user_first_name.' '.$this->collection_by_user_last_name);
    }

    public function getCollectionVerifiedByUserNameAttribute()
    {
        if($this->collection_verified_by_user_first_name && $this->collection_verified_by_user_last_name) {
            return trim($this->collection_verified_by_user_first_name.' '.$this->collection_verified_by_user_last_name);
        }
    }

    public function getNumberAttribute()
    {
        return str_pad($this->id, 5, 0, STR_PAD_LEFT);
    }

    public function setNumberAttribute($value)
    {
        /**
         *  This allows us to modify the order "number" e.g setting the value to "Null"
         *  when transforming the order and need to make the "number" anonymous
         *  when running the makeAnonymous() method.
         */
        $this->number = $value;
    }

    public function getDialToShowCollectionCodeAttribute()
    {
        $code = UssdService::getMobileVerificationShortcode();
        $hasCustomer = $this->customer;

        if($hasCustomer) {

            $customer = $this->customer;
            $customerName = $customer->first_name;
            $mobileNumberWithoutExtension = $customer->mobile_number->withoutExtension;
            $instruction = 'Ask '.$customerName.' to dial '.$code.' on '. $mobileNumberWithoutExtension . ' to show the code to complete this order';

        }else{

            $instruction = 'Ask the customer to dial '.$code.' on their mobile number and enter the code to verify collection';

        }

        return [
            'code' => $code,
            'instruction' => $instruction
        ];
    }

    public function getFollowUpStatusesAttribute()
    {
        /**
         *  Since the status might or might not be casted using the OrderStatus class,
         *  we need to always make sure that we get the original status before casting
         */
        $status = $this->getRawOriginal('status');

        $description = [
            'Completed' => 'Notify the customer that this order has been completed',
            'Cancelled' => 'Notify the customer that this order has been cancelled',
            'Ready For Pickup' => 'Notify the customer that this order is ready for pickup',
            'On Its Way' => 'Notify the customer that this order is on its way to being delivered',
        ];

        if ($this->isWaiting()) {
            $statuses = ['On Its Way', 'Ready For Pickup', 'Completed', 'Cancelled'];
        } elseif ($this->isCancelled()) {
            $statuses = ['On Its Way', 'Ready For Pickup', 'Completed'];
        } elseif ($this->isOnItsWay()) {
            $statuses = ['Ready For Pickup', 'Completed', 'Cancelled'];
        } elseif ($this->isReadyForPickup()) {
            $statuses = ['On Its Way', 'Completed', 'Cancelled'];
        } elseif ($this->isCompleted()) {
            $statuses = [];
        } else {
            $statuses = [];
        }

        return collect($statuses)->map(function($status) use ($description) {
            return [
                'name' => $status,
                'description' => $description[$status]
            ];
        })->toArray();
    }

    public function getCanMarkAsPaidAttribute()
    {
        if($this->grand_total->amount > 0) {

            /**
             *  Get the store from the request store otherwise from the eager loaded store
             *
             *  @var Store $store
             */
            $store = request()->store ?? $this->getRelation('store');

            //  The store must be loaded to determine if we can mark as paid
            if($store == null) return null;

            /**
             *  @var UserStoreAssociation $userStoreAssociation
             */
            $userStoreAssociation = $store->getUserStoreAssociation();

            //  Check if the user and store association is provided to determine if we can mark as paid
            if(!is_null($userStoreAssociation)) {

                return $userStoreAssociation->is_team_member_who_has_joined;

            }

        }

        return false;
    }

    public function getCanRequestPaymentAttribute()
    {
        if($this->grand_total->amount > 0) {

            /**
             *  Get the store from the request store otherwise from the eager loaded store
             *
             *  @var Store $store
             */
            $store = request()->store ?? $this->getRelation('store');

            //  The store must be loaded to determine if we can request payment
            if($store == null) return null;

            $perfectPayEnabled = $store->perfect_pay_enabled;
            $dpoPaymentEnabled = $store->dpo_payment_enabled;
            $hasPayableAmounts = count($this->payable_amounts) > 0;
            $orangeMoneyPaymentEnabled = $store->orange_money_payment_enabled;

            if(($perfectPayEnabled || $dpoPaymentEnabled || $orangeMoneyPaymentEnabled) && $hasPayableAmounts) {

                /**
                 *  @var UserOrderCollectionAssociation $userOrderCollectionAssociation
                 */
                $userOrderCollectionAssociation = $this->getUserOrderCollectionAssociation();

                //  Check if the user and order collection association is provided to determine if we can request payment
                if(!is_null($userOrderCollectionAssociation)) {

                    $isAssociatedAsFriend = $userOrderCollectionAssociation->is_associated_as_friend;
                    $isAssociatedAsCustomer = $userOrderCollectionAssociation->is_associated_as_customer;

                    //  We can request payment if we are associated a the friend or customer listed on this order
                    return $isAssociatedAsFriend || $isAssociatedAsCustomer;

                }

                /**
                 *  @var UserStoreAssociation $userStoreAssociation
                 */
                $userStoreAssociation = $store->getUserStoreAssociation();

                //  Check if the user and store association is provided to determine if we can request payment
                if(!is_null($userStoreAssociation)) {

                    return $userStoreAssociation->is_team_member_who_has_joined;

                }

            }

        }

        return false;
    }

    public function getPayableAmountsAttribute()
    {
        $options = [];

        /**
         *  Get the store from the request store otherwise from the eager loaded store
         *
         *  @var Store $store
         */
        $store = request()->store ?? $this->getRelation('store');

        //  The store must be loaded to acquire the payable amounts
        if($store == null) return null;

        $amountPaidPercentage = $this->getRawOriginal('amount_paid_percentage');
        $amountPendingPercentage = $this->getRawOriginal('amount_pending_percentage');
        $amountOutstandingPercentage = $this->getRawOriginal('amount_outstanding_percentage');

        //  Get the remaining outstanding percentage balance that is still payable
        $remainingPercentage = $amountOutstandingPercentage - $amountPendingPercentage;

        if($store && $remainingPercentage > 0) {

            $option = fn($name, $type, $percentage) => [
                'name' => $name,
                'type' => $type,
                'percentage' => (int) $percentage,
                'amount' => $this->convertToMoneyFormat($this->getRawOriginal('grand_total') * $percentage / 100, $this->getRawOriginal('currency'))
            ];

            //  Get deposit options
            $getDepositPaymentOptions = function($existingOptions) use ($store, $option) {

                //  If the store supports deposit payments
                if($store->allow_deposit_payments) {

                    //  Get the deposit options
                    $depositOptions = collect($store->deposit_percentages)->map(function($percentage) use ($option) {

                        //  Return the deposit option
                        return $option("Deposit ($percentage%)", 'deposit', $percentage);

                    })->toArray();

                    //  Add deposit options
                    array_push($existingOptions, ...$depositOptions);

                }

                return $existingOptions;

            };

            //  Get installment options
            $getInstallmentPaymentOptions = function($existingOptions) use ($remainingPercentage, $store, $option) {

                //  Add remaining balance payment option
                $options[] = $option("Remaining Balance ($remainingPercentage%)", 'remaining_balance', $remainingPercentage);

                //  If the store supports installment payments
                if($store->allow_installment_payments) {

                    //  Get the installment options
                    $installmentOptions = collect($store->installment_percentages)->reject(function($percentage) use ($remainingPercentage) {

                        //  Reject installment percentages that are higher than or equal to the remaining percentage
                        return $percentage > $remainingPercentage || $percentage == $remainingPercentage;

                    })->map(function($percentage) use ($option) {

                        //  Return the installment option
                        return $option("$percentage% Installment", 'installment', $percentage);

                    })->toArray();

                    //  Add installment options
                    array_push($existingOptions, ...$installmentOptions);

                }

                return $existingOptions;
            };

            if($amountPaidPercentage < 100) {

                if($amountPaidPercentage == 0) {

                    //  Add full payment option
                    $options[] = $option('Full Payment', 'full_payment', 100);

                    //  If the store does not support deposit payments
                    if($store->allow_deposit_payments) {

                        //  Add deposit payment options
                        $options = $getDepositPaymentOptions($options);

                    }else{

                        //  Add installment payment options
                        $options = $getInstallmentPaymentOptions($options);

                    }

                }else{

                    //  If the amount outstanding is the amount pending payment
                    if($amountPendingPercentage == $amountOutstandingPercentage) {

                        //  Then we cannot have payment options
                        return [];

                    }else{

                        //  Add remaining balance option
                        $options[] = $option("Remaining Balance ($remainingPercentage%)", 'remaining_balance', $remainingPercentage);

                        //  Add installment payment options
                        $options = $getInstallmentPaymentOptions($options);

                    }

                }

            }

        }

        return $options;
    }

    public function getIsPaidAttribute()
    {
        return $this->isPaid();
    }

    public function getIsUnpaidAttribute()
    {
        return $this->isUnpaid();
    }

    public function getIsPartiallyPaidAttribute()
    {
        return $this->isPartiallyPaid();
    }

    public function getIsPendingPaymentAttribute()
    {
        return $this->isPendingPayment();
    }

    public function getIsWaitingAttribute()
    {
        return $this->isWaiting();
    }

    public function getIsOnItsWayAttribute()
    {
        return $this->isOnItsWay();
    }

    public function getIsReadyForPickupAttribute()
    {
        return $this->isReadyForPickup();
    }

    public function getIsCancelledAttribute()
    {
        return $this->isCancelled();
    }

    public function getIsCompletedAttribute()
    {
        return $this->isCompleted();
    }

    public function getIsOrderingForMeAttribute()
    {
        return strtolower($this->getRawOriginal('order_for')) === 'me';
    }

    public function getIsOrderingForMeAndFriendsAttribute()
    {
        return strtolower($this->getRawOriginal('order_for')) === 'me and friends';
    }

    public function getIsOrderingForFriendsOnlyAttribute()
    {
        return strtolower($this->getRawOriginal('order_for')) === 'friends only';
    }

    public function getIsOrderingForBusinessAttribute()
    {
        return strtolower($this->getRawOriginal('order_for')) === 'business';
    }
}
