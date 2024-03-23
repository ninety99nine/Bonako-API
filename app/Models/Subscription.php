<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Base\BaseModel;
use App\Traits\SubscriptionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends BaseModel
{
    use HasFactory, SubscriptionTrait;

    const FILTERS = [
        'All', 'Active', 'Inactive'
    ];

    protected $casts = [
        'active' => 'boolean',
        'end_at' => 'datetime',
        'start_at' => 'datetime',
    ];

    protected $tranformableCasts = [];

    protected $fillable = [

        /*  Basic Information  */
        'start_at', 'end_at', 'subscription_plan_id', 'user_id',

        /*  Owenership Details  */
        'owner_id', 'owner_type'

    ];

    /*
     *  Scope: Return subscriptions that are being searched
     *
     *  1. We always search on the User model.
     *  2. We always search on the Store model.
     *  3. We never search on SmsAlert or AiAssistant models.
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query->whereHas('user', function ($user) use ($searchWord) {
                $user->search($searchWord);
            })->orWhere(function ($query) use ($searchWord) {
                $query->whereHasMorph('owner', [Store::class], function ($storeQuery) use ($searchWord) {
                    $storeQuery->searchName($searchWord);
                });
            });
    }

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
        return $query->where('user_id', request()->auth_user->id);
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
     *  Returns the associated user
     */
    public function user()
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
     *  Returns the associated transaction owned by this subscription
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'owner');
    }

    /**
     *  Returns the latest payment shortcode owned by this subscription
     */
    public function activePaymentShortcode()
    {
        return $this->morphOne(Shortcode::class, 'owner')->notExpired()->latest();
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
