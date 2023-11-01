<?php

namespace App\Models\Pivots;

use App\Models\User;
use App\Models\Order;
use App\Models\Base\BasePivot;
use Illuminate\Database\Eloquent\Casts\Attribute;

class UserOrderCollectionAssociation extends BasePivot
{
    protected $casts = [
        'can_collect' => 'boolean',
        'collection_code_expires_at' => 'datetime'
    ];

    const ROLES = [
        'Customer', 'Friend'
    ];

    const VISIBLE_COLUMNS = [
        'id', 'role', 'collection_code', 'collection_qr_code', 'collection_code_expires_at', 'can_collect', 'created_at', 'updated_at'
    ];

    /**
     *  Returns the associated user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  Returns the associated order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'is_associated_as_customer', 'is_associated_as_friend'
    ];

    /**
     *  Check if this user is classified as a customer
    */
    protected function isAssociatedAsCustomer(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('role')) === 'customer'
        );
    }

    /**
     *  Check if this user is classified as a customer
    */
    protected function isAssociatedAsFriend(): Attribute
    {
        return new Attribute(
            get: fn () => strtolower($this->getRawOriginal('role')) === 'friend'
        );
    }
}
