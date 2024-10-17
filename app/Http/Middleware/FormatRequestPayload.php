<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Helpers\PayloadNamingConvention;
use App\Exceptions\InvalidJsonFormatException;

class FormatRequestPayload
{
    use BaseTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         *  Check if the request has a input filed called json containing data
         *
         *  Whenever we use form-data or x-www-form-urlencoded data, we are limited
         *  to passing the data as literal strings. This means other data types
         *  such as Integers, Floats, Booleans and Arrays fail to pass while
         *  preserving their original data type. In order to support sending
         *  data while preserving their original data type, we need to catch
         *  the json data which should be stored on the request input called
         *  "json". This should be a valid JSON string containing the data
         *  that are Integers, Floats, Booleans and Arrays. This allows us
         *  to then convert this data into the proper data types and then
         *  append each JSON attribute as a request input e.g
         *
         *  $request->all() = [
         *      "json" => "{"name":"product 1"}",
         *      "field1" => "123",
         *      "field2" => "456"
         *  ]
         *
         *  And convert into this:
         *
         *  $request->all() = [
         *      "name" => "product 1",
         *      "field1" => "123",
         *      "field2" => "456"
         *  ]
         */
        if($request->has('json') && $request->filled('json')) {

            /**
             *  Attempt to decode payload e.g Take this:
             *
             *  $request->all() = [
             *      "json" => "{"name":"product 1"}"
             *  ]
             *
             *  And convert into this:
             *
             *  $request->all() = [
             *      "name" => "product 1"
             *  ]
             */
            $payload = json_decode($request->input('json'), true);

            if (json_last_error() == JSON_ERROR_NONE) {

                // Remove the "json" key from the request inputs
                $request->query->remove('json');

                //  Replace the existing json encoded inputs with the json decoded inputs
                $request->merge($payload);

            }else{

                // Throw an error
                throw new InvalidJsonFormatException;

            }

        }

        /**
         *  In order to maintain consistency, we want to convert non boolean
         *  true/false data types into boolean true/false data types
         */
        foreach(['_no_fields', '_no_attributes', '_no_links', '_no_relationships', '_return'] as $paramemter) {

            if( request()->filled($paramemter) ) {

                request()->merge([
                    $paramemter => $this->isTruthy(request()->input($paramemter))
                ]);

            }

        }

        $request = $this->convertRequestInputsToSnakecaseFormat($request);
        $this->normaliseMobileNumbers();

        return $next($request);
    }

    /**
     * Convert request inputs to snakecase format
     *
     * @return Request
     */
    public function convertRequestInputsToSnakecaseFormat(Request $request): Request
    {
        $snakeCaseFormat = (new PayloadNamingConvention($request->all()))->removeDotNotation()->convertToSnakeCaseFormat();
        $request->replace($snakeCaseFormat);
        return $request;
    }

    /**
     * Normalize mobile numbers.
     * Recursively append the "+" to mobile number fields at any level.
     *
     * @return void
     */
    public function normaliseMobileNumbers(): void
    {
        $data = request()->all();

        /**
         * Normalize function to append "+" to the mobile number.
         */
        $normalizeMobileNumber = function($mobileNumber) {
            if (is_string($mobileNumber) || is_int($mobileNumber)) {
                if (!Str::startsWith($mobileNumber, '+')) {
                    $mobileNumber = '+' . (string) $mobileNumber;
                }
            }
            return $mobileNumber;
        };

        /**
         * Recursively normalize all mobile number fields.
         */
        $normalizeNestedMobileNumbers = function (&$data, $parentKey = null) use (&$normalizeNestedMobileNumbers, $normalizeMobileNumber) {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    // If the value is an array, call the function recursively
                    $normalizeNestedMobileNumbers($value, $key);
                } else {
                    // If the key indicates a mobile number and value is a string or int, normalize it
                    if (Str::contains($key, 'mobile_number') && (is_string($value) || is_int($value))) {
                        $value = $normalizeMobileNumber($value);
                    }
                }
            }
        };

        // Apply normalization to the entire request data
        $normalizeNestedMobileNumbers($data);

        // Merge the normalized data back into the request
        request()->merge($data);
    }
}
