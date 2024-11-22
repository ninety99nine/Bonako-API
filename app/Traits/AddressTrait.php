<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;
use App\Services\Country\CountryService;

trait AddressTrait
{
    use BaseTrait;

    /**
     *  Get complete address
     *
     *  @return bool
     */
    public function completeAddress()
    {
        $countryName = function() {
            if (empty($this->country_code)) return '';
            return CountryService::findCountryNameByTwoLetterCountryCode($this->country_code) ?? '';
        };

        return collect([$this->address_line, $this->address_line2, $this->city, $this->state, $this->zip, $countryName()])->map('trim')->filter()->unique()->join(', ');
    }
}
