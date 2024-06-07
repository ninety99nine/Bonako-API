<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;

trait PaymentMethodTrait
{
    use BaseTrait;

    /**
     *  Check if this is a DPO payment method
     *
     *  @return bool
     */
    public function isDpo()
    {
        return strtolower($this->getRawOriginal('method')) === 'dpo';
    }

    /**
     *  Check if this is an Orange Money payment method
     *
     *  @return bool
     */
    public function isOrangeMoney()
    {
        return strtolower($this->getRawOriginal('method')) === 'orange money';
    }
}
