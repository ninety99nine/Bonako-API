<?php

namespace App\Models\Pivots;

use App\Casts\MobileNumber;
use App\Casts\TeamMemberPermissions;
use App\Models\User;
use App\Models\Store;
use App\Models\Base\BasePivot;
use App\Models\UserStoreOrderStatistic;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserStoreAssociation extends BasePivot
{
    use HasFactory;

    protected $casts = [
        'is_assigned' => 'boolean',

        'last_seen_at' => 'datetime',
        'last_seen_on_ussd_at' => 'datetime',
        'last_seen_on_web_app_at' => 'datetime',
        'last_seen_on_mobile_app_at' => 'datetime',

        'mobile_number' => MobileNumber::class,
        'is_associated_as_customer' => 'boolean',
        'last_subscription_end_at' => 'datetime',
        'team_member_permissions' => TeamMemberPermissions::class,
    ];

    const TEAM_MEMBER_FILTERS = ['All', ...self::TEAM_MEMBER_STATUSES];

    const TEAM_MEMBER_STATUSES = ['Joined', 'Left', 'Invited', 'Declined'];

    const TEAM_MEMBER_ROLES = ['Creator', 'Admin', 'Team Member'];

    const FOLLOWER_FILTERS = ['All', ...self::FOLLOWER_STATUSES];

    const FOLLOWER_STATUSES = ['Following', 'Unfollowed', 'Invited', 'Declined'];

    const CUSTOMER_FILTERS = ['All', 'Loyal'];

    const VISIBLE_COLUMNS = [

        'id',

        /*  User Information  */
        'mobile_number',

        /*  Team Member Information  */
        'team_member_status', 'team_member_role', 'team_member_permissions', 'team_member_join_code', 'invited_to_join_team_by_user_id', 'last_subscription_end_at',

        /*  Follower Information  */
        'follower_status', 'invited_to_follow_by_user_id',

        /*  Customer Information  */
        'is_associated_as_customer',

        /*  Assigned Information  */
        'is_assigned', 'assigned_position',

        /*  Timestamps  */
        'last_seen_on_mobile_app_at',
        'last_seen_on_web_app_at',
        'last_seen_on_ussd_at',
        'last_seen_at',
        'created_at',
        'updated_at'

    ];

    /**
     *  Returns the associated store
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     *  Returns the user who invited the associated user to follow the associated store
     */
    public function userWhoInvitedToFollow()
    {
        return $this->belongsTo(User::class, 'invited_to_follow_by_user_id');
    }

    /**
     *  Returns the user who invited the associated user to join the associated store team
     */
    public function userWhoInvitedToJoinTeam()
    {
        return $this->belongsTo(User::class, 'invited_to_join_team_by_user_id');
    }

    /**
     *  Returns the user store association statistic
     *
     *  @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userStoreOrderStatistic()
    {
        return $this->hasOne(UserStoreOrderStatistic::class, 'user_store_association_id');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'is_follower', 'is_unfollower', 'is_follower_who_is_invited', 'is_follower_who_has_declined',
        'is_team_member_who_has_joined', 'is_team_member_who_has_left', 'is_team_member_who_is_invited', 'is_team_member_who_has_declined',
        'is_team_member_as_creator_or_admin', 'is_team_member_as_creator', 'is_team_member_as_admin',
        'can_manage_everything', 'can_manage_orders', 'can_manage_products', 'can_manage_coupons', 'can_manage_customers',
        'can_manage_team_members', 'can_manage_instant_carts', 'can_manage_settings',
        'has_full_permissions', 'last_subscription_has_expired'
    ];

    /**
     *  Check if this user last subscription has expired
    */
    protected function lastSubscriptionHasExpired(): Attribute
    {
        return new Attribute(
            get: fn () => $this->getRawOriginal('last_subscription_end_at') == null ? null : \Carbon\Carbon::parse($this->getRawOriginal('last_subscription_end_at'))->isBefore(now())
        );
    }

    /**
     *  Check if this user is classified as a follower
    */
    protected function hasFullPermissions(): Attribute
    {
        return new Attribute(
            get: fn () => collect(json_decode($this->getRawOriginal('team_member_permissions')))->contains('*')
        );
    }

    /**
     *  Check if this user is classified as a follower
    */
    protected function isFollower(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('follower_status')) === 'following'
        );
    }

    /**
     *  Check if this user is classified as an unfollower
    */
    protected function isUnfollower(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('follower_status')) === 'unfollowed'
        );
    }

    /**
     *  Check if this user is classified as a follower who has been invited to follow
    */
    protected function isFollowerWhoIsInvited(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('follower_status')) === 'invited'
        );
    }

    /**
     *  Check if this user is classified as a follower who has declined the invitation to follow
    */
    protected function isFollowerWhoHasDeclined(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('follower_status')) === 'declined'
        );
    }

    /**
     *  Check if this user is classified as a team member who has joined the team
     */
    protected function isTeamMemberWhoHasJoined(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('team_member_status')) === 'joined'
        );
    }

    /**
     *  Check if this user is classified as a team member who has left the team
     */
    protected function isTeamMemberWhoHasLeft(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('team_member_status')) === 'left'
        );
    }

    /**
     *  Check if this user is classified as a team member who has been invited to join the team
     */
    protected function isTeamMemberWhoIsInvited(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('team_member_status')) === 'invited'
        );
    }

    /**
     *  Check if this user is classified as a team member who has declined the invitation to join the team
     */
    protected function isTeamMemberWhoHasDeclined(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('team_member_status')) === 'declined'
        );
    }

    /**
     *  Check if this user is classified as a team member who is a creator
     */
    protected function isTeamMemberAsCreatorOrAdmin(): Attribute
    {
        return new Attribute(
            get: fn () => $this->is_team_member_as_creator || $this->is_team_member_as_admin
        );
    }

    /**
     *  Check if this user is classified as a team member who is a creator
     */
    protected function isTeamMemberAsCreator(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('team_member_role')) === 'creator'
        );
    }

    /**
     *  Check if this user is classified as a team member who is a admin
     */
    protected function isTeamMemberAsAdmin(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('team_member_role')) === 'admin'
        );
    }

    /**
     *  Check if this user can manage everything
     */
    protected function canManageEverything(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasPermissionTo('*')
        );
    }

    /**
     *  Check if this user can manage orders
     */
    protected function canManageOrders(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasPermissionTo('manage orders')
        );
    }

    /**
     *  Check if this user can manage products
     */
    protected function canManageProducts(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasPermissionTo('manage products')
        );
    }

    /**
     *  Check if this user can manage coupons
     */
    protected function canManageCoupons(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasPermissionTo('manage coupons')
        );
    }

    /**
     *  Check if this user can manage customers
     */
    protected function canManageCustomers(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasPermissionTo('manage customers')
        );
    }

    /**
     *  Check if this user can manage team members
     */
    protected function canManageTeamMembers(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasPermissionTo('manage team members')
        );
    }

    /**
     *  Check if this user can manage instant carts
     */
    protected function canManageInstantCarts(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasPermissionTo('manage instant carts')
        );
    }

    /**
     *  Check if this user can manage settings
     */
    protected function canManageSettings(): Attribute
    {
        return new Attribute(
            get: fn () => $this->hasPermissionTo('manage settings')
        );
    }

    /**
     *  Check if this user can manage the specified permission
     */
    protected function hasPermissionTo($permission)
    {
        // Check if the user has joined the team
        if($this->is_team_member_who_has_joined) {

            //  Set to empty array if these permissions are null
            $permissions = $this->team_member_permissions->map(fn($permission) => $permission['grant'])->toArray() ?? [];

            //  Check if we have all permissions
            if (in_array('*', $permissions ?? [], true)) return true;

            $lowercasePermissions = array_map('strtolower', $permissions);

            // Check if we have the specific permission
            return in_array(strtolower($permission), $lowercasePermissions, true);

        }else{

            return false;

        }
    }
}
