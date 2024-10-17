<?php

namespace App\Casts;

use App\Services\PhoneNumber\PhoneNumberService;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast as _E164PhoneNumberCast;

class E164PhoneNumberCast extends _E164PhoneNumberCast
{
    public function serialize($model, string $key, $value, array $attributes)
    {
        if (!$value) return null;
        return PhoneNumberService::formatPhoneNumber($value);
    }
}
