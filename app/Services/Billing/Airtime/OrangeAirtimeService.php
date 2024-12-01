<?php

namespace App\Services\Billing\Airtime;

use GuzzleHttp\Client;
use App\Enums\CacheName;
use App\Models\Transaction;
use App\Helpers\CacheManager;
use App\Enums\TransactionFailureType;
use App\Enums\TransactionFailureReason;
use App\Enums\TransactionPaymentStatus;

class OrangeAirtimeService
{
    /**
     *  Bill user on their airtime
     *
     *  @param string $msisdn - The MSISDN (mobile number) of the subscriber to be billed e.g +26772000001
     *  @param string $mobileNetworkProductId - Uniquely identify the product being purchased
     *  @param Transaction $transaction - The Transaction Model
     *
     *  @return Transaction
     */
    public static function billUsingAirtime(string $msisdn, string $mobileNetworkProductId, Transaction $transaction): Transaction
    {
        try {

            //  Remove msisdn "+" (if set)
            $msisdn = ltrim($msisdn, '+');

            //  Set the amount to be billed
            $amount = (float) $transaction->amount->amount;

            //  Set the description for this payment
            $description = $transaction->description;

            //  Set default values
            $failureType = $failureReason = $ratingType = $fundsAfterDeduction = $fundsBeforeDeduction = null;

            if(false) {
            /**
             *  ------------------------
             *  Request the access token
             *  ------------------------
             *
             *  On Success, the response payload is as follows:
             *
             *  [
             *      "status" => true
             *      "body" => [
             *          "access_token" => "c0352550-14c4-3a74-b82e-31bd8d09a556",
             *          "scope" => "am_application_scope default",
             *          "token_type" => "Bearer",
             *          "expires_in" => 3600
             *      ]
             *  ]
             *
             *  On Fail, the response payload is as follows:
             *
             *  [
             *      "status" => false
             *      "body" => [
             *          "error_description" => "Oauth application is not in active state.",
             *          "error" => "invalid_client"
             *      ]
             *  ]
             */
            $response = self::requestNewAirtimeBillingAccessToken();
            $status = $response['status'];

            if($status) {

                $accessToken = $response['body']['access_token'];

                /**
                 *  -----------------------------
                 *  Request the product inventory
                 *  -----------------------------
                 *
                 *  On Success, the response payload is as follows:
                 *
                 *  [
                 *      "status" => true
                 *      "body" => [
                 *          [
                 *              "id" => "8037c89b-f204-428e-9336-d3a4bca1b3fe",
                 *              "ratingType" => "Postpaid",
                 *              "status" => "Active",
                 *              "isBundle" => true,
                 *              "startDate" => "2020-09-17T00 =>00 =>00+0000",
                 *              "productOffering" => [
                 *                  "id" => "Orange_Postpaid",
                 *                  "name" => "MySim"
                 *              ]
                 *          ]
                 *      ]
                 *  ]
                 *
                 *  On Fail, the response payload is as follows:
                 *
                 *  [
                 *      "status" => false
                 *      "body" => [
                 *          "code" => 4001,
                 *          "message" => "Missing parameter",
                 *          "description" => "Parameter publicKey is missing, null or empty"
                 *      ]
                 *  ]
                 */
                $response = self::requestAirtimeBillingProductInventory($msisdn, $accessToken);
                $status = $response['status'];

                if($status) {

                    //  Get the first item of the product inventory array
                    $productInventory = $response['body'][0];

                    //  Determine if this is an active account
                    $isAnActiveAccount = strtolower($productInventory['status']) == 'active';

                    //  If this is an active account
                    if( $status = $isAnActiveAccount ) {

                        //  Get the account rating type
                        $ratingType = strtolower($productInventory['ratingType']);

                        //  Determine if this is a prepaid account
                        $isPrepaidAccount = ($ratingType == 'prepaid');

                        //  Determine if this is a postpaid account
                        $isPostpaidAccount = ($ratingType == 'postpaid');

                        //  If this is a postpaid account, we assume to always have enough funds
                        $hasEnoughFunds = $isPostpaidAccount;

                        /**
                         *  If this is a prepaid account, we need to check the
                         *  account balance to know if we have enough funds.
                         */
                        if( $isPrepaidAccount ) {

                            /**
                             *  -----------------------------
                             *  Request the usage consumption
                             *  -----------------------------
                             *
                             *  On Success, the response payload is as follows:
                             *
                             *  [
                             *      "status" => true
                             *      "body" => [
                             *          "id" => "2b778311-ab1b-4f9b-bdb7-e8f3632a6ca9",
                             *          "effectiveDate" => "2022-01-21T13:24:33+0000",
                             *          "bucket" => [
                             *              ...,
                             *              [
                             *                  "id" => "OCS-0",
                             *                  "name" => "Main Balance",
                             *                  "usageType" => "accountBalance",
                             *                  "bucketBalance" => [
                             *                      [
                             *                          "unit" => "BWP",
                             *                          "remainingValue" => 0,
                             *                          "validFor" => [
                             *                              "startDateTime" => "2019-04-04T00:00:00+0000",
                             *                              "endDateTime" => "2023-01-06T00:00:00+0000"
                             *                          ]
                             *                      ]
                             *                  ]
                             *              ],
                             *              ...
                             *          ]
                             *      ]
                             *  ]
                             *
                             *  On Fail, the response payload is as follows:
                             *
                             *  [
                             *      "status" => false
                             *      "body" => [
                             *          "code" => 4001,
                             *          "message" => "Missing parameter",
                             *          "description" => "Parameter publicKey is missing, null or empty"
                             *      ]
                             *  ]
                             */
                            $response = self::requestAirtimeBillingUsageConsumption($msisdn, $accessToken);
                            $status = $response['status'];

                            if($status) {

                                //  Get the bucket with the id of "OCS-0" as it holds information about the "Main Balance"
                                $accountMainBalanceBucket = collect($response['body']['bucket'])->firstWhere('id', 'OCS-0');

                                //  If the bucket with the id of "OCS-0" was extracted successfully
                                if( $status = !empty($accountMainBalanceBucket) ) {

                                    //  Get the remaining value (The Airtime left that we can bill from the bucket balance)
                                    $remainingValue = (float) $accountMainBalanceBucket['bucketBalance'][0]['remainingValue'];

                                    //  Determine if we have enough funds
                                    $status = $hasEnoughFunds = ($remainingValue >= $amount);

                                    //  Set the funds before deduction
                                    $fundsBeforeDeduction = $remainingValue;

                                    //  Set the funds after deduction
                                    $fundsAfterDeduction = $hasEnoughFunds ? ($remainingValue - $amount) : $remainingValue;

                                    //  If we do not have enough funds
                                    if( !$hasEnoughFunds ) {

                                        $failureType = TransactionFailureType::INSUFFICIENT_FUNDS;

                                    }

                                }else{

                                    $failureType = TransactionFailureType::USAGE_CONSUMPTION_MAIN_BALANCE_NOT_FOUND;

                                }

                            }else{

                                $failureType = TransactionFailureType::USAGE_CONSUMPTION_RETRIEVAL_FAILED;

                                if(isset($response['body'])) {
                                    $body = $response['body'];
                                    $hasMessage = isset($body['message']) && !empty($body['message']);
                                    $hasDescription = isset($body['description']) && !empty($body['description']);

                                    if($hasMessage && $hasDescription) {
                                        $failureReason = trim($body['message']) .": ". trim($body['description']);
                                    }else if($hasDescription) {
                                        $failureReason = trim($body['description']);
                                    }else if($hasMessage) {
                                        $failureReason = trim($body['message']);
                                    }else{
                                        $failureReason = json_encode($body);
                                    }
                                }

                            }

                        }

                        if($status) {

                            /**
                             *  --------------------------
                             *  Request to bill subscriber
                             *  --------------------------
                             *
                             *  On Success, the response payload is as follows:
                             *
                             *  [
                             *      "status" => true
                             *      "body" => [
                             *          "amountTransaction" => [
                             *              "endUserId" => "tel:+ [MSISDN_WITH_COUNTRYCODE]",
                             *              "paymentAmount" => [
                             *                  "chargingInformation" => [
                             *                      "amount" => 5 ,
                             *                      "currency" => "XOF",
                             *                      "description" => [
                             *                          "Short description of the charge"
                             *                      ]
                             *                  ],
                             *                  "totalAmountCharged" => 5 ,
                             *                  "chargingMetaData" => [
                             *                      "productId" => "Daily_subscription",
                             *                      "serviceId" => "Football_results",
                             *                      "purchaseCategoryCode" => "Daily_autorenew_pack "
                             *                  ]
                             *              ],
                             *              "clientCorrelator" => "unique-technical-id",
                             *              "referenceCode" => "Service_provider_payment_reference",
                             *              "transactionOperationStatus" => "Charged",
                             *              "serverReferenceCode" => "5b9bb0235c2dbe6d16d6b5b2",
                             *              "resourceURL" => "/payment/v1/tel%3A%2B [MSISDN_WITH_COUNTRYCODE] /transactions/amount/5b9bb0235c2dbe6d16d6b5b2",
                             *              "link" => []
                             *          ]
                             *      ]
                             *  ]
                             *
                             *  On Fail, the response payload is as follows:
                             *
                             *  Policy error example:
                             *
                             *  [
                             *      "status" => false
                             *      "body" => [
                             *          "requestError" => [
                             *              "policyException" => [
                             *                  "messageId" => "POL2206",
                             *                  "text" => "User forbidden."
                             *              ]
                             *          ]
                             *      ]
                             *  ]
                             *
                             *  or
                             *
                             *  Server error example:
                             *
                             *  [
                             *      "status" => false
                             *      "body" => [
                             *          "requestError" => [
                             *              "serviceException" => [
                             *                  "messageId": "SVC0005",
                             *                  "text": "duplicate correlatorId cc1d2d34",
                             *                  "variables": [
                             *                      "cc1d2d34"
                             *                  ]
                             *              ]
                             *          ]
                             *      ]
                             *  ]
                             */
                            $response = self::requestAirtimeBillingDeductFee($transaction, $msisdn, $amount, $mobileNetworkProductId, $description, $accessToken);

                            if($status = $response['status']) {

                                //  The billing is successful at this point

                            }else{

                                $failureType = TransactionFailureType::PRODUCT_INVENTORY_RETRIEVAL_FAILED;

                                if(isset($response['body']['requestError'])) {
                                    if(isset($response['body']['requestError']['policyException'])) $failureReason = $response['body']['requestError']['policyException']['text'];
                                    if(isset($response['body']['requestError']['serviceException'])) $failureReason = $response['body']['requestError']['serviceException']['text'];
                                }

                                if(!isset($failureReason) && isset($response['body']['message'])) {
                                    $failureReason = $response['body']['message'];
                                }

                                if(!isset($failureReason)){
                                    $failureReason = json_encode($response['body']);
                                }

                            }

                        }

                    }else{

                        $failureType = TransactionFailureType::INACTIVE_ACCOUNT;

                    }

                }else{

                    $failureType = TransactionFailureType::PRODUCT_INVENTORY_RETRIEVAL_FAILED;

                    if(isset($response['body'])) {
                        $body = $response['body'];
                        $hasMessage = isset($body['message']) && !empty($body['message']);
                        $hasDescription = isset($body['description']) && !empty($body['description']);

                        if($hasMessage && $hasDescription) {
                            $failureReason = trim($body['message']) .": ". trim($body['description']);
                        }else if($hasDescription) {
                            $failureReason = trim($body['description']);
                        }else if($hasMessage) {
                            $failureReason = trim($body['message']);
                        }else{
                            $failureReason = json_encode($body);
                        }
                    }

                }

            }else{

                $failureType = TransactionFailureType::TOKEN_GENERATION_FAILED;

                if(isset($response['body'])) {
                    $body = $response['body'];
                    $hasError = isset($body['error']) && !empty($body['error']);
                    $hasErrorDescription = isset($body['error_description']) && !empty($body['error_description']);

                    if($hasError && $hasErrorDescription) {
                        $failureReason = trim($body['error']) .": ". trim($body['error_description']);
                    }else if($hasErrorDescription) {
                        $failureReason = trim($body['error_description']);
                    }else if($hasError) {
                        $failureReason = trim($body['error']);
                    }else{
                        $failureReason = json_encode($body);
                    }
                }

            }
            }else{
                $status = true;
                $ratingType = 'prepaid';
                $fundsBeforeDeduction = 100;
                $fundsAfterDeduction = 100 - $amount;
            }

            //  Update transaction
            $transaction->update([
                'failure_type' => $failureType,
                'failure_reason' => $failureReason,
                'payment_status' => $status ? TransactionPaymentStatus::PAID : TransactionPaymentStatus::FAILED,
                'metadata' => [
                    'airtime_billing_rating_type' => $ratingType,
                    'airtime_billing_funds_after_deduction' => $fundsAfterDeduction,
                    'airtime_billing_funds_before_deduction' => $fundsBeforeDeduction
                ],
            ]);

            //  Return a fresh instance of the transaction
            return $transaction->refresh();

        } catch (\Throwable $th) {

            $failureType = TransactionFailureType::INTERNAL_FAILURE;

            $transaction->update([
                'failure_type' => $failureType,
                'failure_reason' => $th->getMessage(),
                'payment_status' => TransactionPaymentStatus::FAILED
            ]);

            //  Return a fresh instance of the transaction
            return $transaction->refresh();

        }

    }

