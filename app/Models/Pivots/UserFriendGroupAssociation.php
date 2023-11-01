<?php

namespace App\Models\Pivots;

use App\Models\Base\BasePivot;

class UserFriendGroupAssociation extends BasePivot
{
    protected $casts = [
        'last_selected_at' => 'datetime'
    ];

    const ROLES = [
        'Creator', 'Member'
    ];

    const VISIBLE_COLUMNS = ['id', 'role', 'last_selected_at', 'created_at', 'updated_at'];
}
