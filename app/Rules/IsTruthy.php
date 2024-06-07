<?php

namespace App\Rules;

use Closure;
use App\Traits\Base\BaseTrait;
use Illuminate\Contracts\Validation\ValidationRule;

class IsTruthy implements ValidationRule
{
    use BaseTrait;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if($this->isTruthy($value) == false) {
            $fail('The :attribute is required');
        }
    }
}
