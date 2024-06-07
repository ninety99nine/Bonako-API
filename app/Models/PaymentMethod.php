<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Currency;
use App\Casts\Percentage;
use App\Enums\PaymentMethodFilter;
use App\Models\Base\BaseModel;
use App\Traits\Base\BaseTrait;
use App\Traits\PaymentMethodTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends BaseModel
{
    use HasFactory, BaseTrait, PaymentMethodTrait;

    const FILTERS = [
        PaymentMethodFilter::All->value,
        PaymentMethodFilter::Active->value,
        PaymentMethodFilter::Inactive->value
    ];

    protected $casts = [
        'active' => 'boolean',
        'amount' => Money::class,
        'available_on_ussd' => 'boolean',
        'available_in_stores' => 'boolean',
        'available_on_perfect_pay' => 'boolean',
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class,
        'percentage' => Percentage::class
    ];

    protected $fillable = [
        'name', 'method', 'category', 'description',
        'available_on_perfect_pay', 'available_in_stores', 'available_on_ussd',  'active', 'position'
    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /*
     *  Scope: Return payment methods that are being searched
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query->where('name', 'like', "%$searchWord%")
                     ->orWhere('method', 'like', "%$searchWord%")
                     ->orWhere('category', 'like', "%$searchWord%");
    }

    /**
     *  Scope payment methods available for perfect pay
     *  These are payment methods that allow store owners the ability
     *  to choose the payment method of choice when placing an order
     *  on a given store using perfect pay supported payment methods
     */
    public function scopeAvailableOnPerfectPay($query)
    {
        return $query->active()->where('available_on_perfect_pay', '1');
    }

    /**
     *  Scope payment methods available for stores
     *  These are payment methods that allow subscribers the ability
     *  to choose the payment method of choice when placing an order
     *  on a given store
     */
    public function scopeAvailableInStores($query)
    {
        return $query->active()->where('available_in_stores', '1');
    }

    /**
     *  Scope payment methods available for USSD
     *  These are payment methods that allow subscribers the ability to pay on USSD
     */
    public function scopeAvailableOnUssd($query)
    {
        return $query->active()->where('available_on_ussd', '1');
    }

    /**
     *  Scope active payment methods
     */
    public function scopeActive($query)
    {
        return $query->where('active', '1');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'is_dpo', 'is_orange_money'
    ];

    public function getIsDpoAttribute()
    {
        return $this->isDpo();
    }

    public function getIsOrangeMoneyAttribute()
    {
        return $this->isOrangeMoney();
    }
}
