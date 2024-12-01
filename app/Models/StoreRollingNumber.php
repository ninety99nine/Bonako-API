<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Casts\E164PhoneNumberCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreRollingNumber extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'last_called_at' => 'datetime',
        'mobile_number' => E164PhoneNumberCast::class,
    ];

    protected $fillable = [
        'mobile_number', 'last_called_at', 'store_id'
    ];

    /************
     *  SCOPES  *
     ***********/

    public function scopeSearch($query, $searchWord)
    {
        return $query->where('store_rolling_numbers.mobile_number', $searchWord);
    }

    /********************
     *  RELATIONSHIPS   *
     *******************/

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
