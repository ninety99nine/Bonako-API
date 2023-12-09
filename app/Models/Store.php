<?php

namespace App\Models;

use Carbon\Carbon;
use App\Casts\Money;
use App\Casts\Currency;
use App\Casts\JsonToArray;
use App\Casts\MobileNumber;
use App\Models\Base\BaseModel;
use App\Services\Ussd\UssdService;
use App\Casts\DeliveryDestinations;
use App\Traits\UserStoreAssociationTrait;
use App\Models\Pivots\UserStoreAssociation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Pivots\FriendGroupStoreAssociation;
use App\Services\MobileNumber\MobileNumberService;
use App\Models\Pivots\StorePaymentMethodAssociation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends BaseModel
{
    use HasFactory, UserStoreAssociationTrait;

    const CURRENCY = 'BWP';

    const PERMISSIONS = [
        [
            'name' => 'Manage everything',
            'grant' => '*',
            'description' => 'Permission to manage everything'
        ],
        [
            'name' => 'Manage orders',
            'grant' => 'manage orders',
            'description' => 'Permission to manage orders'
        ],
        [
            'name' => 'Manage coupons',
            'grant' => 'manage coupons',
            'description' => 'Permission to manage coupons'
        ],
        [
            'name' => 'Manage products',
            'grant' => 'manage products',
            'description' => 'Permission to manage products'
        ],
        [
            'name' => 'Manage customers',
            'grant' => 'manage customers',
            'description' => 'Permission to manage customers'
        ],
        [
            'name' => 'Manage team members',
            'grant' => 'manage team members',
            'description' => 'Permission to manage team members'
        ],
        [
            'name' => 'Manage instant carts',
            'grant' => 'manage instant carts',
            'description' => 'Permission to manage instant carts'
        ],
        [
            'name' => 'Manage settings',
            'grant' => 'manage settings',
            'description' => 'Permission to manage store settings including updating or deleting store'
        ],
    ];

    const STORE_FILTERS = [
        'All', 'Popular Today', 'Popular This Week', 'Popular This Month', 'Popular This Year'
    ];

    const USER_STORE_FILTERS = [
        'All', 'Team Member', 'Team Member Left', 'Team Member Joined', 'Team Member Invited', 'Team Member Declined',
        'Team Member Joined As Creator', 'Team Member Joined As Non Creator',
        'Follower', 'Unfollower', 'Invited To Follow',
        'Friend Group Member', 'Customer',
        'Assigned', 'Recent Visitor'
    ];

    const REGISTERED_WITH_CIPA_AS = [
        'Company', 'Business', 'Non profit'
    ];

    const BANKING_WITH = [
        'Absa', 'BancABC', 'Bank Gaborone', 'Bank of Baroda',
        'Bank of India', 'Botswana Savings Bank', 'First Capital Bank',
        'First National Bank', 'Stanbic Bank Botswana', 'Standard Chartered Bank',
        'Other'
    ];

    const COLLECTION_TYPES = [
        'Delivery', 'Pickup'
    ];

    const DEFAULT_OFFLINE_MESSAGE = 'We are currently offline';
    const CALL_TO_ACTION_OPTIONS = ['Buy', 'Order', 'Preorder'];

    /**
     *  Magic Numbers
     */
    const MAXIMUM_ADVERTS = 5;
    const MAXIMUM_COUPONS = 50;
    const MAXIMUM_PRODUCTS = 50;
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 25;
    const COMPANY_UIN_CHARACTERS = 13;
    const MAXIMUM_VISIBLE_PRODUCTS = 5;
    const DESCRIPTION_MIN_CHARACTERS = 10;
    const DESCRIPTION_MAX_CHARACTERS = 120;
    const PICKUP_NOTE_MIN_CHARACTERS = 10;
    const PICKUP_NOTE_MAX_CHARACTERS = 120;
    const DELIVERY_NOTE_MIN_CHARACTERS = 10;
    const DELIVERY_NOTE_MAX_CHARACTERS = 120;
    const SMS_SENDER_NAME_MIN_CHARACTERS = 3;
    const SMS_SENDER_NAME_MAX_CHARACTERS = 11;
    const OFFLINE_MESSAGE_MIN_CHARACTERS = 3;
    const OFFLINE_MESSAGE_MAX_CHARACTERS = 120;
    const DPO_COMPANY_TOKEN_MIN_CHARACTERS = 3;
    const DPO_COMPANY_TOKEN_MAX_CHARACTERS = 255;
    CONST NUMBER_OF_EMPLOYEES_MIN_CHARACTERS = 1;
    const PICKUP_DESTINATION_NAME_MIN_CHARACTERS = 3;
    const PICKUP_DESTINATION_NAME_MAX_CHARACTERS = 25;
    const DELIVERY_DESTINATION_NAME_MIN_CHARACTERS = 3;
    const DELIVERY_DESTINATION_NAME_MAX_CHARACTERS = 25;
    const PICKUP_DESTINATION_ADDRESS_MIN_CHARACTERS = 3;
    const ORANGE_MONEY_MERCHANT_CODE_MIN_CHARACTERS = 3;
    const ORANGE_MONEY_MERCHANT_CODE_MAX_CHARACTERS = 255;
    const PICKUP_DESTINATION_ADDRESS_MAX_CHARACTERS = 100;
    const SUPPORTED_PAYMENT_METHOD_NAME_MIN_CHARACTERS = 3;
    const SUPPORTED_PAYMENT_METHOD_NAME_MAX_CHARACTERS = 20;
    const NUMBER_OF_EMPLOYEES_MAX_CHARACTERS = 65535;   //  since we use unsignedSmallInteger() table schema

    protected $casts = [
        'online' => 'boolean',
        'verified' => 'boolean',
        'allow_pickup' => 'boolean',
        'is_brand_store' => 'boolean',
        'allow_delivery' => 'boolean',
        'adverts' => JsonToArray::class,
        'identified_orders' => 'boolean',
        'perfect_pay_enabled' => 'boolean',
        'dpo_payment_enabled' => 'boolean',
        'is_influencer_store' => 'boolean',
        'allow_free_delivery' => 'boolean',
        'registered_with_bank' => 'boolean',
        'registered_with_cipa' => 'boolean',
        'delivery_flat_fee' => Money::class,
        'allow_deposit_payments' => 'boolean',
        'mobile_number' => MobileNumber::class,
        'last_subscription_end_at' => 'datetime',
        'allow_installment_payments' => 'boolean',
        'orange_money_payment_enabled' => 'boolean',
        'deposit_percentages' => JsonToArray::class,
        'pickup_destinations' => JsonToArray::class,
        'installment_percentages' => JsonToArray::class,
        'delivery_destinations' => DeliveryDestinations::class,
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class,

        //  This property is eager loaded using the withAvg() method
        'rating' => 'decimal:1'
    ];

    protected $fillable = [
        'emoji', 'logo', 'cover_photo', 'adverts', 'name', 'call_to_action', 'description', 'mobile_number', 'currency', 'registered_with_bank', 'banking_with', 'registered_with_cipa', 'registered_with_cipa_as',
        'company_uin', 'number_of_employees', 'verified', 'online', 'offline_message', 'identified_orders',
        'user_id', 'last_subscription_end_at', 'allow_delivery', 'allow_free_delivery',
        'pickup_note', 'delivery_note', 'delivery_fee', 'delivery_flat_fee',
        'delivery_destinations', 'allow_pickup', 'pickup_note', 'pickup_destinations',
        'perfect_pay_enabled', 'orange_money_payment_enabled', 'orange_money_merchant_code', 'dpo_payment_enabled', 'dpo_company_token', 'allow_deposit_payments', 'deposit_percentages',
        'allow_installment_payments', 'installment_percentages',
        'is_brand_store', 'is_influencer_store', 'sms_sender_name'
    ];

    /*
     *  Scope: Return stores that are being searched using the store name
     */
    public function scopeSearch($query, $searchWord)
    {
        // Check if the provided string is a Botswana mobile number
        if (MobileNumberService::isValidOrangeMobileNumber($searchWord)) {

            return $query->searchMobileNumber()->orWhereHas('teamMembers', function ($teamMembers) use ($searchWord) {
                $teamMembers->where('team_member_role', 'Creator')
                            ->searchMobileNumber($searchWord);
            });

        // Check if the provided string is a ussd code e.g *250*100#
        }else if (UssdService::isValidUssdCode($searchWord) && $code = UssdService::getUssdLastReply($searchWord)) {

            return $query->whereHas('visitShortcode', function ($visitShortcode) use ($code) {
                $visitShortcode->notExpired()->where('code', $code);
            });

        }else{

            //  If the search word contains letters, then search by store name
            return $query->where('name', 'like', "%$searchWord%");

        }
    }

    /*
     *  Scope: Return stores that are being searched using the mobile number
     */
    public function scopeSearchMobileNumber($query, $mobileNumber)
    {
        $mobileNumber = MobileNumberService::addMobileNumberExtension($mobileNumber);
        return $query->where('stores.mobile_number', $mobileNumber);
    }

    /*
     *  Scope: Return stores that are brand stores
     */
    public function scopeBrandStores($query)
    {
        return $query->where('is_brand_store', '1');
    }

    /*
     *  Scope: Return stores that are influencer stores
     */
    public function scopeInfluencerStores($query)
    {
        return $query->where('is_influencer_store', '1');
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Returns the associated products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     *  Returns the associated reviews
     */
    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    /**
     *  Returns the associated coupons
     */
    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    /**
     *  Returns the associated carts
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     *  Returns the associated orders
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     *  Returns the associated payment methods that have been assigned to this store
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function supportedPaymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'store_payment_method_association', 'store_id', 'payment_method_id')
                    ->withPivot(StorePaymentMethodAssociation::VISIBLE_COLUMNS)
                    ->using(StorePaymentMethodAssociation::class)
                    ->as('store_payment_method_association');
    }

    /**
     *  Returns the current authenticated user and store association
     *
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function authUserStoreAssociation()
    {
        return $this->hasOne(UserStoreAssociation::class, 'store_id')
                    ->where('user_id', auth()->user()->id);
    }

    /**
     *  Returns the associated users that have been assigned to this store
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_store_association', 'store_id', 'user_id')
                    ->withPivot(UserStoreAssociation::VISIBLE_COLUMNS)
                    ->using(UserStoreAssociation::class)
                    ->as('user_store_association');
    }

    /**
     *  Returns the associated users that have been assigned to this store as a team member
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function teamMembers()
    {
        return $this->users()->whereNotNull('team_member_status');
    }

    /**
     *  Returns the associated users that have been assigned to this store as a follower
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function followers()
    {
        return $this->users()->whereNotNull('follower_status');
    }

    /**
     *  Returns the associated users that have been assigned to this store as a customer
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function customers()
    {
        return $this->users()->where('is_associated_as_customer', '1');
    }

    /**
     *  Returns the shortcodes owned by this store
     */
    public function shortcodes()
    {
        return $this->morphMany(Shortcode::class, 'owner');
    }

    /**
     *  Returns the shortcode owned by this store
     */
    public function shortcode()
    {
        return $this->morphOne(Shortcode::class, 'owner');
    }

    /**
     *  Returns the latest payment shortcodes owned by this store
     *  A single store can have multiple payment shortcodes
     *  reserved for various users
     */
    public function activePaymentShortcodes()
    {
        return $this->shortcodes()->action('Pay')->notExpired()->latest();
    }

    /**
     *  Returns the latest payment shortcode owned by this store
     *  and reserved for the current authenticated user
     */
    public function authPaymentShortcode()
    {
        return $this->shortcode()->action('Pay')->notExpired()->belongsToAuth()->latest();
    }

    /**
     *  Returns the latest visit shortcode owned by this store
     */
    public function visitShortcode()
    {
        return $this->shortcode()->action('Visit')->notExpired()->latest();
    }

    /**
     *  Returns the subscriptions to this store
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'owner')->latest();
    }

    /**
     *  Returns the non-expired subscriptions to this store
     */
    public function activeSubscriptions()
    {
        return $this->subscriptions()->notExpired();
    }

    /**
     *  Returns the current authenticated user's non-expired
     *  subscriptions to this store
     */
    public function authSubscriptions()
    {
        return $this->subscriptions()->belongsToAuth();
    }

    /**
     *  Returns the current authenticated user's non-expired
     *  subscriptions to this store
     */
    public function authActiveSubscriptions()
    {
        return $this->subscriptions()->notExpired()->belongsToAuth();
    }

    /**
     *  Returns the current authenticated user's expired
     *  subscriptions to this store
     */
    public function authInactiveSubscriptions()
    {
        return $this->subscriptions()->expired()->belongsToAuth();
    }

    /**
     *  Returns the current authenticated user's non-expired
     *  subscription to this store
     */
    public function authActiveSubscription()
    {
        return $this->morphOne(Subscription::class, 'owner')->notExpired()->belongsToAuth()->latest();
    }

    /**
     *  Returns the current authenticated user's expired
     *  subscription to this store
     */
    public function authInactiveSubscription()
    {
        return $this->morphOne(Subscription::class, 'owner')->expired()->belongsToAuth()->latest();
    }

    /**
     *  Get the Friend Groups of this Store
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function friendGroups()
    {
        return $this->belongsToMany(FriendGroup::class, 'friend_group_store_association', 'store_id', 'friend_group_id')
                    ->withPivot(FriendGroupStoreAssociation::VISIBLE_COLUMNS)
                    ->using(FriendGroupStoreAssociation::class)
                    ->as('friend_group_store_association');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

     protected $appends = [
        'shopper_access', 'team_member_access',
     ];

    /**
     *  Attribute to check if Orange Money payment is enabled for this store
     */
    protected function orangeMoneyPaymentEnabled(): Attribute
    {
         return new Attribute(
             get: fn($value) => $value && !empty($this->orange_money_merchant_code)
         );
    }

    /**
     *  Attribute to check if DPO payment is enabled for this store
     */
    protected function dpoPaymentEnabled(): Attribute
    {
         return new Attribute(
             get: fn($value) => $value && !empty($this->dpo_company_token)
         );
    }

    /**
     *  Attribute to check if the user can access this store as a shopper
     */
    protected function shopperAccess(): Attribute
    {
        /**
         *  @var App\Models\Store $store
         */
        $store = $this;
        $status = false;
        $expiresAt = null;
        $description = null;

        //  Check if the last subscription to this store by any team member exists
        $hasLastSubscriptionByAnyTeamMember = !empty($store->last_subscription_end_at);

        //  Check if the last subscription by any team member has not expired
        $lastSubscriptionByAnyTeamMemberHasNotExpired = $hasLastSubscriptionByAnyTeamMember ? Carbon::parse($store->last_subscription_end_at)->isFuture() : false;

        //  Check if the store is online and if the last subscription by any team member has not yet expired
        if( $store->online && $hasLastSubscriptionByAnyTeamMember && $lastSubscriptionByAnyTeamMemberHasNotExpired ) {

            //  Shopper can access this store
            $status = true;
            $expiresAt = $store->last_subscription_end_at;

        }else{

            //  If the store has a custom offline message
            if( !empty($store->offline_message) ) {

                //  The user cannot shop (show custom message)
                $description = $store->offline_message;

            }else{

                //  The user cannot shop (show default message)
                $description = 'We are currently closed';

            }

        }

        return new Attribute(
            get: fn() => [
                'status' => $status,
                'expires_at' => $expiresAt,
                'description' => $description,
            ]
        );
    }


    /**
     *  Get the user and store association pivot model is provided
     *
     *  return @var App\Models\Pivots\UserStoreAssociation $userStoreAssociation
     */
    public function getUserStoreAssociation()
    {
        $userStoreAssociation = null;

        /**
         *  Check if the user and store association pivot model is loaded so that
         *  we can determine if the user can access this shop. If the relationship
         *  "user_store_association" exists then this store was acquired directly
         *  from a user and store relationship e.g
         *
         *  $user->stores()->first();
         */
        if( $this->relationLoaded('user_store_association') ) {

            /**
             *  @var App\Models\Pivots\UserStoreAssociation $userStoreAssociation
             */
            $userStoreAssociation = $this->user_store_association;

        /**
         *  Check if the user and store association pivot model is loaded so that
         *  we can determine if the user can access this shop. If the relationship
         *  "authUserStoreAssociation" exists then this store was acquired without
         *  a user and store relationship but the UserStoreAssociation was then
         *  eager loaded on the store e.g
         *
         *  Store::with('authUserStoreAssociation')->first();
         */
        }elseif( $this->relationLoaded('authUserStoreAssociation') ) {

            /**
             *  @var App\Models\Pivots\UserStoreAssociation $userStoreAssociation
             */
            $userStoreAssociation = $this->authUserStoreAssociation;

        }

        return $userStoreAssociation;
    }

    /**
     * Attribute to check if the user can access this store as a team member
     */
    protected function teamMemberAccess(): Attribute
    {
        $status = false;
        $expiresAt = null;
        $description = null;
        $userStoreAssociation = $this->getUserStoreAssociation();

        if( !is_null($userStoreAssociation) ) {

            //  If the current authenticated user is associated as a team member
            $authIsTeamMemberWhoHasJoined = $userStoreAssociation->is_team_member_who_has_joined;

            //  If we are a team member
            if( $authIsTeamMemberWhoHasJoined ) {

                //  Check if the current authenticated user's last subscription to this store exists
                $hasLastSubscriptionByCurrentTeamMember = !empty($userStoreAssociation->last_subscription_end_at);

                //  If the team member has subscribed before
                if( $hasLastSubscriptionByCurrentTeamMember ) {

                    //  Check if the last subscription by the current authenticated user has not expired
                    $lastSubscriptionByCurrentTeamMemberHasNotExpired = $hasLastSubscriptionByCurrentTeamMember ? Carbon::parse($userStoreAssociation->last_subscription_end_at)->isFuture() : false;

                    //  If the last subscription by the currrent team member has not yet expired
                    if( $lastSubscriptionByCurrentTeamMemberHasNotExpired ) {

                        //  Team member can access this store
                        $status = true;
                        $expiresAt = $userStoreAssociation->last_subscription_end_at;

                    }else{

                        $description = 'Subscribe to continue selling';

                    }

                }else{

                    $description = 'Subscribe to start selling';

                }

            }

            return new Attribute(
                get: fn() => [
                    'status' => $status,
                    'expires_at' => $expiresAt,
                    'description' => $description,
                ]
            );

        }else{

            return new Attribute(
                get: fn() => null
            );

        }
    }

}
