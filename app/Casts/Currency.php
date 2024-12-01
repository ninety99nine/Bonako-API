<?php

namespace App\Casts;

use App\Services\Currency\CurrencyService;
use App\Traits\Base\BaseTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Currency implements CastsAttributes
{
    use BaseTrait;

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        if($currency = (new CurrencyService)->findCurrencyByCode($value)) {
            return collect($currency)->only(['code', 'symbol'])->toArray();
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if( is_array($value) ) {

            //  If we have the array code value
            if( isset($value['code']) && !empty($value['code']) ) {

                return $value['code'];

            }

        }

        return $value;
    }
}
