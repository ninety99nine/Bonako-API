<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiAssistantTokenUsage extends BaseModel
{
    use HasFactory;

    protected $table = 'ai_assistant_token_usage';

    protected $tranformableCasts = [];

    protected $fillable = [
        'request_tokens_used', 'response_tokens_used', 'free_tokens_used',
        'paid_tokens_used', 'paid_top_up_tokens_used', 'ai_assistant_id'
    ];

    public function aiAssistant()
    {
        return $this->belongsTo(AiAssistant::class);
    }
}
