<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;

trait PaymentMethodTrait
{
    use BaseTrait;

    /**
     *  Check if this is a card payment method
     *
     *  @return bool
     */
    public function isDpoCard()
    {
        return strtolower($this->getRawOriginal('method')) === 'dpo card';
    }

    /**
     *  Check if this is a orange money method
     *
     *  @return bool
     */
    public function isOrangeMoney()
    {
        return strtolower($this->getRawOriginal('method')) === 'orange money';
    }
}