    /**
     *  Requests a new airtime billing access token
     *
     *  @return array
     */
    public static function requestNewAirtimeBillingAccessToken(): array
    {
        $clientId = config('app.ORANGE_AIRTIME_BILLING_CLIENT_ID');
        $clientSecret = config('app.ORANGE_AIRTIME_BILLING_CLIENT_SECRET');

        $cacheManager = (new CacheManager(CacheName::AIRTIME_BILLING_ACCESS_TOKEN_RESPONSE));

        if( $cacheManager->has() ) {

            return $cacheManager->get();

        }else{

            try {

                //  Set the request endpoint
                $endpoint = config('app.ORANGE_AIRTIME_BILLING_URL').'/token';

                //  Set the request options
                $options = [
                    'headers' => [
                        'Content-type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json'
                    ],
                    'form_params' => [
                        "client_id" => trim($clientId),
                        "grant_type" => "client_credentials",
                        "client_secret" => trim($clientSecret),
                    ],
                    'verify' => false,  // Disable SSL certificate verification
                ];

                //  Create a new Http Guzzle Client
                $httpClient = new Client();

                //  Perform and return the Http request
                $response = $httpClient->request('POST', $endpoint, $options);

            } catch (\GuzzleHttp\Exception\BadResponseException $e) {

                $response = $e->getResponse();

            } catch (\Throwable $e) {

                return [
                    'status' => false,
                    'body' => [
                        'error_description' => $e->getMessage()
                    ]
                ];

            }

            /**
             *  Get the response body as a String.
             *
             *  On Success, the response payload is as follows:
             *
             *  {
             *      "access_token":"c0352550-14c4-3a74-b82e-31bd8d09a556",
             *      "scope":"am_application_scope default",
             *      "token_type":"Bearer",
             *      "expires_in":3600
             *  }
             *
             *  On Fail, the response payload is as follows:
             *
             *  {
             *      "error_description": "Oauth application is not in active state.",
             *      "error": "invalid_client"
             *  }
             */
            $jsonString = $response->getBody();

            /**
             *  Get the response body as an Associative Array:
             *
             *  [
             *      "access_token" => "c0352550-14c4-3a74-b82e-31bd8d09a556",
             *      "scope" => "am_application_scope default",
             *      "token_type" => "Bearer",
             *      "expires_in" => 3600
             *  ]
             */
            $bodyAsArray = json_decode($jsonString, true);

            //  Get the response status code e.g "200"
            $statusCode = $response->getStatusCode();

            //  Return the status and the body
            $data = [
                'status' => ($statusCode == 200),
                'body' => $bodyAsArray
            ];

            if($data['status']) {

                /**
                 *  Cache the successful response data for 58 minutes. The token itself is valid for 1 hour (3600 seconds),
                 *  however we must take into consideration any latecy in the network that may delay the response.
                 *  Therefore we are accomodating 2 minutes (120 seconds) for latency costs. This then means we
                 *  can only cache this successful response data for 58 minutes.
                 *
                 *  Return the status and the body (cached)
                 */
                $cacheManager->put($data, now()->addMinutes(58));

            }

            //  Return the status and the body (uncached)
            return $data;

        }
    }

