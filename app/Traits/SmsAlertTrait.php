<?php

namespace App\Traits;

use App\Models\Transaction;
use App\Traits\Base\BaseTrait;

trait SmsAlertTrait
{
    use BaseTrait;

    /**
     *  Craft the sms alerts paid successfully sms messsage
     *
     *  @return string
     */
    public function craftSmsAlertsPaidSuccessfullyMessage(int $smsCredits, Transaction $transaction) {
        return $transaction->amount->amountWithCurrency.' paid successfully for ' . $smsCredits . ($smsCredits == 1 ? ' sms alert' : ' sms alerts').'. You now have '. $this->sms_credits . ($this->sms_credits == 1 ? ' sms alert' : ' sms alerts');
    }

}
