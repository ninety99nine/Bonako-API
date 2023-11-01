<?php

namespace App\Services\Sms;

use Exception;
use App\Models\Store;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;

class SmsService
{
    /**
     *  Send the Orange SMS
     *
     *  @param string $content - The message Model
     *  @param string $recipientMobileNumber - The number of the recipient to receive the sms e.g 26772000001
     *  @param string|Store|null $senderName - The name of the sender sending the sms e.g Company XYZ
     *  @param string|null $senderMobileNumber - The number of the sender sending the sms e.g 26772000001
     *  @param string|null $clientCredentials - The client credentials used for authentication (Provided by Orange BW)
     */
    public static function sendOrangeSms($content, $recipientMobileNumber, $senderName, $senderMobileNumber, $clientCredentials): bool
    {
        try{

            if(config('app.SMS_ENABLED')) {

                // If this sender name is an instance of a Store Model
                if(($store = $senderName) instanceof Store) {

                    // If this store has a sender name
                    if(!empty($store->sms_sender_name)) {

                        // Capture the store sender name
                        $senderName = $store->sms_sender_name;

                        // Capture the store sender mobile number
                        $senderMobileNumber = $store->mobile_number->withExtension;

                    }else{

                        // Set sender name to null
                        $senderName = null;

                        // Set sender mobile number to null
                        $senderMobileNumber = null;

                    }

                }

                $senderName = !empty($senderName) ? $senderName : config('app.SMS_SENDER_NAME');
                $clientCredentials = !empty($clientCredentials) ? $clientCredentials : config('app.SMS_CREDENTIALS');
                $senderMobileNumber = !empty($senderMobileNumber) ? $senderMobileNumber : config('app.SMS_SENDER_MOBILE_NUMBER');

                //  Acquire the access token
                $accessToken = self::requestSmsAccessToken($clientCredentials);

                //  If we have an acccess token
                if($accessToken) {

                    $smsMessage = SmsMessage::create([
                        'content' => $content,
                        'recipient_mobile_number' => $recipientMobileNumber,
                    ]);

                    if($smsMessage) {

                        /**
                         *  Note the following:
                         *
                         *  To test sending sms using POSTMAN then replace "https://aas-bw.com.intraorange:443" with "https://aas.orange.co.bw:443".
                         *  The "https://aas-bw.com.intraorange:443" domain is used to send SMS while the application is hosted on the Orange Server
                         *  The "https://aas.orange.co.bw:443" domain is used to send SMS while the application is hosted outside the Orange Server
                         *  such as on a local machine (Macbook, e.t.c) or POSTMAN. Since this application will be hosted on the Orange Server, we
                         *  will use the "https://aas-bw.com.intraorange:443" domain.
                         *
                         *  Note that "tel:+" converts to "tel%3A%2B" after being encoded
                         */
                        $smsEndpoint = 'https://aas-bw.com.intraorange:443/smsmessaging/v1/outbound/tel%3A%2B'.$senderMobileNumber.'/requests';

                        /**
                         *  Sample Response:
                         *
                         * {
                         *      "outboundSMSMessageRequest": {
                         *      "address": [
                         *           "tel:+26772012345"
                         *      ],
                         *      "senderAddress": "tel:+26772012345",
                         *      "senderName": "Company XYZ",
                         *      "outboundSMSTextMessage": {
                         *          "message": "Welcome to Company XYZ"
                         *      },
                         *      "clientCorrelator": "cf9d467d-2131-4280-b996-dddc5eb70eb2",
                         *      "resourceURL": "/smsmessaging/v1/outbound/tel:+26772012345/requests/req64c2c5261bc1c442747dd2ff",
                         *      "link": [
                         *          {
                         *               "rel": "Date",
                         *               "href": "2023-07-27T19:27:34.612Z"
                         *          }
                         *      ],
                         *      "deliveryInfoList": {
                         *          "resourceURL": "/smsmessaging/v1/outbound/tel:+26772012345/requests/req64c2c5261bc1c442747dd2ff/deliveryInfos",
                         *          "link": [],
                         *          "deliveryInfo": [
                         *              {
                         *                  "address": "tel:+26772012345",
                         *                  "deliveryStatus": "MessageWaiting",
                         *                  "link": []
                         *              }
                         *          ]
                         *      }
                         *  }
                         * }
                         */
                        $options = [
                            'verify' => false,  // Disable SSL certificate verification
                        ];

                        $headers = [
                            'Authorization' => 'Bearer ' . $accessToken,
                            'Content-Type' => 'application/json',
                            'accept' => 'application/json'
                        ];

                        $requestData = [
                            'outboundSMSMessageRequest' => [
                                'address' => ['tel:+'.$recipientMobileNumber],        //  Recepient number to send the SMS message
                                'senderAddress' => 'tel:+'.$senderMobileNumber,       //  Sender number that will be displayed if senderName is not included
                                'senderName' => $senderName,                          //  Sender name e.g "Company XYZ"
                                'outboundSMSTextMessage' => [
                                    'message' => $smsMessage->content.' (mid: '.$smsMessage->id.')'
                                ],
                                'clientCorrelator' => $smsMessage->id .'-'. time()
                            ]
                        ];

                        $response = Http::withOptions($options)->withHeaders($headers)->post($smsEndpoint, $requestData);

                        $responseData = $response->json();

                        // Handle the response as needed
                        if ($response->status() === 201) {

                            $smsMessage->update([
                                'sent' => true,
                                'error' => [
                                    'request' => $requestData,
                                    'response' => $responseData,
                                ]
                            ]);

                            //  Sms sent
                            return true;

                        }else{

                            $smsMessage->update(['error' => [
                                'request' => $requestData,
                                'response' => $responseData,
                            ]]);

                        }

                    }
                }

            }

            // Failed to send sms
            return false;

        }catch(Exception $e) {

            report($e);

            // Failed to send sms
            return false;

        }
    }

