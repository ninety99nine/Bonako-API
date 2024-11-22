<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Currency;
use App\Models\Base\BaseModel;
use App\Casts\E164PhoneNumberCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends BaseModel
{
    use HasFactory;

    const FIRST_NAME_MIN_CHARACTERS = 3;
    const FIRST_NAME_MAX_CHARACTERS = 20;
    const LAST_NAME_MIN_CHARACTERS = 3;
    const LAST_NAME_MAX_CHARACTERS = 20;
    const NOTES_MIN_CHARACTERS = 3;
    const NOTES_MAX_CHARACTERS = 200;

    protected $casts = [
        'birthday' => 'datetime',
        'last_order_at' => 'datetime',
        'total_spend' => Money::class,
        'total_average_spend' => Money::class,
        'mobile_number' => E164PhoneNumberCast::class,
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class
    ];

    protected $fillable = [
        'first_name', 'last_name', 'mobile_number', 'email', 'birthday', 'notes',
        'last_order_at', 'total_orders', 'total_spend', 'total_average_spend', 'currency', 'store_id'
    ];

    /************
     *  SCOPES  *
     ***********/

    public function scopeSearch($query, $searchWord)
    {
        return $query->whereRaw('concat(first_name," ",last_name) like ?', "%{$searchWord}%")
                     ->orWhere('customers.mobile_number', 'like', "%{$searchWord}%");
    }

    public function scopeSearchEmail($query, $email)
    {
        return $query->where('customers.email', $email);
    }

    public function scopeSearchMobileNumber($query, $mobileNumber)
    {
        return $query->where('customers.mobile_number', $mobileNumber);
    }

    /********************
     *  RELATIONSHIPS   *
     *******************/

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'owner');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'name'
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->getRawOriginal('first_name').' '.$this->getRawOriginal('last_name'))
        );
    }
}
