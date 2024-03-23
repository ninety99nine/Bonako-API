<?php

namespace App\Helpers;

use stdClass;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class PayloadNamingConvention {

    private $payload;
    private $removeDotNotation = false;

    public function __construct($payload = [])
    {
        $this->payload = $payload;
    }

    /**
     *  Remove dot notation on the payload link keys
     */
    public function removeDotNotation()
    {
        $this->removeDotNotation = true;
        return $this;
    }

    /**
     *  Response payload can be converted to snakecase or camelcase format (default is camelcase).
     *  To convert the outgoing responses to snakecase the request "_format" input must be set.
     */
    public function getSuitableFormat() {
        return (request()->filled('_format') && request()->input('_format') === 'snakecase') ? 'snakecase' : 'camelcase';
    }

    public function isCamelCaseFormat() {
        return $this->getSuitableFormat() == 'camelcase';
    }

    public function isSnakeCaseFormat() {
        return $this->getSuitableFormat() == 'snakecase';
    }

    public function convertToSuitableFormat()
    {
        if($this->isCamelCaseFormat()) {
            return $this->convertToCamelCaseFormat();
        }else{
            return $this->convertToSnakeCaseFormat();
        }
    }

    /**
     *  Return the payload structure with
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
    public function convertToCamelCaseFormat($payload = null)
    {
        $payload = $payload ?? $this->payload;

        return collect($payload)->mapWithKeys(function ($value, $key) {

            $key = $this->convertKeyToCamelCaseFormat($key);

            //  Check if the value is an Array, stdClass or a Collection
            if( is_array($value) || $value instanceof stdClass || $value instanceof Collection ) {

                //  Set the new camel-case key and converted value
                return [$key => $this->convertToCamelCaseFormat($value)];

            }else{

                //  Set the new snake-case key and value
                return [$key => $value];

            }

        })->toArray();

    }

    public function convertKeyToCamelCaseFormat($key)
    {
        //  Check if this begins with an underscore "_" e.g "_links", "_attributes" or "_relationships"
        $beginsWithUnderscore = Str::startsWith($key, '_');

        //  Convert the key from dot notation to snake case format e.g "account.exists" to "account_exists"
        $key = $this->convertDotKeysToSnakeCaseKeysIfNecessary($key);

        //  Convert the relationship key name to snake-case format e.g "account_exists" to "accountExists"
        $key = Str::camel($key);

        //  If this key begins with an underscore
        if($beginsWithUnderscore) {

            //  Add the underscore at the begining of this key since it has been removed by the Str::camel() method
            //  This will restore keys such as "_attributes", "_relationships", e.t.c on the provided payload
            $key = '_'.$key;

        }

        return $key;
    }

    /**
     *  Return the payload structure with
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
    public function convertToSnakeCaseFormat($payload = null)
    {
        $payload = $payload ?? $this->payload;

        return collect($payload)->mapWithKeys(function ($value, $key) {

            $key = $this->convertKeyToSnakeCaseFormat($key);

            //  Check if the value is an Array, stdClass or a Collection
            if( is_array($value) || $value instanceof stdClass || $value instanceof Collection ) {

                //  Set the new snake-case key and converted value
                return [$key => $this->convertToSnakeCaseFormat($value)];

            }else{

                //  Set the new snake-case key and value
                return [$key => $value];

            }

        })->toArray();

    }

    public function convertKeyToSnakeCaseFormat($key)
    {
        //  Convert the key from dot notation to snake case format e.g "account.exists" to "account_exists"
        $key = $this->convertDotKeysToSnakeCaseKeysIfNecessary($key);

        //  Convert the relationship key name to snake-case format
        $key = Str::lower(Str::snake($key));

        return $key;
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
        if( $this->removeDotNotation === true ) {

            $key = Str::replace('.', '_', $key);

        }

        return $key;
    }

}
