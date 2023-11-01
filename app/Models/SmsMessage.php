<?php

namespace App\Models;

use App\Casts\AddressMetadata;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsMessage extends BaseModel
{
    use HasFactory;

    /**
     *  Magic Numbers
     */
    const CONTENT_MIN_CHARACTERS = 3;
    const CONTENT_MAX_CHARACTERS = 500;

    protected $casts = [
        'sent' => 'boolean',
        'error' => 'array'
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'content', 'recipient_mobile_number', 'sent', 'error'
    ];
}
