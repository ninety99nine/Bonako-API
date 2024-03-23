<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiMessage extends BaseModel
{
    use HasFactory;

    /**
     *  Magic Numbers
     */
    const USER_CONTENT_MIN_CHARACTERS = 2;
    const USER_CONTENT_MAX_CHARACTERS = 500;
    const ASSISTANT_CONTENT_MIN_CHARACTERS = 2;
    const ASSISTANT_CONTENT_MAX_CHARACTERS = 2000;

    protected $casts = [
        'request_at' => 'datetime',
        'response_at' => 'datetime',
    ];

    protected $tranformableCasts = [];

    protected $fillable = [
        'user_content', 'assistant_content', 'category_id', 'user_id',
        'free_tokens_used', 'response_tokens_used', 'request_tokens_used',
        'total_tokens_used', 'remaining_paid_tokens', 'request_at', 'response_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     *  Get the AI message category for this AI message
     */
    public function aiMessageCategory()
    {
        return $this->belongsTo(AiMessageCategory::class, 'category_id');
    }
}
