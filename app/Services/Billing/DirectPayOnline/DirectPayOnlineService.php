<?php

namespace App\Services\Billing\DirectPayOnline;

use c;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class DirectPayOnlineService
{
    private static $ptl = 24;
    private static $ptlType = 'hours';

    /**
     * Create payment link
     *
     * @param string $companyToken
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public static function createPaymentLink(string $companyToken, array $data): array
    {
        try {

            $client = new Client();
            $url = config('app.DPO_CREATE_TOKEN_URL');

            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/xml',
                ],
                'body' => self::prepareCreateTokenXMLTag($companyToken, $data)
            ]);

            $xmlResponse = simplexml_load_string($response->getBody());
            $result = (string) $xmlResponse->Result;

            if($result === '000') {

                $transToken = (string) $xmlResponse->TransToken;
                $transReference = (string) $xmlResponse->TransRef;
                $paymentUrl = config('app.DPO_PAYMENT_URL').'?ID='.$transToken;

                return [
                    'created' => true,
                    'data' => [
                        'dpo_payment_url' => $paymentUrl,
                        'dpo_transaction_token' => $transToken,
                        'dpo_transaction_reference' => $transReference,
                        'dpo_payment_url_expires_at' => self::determinePaymentUrlExpiresAt($data)
                    ]
                ];

            }else{

                $resultExplanation = (string) $xmlResponse->ResultExplanation;

                throw new Exception($resultExplanation);

            }

        } catch (Exception $e) {

            $message = $e->getMessage();

            if(Str::contains($message, ['429 Too Many Requests'])) {
                $message = 'Too many requests';
            }

            return [
                'created' => false,
                'message' => $message
            ];

        }
    }

    /**
     * Verify payment
     *
     * @param string $companyToken
     * @param string $transactionToken
     * @return array
     * @throws \Exception
     */
    public static function verifyPayment(string $companyToken, string $transactionToken): array
    {
        $client = new Client();
        $originalUrl = config('app.DPO_VERIFY_TOKEN_URL');
        $url = Str::endsWith($originalUrl, '/') ? $originalUrl : Str::finish($originalUrl, '/');

        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/xml',
            ],
            'body' => self::prepareVerifyTokenXMLTag($companyToken, $transactionToken)
        ]);

        $xmlResponse = simplexml_load_string($response->getBody());

        $result = (string) $xmlResponse->Result;

        if($result === '000') {

            return [
                'dpo_verified_payment_response' => [
                    'acc_ref' => (string) $xmlResponse->AccRef,
                    'fraud_alert' => (string) $xmlResponse->FraudAlert,
                    'customer_zip' => (string) $xmlResponse->CustomerZip,
                    'customer_name' => (string) $xmlResponse->CustomerName,
                    'customer_city' => (string) $xmlResponse->CustomerCity,
                    'customer_phone' => (string) $xmlResponse->CustomerPhone,
                    'customer_credit' => (string) $xmlResponse->CustomerCredit,
                    'fraud_explnation' => (string) $xmlResponse->FraudExplnation,
                    'customer_country' => (string) $xmlResponse->CustomerCountry,
                    'customer_address' => (string) $xmlResponse->CustomerAddress,
                    'transaction_amount' => (string) $xmlResponse->TransactionAmount,
                    'customer_credit_type' => (string) $xmlResponse->CustomerCreditType,
                    'transaction_approval' => (string) $xmlResponse->TransactionApproval,
                    'transaction_currency' => (string) $xmlResponse->TransactionCurrency,
                    'transaction_net_amount' => (string) $xmlResponse->TransactionNetAmount,
                    'mobile_payment_request' => (string) $xmlResponse->MobilePaymentRequest,
                    'transaction_final_amount' => (string) $xmlResponse->TransactionFinalAmount,
                    'transaction_final_currency' => (string) $xmlResponse->TransactionFinalCurrency,
                    'transaction_settlement_date' => (string) $xmlResponse->TransactionSettlementDate,
                    'transaction_rolling_reserve_date' => (string) $xmlResponse->TransactionRollingReserveDate,
                    'transaction_rolling_reserve_amount' => (string) $xmlResponse->TransactionRollingReserveAmount,
                ],
            ];

        }else{

            $resultExplanation = (string) $xmlResponse->ResultExplanation;
            throw new Exception($resultExplanation);

        }
    }

    /**
     * Verify payment
     *
     * @param string $companyToken
     * @param string $transactionToken
     * @return void
     * @throws \Exception
     */
    public static function cancelPaymentLink(string $companyToken, string $transactionToken): void
    {
        $client = new Client();
        $originalUrl = config('app.DPO_CANCEL_TOKEN_URL');
        $url = Str::endsWith($originalUrl, '/') ? $originalUrl : Str::finish($originalUrl, '/');

        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/xml',
            ],
            'body' => self::prepareCancelTokenXMLTag($companyToken, $transactionToken)
        ]);

        $xmlResponse = simplexml_load_string($response->getBody());

        $result = (string) $xmlResponse->Result;

        if($result !== '000') {

            $resultExplanation = (string) $xmlResponse->ResultExplanation;
            throw new Exception($resultExplanation);

        }
    }

    /**
     * Prepare create token XML tag
     *
     * @param string $companyToken
     * @param array $data
     * @return string
     */
    private static function prepareCreateTokenXMLTag(string $companyToken, array $data)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <API3G>
                    <Request>createToken</Request>
                    '.self::prepareServicesXMLTag($data).'
                    '.self::prepareTransactionXMLTag($data).'
                    <CompanyToken>'.$companyToken.'</CompanyToken>
                </API3G>';
    }

    /**
     * Prepare verify token XML tag
     *
     * @param string $companyToken
     * @param string $transactionToken
     * @return string
     */
    private static function prepareVerifyTokenXMLTag(string $companyToken, string $transactionToken)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <API3G>
                    <Request>verifyToken</Request>
                    <CompanyToken>'.$companyToken.'</CompanyToken>
                    <TransactionToken>'.$transactionToken.'</TransactionToken>
                </API3G>';
    }

    /**
     * Prepare cancel token XML tag
     *
     * @param string $companyToken
     * @param string $transactionToken
     * @return string
     */
    private static function prepareCancelTokenXMLTag(string $companyToken, string $transactionToken)
    {
        return '<?xml version="1.0" encoding="utf-8"?>
                <API3G>
                    <Request>cancelToken</Request>
                    <CompanyToken>'.$companyToken.'</CompanyToken>
                    <TransactionToken>'.$transactionToken.'</TransactionToken>
                </API3G>';
    }

    /**
     * Prepare transaction XML tag
     *
     * @param array $data
     * @return string
     */
    private static function prepareTransactionXMLTag(array $data)
    {
        $requiredFields = [
            'PaymentAmount' => $data['paymentAmount'],
            'PaymentCurrency' => $data['paymentCurrency']
        ];

        $optionalFields = [
            'TransactionSource' => 'API',
            'PTL' => $data['ptl'] ?? self::$ptl,
            'BackURL' => $data['backURL'] ?? null,
            'MetaData' => $data['metadata'] ?? null,
            'CompanyRef' => $data['companyRef'] ?? null,
            'RedirectURL' => $data['redirectURL'] ?? null,
            'customerZip' => $data['customerZip'] ?? null,
            'customerCity' => $data['customerCity'] ?? null,
            'PTLtype' => $data['ptlType'] ?? self::$ptlType,
            'CompanyAccRef' => $data['companyAccRef'] ?? null,
            'customerEmail' => $data['customerEmail'] ?? null,
            'customerPhone' => $data['customerPhone'] ?? null,
            'DefaultPayment' => $data['defaultPayment'] ?? 'CC',
            'CompanyRefUnique' => $data['companyRefUnique'] ?? 1,
            'EmailTransaction' => $data['emailTransaction'] ?? 0,
            'customerAddress' => $data['customerAddress'] ?? null,
            'customerCountry' => $data['customerCountry'] ?? null,
            'customerLastName' => $data['customerLastName'] ?? null,
            'customerDialCode' => $data['customerDialCode'] ?? null,
            'DefaultPaymentMNO' => $data['defaultPaymentMNO'] ?? null,
            'customerFirstName' => $data['customerFirstName'] ?? null,
            'DefaultPaymentCountry' => $data['defaultPaymentCountry'] ?? null,
        ];

        $transaction = collect($requiredFields)->merge($optionalFields)->map(function ($value, $key) {

            if ($value === null) return '';

            if ($key === 'MetaData') {
                return '<'.$key.'><![CDATA['.json_encode($value).']]></'.$key.'>';
            }else{
                return '<'.$key.'>' . htmlspecialchars($value) . '</'.$key.'>';
            }

        })->filter()->join('');

        return '<Transaction>'.$transaction.'</Transaction>';

    }

    /**
     * Determine payment url expires at
     *
     * @param array $data
     * @return Carbon
     */
    private static function determinePaymentUrlExpiresAt(array $data)
    {
        $ptl = $data['ptl'] ?? self::$ptl;
        $ptlType = $data['ptlType'] ?? self::$ptlType;

        if($ptlType == 'hours') {
            return Carbon::now()->addHours($ptl);
        }else if($ptlType == 'minutes') {
            return Carbon::now()->addMinutes($ptl);
        }
    }

    /**
     * Prepare services XML tag
     *
     * @param array $data
     * @return string
     */
    private static function prepareServicesXMLTag(array $data)
    {
        $services = collect($data['services'])->map(function ($service) {
            return '<Service>
                        <ServiceType>3854</ServiceType>
                        <ServiceDate>'.$service['serviceDate'].'</ServiceDate>
                        <ServiceDescription>'.$service['serviceDescription'].'</ServiceDescription>
                    </Service>';
        })->filter()->join('');

        return '<Services>'.$services.'</Services>';
    }
}
