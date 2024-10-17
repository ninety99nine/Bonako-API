<?php

namespace App\Models;

use App\Casts\JsonToArray;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiLesson extends BaseModel
{
    use HasFactory;

    /**
     *  Magic Numbers
     */
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 20;

    protected $casts = [
        'topics' => JsonToArray::class
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'name', 'topics'
    ];
}
