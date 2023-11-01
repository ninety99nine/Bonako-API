<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Status;
use App\Casts\Currency;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionPlan extends BaseModel
{
    use HasFactory;

    const TYPES = ['Fixed', 'Variable'];
    const SERVICES = ['Store Access', 'Store Reporting Access', 'AI Assistant Access'];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $tranformableCasts = [
        'price' => Money::class,
        'active' => Status::class,
        'currency' => Currency::class,
    ];

    protected $fillable = [

        /*  Basic Information  */
        'name', 'description', 'service', 'type', 'frequency', 'duration', 'currency', 'price', 'active'

    ];
}
