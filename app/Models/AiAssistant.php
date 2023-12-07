<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiAssistant extends BaseModel
{
    use HasFactory;


    /**
     *  Cost and Revenue for 8 questions per P1
     *
     *  P0.047 * 5 = P0.235 (per 5 requests and 5 responses = 10,000 tokens)
     *
     *  P0.235 Paid to OpenAI
     *
     *  P1.00 - P0.235 = P0.765
     *
     *  P0.235 Paid to MNO + Tarrifs (54%)
     *
     *  P0.765 * (14 + 40)/100 = P0.4131 (Revenue)
     *
     *  ------------------------------------------
     *
     *  The paid token rate is 10000. This means we offer 10,000 for every P1 that is paid
     *  in subscriptions for the AI Assistant service. If the user pays P5, then they are
     *  provided with 50,000 tokens as per the specified rate "PAID_TOKEN_RATE = 10000"
     */
    const PAID_TOKEN_RATE = 10000;  //  10,000 per P1.00

    const MAXIMUM_FREE_REQUESTS = 0;

    protected $casts = [
        'requires_subscription' => 'boolean',
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'requires_subscription', 'remaining_paid_tokens_after_last_subscription', 'remaining_paid_tokens', 'free_tokens_used', 'response_tokens_used', 'request_tokens_used',
        'total_tokens_used', 'total_requests', 'remaining_paid_tokens_expire_at', 'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  Returns the shortcodes owned by this AI Assistant
     */
    public function shortcodes()
    {
        return $this->morphMany(Shortcode::class, 'owner');
    }

    /**
     *  Returns the shortcode owned by this AI Assistant
     */
    public function shortcode()
    {
        return $this->morphOne(Shortcode::class, 'owner');
    }

    /**
     *  Returns the latest payment shortcode owned by this AI Assistant
     *  and reserved for the current authenticated user
     */
    public function authPaymentShortcode()
    {
        return $this->shortcode()->action('Pay')->notExpired()->belongsToAuth()->latest();
    }

    /**
     *  Returns the subscriptions to this AI Assistant
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'owner')->latest();
    }

    /**
     *  Returns only one subscription to this AI Assistant
     */
    public function subscription()
    {
        return $this->morphOne(Subscription::class, 'owner')->latest();
    }

    /**
     *  Returns the current authenticated user's non-expired
     *  subscription to this AI Assistant
     */
    public function authActiveSubscription()
    {
        return $this->morphOne(Subscription::class, 'owner')->notExpired()->belongsToAuth()->latest();
    }

    /****************************
     *  ACCESSORS               *
     ***************************/

    protected $appends = [
        'used_tokens_percentage', 'unused_tokens_percentage'
    ];

    public function getUsedTokensPercentageAttribute()
    {
        if($this->remaining_paid_tokens_after_last_subscription > 0) {

            $percentage = (($this->remaining_paid_tokens_after_last_subscription - $this->remaining_paid_tokens) / $this->remaining_paid_tokens_after_last_subscription) * 100;
            $value = round($percentage);

        }else{

            $value = 0;

        }

        return $this->convertToPercentageFormat($value);
    }

    public function getUnusedTokensPercentageAttribute()
    {
        if($this->remaining_paid_tokens_after_last_subscription > 0) {

            $percentage = ($this->remaining_paid_tokens / $this->remaining_paid_tokens_after_last_subscription) * 100;
            $value = round($percentage);

        }else{

            $value = 0;

        }

        return $this->convertToPercentageFormat($value);
    }
}
