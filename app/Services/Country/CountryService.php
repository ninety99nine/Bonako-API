<?php

namespace App\Services\Country;

use stdClass;

class CountryService
{
    /**
     *  Get the countries
     *
     * @return array<stdClass>
     */
    public static function getCountries()
    {
        $countries = [

        	// Records 01-99 (Afghanistan to India)

            ['iso' => 'AF', 'name' => 'Afghanistan', 'iso3' => 'AFG', 'numeric_code' => '4', 'dialing_code' => '93'],
            ['iso' => 'AL', 'name' => 'Albania', 'iso3' => 'ALB', 'numeric_code' => '8', 'dialing_code' => '355'],
            ['iso' => 'DZ', 'name' => 'Algeria', 'iso3' => 'DZA', 'numeric_code' => '12', 'dialing_code' => '213'],
            ['iso' => 'AS', 'name' => 'American Samoa', 'iso3' => 'ASM', 'numeric_code' => '16', 'dialing_code' => '1684'],
            ['iso' => 'AD', 'name' => 'Andorra', 'iso3' => 'AND', 'numeric_code' => '20', 'dialing_code' => '376'],
            ['iso' => 'AO', 'name' => 'Angola', 'iso3' => 'AGO', 'numeric_code' => '24', 'dialing_code' => '244'],
            ['iso' => 'AI', 'name' => 'Anguilla', 'iso3' => 'AIA', 'numeric_code' => '660', 'dialing_code' => '1264'],
            ['iso' => 'AQ', 'name' => 'Antarctica', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '0'],
            ['iso' => 'AG', 'name' => 'Antigua and Barbuda', 'iso3' => 'ATG', 'numeric_code' => '28', 'dialing_code' => '1268'],
            ['iso' => 'AR', 'name' => 'Argentina', 'iso3' => 'ARG', 'numeric_code' => '32', 'dialing_code' => '54'],
            ['iso' => 'AM', 'name' => 'Armenia', 'iso3' => 'ARM', 'numeric_code' => '51', 'dialing_code' => '374'],
            ['iso' => 'AW', 'name' => 'Aruba', 'iso3' => 'ABW', 'numeric_code' => '533', 'dialing_code' => '297'],
            ['iso' => 'AU', 'name' => 'Australia', 'iso3' => 'AUS', 'numeric_code' => '36', 'dialing_code' => '61'],
            ['iso' => 'AT', 'name' => 'Austria', 'iso3' => 'AUT', 'numeric_code' => '40', 'dialing_code' => '43'],
            ['iso' => 'AZ', 'name' => 'Azerbaijan', 'iso3' => 'AZE', 'numeric_code' => '31', 'dialing_code' => '994'],
            ['iso' => 'BS', 'name' => 'Bahamas', 'iso3' => 'BHS', 'numeric_code' => '44', 'dialing_code' => '1242'],
            ['iso' => 'BH', 'name' => 'Bahrain', 'iso3' => 'BHR', 'numeric_code' => '48', 'dialing_code' => '973'],
            ['iso' => 'BD', 'name' => 'Bangladesh', 'iso3' => 'BGD', 'numeric_code' => '50', 'dialing_code' => '880'],
            ['iso' => 'BB', 'name' => 'Barbados', 'iso3' => 'BRB', 'numeric_code' => '52', 'dialing_code' => '1246'],
            ['iso' => 'BY', 'name' => 'Belarus', 'iso3' => 'BLR', 'numeric_code' => '112', 'dialing_code' => '375'],
            ['iso' => 'BE', 'name' => 'Belgium', 'iso3' => 'BEL', 'numeric_code' => '56', 'dialing_code' => '32'],
            ['iso' => 'BZ', 'name' => 'Belize', 'iso3' => 'BLZ', 'numeric_code' => '84', 'dialing_code' => '501'],
            ['iso' => 'BJ', 'name' => 'Benin', 'iso3' => 'BEN', 'numeric_code' => '204', 'dialing_code' => '229'],
            ['iso' => 'BM', 'name' => 'Bermuda', 'iso3' => 'BMU', 'numeric_code' => '60', 'dialing_code' => '1441'],
            ['iso' => 'BT', 'name' => 'Bhutan', 'iso3' => 'BTN', 'numeric_code' => '64', 'dialing_code' => '975'],
            ['iso' => 'BO', 'name' => 'Bolivia', 'iso3' => 'BOL', 'numeric_code' => '68', 'dialing_code' => '591'],
            ['iso' => 'BA', 'name' => 'Bosnia and Herzegovina', 'iso3' => 'BIH', 'numeric_code' => '70', 'dialing_code' => '387'],
            ['iso' => 'BW', 'name' => 'Botswana', 'iso3' => 'BWA', 'numeric_code' => '72', 'dialing_code' => '267'],
            ['iso' => 'BV', 'name' => 'Bouvet Island', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '0'],
            ['iso' => 'BR', 'name' => 'Brazil', 'iso3' => 'BRA', 'numeric_code' => '76', 'dialing_code' => '55'],
            ['iso' => 'IO', 'name' => 'British Indian Ocean Territory', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '246'],
            ['iso' => 'BN', 'name' => 'Brunei Darussalam', 'iso3' => 'BRN', 'numeric_code' => '96', 'dialing_code' => '673'],
            ['iso' => 'BG', 'name' => 'Bulgaria', 'iso3' => 'BGR', 'numeric_code' => '100', 'dialing_code' => '359'],
            ['iso' => 'BF', 'name' => 'Burkina Faso', 'iso3' => 'BFA', 'numeric_code' => '854', 'dialing_code' => '226'],
            ['iso' => 'BI', 'name' => 'Burundi', 'iso3' => 'BDI', 'numeric_code' => '108', 'dialing_code' => '257'],
            ['iso' => 'KH', 'name' => 'Cambodia', 'iso3' => 'KHM', 'numeric_code' => '116', 'dialing_code' => '855'],
            ['iso' => 'CM', 'name' => 'Cameroon', 'iso3' => 'CMR', 'numeric_code' => '120', 'dialing_code' => '237'],
            ['iso' => 'CA', 'name' => 'Canada', 'iso3' => 'CAN', 'numeric_code' => '124', 'dialing_code' => '1'],
            ['iso' => 'CV', 'name' => 'Cape Verde', 'iso3' => 'CPV', 'numeric_code' => '132', 'dialing_code' => '238'],
            ['iso' => 'KY', 'name' => 'Cayman Islands', 'iso3' => 'CYM', 'numeric_code' => '136', 'dialing_code' => '1345'],
            ['iso' => 'CF', 'name' => 'Central African Republic', 'iso3' => 'CAF', 'numeric_code' => '140', 'dialing_code' => '236'],
            ['iso' => 'TD', 'name' => 'Chad', 'iso3' => 'TCD', 'numeric_code' => '148', 'dialing_code' => '235'],
            ['iso' => 'CL', 'name' => 'Chile', 'iso3' => 'CHL', 'numeric_code' => '152', 'dialing_code' => '56'],
            ['iso' => 'CN', 'name' => 'China', 'iso3' => 'CHN', 'numeric_code' => '156', 'dialing_code' => '86'],
            ['iso' => 'CX', 'name' => 'Christmas Island', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '61'],
            ['iso' => 'CC', 'name' => 'Cocos (Keeling) Islands', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '672'],
            ['iso' => 'CO', 'name' => 'Colombia', 'iso3' => 'COL', 'numeric_code' => '170', 'dialing_code' => '57'],
            ['iso' => 'KM', 'name' => 'Comoros', 'iso3' => 'COM', 'numeric_code' => '174', 'dialing_code' => '269'],
            ['iso' => 'CG', 'name' => 'Congo', 'iso3' => 'COG', 'numeric_code' => '178', 'dialing_code' => '242'],
            ['iso' => 'CD', 'name' => 'Congo, the Democratic Republic of the', 'iso3' => 'COD', 'numeric_code' => '180', 'dialing_code' => '242'],
            ['iso' => 'CK', 'name' => 'Cook Islands', 'iso3' => 'COK', 'numeric_code' => '184', 'dialing_code' => '682'],
            ['iso' => 'CR', 'name' => 'Costa Rica', 'iso3' => 'CRI', 'numeric_code' => '188', 'dialing_code' => '506'],
            ['iso' => 'CI', 'name' => 'Cote D\'Ivoire', 'iso3' => 'CIV', 'numeric_code' => '384', 'dialing_code' => '225'],
            ['iso' => 'HR', 'name' => 'Croatia', 'iso3' => 'HRV', 'numeric_code' => '191', 'dialing_code' => '385'],
            ['iso' => 'CU', 'name' => 'Cuba', 'iso3' => 'CUB', 'numeric_code' => '192', 'dialing_code' => '53'],
            ['iso' => 'CY', 'name' => 'Cyprus', 'iso3' => 'CYP', 'numeric_code' => '196', 'dialing_code' => '357'],
            ['iso' => 'CZ', 'name' => 'Czech Republic', 'iso3' => 'CZE', 'numeric_code' => '203', 'dialing_code' => '420'],
            ['iso' => 'DK', 'name' => 'Denmark', 'iso3' => 'DNK', 'numeric_code' => '208', 'dialing_code' => '45'],
            ['iso' => 'DJ', 'name' => 'Djibouti', 'iso3' => 'DJI', 'numeric_code' => '262', 'dialing_code' => '253'],
            ['iso' => 'DM', 'name' => 'Dominica', 'iso3' => 'DMA', 'numeric_code' => '212', 'dialing_code' => '1767'],
            ['iso' => 'DO', 'name' => 'Dominican Republic', 'iso3' => 'DOM', 'numeric_code' => '214', 'dialing_code' => '1809'],
            ['iso' => 'EC', 'name' => 'Ecuador', 'iso3' => 'ECU', 'numeric_code' => '218', 'dialing_code' => '593'],
            ['iso' => 'EG', 'name' => 'Egypt', 'iso3' => 'EGY', 'numeric_code' => '818', 'dialing_code' => '20'],
            ['iso' => 'SV', 'name' => 'El Salvador', 'iso3' => 'SLV', 'numeric_code' => '222', 'dialing_code' => '503'],
            ['iso' => 'GQ', 'name' => 'Equatorial Guinea', 'iso3' => 'GNQ', 'numeric_code' => '226', 'dialing_code' => '240'],
            ['iso' => 'ER', 'name' => 'Eritrea', 'iso3' => 'ERI', 'numeric_code' => '232', 'dialing_code' => '291'],
            ['iso' => 'EE', 'name' => 'Estonia', 'iso3' => 'EST', 'numeric_code' => '233', 'dialing_code' => '372'],
            ['iso' => 'ET', 'name' => 'Ethiopia', 'iso3' => 'ETH', 'numeric_code' => '231', 'dialing_code' => '251'],
            ['iso' => 'FK', 'name' => 'Falkland Islands (Malvinas)', 'iso3' => 'FLK', 'numeric_code' => '238', 'dialing_code' => '500'],
            ['iso' => 'FO', 'name' => 'Faroe Islands', 'iso3' => 'FRO', 'numeric_code' => '234', 'dialing_code' => '298'],
            ['iso' => 'FJ', 'name' => 'Fiji', 'iso3' => 'FJI', 'numeric_code' => '242', 'dialing_code' => '679'],
            ['iso' => 'FI', 'name' => 'Finland', 'iso3' => 'FIN', 'numeric_code' => '246', 'dialing_code' => '358'],
            ['iso' => 'FR', 'name' => 'France', 'iso3' => 'FRA', 'numeric_code' => '250', 'dialing_code' => '33'],
            ['iso' => 'GF', 'name' => 'French Guiana', 'iso3' => 'GUF', 'numeric_code' => '254', 'dialing_code' => '594'],
            ['iso' => 'PF', 'name' => 'French Polynesia', 'iso3' => 'PYF', 'numeric_code' => '258', 'dialing_code' => '689'],
            ['iso' => 'TF', 'name' => 'French Southern Territories', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '0'],
            ['iso' => 'GA', 'name' => 'Gabon', 'iso3' => 'GAB', 'numeric_code' => '266', 'dialing_code' => '241'],
            ['iso' => 'GM', 'name' => 'Gambia', 'iso3' => 'GMB', 'numeric_code' => '270', 'dialing_code' => '220'],
            ['iso' => 'GE', 'name' => 'Georgia', 'iso3' => 'GEO', 'numeric_code' => '268', 'dialing_code' => '995'],
            ['iso' => 'DE', 'name' => 'Germany', 'iso3' => 'DEU', 'numeric_code' => '276', 'dialing_code' => '49'],
            ['iso' => 'GH', 'name' => 'Ghana', 'iso3' => 'GHA', 'numeric_code' => '288', 'dialing_code' => '233'],
            ['iso' => 'GI', 'name' => 'Gibraltar', 'iso3' => 'GIB', 'numeric_code' => '292', 'dialing_code' => '350'],
            ['iso' => 'GR', 'name' => 'Greece', 'iso3' => 'GRC', 'numeric_code' => '300', 'dialing_code' => '30'],
            ['iso' => 'GL', 'name' => 'Greenland', 'iso3' => 'GRL', 'numeric_code' => '304', 'dialing_code' => '299'],
            ['iso' => 'GD', 'name' => 'Grenada', 'iso3' => 'GRD', 'numeric_code' => '308', 'dialing_code' => '1473'],
            ['iso' => 'GP', 'name' => 'Guadeloupe', 'iso3' => 'GLP', 'numeric_code' => '312', 'dialing_code' => '590'],
            ['iso' => 'GU', 'name' => 'Guam', 'iso3' => 'GUM', 'numeric_code' => '316', 'dialing_code' => '1671'],
            ['iso' => 'GT', 'name' => 'Guatemala', 'iso3' => 'GTM', 'numeric_code' => '320', 'dialing_code' => '502'],
            ['iso' => 'GN', 'name' => 'Guinea', 'iso3' => 'GIN', 'numeric_code' => '324', 'dialing_code' => '224'],
            ['iso' => 'GW', 'name' => 'Guinea-Bissau', 'iso3' => 'GNB', 'numeric_code' => '624', 'dialing_code' => '245'],
            ['iso' => 'GY', 'name' => 'Guyana', 'iso3' => 'GUY', 'numeric_code' => '328', 'dialing_code' => '592'],
            ['iso' => 'HT', 'name' => 'Haiti', 'iso3' => 'HTI', 'numeric_code' => '332', 'dialing_code' => '509'],
            ['iso' => 'HM', 'name' => 'Heard Island and Mcdonald Islands', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '0'],
            ['iso' => 'VA', 'name' => 'Holy See (Vatican City State)', 'iso3' => 'VAT', 'numeric_code' => '336', 'dialing_code' => '39'],
            ['iso' => 'HN', 'name' => 'Honduras', 'iso3' => 'HND', 'numeric_code' => '340', 'dialing_code' => '504'],
            ['iso' => 'HK', 'name' => 'Hong Kong', 'iso3' => 'HKG', 'numeric_code' => '344', 'dialing_code' => '852'],
            ['iso' => 'HU', 'name' => 'Hungary', 'iso3' => 'HUN', 'numeric_code' => '348', 'dialing_code' => '36'],
            ['iso' => 'IS', 'name' => 'Iceland', 'iso3' => 'ISL', 'numeric_code' => '352', 'dialing_code' => '354'],
            ['iso' => 'IN', 'name' => 'India', 'iso3' => 'IND', 'numeric_code' => '356', 'dialing_code' => '91'],

            // Records 100-199 (Indonesia to Spain)

            ['iso' => 'ID', 'name' => 'Indonesia', 'iso3' => 'IDN', 'numeric_code' => '360', 'dialing_code' => '62'],
            ['iso' => 'IR', 'name' => 'Iran, Islamic Republic of', 'iso3' => 'IRN', 'numeric_code' => '364', 'dialing_code' => '98'],
            ['iso' => 'IQ', 'name' => 'Iraq', 'iso3' => 'IRQ', 'numeric_code' => '368', 'dialing_code' => '964'],
            ['iso' => 'IE', 'name' => 'Ireland', 'iso3' => 'IRL', 'numeric_code' => '372', 'dialing_code' => '353'],
            ['iso' => 'IL', 'name' => 'Israel', 'iso3' => 'ISR', 'numeric_code' => '376', 'dialing_code' => '972'],
            ['iso' => 'IT', 'name' => 'Italy', 'iso3' => 'ITA', 'numeric_code' => '380', 'dialing_code' => '39'],
            ['iso' => 'JM', 'name' => 'Jamaica', 'iso3' => 'JAM', 'numeric_code' => '388', 'dialing_code' => '1876'],
            ['iso' => 'JP', 'name' => 'Japan', 'iso3' => 'JPN', 'numeric_code' => '392', 'dialing_code' => '81'],
            ['iso' => 'JO', 'name' => 'Jordan', 'iso3' => 'JOR', 'numeric_code' => '400', 'dialing_code' => '962'],
            ['iso' => 'KZ', 'name' => 'Kazakhstan', 'iso3' => 'KAZ', 'numeric_code' => '398', 'dialing_code' => '7'],
            ['iso' => 'KE', 'name' => 'Kenya', 'iso3' => 'KEN', 'numeric_code' => '404', 'dialing_code' => '254'],
            ['iso' => 'KI', 'name' => 'Kiribati', 'iso3' => 'KIR', 'numeric_code' => '296', 'dialing_code' => '686'],
            ['iso' => 'KP', 'name' => 'Korea, Democratic People\'s Republic of', 'iso3' => 'PRK', 'numeric_code' => '408', 'dialing_code' => '850'],
            ['iso' => 'KR', 'name' => 'Korea, Republic of', 'iso3' => 'KOR', 'numeric_code' => '410', 'dialing_code' => '82'],
            ['iso' => 'KW', 'name' => 'Kuwait', 'iso3' => 'KWT', 'numeric_code' => '414', 'dialing_code' => '965'],
            ['iso' => 'KG', 'name' => 'Kyrgyzstan', 'iso3' => 'KGZ', 'numeric_code' => '417', 'dialing_code' => '996'],
            ['iso' => 'LA', 'name' => 'Lao People\'s Democratic Republic', 'iso3' => 'LAO', 'numeric_code' => '418', 'dialing_code' => '856'],
            ['iso' => 'LV', 'name' => 'Latvia', 'iso3' => 'LVA', 'numeric_code' => '428', 'dialing_code' => '371'],
            ['iso' => 'LB', 'name' => 'Lebanon', 'iso3' => 'LBN', 'numeric_code' => '422', 'dialing_code' => '961'],
            ['iso' => 'LS', 'name' => 'Lesotho', 'iso3' => 'LSO', 'numeric_code' => '426', 'dialing_code' => '266'],
            ['iso' => 'LR', 'name' => 'Liberia', 'iso3' => 'LBR', 'numeric_code' => '430', 'dialing_code' => '231'],
            ['iso' => 'LY', 'name' => 'Libyan Arab Jamahiriya', 'iso3' => 'LBY', 'numeric_code' => '434', 'dialing_code' => '218'],
            ['iso' => 'LI', 'name' => 'Liechtenstein', 'iso3' => 'LIE', 'numeric_code' => '438', 'dialing_code' => '423'],
            ['iso' => 'LT', 'name' => 'Lithuania', 'iso3' => 'LTU', 'numeric_code' => '440', 'dialing_code' => '370'],
            ['iso' => 'LU', 'name' => 'Luxembourg', 'iso3' => 'LUX', 'numeric_code' => '442', 'dialing_code' => '352'],
            ['iso' => 'MO', 'name' => 'Macao', 'iso3' => 'MAC', 'numeric_code' => '446', 'dialing_code' => '853'],
            ['iso' => 'MK', 'name' => 'Macedonia, the Former Yugoslav Republic of', 'iso3' => 'MKD', 'numeric_code' => '807', 'dialing_code' => '389'],
            ['iso' => 'MG', 'name' => 'Madagascar', 'iso3' => 'MDG', 'numeric_code' => '450', 'dialing_code' => '261'],
            ['iso' => 'MW', 'name' => 'Malawi', 'iso3' => 'MWI', 'numeric_code' => '454', 'dialing_code' => '265'],
            ['iso' => 'MY', 'name' => 'Malaysia', 'iso3' => 'MYS', 'numeric_code' => '458', 'dialing_code' => '60'],
            ['iso' => 'MV', 'name' => 'Maldives', 'iso3' => 'MDV', 'numeric_code' => '462', 'dialing_code' => '960'],
            ['iso' => 'ML', 'name' => 'Mali', 'iso3' => 'MLI', 'numeric_code' => '466', 'dialing_code' => '223'],
            ['iso' => 'MT', 'name' => 'Malta', 'iso3' => 'MLT', 'numeric_code' => '470', 'dialing_code' => '356'],
            ['iso' => 'MH', 'name' => 'Marshall Islands', 'iso3' => 'MHL', 'numeric_code' => '584', 'dialing_code' => '692'],
            ['iso' => 'MQ', 'name' => 'Martinique', 'iso3' => 'MTQ', 'numeric_code' => '474', 'dialing_code' => '596'],
            ['iso' => 'MR', 'name' => 'Mauritania', 'iso3' => 'MRT', 'numeric_code' => '478', 'dialing_code' => '222'],
            ['iso' => 'MU', 'name' => 'Mauritius', 'iso3' => 'MUS', 'numeric_code' => '480', 'dialing_code' => '230'],
            ['iso' => 'YT', 'name' => 'Mayotte', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '269'],
            ['iso' => 'MX', 'name' => 'Mexico', 'iso3' => 'MEX', 'numeric_code' => '484', 'dialing_code' => '52'],
            ['iso' => 'FM', 'name' => 'Micronesia, Federated States of', 'iso3' => 'FSM', 'numeric_code' => '583', 'dialing_code' => '691'],
            ['iso' => 'MD', 'name' => 'Moldova, Republic of', 'iso3' => 'MDA', 'numeric_code' => '498', 'dialing_code' => '373'],
            ['iso' => 'MC', 'name' => 'Monaco', 'iso3' => 'MCO', 'numeric_code' => '492', 'dialing_code' => '377'],
            ['iso' => 'MN', 'name' => 'Mongolia', 'iso3' => 'MNG', 'numeric_code' => '496', 'dialing_code' => '976'],
            ['iso' => 'MS', 'name' => 'Montserrat', 'iso3' => 'MSR', 'numeric_code' => '500', 'dialing_code' => '1664'],
            ['iso' => 'MA', 'name' => 'Morocco', 'iso3' => 'MAR', 'numeric_code' => '504', 'dialing_code' => '212'],
            ['iso' => 'MZ', 'name' => 'Mozambique', 'iso3' => 'MOZ', 'numeric_code' => '508', 'dialing_code' => '258'],
            ['iso' => 'MM', 'name' => 'Myanmar', 'iso3' => 'MMR', 'numeric_code' => '104', 'dialing_code' => '95'],
            ['iso' => 'NA', 'name' => 'Namibia', 'iso3' => 'NAM', 'numeric_code' => '516', 'dialing_code' => '264'],
            ['iso' => 'NR', 'name' => 'Nauru', 'iso3' => 'NRU', 'numeric_code' => '520', 'dialing_code' => '674'],
            ['iso' => 'NP', 'name' => 'Nepal', 'iso3' => 'NPL', 'numeric_code' => '524', 'dialing_code' => '977'],
            ['iso' => 'NL', 'name' => 'Netherlands', 'iso3' => 'NLD', 'numeric_code' => '528', 'dialing_code' => '31'],
            ['iso' => 'AN', 'name' => 'Netherlands Antilles', 'iso3' => 'ANT', 'numeric_code' => '530', 'dialing_code' => '599'],
            ['iso' => 'NC', 'name' => 'New Caledonia', 'iso3' => 'NCL', 'numeric_code' => '540', 'dialing_code' => '687'],
            ['iso' => 'NZ', 'name' => 'New Zealand', 'iso3' => 'NZL', 'numeric_code' => '554', 'dialing_code' => '64'],
            ['iso' => 'NI', 'name' => 'Nicaragua', 'iso3' => 'NIC', 'numeric_code' => '558', 'dialing_code' => '505'],
            ['iso' => 'NE', 'name' => 'Niger', 'iso3' => 'NER', 'numeric_code' => '562', 'dialing_code' => '227'],
            ['iso' => 'NG', 'name' => 'Nigeria', 'iso3' => 'NGA', 'numeric_code' => '566', 'dialing_code' => '234'],
            ['iso' => 'NU', 'name' => 'Niue', 'iso3' => 'NIU', 'numeric_code' => '570', 'dialing_code' => '683'],
            ['iso' => 'NF', 'name' => 'Norfolk Island', 'iso3' => 'NFK', 'numeric_code' => '574', 'dialing_code' => '672'],
            ['iso' => 'MP', 'name' => 'Northern Mariana Islands', 'iso3' => 'MNP', 'numeric_code' => '580', 'dialing_code' => '1670'],
            ['iso' => 'NO', 'name' => 'Norway', 'iso3' => 'NOR', 'numeric_code' => '578', 'dialing_code' => '47'],
            ['iso' => 'OM', 'name' => 'Oman', 'iso3' => 'OMN', 'numeric_code' => '512', 'dialing_code' => '968'],
            ['iso' => 'PK', 'name' => 'Pakistan', 'iso3' => 'PAK', 'numeric_code' => '586', 'dialing_code' => '92'],
            ['iso' => 'PW', 'name' => 'Palau', 'iso3' => 'PLW', 'numeric_code' => '585', 'dialing_code' => '680'],
            ['iso' => 'PS', 'name' => 'Palestinian Territory, Occupied', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '970'],
            ['iso' => 'PA', 'name' => 'Panama', 'iso3' => 'PAN', 'numeric_code' => '591', 'dialing_code' => '507'],
            ['iso' => 'PG', 'name' => 'Papua New Guinea', 'iso3' => 'PNG', 'numeric_code' => '598', 'dialing_code' => '675'],
            ['iso' => 'PY', 'name' => 'Paraguay', 'iso3' => 'PRY', 'numeric_code' => '600', 'dialing_code' => '595'],
            ['iso' => 'PE', 'name' => 'Peru', 'iso3' => 'PER', 'numeric_code' => '604', 'dialing_code' => '51'],
            ['iso' => 'PH', 'name' => 'Philippines', 'iso3' => 'PHL', 'numeric_code' => '608', 'dialing_code' => '63'],
            ['iso' => 'PN', 'name' => 'Pitcairn', 'iso3' => 'PCN', 'numeric_code' => '612', 'dialing_code' => '0'],
            ['iso' => 'PL', 'name' => 'Poland', 'iso3' => 'POL', 'numeric_code' => '616', 'dialing_code' => '48'],
            ['iso' => 'PT', 'name' => 'Portugal', 'iso3' => 'PRT', 'numeric_code' => '620', 'dialing_code' => '351'],
            ['iso' => 'PR', 'name' => 'Puerto Rico', 'iso3' => 'PRI', 'numeric_code' => '630', 'dialing_code' => '1787'],
            ['iso' => 'QA', 'name' => 'Qatar', 'iso3' => 'QAT', 'numeric_code' => '634', 'dialing_code' => '974'],
            ['iso' => 'RE', 'name' => 'Reunion', 'iso3' => 'REU', 'numeric_code' => '638', 'dialing_code' => '262'],
            ['iso' => 'RO', 'name' => 'Romania', 'iso3' => 'ROM', 'numeric_code' => '642', 'dialing_code' => '40'],
            ['iso' => 'RU', 'name' => 'Russian Federation', 'iso3' => 'RUS', 'numeric_code' => '643', 'dialing_code' => '70'],
            ['iso' => 'RW', 'name' => 'Rwanda', 'iso3' => 'RWA', 'numeric_code' => '646', 'dialing_code' => '250'],
            ['iso' => 'SH', 'name' => 'Saint Helena', 'iso3' => 'SHN', 'numeric_code' => '654', 'dialing_code' => '290'],
            ['iso' => 'KN', 'name' => 'Saint Kitts and Nevis', 'iso3' => 'KNA', 'numeric_code' => '659', 'dialing_code' => '1869'],
            ['iso' => 'LC', 'name' => 'Saint Lucia', 'iso3' => 'LCA', 'numeric_code' => '662', 'dialing_code' => '1758'],
            ['iso' => 'PM', 'name' => 'Saint Pierre and Miquelon', 'iso3' => 'SPM', 'numeric_code' => '666', 'dialing_code' => '508'],
            ['iso' => 'VC', 'name' => 'Saint Vincent and the Grenadines', 'iso3' => 'VCT', 'numeric_code' => '670', 'dialing_code' => '1784'],
            ['iso' => 'WS', 'name' => 'Samoa', 'iso3' => 'WSM', 'numeric_code' => '882', 'dialing_code' => '684'],
            ['iso' => 'SM', 'name' => 'San Marino', 'iso3' => 'SMR', 'numeric_code' => '674', 'dialing_code' => '378'],
            ['iso' => 'ST', 'name' => 'Sao Tome and Principe', 'iso3' => 'STP', 'numeric_code' => '678', 'dialing_code' => '239'],
            ['iso' => 'SA', 'name' => 'Saudi Arabia', 'iso3' => 'SAU', 'numeric_code' => '682', 'dialing_code' => '966'],
            ['iso' => 'SN', 'name' => 'Senegal', 'iso3' => 'SEN', 'numeric_code' => '686', 'dialing_code' => '221'],
            ['iso' => 'CS', 'name' => 'Serbia and Montenegro', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '381'],
            ['iso' => 'SC', 'name' => 'Seychelles', 'iso3' => 'SYC', 'numeric_code' => '690', 'dialing_code' => '248'],
            ['iso' => 'SL', 'name' => 'Sierra Leone', 'iso3' => 'SLE', 'numeric_code' => '694', 'dialing_code' => '232'],
            ['iso' => 'SG', 'name' => 'Singapore', 'iso3' => 'SGP', 'numeric_code' => '702', 'dialing_code' => '65'],
            ['iso' => 'SK', 'name' => 'Slovakia', 'iso3' => 'SVK', 'numeric_code' => '703', 'dialing_code' => '421'],
            ['iso' => 'SI', 'name' => 'Slovenia', 'iso3' => 'SVN', 'numeric_code' => '705', 'dialing_code' => '386'],
            ['iso' => 'SB', 'name' => 'Solomon Islands', 'iso3' => 'SLB', 'numeric_code' => '90', 'dialing_code' => '677'],
            ['iso' => 'SO', 'name' => 'Somalia', 'iso3' => 'SOM', 'numeric_code' => '706', 'dialing_code' => '252'],
            ['iso' => 'ZA', 'name' => 'South Africa', 'iso3' => 'ZAF', 'numeric_code' => '710', 'dialing_code' => '27'],
            ['iso' => 'GS', 'name' => 'South Georgia and the South Sandwich Islands', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '0'],
            ['iso' => 'ES', 'name' => 'Spain', 'iso3' => 'ESP', 'numeric_code' => '724', 'dialing_code' => '34'],

            // Records 200-253 (Sri Lanka to South Sudan)

            ['iso' => 'LK', 'name' => 'Sri Lanka', 'iso3' => 'LKA', 'numeric_code' => '144', 'dialing_code' => '94'],
            ['iso' => 'SD', 'name' => 'Sudan', 'iso3' => 'SDN', 'numeric_code' => '736', 'dialing_code' => '249'],
            ['iso' => 'SR', 'name' => 'Suriname', 'iso3' => 'SUR', 'numeric_code' => '740', 'dialing_code' => '597'],
            ['iso' => 'SJ', 'name' => 'Svalbard and Jan Mayen', 'iso3' => 'SJM', 'numeric_code' => '744', 'dialing_code' => '47'],
            ['iso' => 'SZ', 'name' => 'Swaziland', 'iso3' => 'SWZ', 'numeric_code' => '748', 'dialing_code' => '268'],
            ['iso' => 'SE', 'name' => 'Sweden', 'iso3' => 'SWE', 'numeric_code' => '752', 'dialing_code' => '46'],
            ['iso' => 'CH', 'name' => 'Switzerland', 'iso3' => 'CHE', 'numeric_code' => '756', 'dialing_code' => '41'],
            ['iso' => 'SY', 'name' => 'Syrian Arab Republic', 'iso3' => 'SYR', 'numeric_code' => '760', 'dialing_code' => '963'],
            ['iso' => 'TW', 'name' => 'Taiwan, Province of China', 'iso3' => 'TWN', 'numeric_code' => '158', 'dialing_code' => '886'],
            ['iso' => 'TJ', 'name' => 'Tajikistan', 'iso3' => 'TJK', 'numeric_code' => '762', 'dialing_code' => '992'],
            ['iso' => 'TZ', 'name' => 'Tanzania, United Republic of', 'iso3' => 'TZA', 'numeric_code' => '834', 'dialing_code' => '255'],
            ['iso' => 'TH', 'name' => 'Thailand', 'iso3' => 'THA', 'numeric_code' => '764', 'dialing_code' => '66'],
            ['iso' => 'TL', 'name' => 'Timor-Leste', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '670'],
            ['iso' => 'TG', 'name' => 'Togo', 'iso3' => 'TGO', 'numeric_code' => '768', 'dialing_code' => '228'],
            ['iso' => 'TK', 'name' => 'Tokelau', 'iso3' => 'TKL', 'numeric_code' => '772', 'dialing_code' => '690'],
            ['iso' => 'TO', 'name' => 'Tonga', 'iso3' => 'TON', 'numeric_code' => '776', 'dialing_code' => '676'],
            ['iso' => 'TT', 'name' => 'Trinidad and Tobago', 'iso3' => 'TTO', 'numeric_code' => '780', 'dialing_code' => '1868'],
            ['iso' => 'TN', 'name' => 'Tunisia', 'iso3' => 'TUN', 'numeric_code' => '788', 'dialing_code' => '216'],
            ['iso' => 'TR', 'name' => 'Turkey', 'iso3' => 'TUR', 'numeric_code' => '792', 'dialing_code' => '90'],
            ['iso' => 'TM', 'name' => 'Turkmenistan', 'iso3' => 'TKM', 'numeric_code' => '795', 'dialing_code' => '7370'],
            ['iso' => 'TC', 'name' => 'Turks and Caicos Islands', 'iso3' => 'TCA', 'numeric_code' => '796', 'dialing_code' => '1649'],
            ['iso' => 'TV', 'name' => 'Tuvalu', 'iso3' => 'TUV', 'numeric_code' => '798', 'dialing_code' => '688'],
            ['iso' => 'UG', 'name' => 'Uganda', 'iso3' => 'UGA', 'numeric_code' => '800', 'dialing_code' => '256'],
            ['iso' => 'UA', 'name' => 'Ukraine', 'iso3' => 'UKR', 'numeric_code' => '804', 'dialing_code' => '380'],
            ['iso' => 'AE', 'name' => 'United Arab Emirates', 'iso3' => 'ARE', 'numeric_code' => '784', 'dialing_code' => '971'],
            ['iso' => 'GB', 'name' => 'United Kingdom', 'iso3' => 'GBR', 'numeric_code' => '826', 'dialing_code' => '44'],
            ['iso' => 'US', 'name' => 'United States', 'iso3' => 'USA', 'numeric_code' => '840', 'dialing_code' => '1'],
            ['iso' => 'UM', 'name' => 'United States Minor Outlying Islands', 'iso3' => NULL,'numeric_code' => NULL,'dialing_code' => '1'],
            ['iso' => 'UY', 'name' => 'Uruguay', 'iso3' => 'URY', 'numeric_code' => '858', 'dialing_code' => '598'],
            ['iso' => 'UZ', 'name' => 'Uzbekistan', 'iso3' => 'UZB', 'numeric_code' => '860', 'dialing_code' => '998'],
            ['iso' => 'VU', 'name' => 'Vanuatu', 'iso3' => 'VUT', 'numeric_code' => '548', 'dialing_code' => '678'],
            ['iso' => 'VE', 'name' => 'Venezuela', 'iso3' => 'VEN', 'numeric_code' => '862', 'dialing_code' => '58'],
            ['iso' => 'VN', 'name' => 'Viet Nam', 'iso3' => 'VNM', 'numeric_code' => '704', 'dialing_code' => '84'],
            ['iso' => 'VG', 'name' => 'Virgin Islands, British', 'iso3' => 'VGB', 'numeric_code' => '92', 'dialing_code' => '1284'],
            ['iso' => 'VI', 'name' => 'Virgin Islands, U.S.', 'iso3' => 'VIR', 'numeric_code' => '850', 'dialing_code' => '1340'],
            ['iso' => 'WF', 'name' => 'Wallis and Futuna', 'iso3' => 'WLF', 'numeric_code' => '876', 'dialing_code' => '681'],
            ['iso' => 'EH', 'name' => 'Western Sahara', 'iso3' => 'ESH', 'numeric_code' => '732', 'dialing_code' => '212'],
            ['iso' => 'YE', 'name' => 'Yemen', 'iso3' => 'YEM', 'numeric_code' => '887', 'dialing_code' => '967'],
            ['iso' => 'ZM', 'name' => 'Zambia', 'iso3' => 'ZMB', 'numeric_code' => '894', 'dialing_code' => '260'],
            ['iso' => 'ZW', 'name' => 'Zimbabwe', 'iso3' => 'ZWE', 'numeric_code' => '716', 'dialing_code' => '263'],
            ['iso' => 'RS', 'name' => 'Serbia', 'iso3' => 'SRB', 'numeric_code' => '688', 'dialing_code' => '381'],
            ['iso' => 'AP', 'name' => 'Asia / Pacific Region', 'iso3' => '0', 'numeric_code' => '0', 'dialing_code' => '0'],
            ['iso' => 'ME', 'name' => 'Montenegro', 'iso3' => 'MNE', 'numeric_code' => '499', 'dialing_code' => '382'],
            ['iso' => 'AX', 'name' => 'Aland Islands', 'iso3' => 'ALA', 'numeric_code' => '248', 'dialing_code' => '358'],
            ['iso' => 'BQ', 'name' => 'Bonaire, Sint Eustatius and Saba', 'iso3' => 'BES', 'numeric_code' => '535', 'dialing_code' => '599'],
            ['iso' => 'CW', 'name' => 'Curacao', 'iso3' => 'CUW', 'numeric_code' => '531', 'dialing_code' => '599'],
            ['iso' => 'GG', 'name' => 'Guernsey', 'iso3' => 'GGY', 'numeric_code' => '831', 'dialing_code' => '44'],
            ['iso' => 'IM', 'name' => 'Isle of Man', 'iso3' => 'IMN', 'numeric_code' => '833', 'dialing_code' => '44'],
            ['iso' => 'JE', 'name' => 'Jersey', 'iso3' => 'JEY', 'numeric_code' => '832', 'dialing_code' => '44'],
            ['iso' => 'XK', 'name' => 'Kosovo', 'iso3' => '---', 'numeric_code' => '0', 'dialing_code' => '381'],
            ['iso' => 'BL', 'name' => 'Saint Barthelemy', 'iso3' => 'BLM', 'numeric_code' => '652', 'dialing_code' => '590'],
            ['iso' => 'MF', 'name' => 'Saint Martin', 'iso3' => 'MAF', 'numeric_code' => '663', 'dialing_code' => '590'],
            ['iso' => 'SX', 'name' => 'Sint Maarten', 'iso3' => 'SXM', 'numeric_code' => '534', 'dialing_code' => '1'],
            ['iso' => 'SS', 'name' => 'South Sudan', 'iso3' => 'SSD', 'numeric_code' => '728', 'dialing_code' => '211']
        ];

        return collect($countries)->map(function($country) {

            $obj = new stdClass;
            $obj->iso = $country['iso'];
            $obj->iso3 = $country['iso3'];
            $obj->name = $country['name'];
            $obj->numericCode = $country['numeric_code'];
            $obj->dialingCode = $country['dialing_code'];

            return $obj;
        })->all();
    }

