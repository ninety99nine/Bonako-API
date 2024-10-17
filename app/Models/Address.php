<?php

namespace App\Models;

use App\Enums\AddressType;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends BaseModel
{
    use HasFactory;

    const ADDRESS_MAX_CHARACTERS = 255;
    const ADDRESS2_MAX_CHARACTERS = 255;
    const CITY_MAX_CHARACTERS = 100;
    const STATE_MAX_CHARACTERS = 100;
    const ZIP_MAX_CHARACTERS = 20;

    public static function TYPES(): array
    {
        return array_map(fn($type) => $type->value, AddressType::cases());
    }

    protected $fillable = [
        'type', 'address_line', 'address_line2', 'city', 'state', 'zip', 'country_code', 'place_id',
        'latitude', 'longitude', 'description', 'owner_id', 'owner_type'
    ];

    /************
     *  SCOPES  *
     ***********/

    public function scopeSearch($query, $searchWord)
    {
        return $query->whereRaw('concat(address_line," ",address_line2," ",city) like ?', "%{$searchWord}%");
    }

    /********************
     *  RELATIONSHIPS   *
     *******************/

    public function owner()
    {
        return $this->morphTo();
    }
}
