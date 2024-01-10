<?php

namespace App\Traits\Base;

use stdClass;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;

trait BaseTrait
{
    public $supportedCurrencySymbols = [
        'BWP' => 'P'
    ];

    public function convertToPercentageFormat($value)
    {
        return [
            'value' => $value,
            'value_symbol' => $value.'%',
        ];
    }

    public function convertToMobileNumberFormat($numberWithExtension)
    {
        $obj = new stdClass();
        $obj->withExtension = $numberWithExtension;
        $obj->extension = substr($numberWithExtension, 0, 3);
        $obj->withoutExtension = substr($numberWithExtension, 3);

        return $obj;
    }

    public function convertToCurrencyFormat($currencyCode = null)
    {
        $symbol = '';
        $currencyCode = $currencyCode ? (is_object($currencyCode) ? $currencyCode->code : $currencyCode) : $this->currency;

        //  If we have the currency code
        if( $currencyCode ) {

            //  If the currency has a matching symbol
            if( isset( $this->supportedCurrencySymbols[ $currencyCode ] ) ) {

                //  Set the symbol
                $symbol = $this->supportedCurrencySymbols[ $currencyCode ];

            }

        }

        $obj = new stdClass();
        $obj->symbol = $symbol;
        $obj->code = $currencyCode;

        return $obj;
    }

    public function convertToMoneyFormat($value = 0, $currencyCode = null)
    {
        $symbol = $this->convertToCurrencyFormat($currencyCode)->symbol;

        //  Convert value to money format
        $money = number_format($value, 2, '.', ',');

        //  Convert value to float
        $amount = (float) $value;

        $obj = new stdClass();
        $obj->amount = $amount;
        $obj->amountWithoutCurrency = $money;
        $obj->amountWithCurrency = $symbol . $money;

        return $obj;
    }

    public function convertNumberToShortenedPrefix($number)
    {
        $input = number_format($number);

        $input_count = substr_count($input, ',');

        if($input_count != '0') {
            if($input_count == '1'){
                return substr($input, 0, -4).'k';
            } else if($input_count == '2'){
                return substr($input, 0, -8).'m';
            } else if($input_count == '3'){
                return substr($input, 0,  -12).'b';
            } else {
                return;
            }
        } else {
            return $input;
        }
    }

    /**
     *  Prepare the item line for insertion into the database
     *
     *  @param int $cartId
     *  @param boolean $convertToJson
     */
    public function readyForDatabase($convertToJson = true)
    {
        /**
         *  Convert the specified item line (product line or coupon) to array.
         *  This is because we don't want the casting functionality of the
         *  ProductLine / CouponLine Model e.g To avoid automatic casting
         *  to array or vice-versa.
         */
        $output = $this->toArray();

        /**
         *  Foreach of the item line attributes, convert the value to a JSON representation
         *  of itself in the case that the value is an array. This is so that we can insert
         *  the value into the database without the "Array to string conversion" error
         *  especially when using Illuminate\Support\Facades\DB
         *
         *  Sometimes however we may not need to do this especially if we are updating an
         *  existing Model that already implements the cast to "array" feature, since that
         *  will cause double casting which is not desired. Laravel does not automatically
         *  check if the value is a string or an array before converting to Json. It should
         *  only convert an array to string, but sometimes when it receives a string it will
         *  process the string causing unwanted results. Because of this you can conviniently
         *  indicate whether to convert to JSON or not.
         */
        if( $convertToJson ) {

            // Get any fields that must be cast to dates
            $dateFields = collect($this->casts)->filter(function($castValue, $castName) {
                return $castValue == 'datetime';
            })->keys();

            // Get any fields that must be cast to money
            $moneyFields = collect($this->casts)->filter(function($castValue, $castName) {
                return $castValue == 'App\Casts\Money';
            })->keys();

            foreach($output as $attributeName => $attributeValue) {

                //  Check if this field is a money field
                $isMoneyField = collect($moneyFields)->contains($attributeName);

                //  Check if this field is a date field
                $isDateTimeField = collect($dateFields)->contains($attributeName);

                //  If this attribute value is a type of array and is not a carbon date
                if( is_array( $attributeValue ) ) {

                    //  Convert this value to a JSON representation of itself
                    $output[$attributeName] = json_encode($attributeValue);

                }elseif($isDateTimeField) {

                    //  Convert this value to carbon date format
                    $output[$attributeName] = Carbon::parse($attributeValue);

                }elseif($isMoneyField) {

                    //  Convert this value to float format
                    $output[$attributeName] = $attributeValue->amount;

                }


            }

        }

        return $output;
    }

    /**
     *  Get the current class basename as lowercase words separated by spaces
     *
     *  e.g ProductLine into product line
     */
    public function getResourceName()
    {
        return $this->separateWordsThenLowercase(class_basename($this));
    }

    /**
     *  Convert the type to the correct format if it has been set on the request inputs
     *
     *  Example: convert "teamMember", "TeamMember" or "Team Member" into "team member"
     */
    public function separateWordsThenLowercase($value) {
        return strtolower(Str::snake($value, ' '));
    }

    /**
     *  Choose the appropriate user to return based on the information provided.
     *  If this request is performed on the "/users/{user}" then the $user param
     *  will represent a database user record matching the user id specified.
     *  This record will have the user information including the user id. If
     *  this request is performed on the "/auth/user" then the $user param
     *  will not represent a database user record since the request
     *  "{user}" is not provided. Instead Laravel will create a
     *  blank User instance as a placeholder to compansate the
     *  route (User $user) parameter i.e ($user = new User).
     *
     *  We need to check if this request is being performed on the
     *  "/users/{user}" or "/auth/user" routes. This will allow
     *  us to choose the appropriate user that this request
     *  should focus on. We can do this by checking the
     *  existence of the request user.
     *
     *  @return User
     */
    private function chooseUser() {
        return request()->user ? request()->user : auth()->user();
    }
}
