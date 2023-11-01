<?php

namespace App\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variable extends BaseModel
{
    use HasFactory;

    protected $fillable = ['name', 'value', 'product_id'];

    /**
     *  Magic Numbers
     */
    const NAME_MIN_CHARACTERS = 1;
    const NAME_MAX_CHARACTERS = 20;

    const VALUE_MIN_CHARACTERS = 1;
    const VALUE_MAX_CHARACTERS = 40;

    const INSTRUCTION_MIN_CHARACTERS = 1;
    const INSTRUCTION_MAX_CHARACTERS = 120;

    /**
     *  Returns the product that this variable is applied
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
