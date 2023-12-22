<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Traits\UserFriendGroupAssociationTrait;
use App\Models\Pivots\UserFriendGroupAssociation;
use App\Models\Pivots\FriendGroupOrderAssociation;
use App\Models\Pivots\FriendGroupStoreAssociation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class FriendGroup extends BaseModel
{
    use HasFactory, UserFriendGroupAssociationTrait;

    const FILTERS = ['Groups', 'Shared Groups'];    //  or ['Created', 'Shared']
    const MEMBER_FILTERS = ['All', 'Creator', 'Admins', 'Members'];
    const STORE_FILTERS = ['All', 'Popular'];

    protected $casts = [
        'shared' => 'boolean',
        'can_add_friends' => 'boolean',
    ];

    protected $fillable = [
        'emoji', 'name', 'description', 'shared', 'can_add_friends'
    ];

    /**
     *  Magic Numbers
     */
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 60;
    const DESCRIPTION_MIN_CHARACTERS = 3;
    const DESCRIPTION_MAX_CHARACTERS = 120;

    /*
     *  Scope: Return friend groups that are being searched
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query->where('name', 'like', "%$searchWord%");
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Get the Users of this Friend Group
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_friend_group_association', 'friend_group_id', 'user_id')
                    ->withPivot(UserFriendGroupAssociation::VISIBLE_COLUMNS)
                    ->using(UserFriendGroupAssociation::class)
                    ->as('user_friend_group_association');
    }

    /**
     *  Get the Stores of this Friend Group
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'friend_group_store_association', 'friend_group_id', 'store_id')
                    ->withPivot(FriendGroupStoreAssociation::VISIBLE_COLUMNS)
                    ->using(FriendGroupStoreAssociation::class)
                    ->as('friend_group_store_association');
    }

    /**
     *  Get the Orders of this Friend Group
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'friend_group_order_association', 'friend_group_id', 'order_id')
                    ->withPivot(FriendGroupOrderAssociation::VISIBLE_COLUMNS)
                    ->using(FriendGroupOrderAssociation::class)
                    ->as('friend_group_order_association');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [];
}
