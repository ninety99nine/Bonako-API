<?php

namespace App\Models\Pivots;

use App\Models\Base\BasePivot;

class UserOrderViewAssociation extends BasePivot
{
    protected $casts = [
        'last_seen_at' => 'datetime'
    ];

    const VISIBLE_COLUMNS = ['id', 'views', 'last_seen_at', 'updated_at', 'created_at'];
}
