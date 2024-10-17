<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Traits\FriendGroupUserAssociationTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Pivots\FriendGroupUserAssociation;
use App\Models\Pivots\FriendGroupStoreAssociation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class FriendGroup extends BaseModel
{
    use HasFactory, FriendGroupUserAssociationTrait;

    const FILTERS = ['Groups', 'Shared Groups'];    //  or ['Created', 'Shared']
    const MEMBER_FILTERS = ['All', 'Creator', 'Admins', 'Members'];
    const STORE_FILTERS = ['All', 'Popular'];

    protected $casts = [
        'shared' => 'boolean',
        'can_add_friends' => 'boolean',
        'created_by_super_admin' => 'boolean',
    ];

    protected $fillable = [
        'emoji', 'name', 'description', 'shared', 'can_add_friends', 'created_by_super_admin'
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
        return $this->belongsToMany(User::class, 'friend_group_user_association', 'friend_group_id', 'user_id')
                    ->withPivot(FriendGroupUserAssociation::VISIBLE_COLUMNS)
                    ->using(FriendGroupUserAssociation::class)
                    ->as('friend_group_user_association');
    }

    /**
     *  Get the Users of this Friend Group
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function nonGuestUsers()
    {
        return $this->users()->notGuest();
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
        return $this->hasMany(Order::class);
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'name_with_emoji'
    ];

    public function nameWithEmoji(): Attribute
    {
        return new Attribute(
            get: fn() => empty($this->emoji) ? $this->name : $this->emoji.' '.$this->name
        );
    }
}
