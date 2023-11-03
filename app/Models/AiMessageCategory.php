<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiMessageCategory extends BaseModel
{
    use HasFactory;

    /**
     *  Magic Numbers
     */
    const NAME_MIN_CHARACTERS = 3;
    const NAME_MAX_CHARACTERS = 40;
    const DESCRIPTION_MIN_CHARACTERS = 3;
    const DESCRIPTION_MAX_CHARACTERS = 200;
    const SYSTEM_PROMPT_MIN_CHARACTERS = 3;
    const SYSTEM_PROMPT_MAX_CHARACTERS = 500;

    protected $casts = [];

    protected $tranformableCasts = [];

    protected $fillable = ['name', 'description', 'system_prompt'];

    /****************************
     *  SCOPES                  *
     ***************************/

    /*
     *  Scope: Return categories that are being searched using the category name
     */
    public function scopeSearch($query, $searchWord)
    {
        return $query->active()->where('name', $searchWord);
    }

    /**
     *  Get the AI messages for this AI message category
     */
    public function aiMessages()
    {
        return $this->hasMany(AiMessage::class, 'category_id');
    }
}
