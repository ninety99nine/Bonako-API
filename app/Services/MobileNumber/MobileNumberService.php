<?php

namespace App\Services\MobileNumber;

use Propaganistas\LaravelPhone\PhoneNumber;

class MobileNumberService
{
    /**
     * Check if the Orange mobile number is valid.
     *
     * @param string $mobileNumber
     * @return bool
     */
    public static function isValidOrangeMobileNumber($mobileNumber): bool
    {
        return self::getMobileNetworkName($mobileNumber) === "orange";
    }

    /**
     * Check if the Mascom mobile number is valid.
     *
     * @param string $mobileNumber
     * @return bool
     */
    public static function isValidMascomMobileNumber($mobileNumber): bool
    {
        return self::getMobileNetworkName($mobileNumber) === "mascom";
    }

    /**
     * Check if the Btc mobile number is valid.
     *
     * @param string $mobileNumber
     * @return bool
     */
    public static function isValidBtcMobileNumber($mobileNumber): bool
    {
        return self::getMobileNetworkName($mobileNumber) === "btc";
    }
    /**
     * Get the mobile network by name.
     *
     * @param string $mobileNumber
     * @return bool
     *
     * Reference: https://en.wikipedia.org/wiki/Telephone_numbers_in_Botswana
     */
    public static function getMobileNetworkName(string $mobileNumber): bool
    {
        if (!is_numeric($mobileNumber)) return null;
        $number = (int) $mobileNumber;

        $isMascomRange =
            ($number >= 71000000 && $number <= 71999999) ||
            ($number >= 74000000 && $number <= 74299999) ||
            ($number >= 74500000 && $number <= 74799999) ||
            ($number >= 75400000 && $number <= 75699999) ||
            ($number >= 75900000 && $number <= 75999999) ||
            ($number >= 76000000 && $number <= 76299999) ||
            ($number >= 76600000 && $number <= 76799999) ||
            ($number >= 77000000 && $number <= 77199999) ||
            ($number >= 77600000 && $number <= 77799999) ||
            ($number >= 77800000 && $number <= 77899999);

        $isOrangeRange =
            ($number >= 72000000 && $number <= 72999999) ||
            ($number >= 74300000 && $number <= 74499999) ||
            ($number >= 74800000 && $number <= 74899999) ||
            ($number >= 75000000 && $number <= 75399999) ||
            ($number >= 75700000 && $number <= 75799999) ||
            ($number >= 76300000 && $number <= 76599999) ||
            ($number >= 76900000 && $number <= 76999999) ||
            ($number >= 77400000 && $number <= 77599999) ||
            ($number >= 77900000 && $number <= 77999999) ||
            ($number >= 77300000 && $number <= 77399999);

        $isBtcRange =
            ($number >= 73000000 && $number <= 73999999) ||
            ($number >= 74900000 && $number <= 74999999) ||
            ($number >= 75800000 && $number <= 75899999) ||
            ($number >= 76800000 && $number <= 76899999) ||
            ($number >= 77200000 && $number <= 77200999);

        if ($isMascomRange) {
            return "mascom";
        } elseif ($isOrangeRange) {
            return "orange";
        } elseif ($isBtcRange) {
            return "btc";
        } else {
            return null;
        }
    }
}
