<?php

namespace App\Models;

use App\Casts\AddressMetadata;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends BaseModel
{
    use HasFactory;

    /**
     *  Magic Numbers
     */
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 20;
    const ADDRESS_LINE_MIN_CHARACTERS = 10;
    const ADDRESS_LINE_MAX_CHARACTERS = 200;

    protected $casts = [
        'share_address' => 'boolean',
        'metadata' => AddressMetadata::class,
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'name', 'address_line', 'share_address', 'user_id'
    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /**
     *  Scope shared addresses
     */
    public function scopeShared($query)
    {
        return $query->where('share_address', '1');
    }
}
