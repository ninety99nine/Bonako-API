<?php

namespace App\Traits;

use App\Traits\Base\BaseTrait;
use App\Services\Country\CountryService;

trait DeliveryAddressTrait
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
            if (empty($this->country)) return '';
            return CountryService::findCountryNameByTwoLetterCountryCode($this->country) ?? '';
        };

        return collect([$this->address_line, $this->address_line2, $this->city, $this->state, $this->zip, $countryName()])->map('trim')->filter()->unique()->join(', ');
    }

    /**
     *  Get complete address without city and country
     *
     *  @return bool
     */
    public function completeAddressWithoutCityAndCountry()
    {
        return collect([$this->address_line, $this->address_line2, $this->state == $this->city ? '' : $this->state, $this->zip])->map('trim')->filter()->unique()->join(', ');
    }
}
