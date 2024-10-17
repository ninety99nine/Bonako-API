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
        'start_at', 'end_at', 'user_id', 'transaction_id', 'pricing_plan_id',

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
     * Get the owning resource e.g Store, AI Assistant, e.t.c
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     *  Returns user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  Returns transaction
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'owner');
    }

    /**
     *  Returns pricing plan
     */
    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class);
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
