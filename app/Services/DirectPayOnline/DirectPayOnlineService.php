<?php

namespace App\Services\DirectPayOnline;

use Exception;
use App\Models\Store;
use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Repositories\OrderRepository;
use App\Notifications\Orders\OrderPaid;
use Illuminate\Support\Facades\Notification;
use App\Exceptions\DPOCompanyTokenNotProvidedException;
use App\Services\Sms\SmsService;

class DirectPayOnlineService
{
    public static $paymentTimeLimitInHours = 24;

    /**
     *  Return the OrderRepository instance
     *
     *  @return OrderRepository
     */
    public static function orderRepository()
    {
        return resolve(OrderRepository::class);
    }

    /**
     *  Create a new order payment link and attach it to this transaction
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public static function createOrderPaymentLink(Transaction $transaction)
    {
        $relationships = [];

        if($transaction->relationLoaded('payingUser') == false) {
            array_push($relationships, 'payingUser');
        }

        if($transaction->relationLoaded('owner') == false) {
            if(strtolower($transaction->owner_type) == 'order') {
                array_push($relationships, 'owner.store', 'owner.cart.productLines');
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
         *  @var Cart $cart
         */
        $cart = $order->cart;

        /**
         *  @var Store $store
         */
        $store = $order->store;
        $companyToken = $store->dpo_company_token;

        if(empty($companyToken)) {
            throw new DPOCompanyTokenNotProvidedException();
        }

        $transactionId = $transaction->id;
        $transactionCurrency = $transaction->currency;
        $transactionAmount = $transaction->amount->amount;

        $payingUser = $transaction->payingUser;
        $lastName = $payingUser->last_name ?? null;
        $firstName = $payingUser->first_name ?? null;
        $mobileNumber = $payingUser->mobile_number ?? null;

        $metaData = 'Store ID: '.$store->id.'\n'.
                    'Store Name: '.$store->name.'\n'.
                    'Order ID: '.$order->id.'\n'.
                    'Order Number: '.$order->number.'\n'.
                    'Transaction ID: '.$transaction->id.'\n'.
                    'Transaction Number: '.$transaction->number.'\n'.
                    'Transaction Description: '.$transaction->description;

        /*
        $services = collect($cart->productLines)->map(function($productLine) use ($order) {

            // ServiceType is provided by DPO: (Use code only)
            // 3854 - Test Product
            // 3854 - Test Service
            return '<Service>
                        <ServiceType>3854</ServiceType>
                        <ServiceDescription>'.$productLine->quantity .'x('. $productLine->name.')'.'</ServiceDescription>
                        <ServiceDate>'.$order->created_at.'</ServiceDate>
                    </Service>';

        })->join('');
        */

        $services = '<Service>
                        <ServiceType>3854</ServiceType>
                        <ServiceDescription>'.$transaction->description.' - '.$order->summary.'</ServiceDescription>
                        <ServiceDate>'.$order->created_at.'</ServiceDate>
                    </Service>';

        //  Construct Direct Pay Online (DPO) XML request
        $xmlRequest = '
            <?xml version="1.0" encoding="utf-8"?>
            <API3G>
                <CompanyToken>'.$companyToken.'</CompanyToken>
                <Request>createToken</Request>
                <Transaction>
                    <CompanyRefUnique>1</CompanyRefUnique>
                    <CompanyRef>'.$transactionId.'</CompanyRef>
                    <PaymentAmount>'.$transactionAmount.'</PaymentAmount>
                    <PaymentCurrency>'.$transactionCurrency.'</PaymentCurrency>
                    <CompanyAccRef>'.$order->number.'</CompanyAccRef>
                    <PTL>'.self::$paymentTimeLimitInHours.'</PTL>'.
                    ($lastName == null ? '' : '<customerLastName>'.$lastName.'</customerLastName>').
                    ($firstName == null ? '' : '<customerFirstName>'.$firstName.'</customerFirstName>').
                    ($mobileNumber == null ? '' : '<customerPhone>'.$mobileNumber->withExtension.'</customerPhone>').
                    '<MetaData>'.$metaData.'</MetaData>
                    <customerCity>Gabarone</customerCity>
                    <customerDialCode>'.config('app.DPO_COUNTRY_CODE').'</customerDialCode>
                    <customerCountry>'.config('app.DPO_COUNTRY_CODE').'</customerCountry>
                    <CustomerZip>0000</CustomerZip>
                    <RedirectURL>'.route('payment.success.page', ['transaction' => $transaction->id]).'</RedirectURL>
                    <BackURL>'.route('perfect.pay.advertisement.page').'</BackURL>
                    <TransactionType>Pending Payment</TransactionType>
                    <TransactionSource>Marketplace</TransactionSource>
                    <DefaultPayment>CC</DefaultPayment>
                </Transaction>
                <Services>'.$services.'</Services>
            </API3G>';

