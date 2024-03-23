<?php

namespace App\Models\Pivots;

use App\Models\User;
use App\Models\Store;
use App\Casts\MobileNumber;
use App\Models\Base\BasePivot;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserFriendGroupAssociation extends BasePivot
{
    protected $casts = [
        'last_selected_at' => 'datetime',
        'mobile_number' => MobileNumber::class,
    ];

    CONST DEFAULT_ROLE = 'Member';
    const ROLES = ['Creator', 'Admin', 'Member'];

    CONST DEFAULT_STATUS = 'Invited';
    const STATUSES = ['Joined', 'Left', 'Invited', 'Declined'];

    const VISIBLE_COLUMNS = ['id', 'role', 'status', 'mobile_number', 'last_selected_at', 'invited_to_join_by_user_id', 'created_at', 'updated_at'];

    /**
     *  Returns the associated friend group
     */
    public function friendGroup()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     *  Returns the user who invited the associated user to join the associated friend group
     */
    public function userWhoInvitedToJoinGroup()
    {
        return $this->belongsTo(User::class, 'invited_to_join_by_user_id');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

     protected $appends = [
        'is_user_who_has_joined', 'is_user_who_has_left', 'is_user_who_is_invited', 'is_user_who_has_declined',
        'is_creator_or_admin', 'is_creator', 'is_admin', 'is_guest'
    ];

    /**
     *  Check if this user is classified as a user who has joined the friend group
     */
    protected function isUserWhoHasJoined(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('status')) === 'joined'
        );
    }

    /**
     *  Check if this user is classified as a user who has left the friend group
     */
    protected function isUserWhoHasLeft(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('status')) === 'left'
        );
    }

    /**
     *  Check if this user is classified as a user who has been invited to join the friend group
     */
    protected function isUserWhoIsInvited(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('status')) === 'invited'
        );
    }

    /**
     *  Check if this user is classified as a user who has declined the invitation to join the friend group
     */
    protected function isUserWhoHasDeclined(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('status')) === 'declined'
        );
    }

    /**
     *  Check if this user is classified as a user who is a creator or admin
     */
    protected function isCreatorOrAdmin(): Attribute
    {
        return new Attribute(
            get: fn () => $this->is_creator || $this->is_admin
        );
    }

    /**
     *  Check if this user is classified as a user who is a creator
     */
    protected function isCreator(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('role')) === 'creator'
        );
    }

    /**
     *  Check if this user is classified as a user who is a admin
     */
    protected function isAdmin(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('role')) === 'admin'
        );
    }

    /**
     *  Check if this user is classified as a user who is a guest
     */
    protected function isGuest(): Attribute
    {
        return new Attribute(
            get: fn () => !empty($this->getRawOriginal('mobile_number'))
        );
    }
}
