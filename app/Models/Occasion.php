<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Occasion extends BaseModel
{
    use HasFactory;

    /**
     *  Magic Numbers
     */
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 40;

    protected $casts = [];

    protected $tranformableCasts = [];

    protected $fillable = ['name'];

    /****************************
     *  SCOPES                  *
     ***************************/

    /*
     *  Scope: Return occasions that are being searched using the occasion name
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query->active()->where('name', $searchWord);
    }
}