        try {

            $client = new Client();
            $response = $client->post(config('app.DPO_CREATE_TOKEN_URL'), [
                'headers' => [
                    'Content-Type' => 'application/xml',
                ],
                'body' => $xmlRequest,
            ]);

            // Parse the XML response
            $xmlResponse = simplexml_load_string($response->getBody());

            // Extract the necessary information from the response
            $result = (string) $xmlResponse->Result;
            $resultExplanation = (string) $xmlResponse->ResultExplanation;
            $transToken = (string) $xmlResponse->TransToken;
            $transRef = (string) $xmlResponse->TransRef;

            //  If the token was created successfully
            if($result === '000') {

                $paymentUrl = config('app.DPO_PAYMENT_URL').'?ID='.$transToken;

                $transaction->update([
                    'dpo_payment_url' => $paymentUrl,
                    'dpo_payment_url_expires_at' => now()->addHours(self::$paymentTimeLimitInHours)
                ]);

                return $transaction->fresh();

            }else{

                // Handle any exceptions or errors that occurred during the API request
                // ...

            }

            return $transaction;

        } catch (Exception $e) {

            // Handle any exceptions or errors that occurred during the API request
            // ...
            throw $e;

        }

    }

    /**
     *  Verify the payment and capture the response information on the transaction
     *
     * @param Transaction $transaction
     * @param Request $request
     * @return Transaction
     */
    public static function verifyPayment(Transaction $transaction, Request $request)
    {
        $client = new Client();

        //  Get the request information
        $pnrID = $request->input('PnrID');
        $transID = $request->input('TransID');
        $companyRef = $request->input('CompanyRef');
        $ccdApproval = $request->input('CCDapproval');
        $transactionToken = $request->input('TransactionToken');

        /**
         *  @var Order $order
         */
        $order = $transaction->owner;

        /**
         *  @var Store $store
         */
        $store = $order->store;
        $companyToken = $store->dpo_company_token;

        //  Construct Direct Pay Online (DPO) XML request
        $xmlRequest = '
            <?xml version="1.0" encoding="utf-8"?>
            <API3G>
                <Request>verifyToken</Request>
                <CompanyToken>'.$companyToken.'</CompanyToken>
                <TransactionToken>'.$transactionToken.'</TransactionToken>
            </API3G>';

        try {

            /**
             *  DPO throws an error when the verify token URL does not end with a suffixed "/" e.g
             *
             *  https://secure.3gdirectpay.com/API/v6/ (works fine)
             *  https://secure.3gdirectpay.com/API/v6  (throws an error)
             *
             *  We need to always make sure that we have checked for this issue before
             *  consuming the DPO API to verify the transaction.
             */
            $originalUrl = config('app.DPO_VERIFY_TOKEN_URL');

            // Check if the URL ends with "/"
            $url = Str::endsWith($originalUrl, '/') ? $originalUrl : Str::finish($originalUrl, '/');

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/xml',
                ],
                'body' => $xmlRequest
            ]);

            // Parse the XML response
            $xmlResponse = simplexml_load_string($response->getBody());

            // Extract the necessary information from the response
            $result = (string) $xmlResponse->Result;
            $accRef = (string) $xmlResponse->AccRef;
            $fraudAlert = (string) $xmlResponse->FraudAlert;
            $customerZip = (string) $xmlResponse->CustomerZip;
            $customerName = (string) $xmlResponse->CustomerName;
            $customerCity = (string) $xmlResponse->CustomerCity;
            $customerPhone = (string) $xmlResponse->CustomerPhone;
            $customerCredit = (string) $xmlResponse->CustomerCredit;
            $fraudExplnation = (string) $xmlResponse->FraudExplnation;
            $customerCountry = (string) $xmlResponse->CustomerCountry;
            $customerAddress = (string) $xmlResponse->CustomerAddress;
            $resultExplanation = (string) $xmlResponse->ResultExplanation;
            $transactionAmount = (string) $xmlResponse->TransactionAmount;
            $customerCreditType = (string) $xmlResponse->CustomerCreditType;
            $transactionApproval = (string) $xmlResponse->TransactionApproval;
            $transactionCurrency = (string) $xmlResponse->TransactionCurrency;
            $transactionNetAmount = (string) $xmlResponse->TransactionNetAmount;
            $mobilePaymentRequest = (string) $xmlResponse->MobilePaymentRequest;
            $transactionFinalAmount = (string) $xmlResponse->TransactionFinalAmount;
            $transactionFinalCurrency = (string) $xmlResponse->TransactionFinalCurrency;
            $transactionSettlementDate = (string) $xmlResponse->TransactionSettlementDate;
            $transactionRollingReserveDate = (string) $xmlResponse->TransactionRollingReserveDate;
            $transactionRollingReserveAmount = (string) $xmlResponse->TransactionRollingReserveAmount;

            //  Set the payment status of this transaction
            $paymentStatus = $result === '000' ? 'Paid' : $transaction->payment_status;

            $dpoPaymentResponse = [
                'onProcessPaymentResponse' => [
                    'pnrID' => $pnrID,
                    'transID' => $transID,
                    'companyRef' => $companyRef,
                    'ccdApproval' => $ccdApproval,
                    'transactionToken' => $transactionToken,
                ],
                'onVerifyPaymentResponse' => [
                    'result' => $result,
                    'accRef' => $accRef,
                    'fraudAlert' => $fraudAlert,
                    'customerZip' => $customerZip,
                    'customerName' => $customerName,
                    'customerCity' => $customerCity,
                    'customerPhone' => $customerPhone,
                    'customerCredit' => $customerCredit,
                    'fraudExplnation' => $fraudExplnation,
                    'customerCountry' => $customerCountry,
                    'customerAddress' => $customerAddress,
                    'resultExplanation' => $resultExplanation,
                    'transactionAmount' => $transactionAmount,
                    'customerCreditType' => $customerCreditType,
                    'transactionApproval' => $transactionApproval,
                    'transactionCurrency' => $transactionCurrency,
                    'transactionNetAmount' => $transactionNetAmount,
                    'mobilePaymentRequest' => $mobilePaymentRequest,
                    'transactionFinalAmount' => $transactionFinalAmount,
                    'transactionFinalCurrency' => $transactionFinalCurrency,
                    'transactionSettlementDate' => $transactionSettlementDate,
                    'transactionRollingReserveDate' => $transactionRollingReserveDate,
                    'transactionRollingReserveAmount' => $transactionRollingReserveAmount,
                ],
            ];

            //  Capture the response information on this transaction
            $transaction->update([
                'payment_status' => $paymentStatus,
                'dpo_payment_response' => $dpoPaymentResponse
            ]);

            //  If this order is paid
            if($paymentStatus == 'Paid') {

                //  Update the order amount balance
                self::orderRepository()->setModel($order)->updateOrderAmountBalance();

                //  Refresh the transaction
                $transaction = $transaction->fresh();

                //  Send order payment notification to the customer, friends and team members
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    //  Send notifications to the team members who joined
                    collect($store->teamMembers()->joinedTeam()->get())->merge(
                        //  As well as the custoemr and friends who were tagged on this order
                        $order->users()->get()
                    ),
                    new OrderPaid($order, $transaction)
                );

                /**
                 *  Get the users associated with this order as a customer or friend
                 *
                 *  @var Collection<User> $users
                 */
                $users = $order->users()->get();

                /**
                 *  Get the store team members (exclude the users associated with this order as a customer or friend)
                 *
                 *  @var Collection<User> $teamMembers
                 */
                $teamMembers = $store->teamMembers()->whereNotIn('users.id', $users->pluck('id'))->joinedTeam()->get();

                foreach($users->concat($teamMembers) as $user) {

                    /// Send order mark as verified payment sms to user
                    SmsService::sendOrangeSms(
                        $order->craftOrderMarkAsVerifiedPaymentSmsMessage($store, $transaction),
                        $user->mobile_number->withExtension,
                        $store, null, null
                    );

                }

            }else{

            }

            return $transaction;

        } catch (Exception $e) {

            // Handle any exceptions or errors that occurred during the API request
            // ...
            throw $e;

        }

    }
}
