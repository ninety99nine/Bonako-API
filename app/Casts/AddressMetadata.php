<?php

namespace App\Casts;

use App\Traits\Base\BaseTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class AddressMetadata implements CastsAttributes
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
        if( is_null($value) ) {

            return null;

        }else{

            /**
             *  Json decode the data to convert json string to array
             *
             *  Reference: https://www.php.net/manual/en/function.json-decode.php
             */
            return json_decode($value, true);

            return $data;

        }
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
        if(is_array($value)) {

            /**
             *  Json encode the data to convert array to json string
             *
             *  Reference: https://www.php.net/manual/en/function.json-decode.php
             */
            return json_encode($value);

        }else{

            //  Return value as is e.g null
            return $value;

        }
    }
}
