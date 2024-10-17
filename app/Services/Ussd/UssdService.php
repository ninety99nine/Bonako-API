<?php

namespace App\Services\Ussd;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UssdService
{
    private static $ussdCodesByCountryCodes = [
        'BW' => '250'
    ];

    /**
     *  Get main shortcode e.g *250#
     *
     *  @param string $countryCode
     *  @return string|null
     */
    public static function getMainShortcode(string $countryCode): string|null
    {
        return isset(self::$ussdCodesByCountryCodes[$countryCode]) ? '*'.self::$ussdCodesByCountryCodes[$countryCode].'#' : null;
    }

    /**
     *  Append to main shortcode e.g *123*250#
     *
     *  @param string $countryCode
     *  @return string|null
     */
    public static function appendToMainShortcode(string $number, string $countryCode): string|null
    {
        $number = Str::replace(' ', '', $number);
        return ($mainShortcode = self::getMainShortcode($countryCode)) ? Str::replaceFirst('#', '*'.$number.'#', $mainShortcode) : null;
    }

    /**
     *  Get the mobile verification shortcode e.g *250*0000#
     *
     *  @param string $countryCode
     *  @return string|null
     */
    public static function getMobileVerificationShortcode(string $countryCode): string|null
    {
        return isset(self::$ussdCodesByCountryCodes[$countryCode]) ? '*'.self::$ussdCodesByCountryCodes[$countryCode].'*0000#' : null;
    }

    /**
     *  Verify if the incoming request is from the USSD server
     *
     *  @return bool
     */
    public static function verifyIfRequestFromUssdServer(): bool
    {
        return request()->filled('ussd_token') ? request()->input('ussd_token') == config('app.USSD_TOKEN') : false;
    }

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
    public static function getReservedShortcodeRange(): int
    {
        return (int) config('app.USSD_RESERVED_SHORTCODE_RANGE');
    }

    /**
     * Check if the USSD code is valid, e.g., *250*1#
     *
     * @param string $ussd - The ussd code e.g *250*1#
     * @return bool
     */
    public static function isValidUssdCode($ussd): bool
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
    public static function getUssdLastReply($ussd): string|null
    {
        $pattern = '/[*#]/';
        $parts = preg_split($pattern, $ussd, -1, PREG_SPLIT_NO_EMPTY);

        return end($parts) ?: null;
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
            $msisdn = $request->auth_user->mobile_number->formatE164();

            if($requestType == '1') {
                $msg = self::getMainShortcode($request->auth_user->mobile_number->getCountry());
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