    /**
     * Find country by two-letter country code defined in ISO 3166-1.
     *
     * @param string $twoLetterCountryCode - "US" for the United States.
     * @return stdClass|null
     */
    public static function findCountryByTwoLetterCountryCode(string $twoLetterCountryCode)
    {
        return collect(self::getCountries())->firstWhere('iso', $twoLetterCountryCode);
    }

    /**
     * Find country by three-letter country code defined in ISO 3166-1.
     *
     * @param string $threeLetterCountryCode - "USA" for the United States.
     * @return stdClass|null
     */
    public static function findCountryByThreeLetterCountryCode(string $threeLetterCountryCode)
    {
        return collect(self::getCountries())->firstWhere('iso3', $threeLetterCountryCode);
    }

    /**
     * Find country name by two-letter country code defined in ISO 3166-1.
     *
     * @param string $twoLetterCountryCode - "US" for the United States.
     * @return string|null
     */
    public static function findCountryNameByTwoLetterCountryCode(string $twoLetterCountryCode)
    {
        return collect(self::getCountries())->firstWhere('iso', $twoLetterCountryCode)?->name;
    }

    /**
     * Find country name by three-letter country code defined in ISO 3166-1.
     *
     * @param string $threeLetterCountryCode - "USA" for the United States.
     * @return string|null
     */
    public static function findCountryNameByThreeLetterCountryCode(string $threeLetterCountryCode)
    {
        return collect(self::getCountries())->firstWhere('iso3', $threeLetterCountryCode)?->name;
    }

    /**
     * Find country by three-letter country code defined in ISO 3166-1.
     *
     * @param string|int $numericCode - "840" for the United States.
     * @return stdClass|null
     */
    public static function findCountryByNumericCode(string|int $numericCode)
    {
        return collect(self::getCountries())->firstWhere('numeric_code', $numericCode);
    }

    /**
     * Find country by dialing code.
     *
     * @param string|int $dialingCode - "1" for the United States.
     * @return stdClass|null
     */
    public static function findCountryByDialingCode(string $dialingCode)
    {
        return collect(self::getCountries())->firstWhere('dialing_code', $dialingCode);
    }
}
