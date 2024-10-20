<?php

namespace App\Models;

use Carbon\Carbon;
use App\Casts\Money;
use App\Casts\Currency;
use App\Traits\AuthTrait;
use App\Traits\StoreTrait;
use App\Casts\JsonToArray;
use App\Enums\CallToAction;
use App\Models\Base\BaseModel;
use App\Casts\E164PhoneNumberCast;
use App\Casts\DeliveryDestinations;
use App\Traits\UserStoreAssociationTrait;
use App\Models\Pivots\UserStoreAssociation;
use Propaganistas\LaravelPhone\PhoneNumber;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Pivots\FriendGroupStoreAssociation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends BaseModel
{
    use HasFactory, StoreTrait, UserStoreAssociationTrait, AuthTrait;

    const CURRENCY = 'BWP';
    const DEFAULT_OFFLINE_MESSAGE = 'We are currently offline';

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

    const USER_STORE_FILTERS = [
        'All', 'Team Member', 'Team Member Left', 'Team Member Joined', 'Team Member Invited', 'Team Member Declined',
        'Team Member Joined As Creator', 'Team Member Joined As Non Creator', 'Follower', 'Unfollower', 'Invited To Follow',
        'Friend Group Member', 'Customer', 'Assigned', 'Recent Visitor', 'Associated', 'Active Subscription'
    ];

    public static function CALL_TO_ACTION_OPTIONS(): array
    {
        return array_map(fn($method) => $method->value, CallToAction::cases());
    }

    /**
     *  Magic Numbers
     */
    const MAXIMUM_ADVERTS = 5;
    const MAXIMUM_COUPONS = 50;
    const MAXIMUM_PRODUCTS = 50;
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 25;
    const ALIAS_MIN_CHARACTERS = 3;
    const ALIAS_MAX_CHARACTERS = 25;
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
        'allow_delivery' => 'boolean',
        'identified_orders' => 'boolean',
        'allow_free_delivery' => 'boolean',
        'delivery_flat_fee' => Money::class,
        'allow_deposit_payments' => 'boolean',
        'last_subscription_end_at' => 'datetime',
        'allow_installment_payments' => 'boolean',
        'deposit_percentages' => JsonToArray::class,
        'pickup_destinations' => JsonToArray::class,
        'has_automated_payment_methods' => 'boolean',
        'installment_percentages' => JsonToArray::class,
        'ussd_mobile_number' => E164PhoneNumberCast::class,
        'contact_mobile_number' => E164PhoneNumberCast::class,
        'whatsapp_mobile_number' => E164PhoneNumberCast::class,
        'delivery_destinations' => DeliveryDestinations::class,
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class,
        'rating' => 'decimal:1' //  Eager loaded using the withAvg() method
    ];

    protected $fillable = [
        'emoji', 'name', 'alias', 'ussd_mobile_number', 'contact_mobile_number', 'whatsapp_mobile_number', 'call_to_action',
        'description', 'currency', 'verified', 'online', 'offline_message', 'identified_orders', 'user_id',
        'last_subscription_end_at', 'allow_delivery', 'allow_free_delivery', 'pickup_note', 'delivery_note',
        'delivery_fee', 'delivery_flat_fee', 'delivery_destinations', 'allow_pickup', 'pickup_note',
        'pickup_destinations', 'allow_deposit_payments', 'deposit_percentages',
        'allow_installment_payments', 'installment_percentages',
        'sms_sender_name', 'has_automated_payment_methods'
    ];

    /************
     *  SCOPES  *
     ***********/

    public function scopeSearch($query, $searchWord)
    {
        $mobileNumber = $searchWord[0] === '+' ? $searchWord : '+' . $searchWord;
        $isMobileNumber = (new PhoneNumber($searchWord))->isValid();

        if($isMobileNumber) {

            $query->where('stores.ussd_mobile_number', $mobileNumber)
                 ->orWhere('stores.contact_mobile_number', $mobileNumber)
                 ->orWhere('stores.whatsapp_mobile_number', $mobileNumber);

        }else{

            return $query->searchName($searchWord);

        }
    }

    /*
     *  Scope: Return stores searched by name
     */
    public function scopeSearchName($query, $searchWord)
    {
        return $query->where('stores.name', 'like', "%$searchWord%");
    }

    /*
     *  Scope: Return stores searched by alias
     */
    public function scopeSearchAlias($query, $searchWord)
    {
        return $query->where('stores.alias', $searchWord);
    }

    /*
     *  Scope: Return stores searched by USSD mobile number
     */
    public function scopeSearchUssdMobileNumber($query, $mobileNumber)
    {
        return $query->where('stores.ussd_mobile_number', $mobileNumber);
    }

    /*
     *  Scope: Return stores searched by contact mobile number
     */
    public function scopeSearchContactMobileNumber($query, $mobileNumber)
    {
        return $query->where('stores.contact_mobile_number', $mobileNumber);
    }

    /*
     *  Scope: Return stores searched by whatsapp mobile number
     */
    public function scopeSearchWhatsappMobileNumber($query, $mobileNumber)
    {
        return $query->where('stores.whatsapp_mobile_number', $mobileNumber);
    }

    /*
     *  Scope: Return stores that have an active subscription
     */
    public function scopeHasActiveSubscription($query)
    {
        return $query->where('stores.last_subscription_end_at', '>' , now());
    }

    /********************
     *  RELATIONSHIPS   *
     *******************/

    public function logo()
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', 'logo');
    }

    public function adverts()
    {
        return $this->morphMany(MediaFile::class, 'mediable')->where('type', 'advert');
    }

    public function coverPhoto()
    {
        return $this->morphOne(MediaFile::class, 'mediable')->where('type', 'cover_photo');
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'owner');
    }

    public function storeQuota()
    {
        return $this->hasOne(StoreQuota::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function placedOrders()
    {
        return $this->orders()->where('placed_by_user_id', $this->hasAuthUser() ? $this->getAuthUser()->id : 0);
    }

    public function createdOrders()
    {
        return $this->belongsTo(User::class, 'created_by_user_id', $this->hasAuthUser() ? $this->getAuthUser()->id : 0);
    }

    /**
     *  Returns the current authenticated user and store association
     *
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function authUserStoreAssociation()
    {
        return $this->hasOne(UserStoreAssociation::class, 'store_id')
                    ->where('user_id', request()->auth_user->id);
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

    public function teamMembers()
    {
        return $this->users()->whereNotNull('team_member_status');
    }

    public function teamMembersWhoLeft()
    {
        return $this->teamMembers()->leftTeam();
    }

    public function teamMembersWhoJoined()
    {
        return $this->teamMembers()->joinedTeam();
    }

    public function teamMemberAsCreator()
    {
        return $this->teamMembers()->joinedTeamAsCreator();
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
     *  Returns the transactions to this store
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
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

    /**
     *  Returns the friend group and store association
     *
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function friendGroupStoreAssociation()
    {
        return $this->hasOne(FriendGroupStoreAssociation::class, 'store_id');
    }

    /**
     *  Get the sms messages associated with this store
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function smsMessages()
    {
        return $this->hasMany(SmsMessage::class);
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'name_with_emoji', 'shopper_access', 'team_member_access',
    ];

    public function nameWithEmoji(): Attribute
    {
        return new Attribute(
            get: fn() => empty($this->emoji) ? $this->name : $this->emoji.' '.$this->name
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
     *  Get the user and store association pivot model if provided
     *
     *  return @var App\Models\Pivots\UserStoreAssociation $userStoreAssociation
     */
    public function getUserStoreAssociationAttribute()
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
            $userStoreAssociation = $this->getRelation('user_store_association');

        /**
         *  Check if the user and store association pivot model is loaded so that
         *  we can determine if the user can access this store. If the relationship
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
            $userStoreAssociation = $this->getRelation('authUserStoreAssociation');

        }

        return $userStoreAssociation;
    }

    /**
     *  Get the friend group and store association pivot model if provided
     *
     *  return @var App\Models\Pivots\FriendGroupStoreAssociation $friendGroupStoreAssociation
     */
    public function getFriendGroupStoreAssociationAttribute()
    {
        $friendGroupStoreAssociation = null;

        /**
         *  Check if the friend group and store association pivot model is loaded.
         *  If the relationship "friend_group_store_association" exists then this
         *  store was acquired directly from a friend group and store relationship
         *  e.g
         *
         *  $friendGroup->stores()->first();
         */
        if( $this->relationLoaded('friend_group_store_association') ) {

            /**
             *  @var App\Models\Pivots\FriendGroupStoreAssociation $friendGroupStoreAssociation
             */
            $friendGroupStoreAssociation = $this->getRelation('friend_group_store_association');

        /**
         *  Check if the friend group and store association pivot model is loaded.
         *  If the relationship "friendGroupStoreAssociation" exists then this
         *  store was acquired without a friend group and store relationship
         *  but the FriendGroupStoreAssociation was then eager loaded on the
         *  store e.g
         *
         *  FriendGroup::with('friendGroupStoreAssociation')->first();
         */
        }elseif( $this->relationLoaded('friendGroupStoreAssociation') ) {

            /**
             *  @var App\Models\Pivots\FriendGroupStoreAssociation $friendGroupStoreAssociation
             */
            $friendGroupStoreAssociation = $this->getRelation('friendGroupStoreAssociation');

        }

        return $friendGroupStoreAssociation;
    }

    /**
     * Attribute to check if the user can access this store as a team member
     */
    protected function teamMemberAccess(): Attribute
    {
        $status = false;
        $expiresAt = null;
        $description = null;
        $userStoreAssociation = $this->user_store_association;

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
