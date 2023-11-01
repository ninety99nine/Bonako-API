<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstantCart extends BaseModel
{
    use HasFactory;

    protected $casts = [

    ];

    protected $tranformableCasts = [

    ];

    protected $fillable = [

    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /*
     *  Scope: Return instant carts that are being searched
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query->where('name', $searchWord);
    }
}
