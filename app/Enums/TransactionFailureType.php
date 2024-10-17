<?php

namespace App\Enums;

enum TransactionFailureType:string {

    /**
     *  Airtime Billing Failure Types:
     */
    case INTERNAL_FAILURE = 'internal failure';
    case INACTIVE_ACCOUNT = 'inactive account';
    case INSUFFICIENT_FUNDS = 'insufficient funds';
    case TOKEN_GENERATION_FAILED = 'token generation failed';
    case PRODUCT_INVENTORY_RETRIEVAL_FAILED = 'product inventory retrieval failed';
    case USAGE_CONSUMPTION_RETRIEVAL_FAILED = 'usage consumption retrieval failed';
    case USAGE_CONSUMPTION_MAIN_BALANCE_NOT_FOUND = 'main balance not found';

    /**
     *  DPO Billing Failure Types:
     */
    case PAYMENT_REQUEST_FAILED = 'Payment Request Failed';
    case PAYMENT_VERIFICATION_FAILED = 'Payment Verification Failed';
}

