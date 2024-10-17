<?php

namespace App\Services\Billing\OrangeMoney;

use Exception;
use App\Models\Store;
use App\Models\Order;
use GuzzleHttp\Client;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use App\Exceptions\OrangeMoneyMerchantCodeNotProvidedException;

class OrangeMoneyService
{
    /**
     *  Create a new order payment link and attach it to this transaction
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public static function createOrderPaymentLink(Transaction $transaction)
    {
        $relationships = [];

        if($transaction->relationLoaded('customer') == false) {
            array_push($relationships, 'customer');
        }

        if($transaction->relationLoaded('owner') == false) {
            if(strtolower($transaction->owner_type) == 'order') {
                array_push($relationships, 'owner.store');
            }
        }

        if(count($relationships)) {
            $transaction = $transaction->load($relationships);
        }

        /**
         *  @var Order $order
         */
        $order = $transaction->owner;

        /**
         *  @var Store $store
         */
        $store = $order->store;
        $merchantCode = $store->orange_money_merchant_code;

        if(empty($merchantCode)) {
            throw new OrangeMoneyMerchantCodeNotProvidedException();
        }

        $transactionId = $transaction->id;
        $transactionAmount = $transaction->amount->amount;

        $customer = $transaction->customer;
        $mobileNumber = $customer->mobile_number->formatE164();

        try {

            /**
             *  Always make sure that the amount is never zero since Orange Money would
             *  return a $jsonBody['message'] = "A required field is missing or empty"
             */
            $response = Http::post(config('app.ORANGE_MONEY_PUSH_PAYMENT_URL'), [
                'msisdn' => $mobileNumber,
                'payerRef' => $transactionId,
                'amount' => $transactionAmount,
                'merchantCode' => $merchantCode,
            ]);

            /**
             *  Get the response body as a string e.g
             *
             *  $body = {"statusCode":200,"message":"Successfully initiated payment request for MSISDN 26772882239"} or
             *  $body = {"statusCode":405,"message":"Duplicate transaction"}
             *
             *  Note that Orange Money will always return a status = 200, however they will include their own
             *  status code and message as part of the body response.
             *
             *  This means that we need to check the actual status from the contents of the response body since we know
             *  that $response->status() will always be "200" while the $jsonBody['statusCode'] might be a "405"
             */
            $jsonBody = $response->json();

            $orangeMoneyPaymentResponse = [
                'onPushPaymentResponse' => $jsonBody,
            ];

            //  Capture the response information on this transaction
            $transaction->update([
                'metadata' => [
                    'orange_money_payment_response' => $orangeMoneyPaymentResponse
                ]
            ]);

            //  Handle the response as needed
            if ($jsonBody['statusCode'] === 200) {

            }else{

                // Handle any exceptions or errors that occurred during the API request
                // ...
                new Exception('Orange Money: '.$jsonBody['message'], 400);

            }

            return $transaction;

        } catch (Exception $e) {

            // Handle any exceptions or errors that occurred during the API request
            // ...
            throw $e;

        }

    }
}
