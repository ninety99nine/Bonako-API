<?php

namespace App\Models\Pivots;

use App\Models\Base\BasePivot;

class UserFriendAssociation extends BasePivot
{
    protected $casts = [
        'last_selected_at' => 'datetime'
    ];

    const VISIBLE_COLUMNS = ['id', 'last_selected_at', 'created_at', 'updated_at'];
}
