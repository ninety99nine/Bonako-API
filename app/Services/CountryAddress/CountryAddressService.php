<?php

namespace App\Services\CountryAddress;

class CountryAddressService
{
    /**
     *  Get the country address options.
     *
     * @return array<stdClass>
     */
    public static function getCountryAddressOptions()
    {
        return [
            'AF' => [ // Afghanistan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AL' => [ // Albania
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'DZ' => [ // Algeria
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AS' => [ // American Samoa
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AD' => [ // Andorra
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AO' => [ // Angola
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AI' => [ // Anguilla
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AQ' => [ // Antarctica
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'AG' => [ // Antigua and Barbuda
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AR' => [ // Argentina
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AM' => [ // Armenia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AW' => [ // Aruba
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AU' => [ // Australia
                'postal_code' => ['required' => true, 'label' => 'Postcode'],
                'state' => ['required' => true, 'label' => 'State/Territory'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AT' => [ // Austria
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AZ' => [ // Azerbaijan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BS' => [ // Bahamas
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BH' => [ // Bahrain
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BD' => [ // Bangladesh
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BB' => [ // Barbados
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BY' => [ // Belarus
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BE' => [ // Belgium
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BZ' => [ // Belize
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BJ' => [ // Benin
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BM' => [ // Bermuda
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BT' => [ // Bhutan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BO' => [ // Bolivia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Department'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BA' => [ // Bosnia and Herzegovina
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BW' => [ // Botswana
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BV' => [ // Bouvet Island
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'BR' => [ // Brazil
                'postal_code' => ['required' => true, 'label' => 'CEP'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IO' => [ // British Indian Ocean Territory
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'BN' => [ // Brunei
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BG' => [ // Bulgaria
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BF' => [ // Burkina Faso
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BI' => [ // Burundi
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KH' => [ // Cambodia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CM' => [ // Cameroon
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CA' => [ // Canada
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CV' => [ // Cape Verde
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KY' => [ // Cayman Islands
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CF' => [ // Central African Republic
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TD' => [ // Chad
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CL' => [ // Chile
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CN' => [ // China
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CX' => [ // Christmas Island
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CC' => [ // Cocos (Keeling) Islands
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CO' => [ // Colombia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Department'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KM' => [ // Comoros
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CG' => [ // Congo
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CD' => [ // Democratic Republic of the Congo
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CK' => [ // Cook Islands
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CR' => [ // Costa Rica
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CI' => [ // Côte d'Ivoire
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'HR' => [ // Croatia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CU' => [ // Cuba
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CY' => [ // Cyprus
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CZ' => [ // Czech Republic
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'DK' => [ // Denmark
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'DJ' => [ // Djibouti
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'DM' => [ // Dominica
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'DO' => [ // Dominican Republic
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'EC' => [ // Ecuador
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'EG' => [ // Egypt
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Governorate'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SV' => [ // El Salvador
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GQ' => [ // Equatorial Guinea
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'ER' => [ // Eritrea
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'EE' => [ // Estonia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'ET' => [ // Ethiopia
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'FK' => [ // Falkland Islands
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'FO' => [ // Faroe Islands
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'FJ' => [ // Fiji
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'FI' => [ // Finland
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'FR' => [ // France
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GF' => [ // French Guiana
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PF' => [ // French Polynesia
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TF' => [ // French Southern Territories
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'GA' => [ // Gabon
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GM' => [ // Gambia
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GE' => [ // Georgia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'DE' => [ // Germany
                'postal_code' => ['required' => true, 'label' => 'Postleitzahl'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GH' => [ // Ghana
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GI' => [ // Gibraltar
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GR' => [ // Greece
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GL' => [ // Greenland
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GD' => [ // Grenada
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GP' => [ // Guadeloupe
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GU' => [ // Guam
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GT' => [ // Guatemala
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GN' => [ // Guinea
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GW' => [ // Guinea-Bissau
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GY' => [ // Guyana
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'HT' => [ // Haiti
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'HM' => [ // Heard Island and McDonald Islands
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'VA' => [ // Vatican City
                'postal_code' => ['required' => true, 'label' => 'CAP'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'HN' => [ // Honduras
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => true, 'label' => 'Department'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'HK' => [ // Hong Kong
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'HU' => [ // Hungary
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IS' => [ // Iceland
                'postal_code' => ['required' => true, 'label' => 'Postnumer'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IN' => [ // India
                'postal_code' => ['required' => true, 'label' => 'PIN Code'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'ID' => [ // Indonesia
                'postal_code' => ['required' => true, 'label' => 'Kode Pos'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IR' => [ // Iran
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IQ' => [ // Iraq
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Governorate'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IE' => [ // Ireland
                'postal_code' => ['required' => true, 'label' => 'Eircode'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IL' => [ // Israel
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IT' => [ // Italy
                'postal_code' => ['required' => true, 'label' => 'CAP'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'JM' => [ // Jamaica
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Parish'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'JP' => [ // Japan
                'postal_code' => ['required' => true, 'label' => '郵便番号 (Yubin Bango)'],
                'state' => ['required' => true, 'label' => 'Prefecture'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'JO' => [ // Jordan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Governorate'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KZ' => [ // Kazakhstan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KE' => [ // Kenya
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KI' => [ // Kiribati
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KP' => [ // North Korea
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KR' => [ // South Korea
                'postal_code' => ['required' => true, 'label' => '우편번호 (Ujeon Beonho)'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KW' => [ // Kuwait
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KG' => [ // Kyrgyzstan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LA' => [ // Laos
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LV' => [ // Latvia
                'postal_code' => ['required' => true, 'label' => 'Pasta indekss'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LB' => [ // Lebanon
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LS' => [ // Lesotho
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LR' => [ // Liberia
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LY' => [ // Libya
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'District'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LI' => [ // Liechtenstein
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LT' => [ // Lithuania
                'postal_code' => ['required' => true, 'label' => 'Pašto Kodas'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LU' => [ // Luxembourg
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MO' => [ // Macau
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MK' => [ // North Macedonia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MG' => [ // Madagascar
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MW' => [ // Malawi
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MY' => [ // Malaysia
                'postal_code' => ['required' => true, 'label' => 'Poskod'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MV' => [ // Maldives
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'ML' => [ // Mali
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MT' => [ // Malta
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MH' => [ // Marshall Islands
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MQ' => [ // Martinique
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MR' => [ // Mauritania
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MU' => [ // Mauritius
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'YT' => [ // Mayotte
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MX' => [ // Mexico
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'FM' => [ // Micronesia
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MD' => [ // Moldova
                'postal_code' => ['required' => true, 'label' => 'Cod poștal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MC' => [ // Monaco
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MN' => [ // Mongolia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MS' => [ // Montserrat
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MA' => [ // Morocco
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MZ' => [ // Mozambique
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MM' => [ // Myanmar
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NA' => [ // Namibia
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NR' => [ // Nauru
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NP' => [ // Nepal
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NL' => [ // Netherlands
                'postal_code' => ['required' => true, 'label' => 'Postcode'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AN' => [ // Netherlands Antilles
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NC' => [ // New Caledonia
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NZ' => [ // New Zealand
                'postal_code' => ['required' => true, 'label' => 'Postcode'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NI' => [ // Nicaragua
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NE' => [ // Niger
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NG' => [ // Nigeria
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NU' => [ // Niue
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NF' => [ // Norfolk Island
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MP' => [ // Northern Mariana Islands
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'NO' => [ // Norway
                'postal_code' => ['required' => true, 'label' => 'Postnummer'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'OM' => [ // Oman
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PK' => [ // Pakistan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PW' => [ // Palau
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PS' => [ // Palestine
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Governorate'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PA' => [ // Panama
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PG' => [ // Papua New Guinea
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PY' => [ // Paraguay
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PE' => [ // Peru
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PH' => [ // Philippines
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PN' => [ // Pitcairn
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PL' => [ // Poland
                'postal_code' => ['required' => true, 'label' => 'Kod Pocztowy'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PT' => [ // Portugal
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PR' => [ // Puerto Rico
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'QA' => [ // Qatar
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'RE' => [ // Réunion
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'RO' => [ // Romania
                'postal_code' => ['required' => true, 'label' => 'Cod Poștal'],
                'state' => ['required' => true, 'label' => 'County'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'RU' => [ // Russia
                'postal_code' => ['required' => true, 'label' => 'Почтовый индекс'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'RW' => [ // Rwanda
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SH' => [ // Saint Helena
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'KN' => [ // Saint Kitts and Nevis
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'LC' => [ // Saint Lucia
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'PM' => [ // Saint Pierre and Miquelon
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'VC' => [ // Saint Vincent and the Grenadines
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'WS' => [ // Samoa
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SM' => [ // San Marino
                'postal_code' => ['required' => true, 'label' => 'CAP'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'ST' => [ // Sao Tome and Principe
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SA' => [ // Saudi Arabia
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SN' => [ // Senegal
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CS' => [ // Serbia (historical)
                'postal_code' => ['required' => true, 'label' => 'Poštanski Kod'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SC' => [ // Seychelles
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SL' => [ // Sierra Leone
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SG' => [ // Singapore
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SK' => [ // Slovakia
                'postal_code' => ['required' => true, 'label' => 'PSČ'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SI' => [ // Slovenia
                'postal_code' => ['required' => true, 'label' => 'Poštna Številka'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SB' => [ // Solomon Islands
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SO' => [ // Somalia
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'ZA' => [ // South Africa
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GS' => [ // South Georgia and the South Sandwich Islands
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'ES' => [ // Spain
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => true, 'label' => 'Provincia'],
                'city' => ['required' => true, 'label' => 'Ciudad'],
            ],
            'LK' => [ // Sri Lanka
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SD' => [ // Sudan
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SR' => [ // Suriname
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SJ' => [ // Svalbard and Jan Mayen
                'postal_code' => ['required' => true, 'label' => 'Postnummer'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SZ' => [ // Eswatini (Swaziland)
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SE' => [ // Sweden
                'postal_code' => ['required' => true, 'label' => 'Postnummer'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CH' => [ // Switzerland
                'postal_code' => ['required' => true, 'label' => 'Postleitzahl'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SY' => [ // Syria
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Governorate'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TW' => [ // Taiwan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'City'],
                'city' => ['required' => true, 'label' => 'District'],
            ],
            'TJ' => [ // Tajikistan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TZ' => [ // Tanzania
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TH' => [ // Thailand
                'postal_code' => ['required' => true, 'label' => 'รหัสไปรษณีย์'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TL' => [ // Timor-Leste
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TG' => [ // Togo
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TK' => [ // Tokelau
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TO' => [ // Tonga
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TT' => [ // Trinidad and Tobago
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TN' => [ // Tunisia
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => true, 'label' => 'Governorate'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TR' => [ // Turkey
                'postal_code' => ['required' => true, 'label' => 'Posta Kodu'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TM' => [ // Turkmenistan
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TC' => [ // Turks and Caicos Islands
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'TV' => [ // Tuvalu
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'UG' => [ // Uganda
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'District'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'UA' => [ // Ukraine
                'postal_code' => ['required' => true, 'label' => 'Поштовий індекс'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AE' => [ // United Arab Emirates
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Emirate'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GB' => [ // United Kingdom
                'postal_code' => ['required' => true, 'label' => 'Postcode'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'US' => [ // United States
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'UM' => [ // United States Minor Outlying Islands
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'UY' => [ // Uruguay
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => true, 'label' => 'Department'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'UZ' => [ // Uzbekistan
                'postal_code' => ['required' => true, 'label' => 'Pochta Indeksi'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'VU' => [ // Vanuatu
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'VE' => [ // Venezuela
                'postal_code' => ['required' => true, 'label' => 'Código Postal'],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'VN' => [ // Vietnam
                'postal_code' => ['required' => true, 'label' => 'Mã Bưu Chính'],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'VG' => [ // British Virgin Islands
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'VI' => [ // U.S. Virgin Islands
                'postal_code' => ['required' => true, 'label' => 'ZIP Code'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'WF' => [ // Wallis and Futuna
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'EH' => [ // Western Sahara
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'YE' => [ // Yemen
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Governorate'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'ZM' => [ // Zambia
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'ZW' => [ // Zimbabwe
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'Province'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'RS' => [ // Serbia
                'postal_code' => ['required' => true, 'label' => 'Poštanski Kod'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AP' => [ // Asia Pacific Region
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => false, 'label' => null],
            ],
            'ME' => [ // Montenegro
                'postal_code' => ['required' => true, 'label' => 'Poštanski Kod'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'AX' => [ // Åland Islands
                'postal_code' => ['required' => true, 'label' => 'Postnummer'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BQ' => [ // Bonaire, Sint Eustatius and Saba
                'postal_code' => ['required' => true, 'label' => 'Postcode'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'CW' => [ // Curaçao
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'GG' => [ // Guernsey
                'postal_code' => ['required' => true, 'label' => 'Postcode'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'IM' => [ // Isle of Man
                'postal_code' => ['required' => true, 'label' => 'Postcode'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'JE' => [ // Jersey
                'postal_code' => ['required' => true, 'label' => 'Postcode'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'XK' => [ // Kosovo
                'postal_code' => ['required' => true, 'label' => 'Postal Code'],
                'state' => ['required' => true, 'label' => 'Region'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'BL' => [ // Saint Barthélemy
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'MF' => [ // Saint Martin (French part)
                'postal_code' => ['required' => true, 'label' => 'Code Postal'],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SX' => [ // Sint Maarten (Dutch part)
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => false, 'label' => null],
                'city' => ['required' => true, 'label' => 'City'],
            ],
            'SS' => [ // South Sudan
                'postal_code' => ['required' => false, 'label' => null],
                'state' => ['required' => true, 'label' => 'State'],
                'city' => ['required' => true, 'label' => 'City'],
            ],
        ];
    }
}