    /**
     *  Request the airtime billing product inventory data.
     *  This helps us learn the account details, for instance, whether the account
     *  is Active and whether the account is Prepaid or Postpaid.
     *
     *  @param string $msisdn - The MSISDN (mobile number) of the subscriber to be billed e.g 26772000001
     *  @param string $accessToken - The access token
     *
     *  @return array
     */
    public static function requestAirtimeBillingProductInventory($msisdn, $accessToken): array
    {
        try {

            //  Set the request endpoint
            $endpoint = config('app.ORANGE_AIRTIME_BILLING_URL').'/customer/productInventory/v1/product?publicKey='.$msisdn;

            //  Set the request options
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'verify' => false,  // Disable SSL certificate verification
            ];

            //  Create a new Http Guzzle Client
            $httpClient = new Client();

            //  Perform and return the Http request
            $response = $httpClient->request('GET', $endpoint, $options);

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {

            $response = $e->getResponse();

        } catch (\Throwable $e) {

            return [
                'status' => false,
                'body' => [
                    'message' => $e->getMessage()
                ]
            ];

        }

        /**
         *  Get the response body as a String.
         *
         *  On Success, the response payload is as follows:
         *
         *  [
         *      {
         *          "id": "8037c89b-f204-428e-9336-d3a4bca1b3fe",
         *          "ratingType": "Postpaid",
         *          "status": "Active",
         *          "isBundle": true,
         *          "startDate": "2020-09-17T00:00:00+0000",
         *          "productOffering": {
         *              "id": "Orange_Postpaid",
         *              "name": "MySim"
         *          }
         *      }
         *  ]
         *
         *  On Fail, the response payload is as follows:
         *
         *  {
         *      "code": 4001,
         *      "message": "Missing parameter",
         *      "description": "Parameter publicKey is missing, null or empty"
         *  }
         */
        $jsonString = $response->getBody();

        /**
         *  Get the response body as an Associative Array:
         *
         *  [
         *      [
         *          "id" => "8037c89b-f204-428e-9336-d3a4bca1b3fe",
         *          "ratingType" => "Postpaid",
         *          "status" => "Active",
         *          "isBundle": true,
         *          "startDate" => "2020-09-17T00:00:00+0000",
         *          "productOffering": [
         *              "id" => "Orange_Postpaid",
         *              "name" => "MySim"
         *          ]
         *      ]
         *  ]
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

    /**
     *  Request the airtime billing usage consumption data.
     *  This helps us learn how much service consumption is available e.g
     *  The available airtime balance, sms and mobile data left that can be consumed.
     *
     *  @param string $msisdn - The MSISDN (mobile number) of the subscriber to be billed e.g 26772000001
     *  @param string $accessToken - The access token
     *
     *  @return array
     */
    public static function requestAirtimeBillingUsageConsumption($msisdn, $accessToken): array
    {
        try {

            //  Set the request endpoint
            $endpoint = config('app.ORANGE_AIRTIME_BILLING_URL').'/customer/usageConsumption/v1/usageConsumptionReport?publicKey='.$msisdn;

            //  Set the request options
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'verify' => false,  // Disable SSL certificate verification
            ];

            //  Create a new Http Guzzle Client
            $httpClient = new Client();

            //  Perform and return the Http request
            $response = $httpClient->request('GET', $endpoint, $options);

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {

            $response = $e->getResponse();

        } catch (\Throwable $e) {

            return [
                'status' => false,
                'body' => [
                    'message' => $e->getMessage()
                ]
            ];

        }

        /**
         *  Get the response body as a String.
         *
         *  On Success, the response payload is as follows:
         *
         *  Return the reponse body, the structure is as follows:
         *
         *  {
         *      "id": "2b778311-ab1b-4f9b-bdb7-e8f3632a6ca9",
         *      "effectiveDate": "2022-01-21T13:24:33+0000",
         *      "bucket": [
         *          {
         *              "id": "OCS-0",
         *              "name": "Main Balance",
         *              "usageType": "accountBalance",
         *              "bucketBalance": [
         *                  {
         *                      "unit": "BWP",
         *                      "remainingValue": 0,
         *                      "validFor": {
         *                           "startDateTime": "2019-04-04T00:00:00+0000",
         *                           "endDateTime": "2023-01-06T00:00:00+0000"
         *                       }
         *                   }
         *                ]
         *              },
         *          {
         *              "id": "OCS-2",
         *              "name": "On-Net",
         *              "usageType": "accountBalance",
         *              "bucketBalance": [
         *                  {
         *                      "unit": "BWP",
         *                      "remainingValue": 0,
         *                      "validFor": {
         *                           "startDateTime": "2022-01-02T12:54:34+0000",
         *                           "endDateTime": "2022-01-20T17:51:06+0000"
         *                          }
         *                      }
         *                  ]
         *              },
         *              {
         *              "id": "OCS-5",
         *              "name": "National SMS",
         *              "usageType": "sms",
         *              "bucketBalance": [
         *                  {
         *                      "unit": "SMS",
         *                      "remainingValue": 11,
         *                      "validFor": {
         *                           "startDateTime": "2019-04-07T00:00:00+0000",
         *                           "endDateTime": "2032-01-04T00:00:00+0000"
         *                          }
         *                      }
         *                  ]
         *              },
         *       ]
         *  }
         *
         *  On Fail, the response payload is as follows:
         *
         *  {
         *      "code": 4001,
         *      "message": "Missing parameter",
         *      "description": "Parameter publicKey is missing, null or empty"
         *  }
         */
        $jsonString = $response->getBody();

        /**
         *  Get the response body as an Associative Array:
         *
         *  [
         *      "id" => "2b778311-ab1b-4f9b-bdb7-e8f3632a6ca9",
         *      "effectiveDate" => "2022-01-21T13:24:33+0000",
         *      "bucket" => [
         *          [
         *              "id" => "OCS-0",
         *              "name" => "Main Balance",
         *              "usageType" => "accountBalance",
         *              "bucketBalance" => [
         *                  [
         *                      "unit" => "BWP",
         *                      "remainingValue" => 0,
         *                      "validFor" => [
         *                           "startDateTime" => "2019-04-04T00:00:00+0000",
         *                           "endDateTime" => "2023-01-06T00:00:00+0000"
         *                       ]
         *                   ]
         *                ]
         *              ],
         *          ],
         *          ...
         *      ]
         *  ]
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

    /**
     *  Request to bill the subscriber on the given amount
     *
     *  @param Transaction $transaction - The Transaction Model
     *  @param string $msisdn - The MSISDN (mobile number) of the subscriber to be billed e.g 26772000001
     *  @param float $amount - The amount to be billed e.g 10.00
     *  @param string $mobileNetworkProductId - Uniquely identify the product being purchased
     *  @param string $description - The description of the transaction
     *  @param string $accessToken - The access token
     *
     *  @return array
     */
    public static function requestAirtimeBillingDeductFee($transaction, $msisdn, $amount, $mobileNetworkProductId, $description, $accessToken): array
    {
        try {

            //  Set the request endpoint
            $endpoint = config('app.ORANGE_AIRTIME_BILLING_URL').'/payment/v1/tel%3A%2B'.$msisdn.'/transactions/amount';

            //  Set the request options
            $options = [
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'amountTransaction' => [
                        'endUserId' => 'tel:+'.$msisdn,
                        'paymentAmount' => [
                            'chargingInformation' => [
                                'amount' => $amount,
                                'currency' => config('app.CURRENCY'),
                                'description' => [
                                    0 => $description,
                                ],
                            ],
                            'chargingMetaData' => [
                                'productId' => $mobileNetworkProductId,
                                'purchaseCategoryCode' => config('app.ORANGE_AIRTIME_BILLING_ON_BEHALF_OF')
                            ],
                        ],
                        'transactionOperationStatus' => 'Charged',

                        /**
                         *  referenceCode:
                         *  Textual information to uniquely identify the request.
                         *  Used for business logic, not for operational logic.
                         */
                        'referenceCode' => $transaction->id,

                        /**
                         *  clientCorrelator:
                         *  A unique (random) identifier set by the application that will be used by AAS to avoid erroneous repeat of requests.
                         *  If two requests are received with the same clientCorrelator, the second will be rejected.
                         */
                        'clientCorrelator' => $transaction->id,
                    ],
                ],
                'verify' => false,  // Disable SSL certificate verification
            ];

            //  Create a new Http Guzzle Client
            $httpClient = new Client();

            //  Perform and return the Http request
            $response = $httpClient->request('POST', $endpoint, $options);

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {

            $response = $e->getResponse();

        } catch (\Throwable $e) {

            return [
                'status' => false,
                'body' => [
                    'message' => $e->getMessage()
                ]
            ];

        }

        /**
         *  Get the response body as a String.
         *
         *  On Success, the response payload is as follows:
         *
         *  Return the reponse body, the structure is as follows:
         *
         *  {
         *      "amountTransaction": {
         *          "endUserId": "tel:+ {MSISDN_WITH_COUNTRYCODE} ",
         *          "paymentAmount": {
         *              "chargingInformation": {
         *                  "amount": 5 ,
         *                  "currency": " XOF ",
         *                  "description": [
         *                      "Short description of the charge"
         *                  ]
         *              },
         *              "totalAmountCharged": 5 ,
         *              "chargingMetaData": {
         *                  "productId": " Daily_subscription ",
         *                  "serviceId": " Football_results ",
         *                  "purchaseCategoryCode": " Daily_autorenew_pack "
         *              }
         *          },
         *          "clientCorrelator": "unique-technical-id",
         *          "referenceCode": "Service_provider_payment_reference",
         *          "transactionOperationStatus": "Charged",
         *          "serverReferenceCode": "5b9bb0235c2dbe6d16d6b5b2",
         *          "resourceURL": "/payment/v1/tel%3A%2B {MSISDN_WITH_COUNTRYCODE} /transactions/amount/5b9bb0235c2dbe6d16d6b5b2",
         *          "link": []
         *      }
         *  }
         *
         *  On Fail, the response payload is as follows:
         *
         *  403 status (Policy error example):
         *
         *  {
         *      "requestError": {
         *          "policyException": {
         *              "messageId": " POL2206",
         *              "text": "User forbidden."
         *          }
         *      }
         *  }
         *
         *  409 status (Service error example):
         *
         *  {
         *      "requestError": {
         *          "serviceException": {
         *              "messageId": "SVC0005",
         *              "text": "duplicate correlatorId cc1d2d34",
         *              "variables": [
         *                  "cc1d2d34"
         *              ]
         *          }
         *      }
         *  }
         */
        $jsonString = $response->getBody();

        /**
         *  Get the response body as an Associative Array:
         *
         *  [
         *      "amountTransaction" => [
         *          "endUserId" => "tel:+ [MSISDN_WITH_COUNTRYCODE]",
         *          "paymentAmount" => [
         *              "chargingInformation" => [
         *                  "amount" => 5 ,
         *                  "currency" => "XOF",
         *                  "description" => [
         *                      "Short description of the charge"
         *                  ]
         *              ],
         *              "totalAmountCharged" => 5 ,
         *              "chargingMetaData" => [
         *                  "productId" => "Daily_subscription",
         *                  "serviceId" => "Football_results",
         *                  "purchaseCategoryCode" => "Daily_autorenew_pack "
         *              ]
         *          ],
         *          "clientCorrelator" => "unique-technical-id",
         *          "referenceCode" => "Service_provider_payment_reference",
         *          "transactionOperationStatus" => "Charged",
         *          "serverReferenceCode" => "5b9bb0235c2dbe6d16d6b5b2",
         *          "resourceURL" => "/payment/v1/tel%3A%2B [MSISDN_WITH_COUNTRYCODE] /transactions/amount/5b9bb0235c2dbe6d16d6b5b2",
         *          "link" => []
         *      ]
         *  ]
         */
        $bodyAsArray = json_decode($jsonString, true);

        //  Get the response status code e.g "201"
        $statusCode = $response->getStatusCode();

        //  Return the status and the body
        return [
            'status' => ($statusCode == 201),
            'body' => $bodyAsArray
        ];
    }
}
