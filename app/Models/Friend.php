<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Casts\E164PhoneNumberCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Friend extends BaseModel
{
    use HasFactory;

    const FIRST_NAME_MIN_CHARACTERS = 3;
    const FIRST_NAME_MAX_CHARACTERS = 20;
    const LAST_NAME_MIN_CHARACTERS = 3;
    const LAST_NAME_MAX_CHARACTERS = 20;

    protected $casts = [
        'last_selected_at' => 'datetime',
        'mobile_number' => E164PhoneNumberCast::class
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'first_name', 'last_name', 'mobile_number', 'last_selected_at', 'user_id'
    ];

    /************
     *  SCOPES  *
     ***********/

    public function scopeSearch($query, $searchWord)
    {
        return $query->whereRaw('concat(first_name," ",last_name) like ?', "%{$searchWord}%")
                     ->orWhere('friends.mobile_number', 'like', "%{$searchWord}%");
    }

    public function scopeSearchMobileNumber($query, $mobileNumber)
    {
        return $query->where('friends.mobile_number', $mobileNumber);
    }

    /********************
     *  RELATIONSHIPS   *
     *******************/

    /**
     *  Return user
     *
     *  @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
