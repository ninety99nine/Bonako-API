<?php

namespace App\Services\PhoneNumber;

use stdClass;
use Illuminate\Support\Str;
use App\Services\Country\CountryService;
use Propaganistas\LaravelPhone\PhoneNumber;

class PhoneNumberService
{
    /**
     *  Format phone number
     *
     *  @param PhoneNumber|string $phoneNumber
     *  @return stdClass
     */
    public static function formatPhoneNumber(PhoneNumber|string $phoneNumber): stdClass
    {
        $phoneNumber = $phoneNumber instanceof PhoneNumber ? $phoneNumber : (new PhoneNumber($phoneNumber));

        $obj = new stdClass();
        $obj->country = $phoneNumber->getCountry();
        $obj->dialing_code = self::getDialingCode($phoneNumber);
        $obj->national = self::getNationalPhoneNumberWithoutSpaces($phoneNumber);
        $obj->international = $phoneNumber->formatE164();

        return $obj;
    }

    /**
     *  Get national phone number without spaces
     *
     *  @param PhoneNumber|string $phoneNumber
     *  @return string
     */
    public static function getNationalPhoneNumberWithoutSpaces(string|PhoneNumber $phoneNumber): string
    {
        $phoneNumber = $phoneNumber instanceof PhoneNumber ? $phoneNumber : (new PhoneNumber($phoneNumber));
        return Str::replace(' ', '', $phoneNumber->formatNational());
    }

    /**
     *  Get dialing code
     *
     *  @param PhoneNumber|string $phoneNumber
     *  @return string|null
     */
    public static function getDialingCode(string|PhoneNumber $phoneNumber): string|null
    {
        $phoneNumber = $phoneNumber instanceof PhoneNumber ? $phoneNumber : (new PhoneNumber($phoneNumber));
        $dialingCode = CountryService::findCountryByTwoLetterCountryCode($phoneNumber->getCountry())?->dialingCode;
        return $dialingCode ? '+'.$dialingCode : null;
    }
}
