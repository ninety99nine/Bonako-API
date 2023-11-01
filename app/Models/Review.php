<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends BaseModel
{
    use HasFactory;

    /**
     *  Magic Numbers
     */
    const RATING_MIN = 1;
    const RATING_MAX = 5;

    const COMMENT_MIN_CHARACTERS = 3;
    const COMMENT_MAX_CHARACTERS = 160;

    const FILTERS = ['All', ...self::SUBJECTS, 'Me'];
    const SUBJECTS = ['Product', 'Customer Service', 'Delivery', 'Payment'];

    protected $fillable = ['rating', 'subject', 'comment', 'store_id', 'user_id'];

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     * Get the Store that owns this Review
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the User that owns this Review
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
