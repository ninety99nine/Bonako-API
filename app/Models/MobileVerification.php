<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileVerification extends BaseModel
{
    use HasFactory;

    /**
     *  Magic Numbers
     */
    const CODE_CHARACTERS = 6;

    protected $fillable = [
        'code', 'mobile_number'
    ];
}
