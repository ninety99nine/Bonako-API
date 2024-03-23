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
     *  Returns the shortcodes owned by this SMS Alert
     */
    public function shortcodes()
    {
        return $this->morphMany(Shortcode::class, 'owner');
    }

    /**
     *  Returns the shortcode owned by this SMS Alert
     */
    public function shortcode()
    {
        return $this->morphOne(Shortcode::class, 'owner');
    }

    /**
     *  Returns the latest payment shortcode owned by this SMS Alert
     *  and reserved for the current authenticated user
     */
    public function authPaymentShortcode()
    {
        return $this->shortcode()->action('Pay')->notExpired()->belongsToAuth()->latest();
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
