<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends BaseModel
{
    use HasFactory;

    const FILTERS = [
        'All', 'Active', 'Inactive'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $tranformableCasts = [];

    protected $fillable = [

        /*  Basic Information  */
        'start_at', 'end_at', 'subscription_plan_id', 'user_id',

        /*  Owenership Details  */
        'owner_id', 'owner_type'

    ];

    public function scopeExpired($query)
    {
        return $query->where('end_at', '<=', Carbon::now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where('end_at', '>', Carbon::now());
    }

    public function scopeBelongsToAuth($query)
    {
        return $query->where('user_id', auth()->user()->id);
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     * Get the owning resource e.g Store, Instant cart, e.t.c
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     *  Returns the associated subscriber
     */
    public function subscriber()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  Returns the associated subscription plan
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     *  Returns the latest payment shortcode owned by this subscription
     */
    public function activePaymentShortcode()
    {
        return $this->morphOne(Shortcode::class, 'owner')->notExpired()->latest();
    }

    /**
     *  Returns the associated transaction owned by this subscription
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'owner');
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'has_expired'
    ];

    public function getHasExpiredAttribute()
    {
        return \Carbon\Carbon::parse($this->end_at)->isBefore(now());
    }
}
