<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Status;
use App\Casts\Currency;
use App\Casts\JsonToArray;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubscriptionPlan extends BaseModel
{
    use HasFactory;

    const TYPES = ['Subscription', 'One-Off'];
    const SERVICES = ['Store Access', 'Store Reporting Access', 'AI Assistant Access', 'SMS Alerts'];

    protected $casts = [
        'active' => 'boolean',
        'price' => Money::class,
        'metadata' => JsonToArray::class,
    ];

    protected $tranformableCasts = [
        'active' => Status::class,
        'currency' => Currency::class,
    ];

    protected $fillable = [

        /*  Basic Information  */
        'name', 'description', 'service', 'type', 'currency', 'price', 'active', 'metadata'

    ];
}