    /**
     *  Request the Orange SMS Access Token
     */
    public static function requestSmsAccessToken($clientCredentials): bool|string
    {
        try{

            /**
             *  Note the following:
             *
             *  To test sending sms using POSTMAN then replace "https://aas-bw.com.intraorange:443" with "https://aas.orange.co.bw:443".
             *  The "https://aas-bw.com.intraorange:443" domain is used to send SMS while the application is hosted on the Orange Server
             *  The "https://aas.orange.co.bw:443" domain is used to send SMS while the application is hosted outside the Orange Server
             *  such as on a local machine (Macbook, e.t.c) or POSTMAN. Since this application will be hosted on the Orange Server, we
             *  will use the "https://aas-bw.com.intraorange:443" domain
             */
            $tokenEndpoint = 'https://aas-bw.com.intraorange:443/token';

            /**
             *  Sample Response:
             *
             *  {
             *      "access_token": "eyJ4NXQiOiJOalUzWWpJeE5qRTVObU0wWVRkbE1XRmhNVFEyWWpkaU1tUXdNemMwTmpreFkyTmlaRE0xTlRrMk9EaGxaVFkwT0RFNU9EZzBNREkwWlRreU9HRmxOZyIsImtpZCI6Ik5qVTNZakl4TmpFNU5tTTBZVGRsTVdGaE1UUTJZamRpTW1Rd016YzBOamt4WTJOaVpETTFOVGsyT0RobFpUWTBPREU1T0RnME1ESTBaVGt5T0dGbE5nX1JTMjU2IiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiJPQldfSU5URUdSQVRJT05AY2FyYm9uLnN1cGVyIiwiYXV0IjoiQVBQTElDQVRJT04iLCJhdWQiOiJST2VHNFUxMXBhOUI4ZWludGVPUk5Mcjh1RWdhIiwibmJmIjoxNjkwNDY1MzY5LCJhenAiOiJST2VHNFUxMXBhOUI4ZWludGVPUk5Mcjh1RWdhIiwic2NvcGUiOiJhbV9hcHBsaWNhdGlvbl9zY29wZSBkZWZhdWx0IiwiaXNzIjoiaHR0cHM6XC9cL2Fhcy1idy1ndy5jb20uaW50cmFvcmFuZ2U6NDQzXC9vYXV0aDJcL3Rva2VuIiwiZXhwIjoxNjkwNDY4OTY5LCJpYXQiOjE2OTA0NjUzNjksImp0aSI6Ijg1ZDk2ZGJmLTNjYTAtNGEyMS05NzAwLWFlNGNlMTYzMDRjNiJ9.fFSjVkPWfxdLpYAmF86tGZInSI65Wtwz1sDYuQ_9QxHilqU2hUi5bJHB6Iw3cQepayJeY4899RLQ10H27YV9-P1zcVO_DJsiKA1itMZqcdwI5zMjmtOyJ7hbbACWLNXui4wYkuhWP2PhV3YgenB3wcNHIHtt-6dz4p4OIEkL22dmr_g5d6T-eBR3JLqGtP2ijyAfxxuS0brF6clEF04m2XzzE_RH4YoFzLvQPA56cuD45uMsNodhsK7D4f4xLOKyDiLjzXxwrnPuEgzsLp8LrZYmFgNRasLvdbazJFeOmZY9DrPk0vtYD93Bjb3nEmH5Mdgv4PsxoN_medTJdJ6Efw",
             *      "scope": "am_application_scope default",
             *      "token_type": "Bearer",
             *      "expires_in": 3600
             *  }
             *
             */
            $options = [
                'verify' => false,  // Disable SSL certificate verification
            ];

            $headers = [
                'Authorization' => 'Basic '.$clientCredentials,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];

            $requestData = [
                'grant_type' => 'client_credentials'
            ];

            $response = Http::asForm()->withOptions($options)->withHeaders($headers)->post($tokenEndpoint, $requestData);

            // Handle the response as needed
            if ($response->status() === 200) {

                $responseData = $response->json();

                // Return the access token
                return $responseData['access_token'];

            }

            // Failed to create token
            return false;

        }catch(Exception $e) {

            report($e);

            // Failed to create token
            return false;

        }
    }
}
