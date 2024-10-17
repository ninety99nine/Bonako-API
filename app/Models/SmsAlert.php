<?php

namespace App\Models;

use App\Traits\SmsAlertTrait;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsAlert extends BaseModel
{
    use HasFactory, SmsAlertTrait;

    //   protected $with = ['smsAlertActivityAssociations'];

    protected $fillable = ['sms_credits', 'user_id'];

    /**
     *  Returns the user
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsToMany
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  Returns the transactions owned by this SMS Alert
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'owner');
    }

    /**
     *  Returns the transaction owned by this SMS Alert
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'owner');
    }

    /**
     *  Returns the latest transaction owned by this SMS Alert
     */
    public function latestTransaction()
    {
        return $this->transaction()->latest();
    }

    /**
     *  Returns the sms alert activity associations
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::hasMany
     */
    public function smsAlertActivityAssociations()
    {
        return $this->hasMany(SmsAlertActivityAssociation::class);
    }
}
