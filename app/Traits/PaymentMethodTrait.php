<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;
use App\Enums\PaymentMethodType;
use App\Enums\PaymentMethodCategory;

trait PaymentMethodTrait
{
    use BaseTrait;

    /**
     *  Check if DPO payment method
     *
     *  @return bool
     */
    public function isDpo()
    {
        return $this->getRawOriginal('type') === PaymentMethodType::DPO->value;
    }

    /**
     *  Check if Orange Money payment method
     *
     *  @return bool
     */
    public function isOrangeMoney()
    {
        return $this->getRawOriginal('type') === PaymentMethodType::ORANGE_MONEY->value;
    }

    /**
     *  Check if Orange Airtime payment method
     *
     *  @return bool
     */
    public function isOrangeAirtime()
    {
        return $this->getRawOriginal('type') === PaymentMethodType::ORANGE_AIRTIME->value;
    }

    /**
     *  Check if manual
     *
     *  @return bool
     */
    public function isManual()
    {
        return $this->getRawOriginal('category') === PaymentMethodCategory::MANUAL->value;
    }

    /**
     *  Check if local
     *
     *  @return bool
     */
    public function isLocal()
    {
        return $this->getRawOriginal('category') === PaymentMethodCategory::LOCAL->value;
    }

    /**
     *  Check if automated
     *
     *  @return bool
     */
    public function isAutomated()
    {
        return $this->getRawOriginal('category') === PaymentMethodCategory::AUTOMATED->value;
    }
}
