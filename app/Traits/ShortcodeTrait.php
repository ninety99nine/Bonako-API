<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;

trait ShortcodeTrait
{
    use BaseTrait;

    /**
     *  Check if this shortcode is for visiting
     *
     *  @return bool
     */
    public function forVisiting()
    {
        return strtolower($this->getRawOriginal('action')) === 'visit';
    }

    /**
     *  Check if this shortcode is for paying
     *
     *  @return bool
     */
    public function forPaying()
    {
        return strtolower($this->getRawOriginal('action')) === 'pay';
    }
}
