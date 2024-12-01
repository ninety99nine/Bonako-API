<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiAssistant extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'requires_subscription' => 'boolean',
        'remaining_paid_tokens_expire_at' => 'datetime',
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'total_paid_tokens', 'remaining_free_tokens', 'remaining_paid_tokens', 'remaining_paid_top_up_tokens',
        'requires_subscription', 'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aiMessages()
    {
        return $this->hasMany(AiMessage::class);
    }

    public function subscription()
    {
        return $this->morphOne(Subscription::class, 'owner')->latest();
    }

    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'owner')->latest();
    }

    public function activeSubscription()
    {
        return $this->morphOne(Subscription::class, 'owner')->active();
    }

    public function aiAssistantTokenUsage()
    {
        return $this->hasMany(AiAssistantTokenUsage::class);
    }
}
