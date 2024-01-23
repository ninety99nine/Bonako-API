<?php

namespace App\Models;

use App\Traits\UserTrait;
use App\Casts\MobileNumber;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Models\Base\BaseAuthenticatable;
use App\Traits\UserStoreAssociationTrait;
use App\Models\Pivots\UserStoreAssociation;
use App\Models\Pivots\UserFriendAssociation;
use App\Traits\UserOrderViewAssociationTrait;
use App\Traits\UserFriendGroupAssociationTrait;
use App\Models\Pivots\UserFriendGroupAssociation;
use App\Models\Pivots\UserOrderCollectionAssociation;
use App\Notifications\Orders\OrderCreated;
use App\Notifications\Orders\OrderUpdated;
use App\Notifications\Stores\StoreCreated;
use App\Services\MobileNumber\MobileNumberService;
use App\Services\Ussd\UssdService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notification;


class User extends BaseAuthenticatable /* Authenticatable */
{
    use HasApiTokens, HasFactory, Notifiable, UserTrait, UserStoreAssociationTrait,
    UserOrderViewAssociationTrait, UserFriendGroupAssociationTrait;

    /**
     *  Magic Numbers
     */
    const FIRST_NAME_MIN_CHARACTERS = 3;
    const FIRST_NAME_MAX_CHARACTERS = 20;
    const LAST_NAME_MIN_CHARACTERS = 3;
    const LAST_NAME_MAX_CHARACTERS = 20;
    const PASSWORD_MIN_CHARACTERS = 6;
    const ABOUT_ME_MAX_CHARACTERS = 200;
    const ABOUT_ME_MIN_CHARACTERS = 3;
    const NOTIFICATION_FILTERS = ['All', 'Read', 'Unread', 'Orders', 'Followers', 'Invitations', 'Friend Groups'];

    protected $casts = [
        'accepted_terms_and_conditions' => 'boolean',
        'mobile_number_verified_at' => 'datetime',
        'mobile_number' => MobileNumber::class,
        'is_super_admin' => 'boolean',
        'last_seen_at' => 'datetime',
        'is_guest' => 'boolean',
    ];

    protected $fillable = [
        'first_name', 'last_name', 'about_me', 'profile_photo', 'password', 'mobile_number', 'mobile_number_verified_at',
        'accepted_terms_and_conditions', 'last_seen_at', 'registered_by_user_id', 'is_guest', 'is_super_admin'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];


    /**
     * Route notifications for the OneSignal channel.
     *
     * Reference: https://github.com/laravel-notification-channels/onesignal
     */
    public function routeNotificationForOneSignal(Notification $notification): mixed
    {
        return ['include_external_user_ids' => ["$this->id"]];
    }

    /**
     * Route notifications for the Slack channel.
     */
    public function routeNotificationForSlack(Notification $notification): mixed
    {
        if($notification instanceof OrderCreated || $notification instanceof OrderUpdated) {

            return config('app.ORDERS_SLACK_WEBHOOK_URL');

        }elseif($notification instanceof StoreCreated) {

            return config('app.STORES_SLACK_WEBHOOK_URL');

        }
    }

