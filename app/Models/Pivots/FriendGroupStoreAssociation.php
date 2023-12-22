<?php

namespace App\Models\Pivots;

use App\Models\Base\BasePivot;

class FriendGroupStoreAssociation extends BasePivot
{
    const VISIBLE_COLUMNS = ['id', 'added_by_user_id', 'created_at', 'updated_at'];
}
