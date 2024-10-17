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
        'user_content', 'assistant_content', 'prompt_tokens', 'completion_tokens', 'total_tokens',
        'request_at', 'response_at', 'ai_message_category_id', 'ai_assistant_id'
    ];

    public function aiAssistant()
    {
        return $this->belongsTo(AiAssistant::class);
    }

    public function aiMessageCategory()
    {
        return $this->belongsTo(AiMessageCategory::class);
    }
}