    /**
     *  The channels the user receives notification broadcasts on.
     *
     *  Reference: https://laravel.com/docs/10.x/notifications#customizing-the-notification-channel
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'user.notifications.'.$this->id;
    }

    /*
     *  Scope: Return users that are being searched
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query->whereRaw('concat(first_name," ",last_name) like ?', "%{$searchWord}%")
                     ->orWhere('mobile_number', 'like', "%{$searchWord}%");

        //  If the search word contains numbers, then search by shortcode
    }

    /*
     *  Scope: Return users that are being searched using the mobile number
     */
    public function scopeSearchMobileNumber($query, $mobileNumber)
    {
        $mobileNumber = MobileNumberService::addMobileNumberExtension($mobileNumber);
        return $query->where('users.mobile_number', $mobileNumber);
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Returns the associated stores that have been assigned to this user
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'user_store_association', 'user_id', 'store_id')
                    ->withPivot(UserStoreAssociation::VISIBLE_COLUMNS)
                    ->using(UserStoreAssociation::class)
                    ->as('user_store_association');
    }

    /**
     *  Get the Stores that have this User assigned as a team member
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function storesAsRecentVisitor()
    {
        return $this->stores()->whereNotNull('last_seen_at');
    }

    /**
     *  Get the Stores that have this User assigned as a team member
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function storesAsTeamMember()
    {
        return $this->stores()->whereNotNull('team_member_status');
    }

    /**
     *  Get the Stores that have this User assigned as a follower
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function storesAsFollower()
    {
        return $this->stores()->whereNotNull('follower_status');
    }

    /**
     *  Get the Stores that have this User assigned as a customer
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function storesAsCustomer()
    {
        return $this->stores()->where('is_associated_as_customer', '1');
    }

    /**
     *  Get the Stores that have been assigned to this user
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function storesAsAssigned()
    {
        return $this->stores()->where('is_assigned', '1');
    }

    /**
     *  Returns the associated reviews that have been submitted by this user
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     *  Returns the associated addresses that have been assigned to this user
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     *  Get the friends (Users) of this User
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function friends()
    {
        return $this->belongsToMany(User::class, 'user_friend_association', 'user_id', 'friend_user_id')
                    ->withPivot(UserFriendAssociation::VISIBLE_COLUMNS)
                    ->using(UserFriendAssociation::class)
                    ->as('user_friend_association');
    }

    /**
     *  Get the Friend Groups of this User
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function friendGroups()
    {
        return $this->belongsToMany(FriendGroup::class, 'user_friend_group_association', 'user_id', 'friend_group_id')
                    ->withPivot(UserFriendGroupAssociation::VISIBLE_COLUMNS)
                    ->using(UserFriendGroupAssociation::class)
                    ->as('user_friend_group_association');
    }

    /**
     *  Get the Orders where this User is listed as a customer or friend
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'user_order_collection_association', 'user_id', 'order_id')
                    ->withPivot(UserOrderCollectionAssociation::VISIBLE_COLUMNS)
                    ->using(UserOrderCollectionAssociation::class)
                    ->as('user_order_collection_association');
    }

    /**
     *  Get the Orders where this User is listed as a customer
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function ordersAsCustomerOrFriend()
    {
        return $this->orders()->where('user_order_collection_association.role', 'Customer')
                              ->orWhere('user_order_collection_association.role', 'Friend');
    }

    /**
     *  Get the Orders where this User is listed as a customer
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function ordersAsCustomer()
    {
        return $this->orders()->where('user_order_collection_association.role', 'Customer');
    }

    /**
     *  Get the Orders where this User is listed as a friend
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function ordersAsFriend()
    {
        return $this->orders()->where('user_order_collection_association.role', 'Friend');
    }

    public function aiAssistant()
    {
        return $this->hasOne(AiAssistant::class);
    }

    public function aiMessages()
    {
        return $this->hasMany(AiMessage::class);
    }

    public function smsAlert()
    {
        return $this->hasOne(SmsAlert::class);
    }

    /**
     *  Returns transactions where the user is associated as a payer
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany
     */
    public function transactionsAsPayer()
    {
        return $this->hasMany(Transaction::class, 'paid_by_user_id');
    }

    /**
     *  Returns paid transactions where the user is associated as a payer
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany
     */
    public function paidTransactionsAsPayer()
    {
        return $this->transactionsAsPayer()->where('payment_status', 'Paid');
    }

    /**
     *  Returns latest transaction where the user is associated as a payer
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany
     */
    public function latestTransactionAsPayer()
    {
        return $this->hasOne(Transaction::class, 'paid_by_user_id')->latest();
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'name', 'requires_password', 'mobile_number_shortcode'
    ];

    public function getNameAttribute()
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getMobileNumberShortcodeAttribute()
    {
        return UssdService::appendToMainShortcode(MobileNumberService::removeMobileNumberExtension($this->getRawOriginal('mobile_number')));
    }

    public function getRequiresPasswordAttribute()
    {
        return empty($this->password);
    }

    public function getRequiresMobileNumberVerificationAttribute()
    {
        return empty($this->mobile_number_verified_at);
    }
}
