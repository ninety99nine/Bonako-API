<?php

namespace App\Traits;

use App\Models\Pivots\UserFriendGroupAssociation;

trait UserFriendGroupAssociationTrait
{
    /*
     *  Scope: Return users that have joined the friend group team as a creator
     */
    public function scopeJoinedGroupAsCreator($query)
    {
        return $query->where('user_friend_group_association.role', 'Creator');
    }

    /*
     *  Scope: Return users that have joined the friend group team as a non-creator
     */
    public function scopeJoinedGroupAsNonCreator($query)
    {
        return $query->where('user_friend_group_association.role', 'Member');
    }
}
