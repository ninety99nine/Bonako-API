<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Exceptions\InvalidJsonFormatException;
use App\Http\Resources\Helpers\ResourceNamingConvention;

class FormatRequestAndResponsePayload
{
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
         *  request()->all() = [
         *      "json" => "{"name":"product 1"}",
         *      "field1" => "123",
         *      "field2" => "456"
         *  ]
         *
         *  And convert into this:
         *
         *  request()->all() = [
         *      "name" => "product 1",
         *      "field1" => "123",
         *      "field2" => "456"
         *  ]
         */
        if(request()->has('json') && request()->filled('json')) {

            /**
             *  Attempt to decode payload e.g Take this:
             *
             *  request()->all() = [
             *      "json" => "{"name":"product 1"}"
             *  ]
             *
             *  And convert into this:
             *
             *  request()->all() = [
             *      "name" => "product 1"
             *  ]
             */
            $payload = json_decode(request()->input('json'), true);

            if (json_last_error() == JSON_ERROR_NONE) {

                // Remove the "json" key from the request inputs
                request()->request->remove('json');

                //  Replace the existing json encoded inputs with the json decoded inputs
                request()->merge($payload);

            }else{

                // Throw an error
                throw new InvalidJsonFormatException;

            }

        }

        //  Incoming requests must be converted to snakecase format
        $convertToSnakecace = true;

        //  Convert the incoming request payload to snakecase format
        $request->replace(
            $this->convertToAcceptedNamingConvention($request->all(), $convertToSnakecace)->toArray()
        );

        /**
         *  @var \Illuminate\Http\Response $response
         */
        $response = $next($request);

        /**
         *  Outgoing requests can be converted to snakecase or camelcase format (default is camelcase).
         *  To convert the outgoing responses to snakecase the request "_format" input must be set.
         */
        $convertToSnakecace = request()->filled('_format') && request()->input('_format') === 'snakecase';

        //  If the original response content is a string or null
        if(is_string($response->getOriginalContent()) || is_null($response->getOriginalContent())) {

            //  Return the response as is
            return $response;

        }else{

            //  Get the response content as JSON string and convert it into an associative array
            $responseContent = json_decode($response->getContent(), true);

            //  Convert the outgoing response payload to snakecase or camelcase format
            return $response->setContent(
                $this->convertToAcceptedNamingConvention($responseContent, $convertToSnakecace)
            );

        }
    }

    public function convertToAcceptedNamingConvention($data, $convertToSnakecace) {
        /**
         *  If the request does not intend to hide the relationships completely.
         *
         *  Also refer to the Exceptions/Handler.php file since we modify the
         *  validation exceptions in the same way
         */
        if( $convertToSnakecace ) {

            //  Convert the entire data structure to snake-case format
            return (new ResourceNamingConvention($data))->avoidDotNotation()->convertToSnakeCaseFormat();

        }else{

            //  Convert the entire data structure to camel-case format
            return (new ResourceNamingConvention($data))->avoidDotNotation()->convertToCamelCaseFormat();

        }
    }
}
