<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;
use App\Enums\TransactionPaymentStatus;
use App\Enums\TransactionVerificationType;

trait TransactionTrait
{
    use BaseTrait;

    /**
     *  Check if transaction has been paid
     *
     *  @return bool
     */
    public function isPaid()
    {
        return strtolower($this->getRawOriginal('payment_status')) === strtolower(TransactionPaymentStatus::PAID->value);
    }

    /**
     *  Check if transaction has failed payment
     *
     *  @return bool
     */
    public function isFailedPayment()
    {
        return strtolower($this->getRawOriginal('payment_status')) === strtolower(TransactionPaymentStatus::FAILED->value);
    }

    /**
     *  Check if transaction is pending payment
     *
     *  @return bool
     */
    public function isPendingPayment()
    {
        return strtolower($this->getRawOriginal('payment_status')) === strtolower(TransactionPaymentStatus::PENDING->value);
    }

    /**
     *  Check if transaction is subject to manual verification
     *
     *  @return bool
     */
    public function isSubjectToManualVerification()
    {
        return strtolower($this->getRawOriginal('verification_type')) === strtolower(TransactionVerificationType::MANUAL->value);
    }

    /**
     *  Check if transaction is subject to automatic verification
     *
     *  @return bool
     */
    public function isSubjectToAutomaticVerification()
    {
        return strtolower($this->getRawOriginal('verification_type')) === strtolower(TransactionVerificationType::AUTOMATIC->value);
    }
}
