<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use App\Models\Pivots\SmsMessageStoreAssociation;
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

    /**
     *  Get the store associated with this sms message
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function store()
    {
        return $this->belongsToMany(Store::class, 'sms_message_store_association', 'store_id', 'sms_message_id')
                    ->withPivot(SmsMessageStoreAssociation::VISIBLE_COLUMNS)
                    ->using(SmsMessageStoreAssociation::class)
                    ->as('user_friend_association');
    }
}
