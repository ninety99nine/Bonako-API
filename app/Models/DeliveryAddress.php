<?php

namespace App\Models;

use App\Casts\AddressMetadata;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 *  Note that this model extends the Address Model instead of the BaseModel.
 */
class DeliveryAddress extends Address
{
    protected $fillable = [
        'name', 'address_line', 'share_address', 'user_id', 'address_id'
    ];
}
