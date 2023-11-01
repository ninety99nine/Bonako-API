<?php

namespace App\Models\Base;

use App\Traits\Base\BaseTrait;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

class BasePivot extends BaseModel
{
    use AsPivot, BaseTrait;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
