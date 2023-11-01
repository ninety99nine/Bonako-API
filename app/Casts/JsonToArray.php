<?php

namespace App\Casts;

use App\Traits\Base\BaseTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 *  Instead of using the built in Laravel array cast e.g
 *
 *  protected $casts = [
 *      'custom_colum' => 'array'
 *  ]
 *
 *  Reference 1: https://laravel.com/docs/8.x/eloquent-mutators#attribute-casting
 *  Reference 2: https://laravel.com/docs/8.x/eloquent-mutators#custom-casts
 *
 *  We need to implement our own logic because Laravel "array" casting does not
 *  prevent double casting. This means that a model attribute can be cast from
 *  a json string stored in the database to an array equivalent value, however
 *  if this model encounters another attempt to recast its model attribute,
 *  the value will then be converted from an array to a jsonstring which
 *  is not desirable outcome. We want the value to remain as an array
 *  especailly if we attempt to recast the value.
 */
class JsonToArray implements CastsAttributes
{
    use BaseTrait;

    /**
     *  Indication of whether this can return null
     */
    private $returnType;

    public function __construct($type = 'array')
    {
        //  Set the return type
        $this->returnType = $type;
    }

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

            if($this->returnType == 'array') {

                return [];

            }elseif($this->returnType == 'null') {

                return null;

            }

        }else if(is_array($value)) {

            return $value;

        }else{

            /**
             *  Json decode the data to convert json string to array
             *
             *  Reference: https://www.php.net/manual/en/function.json-decode.php
             */
            return json_decode($value, true);

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
