<?php

namespace App\Services\Ussd;

use GuzzleHttp\Client;
use App\Models\InstantCart;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UssdService
{
    /**
     *  Get the reserved shortcode range
     *
     *  The USSD service has reserved the first 50 shared shortcodes
     *  to be used by the application for special use cases. This
     *  means that the first 50 shared shortcodes should not be
     *  generated and assigned to users or any other resource.
     *
     *  Examples:
     *
     *  If set to "50" the *250*0#, *250*1#, *250*2#, ... *250*50#
     *  are reserved to be used for system specific tasks.
     *
     *  @return int
     */
    public static function getReservedShortcodeRange()
    {
        return (int) config('app.USSD_RESERVED_SHORTCODE_RANGE');
    }

    /**
     *  Get the main shortcode
     *  @return string
     */
    public static function getMainShortcode()
    {
        return '*'.config('app.USSD_MAIN_SHORT_CODE').'#';
    }

    /**
     *  Get the mobile verification shortcode
     *  @return string
     */
    public static function getMobileVerificationShortcode()
    {
        return '*'.config('app.USSD_MAIN_SHORT_CODE').'*0000#';
    }

    /**
     *  Verify if the incoming request is from the USSD server
     *  @return boolean
     */
    public static function verifyIfRequestFromUssdServer()
    {
        if( request()->filled('ussd_token') ) {

            //  Validate the ussd token
            return request()->input('ussd_token') == config('app.USSD_TOKEN');

        }else{

            return false;

        }
    }

    public static function appendToMainShortcode($code)
    {
        return Str::replaceFirst('#', '*'.$code.'#', self::getMainShortcode());
    }

    /**
     * Check if the USSD code is valid, e.g., *250*1#
     *
     * @param string $ussd - The ussd code e.g *250*1#
     * @return bool
     */
    public static function isValidUssdCode($ussd)
    {
        /**
         *  This Regex pattern will ACCEPT the following:
         *
         *  *1#
         *  *12#
         *  *123#
         *  *123*1#
         *  *123*12#
         *  *123*12*1#
         *
         *  But will REJECT the following:
         *
         *  *
         *  #
         *  *#
         *  **123#
         *  *123##
         *  *123*#
         *  *123*12*#
         */
        return preg_match('/^\*[0-9]+(\*[0-9]+)*#$/', $ussd) === 1;
    }

    /**
     * Get the last reply of the USSD code.
     * Returns the last reply (e.g., "1" for USSD code *250*1#).
     *
     * @param string $ussd - The USSD code (e.g., *250*1#).
     * @return string|null - The last reply or null if not found.
     */
    public static function getUssdLastReply($ussd)
    {
        $pattern = '/[*#]/';
        $parts = preg_split($pattern, $ussd, -1, PREG_SPLIT_NO_EMPTY);

        return end($parts) ?: null;
    }

    /**
     *  This method will generate a new unique code that can be used in relation
     *  to the specified shortcode e.g This could be a code being generated for
     *  a shortcode that allows users to visit a store or pay for a subscription
     *
     *  @param \Illuminate\Database\Eloquent\Model $shortcode - The shortcode that this code is created for
     *  @param string $action - The action to be performed by this shortcode e.g Visit, Pay, e.t.c
     *  @return int
     */
    public static function generateResourceCode($shortcode, $action)
    {
        /**
         *  Count the total number of similar short codes so that we
         *  can know the total number of shortcodes that perform the
         *  same kind of action.
         */
        $total = $shortcode->action($action)->count();

        //  The new code must be an increment of this total
        $code = ($total + 1);

        //  If this is a "Visit" action
        if( $action == 'Visit' ) {

            //  If this shortcode is for visiting an instant cart
            if($shortcode->owner_type == (new InstantCart)->getResourceName()) {

                //  Prepend a single zero before this code
                $code = '0'.$code;

            //  If this shortcode is for visiting a store
            }else{

                /**
                 *  Offset the code by the reserved shortcode range
                 *  so that we are generating a code that is beyond
                 *  the range of reserved shortcodes. This is so
                 *  that we can avoid conflicting shortcode use.
                 */
                $code += self::getReservedShortcodeRange();

            }

        }else if( $action == 'Pay' ) {

            //  Prepend double zero before this code
            $code = '00'.$code;

        }

        //  Return the code
        return $code;

    }

    /**
     *  Launch the USSD service
     *
     *  @param Request $request
     */
    public static function launchUssd(Request $request)
    {
        try {

            //  Set the request endpoint
            $endpoint = config('app.USSD_ENDPOINT');

            $msg = $request->input('msg');
            $sessionId = $request->input('session_id');
            $requestType = $request->input('request_type');
            $msisdn = $request->auth_user->mobile_number->withExtension;

            if($requestType == '1') {
                $msg = self::getMainShortcode();
            }

            //  Set the request options
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    "msg" => $msg,
                    "msisdn" => $msisdn,
                    "session_id" => $sessionId,
                    "request_type" => $requestType
                ],
            ];

            //  Create a new Http Guzzle Client
            $httpClient = new Client();

            //  Perform and return the Http request
            $response = $httpClient->request('POST', $endpoint, $options);

        } catch (\Throwable $e) {

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'exception' => $e->__toString(),
            ];

        }

        /**
         *  Get the response body as a String.
         */
        $jsonString = $response->getBody();

        /**
         *  Get the response body as an Associative Array.
         */
        $bodyAsArray = json_decode($jsonString, true);

        //  Get the response status code e.g "200"
        $statusCode = $response->getStatusCode();

        //  Return the status and the body
        return [
            'status' => ($statusCode == 200),
            'body' => $bodyAsArray
        ];
    }
}
