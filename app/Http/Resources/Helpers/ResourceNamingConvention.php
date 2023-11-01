<?php

namespace App\Http\Resources\Helpers;

use App\Http\Resources\BaseResource;
use App\Http\Resources\BaseResources;
use stdClass;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class ResourceNamingConvention {

    private $resource;
    private $avoidDotNotation = false;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     *  Avoid dot notation on the resource link keys
     */
    public function avoidDotNotation()
    {
        $this->avoidDotNotation = true;
        return $this;
    }

    /**
     *  Return the resource structure with
     *  array keys in snake-case format
     *
     *  Convert this:
     *
     *  [
     *      'first_name' => 'John',
     *      'last_name' => 'Doe',
     *      'user_addresses' => [
     *          'address_1' => 'abc',
     *          'address_2' => [
     *              'work_area_1' => 'def',
     *              'work_area_2' => 'ghi'
     *              ]
     *          ]
     *      ]
     *  ];
     *
     *  Into this:
     *
     *  [
     *      'firstName' => 'John',
     *      'lastName' => 'Doe',
     *      'userAddresses' => [
     *          'address1' => 'abc',
     *          'address2' => [
     *              'workArea1' => 'def',
     *              'workArea2' => 'ghi'
     *              ]
     *          ]
     *      ]
     *  ];
     *
     */
    public function convertToCamelCaseFormat($data = null)
    {
        //  Capture the data
        $data = $data ?? $this->resource;

        return collect($data)->mapWithKeys(function ($value, $key) {

            //  Convert the key from dot notation to camel-case notation if necessary
            $key = $this->convertDotKeysToSnakeCaseKeysIfNecessary($key);

            //  Convert the relationship key name to snake-case format
            $key = Str::camel($key);

            //  Check if the value is an array or a collection
            if( is_array($value) || $value instanceof stdClass || $value instanceof BaseResource || $value instanceof BaseResources || $value instanceof Collection ) {

                //  Set the new snake-case key and converted value
                return [$key => $this->convertToCamelCaseFormat($value)];

            }else{

                //  Set the new snake-case key and value
                return [$key => $value];

            }

        });

    }

    /**
     *  Return the resource structure with
     *  array keys in snake-case format
     *
     *  Convert this:
     *
     *  [
     *      'firstName' => 'John',
     *      'lastName' => 'Doe',
     *      'userAddresses' => [
     *          'address1' => 'abc',
     *          'address2' => [
     *              'workArea1' => 'def',
     *              'workArea2' => 'ghi'
     *              ]
     *          ]
     *      ]
     *  ];
     *
     *  Into this:
     *
     *  [
     *      'first_name' => 'John',
     *      'last_name' => 'Doe',
     *      'user_addresses' => [
     *          'address_1' => 'abc',
     *          'address_2' => [
     *              'work_area_1' => 'def',
     *              'work_area_2' => 'ghi'
     *              ]
     *          ]
     *      ]
     *  ];
     *
     */
    public function convertToSnakeCaseFormat($data = null)
    {
        //  Capture the data
        $data = $data ?? $this->resource;

        return collect($data)->mapWithKeys(function ($value, $key) {

            //  Convert the key from dot notation to camel-case notation if necessary
            $key = $this->convertDotKeysToSnakeCaseKeysIfNecessary($key);

            //  Convert the relationship key name to snake-case format
            $key = Str::snake($key);

            //  Check if the value is an array or a collection
            if( is_array($value) || $value instanceof Collection ) {


                //  Set the new snake-case key and converted value
                return [$key => $this->convertToSnakeCaseFormat($value)];

            }else{

                //  Set the new snake-case key and value
                return [$key => $value];

            }

        });

    }

    public function convertDotKeysToSnakeCaseKeysIfNecessary($key)
    {
        /**
         *  Since we use dot notation on the resource link keys,
         *  we need to make a standard conversion to snake case
         *  if we do not permit dot notation.
         *
         *  e.g convert 'confirm.delete' into 'confirm_delete'
         */
        if( $this->avoidDotNotation === true ) {

            $key = Str::replace('.', '_', $key);

        }

        return $key;
    }
}
