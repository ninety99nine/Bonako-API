<?php

namespace App\Models;

use App\Traits\StoreTrait;
use App\Models\Base\BaseModel;
use App\Traits\UserStoreAssociationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreQuota extends BaseModel
{
    use HasFactory, StoreTrait, UserStoreAssociationTrait;

    protected $casts = [
        'sms_credits_expire_at' => 'datetime',
        'email_credits_expire_at' => 'datetime',
        'whatsapp_credits_expire_at' => 'datetime'
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'sms_credits', 'email_credits', 'whatsapp_credits',
        'sms_credits_expire_at', 'email_credits_expire_at', 'whatsapp_credits_expire_at', 'store_id'
    ];

    /********************
     *  RELATIONSHIPS   *
     *******************/

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
