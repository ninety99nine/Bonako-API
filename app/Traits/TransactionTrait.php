<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;

trait TransactionTrait
{
    use BaseTrait;

    /**
     *  Check if this transaction is paid
     *
     *  @return bool
     */
    public function isPaid()
    {
        return strtolower($this->getRawOriginal('payment_status')) === 'paid';
    }

    /**
     *  Check if this transaction is pending payment
     *
     *  @return bool
     */
    public function isPendingPayment()
    {
        return strtolower($this->getRawOriginal('payment_status')) === 'pending payment';
    }

    /**
     *  Check if this transaction is cancelled
     *
     *  @return bool
     */
    public function isCancelled()
    {
        return $this->is_cancelled;
    }
}
