<?php

namespace App\Enums;

enum TransactionFailureReason:string {

    /**
     *  Airtime Billing Failure Reasons:
     */
    case INACTIVE_ACCOUNT = 'The mobile number is currently inactive';
    case INSUFFICIENT_FUNDS = 'You do not have enough funds to complete this transaction';
    case USAGE_CONSUMPTION_MAIN_BALANCE_NOT_FOUND = 'The Main Balance information was not found on the airtime billing usage consumption response';
}
