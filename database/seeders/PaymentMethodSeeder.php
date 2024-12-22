<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use App\Enums\PaymentMethodType;
use App\Enums\PaymentMethodCategory;
use Database\Seeders\Traits\SeederHelper;

class PaymentMethodSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getPaymentMethodTemplates() as $paymentMethodTemplate) {
            PaymentMethod::create($paymentMethodTemplate);
        }
    }

    /**
     * Return payment methods
     *
     * @return array
     */
    public function getPaymentMethodTemplates()
    {
        return [
            [
                'active' => 1,
                'name' => 'DPO (Direct Pay Online)',
                'type' => PaymentMethodType::DPO,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with DPO (Direct Pay Online) and experience fast, secure payments with ease',
                'metadata' => [
                    'company_token' =>  config('app.DPO_COMPANY_TOKEN'),
                    'email_payment_request' =>  false,
                    'default_country_code' =>  'BW'
                ],
                'countries' => [
                    'BW', // Botswana
                    'BF', // Burkina Faso
                    'BI', // Burundi
                    'CM', // Cameroon
                    'CV', // Cape Verde
                    'CF', // Central African Republic
                    'TD', // Chad
                    'KM', // Comoros
                    'CG', // Congo - Brazzaville
                    'CI', // Côte d'Ivoire
                    'DJ', // Djibouti
                    'EG', // Egypt
                    'GQ', // Equatorial Guinea
                    'ER', // Eritrea
                    'SZ', // Eswatini
                    'ET', // Ethiopia
                    'GA', // Gabon
                    'GM', // Gambia
                    'GH', // Ghana
                    'GN', // Guinea
                    'GW', // Guinea-Bissau
                    'KE', // Kenya
                    'LS', // Lesotho
                    'LR', // Liberia
                    'LY', // Libya
                    'MG', // Madagascar
                    'MW', // Malawi
                    'ML', // Mali
                    'MR', // Mauritania
                    'MU', // Mauritius
                    'MA', // Morocco
                    'MZ', // Mozambique
                    'NA', // Namibia
                    'NE', // Niger
                    'NG', // Nigeria
                    'RW', // Rwanda
                    'ST', // São Tomé and Príncipe
                    'SN', // Senegal
                    'SC', // Seychelles
                    'SL', // Sierra Leone
                    'SO', // Somalia
                    'ZA', // South Africa
                    'SS', // South Sudan
                    'SD', // Sudan
                    'TZ', // Tanzania
                    'TG', // Togo
                    'TN', // Tunisia
                    'UG', // Uganda
                    'ZM', // Zambia
                    'ZW'  // Zimbabwe
                ]
            ],
            [
                'active' => 1,
                'name' => 'Pix',
                'type' => PaymentMethodType::PIX,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Pix for instant, secure payments directly from your bank account',
                'countries' => [
                    'BR' // Brazil
                ]
            ],
            [
                'active' => 1,
                'name' => 'UPI',
                'type' => PaymentMethodType::UPI,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with UPI for instant, secure transactions straight from your mobile device',
                'countries' => [
                    'IN' // India
                ]
            ],
            [
                'active' => 1,
                'name' => 'Yoco',
                'type' => PaymentMethodType::YOCO,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Yoco for fast and secure transactions using your mobile device',
                'countries' => [
                    'ZA' // South Africa
                ]
            ],
            [
                'active' => 1,
                'name' => 'QRIS',
                'type' => PaymentMethodType::QRIS,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with QRIS for quick and secure transactions using your smartphone. Simply scan the QR code for a seamless and hassle-free payment experience',
                'countries' => [
                    'ID' // Indonesia
                ]
            ],
            [
                'active' => 1,
                'name' => 'Wise',
                'type' => PaymentMethodType::WISE,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Wise for fast, secure, and cost-effective international transactions',
                'countries' => null // Global
            ],
            [
                'active' => 1,
                'name' => 'Lynk',
                'type' => PaymentMethodType::LYNK,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Lynk for secure and efficient mobile payments',
                'countries' => [
                    'JM' // Jamaica
                ]
            ],
            [
                'active' => 1,
                'name' => 'Cash',
                'type' => PaymentMethodType::CASH,
                'category' => PaymentMethodCategory::MANUAL,
                'instruction' => 'Pay with Cash for a straightforward and reliable payment option',
                'countries' => null // Global
            ],
            [
                'active' => 1,
                'name' => 'Unayo',
                'type' => PaymentMethodType::UNAYO,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Unayo for secure and easy mobile transactions',
                'countries' => [
                    'BW', // Botswana
                    'GH', // Ghana
                    'KE', // Kenya
                    'LS', // Lesotho
                    'MW', // Malawi
                    'ZA', // South Africa
                    'TZ', // Tanzania
                    'UG', // Uganda
                ]
            ],
            [
                'active' => 1,
                'name' => 'GCash',
                'type' => PaymentMethodType::GCASH,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with GCash for fast, secure, and convenient mobile payments',
                'countries' => [
                    'PH', // Philippines
                    'MY', // Malaysia
                    'SG', // Singapore
                    'TH', // Thailand
                    'VN', // Vietnam
                    'ID', // Indonesia
                    'HK', // Hong Kong
                    'TW', // Taiwan
                    'JP', // Japan
                    'KR', // South Korea
                    'IN', // India
                    'PK', // Pakistan
                    'BD', // Bangladesh
                    'MM', // Myanmar
                    'LA', // Laos
                    'KH', // Cambodia
                    'BR', // Brazil
                    'MX', // Mexico
                    'US', // United States
                    'CA', // Canada
                    'GB', // United Kingdom
                    'AU', // Australia
                    'NZ', // New Zealand
                    'ZA', // South Africa
                    'KE', // Kenya
                    'NG', // Nigeria
                    'GH', // Ghana
                    'TZ', // Tanzania
                    'UG', // Uganda
                    'ZM', // Zambia
                    'MW', // Malawi
                ]
            ],
            [
                'active' => 1,
                'name' => 'eSewa',
                'type' => PaymentMethodType::ESEWA,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with eSewa for secure and efficient mobile payments',
                'countries' => [
                    'NP', // Nepal
                    'IN', // India
                    'BD', // Bangladesh
                    'PK', // Pakistan
                    'LK', // Sri Lanka
                ]
            ],
            [
                'active' => 1,
                'name' => 'Venmo',
                'type' => PaymentMethodType::VENMO,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Venmo for fast and secure payments directly from your mobile device',
                'countries' => [
                    'US' // United States
                ]
            ],
            [
                'active' => 1,
                'name' => 'Zelle',
                'type' => PaymentMethodType::ZELLE,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Zelle for instant, secure payments directly from your bank account',
                'countries' => [
                    'US' // United States
                ]
            ],
            [
                'active' => 1,
                'name' => 'Ziina',
                'type' => PaymentMethodType::ZIINA,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Ziina for secure and effortless mobile transactions',
                'countries' => [
                    'AE' // United Arab Emirates
                ]
            ],
            [
                'active' => 1,
                'name' => 'Kaspi',
                'type' => PaymentMethodType::KASPI,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Kaspi for secure and efficient mobile transactions',
                'countries' => [
                    'KZ' // Kazakhstan
                ]
            ],
            [
                'active' => 1,
                'name' => 'M-Pesa',
                'type' => PaymentMethodType::MPESA,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with M-Pesa for secure and convenient mobile money transfers',
                'countries' => [
                    'KE', // Kenya
                    'TZ', // Tanzania
                    'UG', // Uganda
                    'RW', // Rwanda
                    'ZM', // Zambia
                    'MZ'  // Mozambique
                ]
            ],
            [
                'active' => 1,
                'name' => 'MyZaka',
                'type' => PaymentMethodType::MYZAKA,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with MyZaka for fast and secure mobile transactions',
                'countries' => [
                    'BW' // Botswana
                ]
            ],
            [
                'active' => 1,
                'name' => 'Stripe',
                'type' => PaymentMethodType::STRIPE,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with Stripe for fast, secure, and scalable online payments',
                'countries' => [
                    'AU', // Australia
                    'AT', // Austria
                    'BE', // Belgium
                    'BR', // Brazil
                    'BG', // Bulgaria
                    'CA', // Canada
                    'HR', // Croatia
                    'CY', // Cyprus
                    'CZ', // Czech Republic
                    'DK', // Denmark
                    'EE', // Estonia
                    'FI', // Finland
                    'FR', // France
                    'DE', // Germany
                    'GH', // Ghana (Extended network)
                    'GI', // Gibraltar
                    'GR', // Greece
                    'HK', // Hong Kong
                    'HU', // Hungary
                    'IN', // India
                    'ID', // Indonesia (Invite Only)
                    'IE', // Ireland
                    'IT', // Italy
                    'JP', // Japan
                    'KE', // Kenya (Extended network)
                    'LV', // Latvia
                    'LI', // Liechtenstein
                    'LT', // Lithuania
                    'LU', // Luxembourg
                    'MY', // Malaysia
                    'MT', // Malta
                    'MX', // Mexico
                    'NL', // Netherlands
                    'NZ', // New Zealand
                    'NG', // Nigeria (Extended network)
                    'NO', // Norway
                    'PL', // Poland
                    'PT', // Portugal
                    'RO', // Romania
                    'SG', // Singapore
                    'SK', // Slovakia
                    'SI', // Slovenia
                    'ZA', // South Africa (Extended network)
                    'ES', // Spain
                    'SE', // Sweden
                    'CH', // Switzerland
                    'TH', // Thailand
                    'AE', // United Arab Emirates
                    'GB', // United Kingdom
                    'US'  // United States
                ]
            ],
            [
                'active' => 1,
                'name' => 'PayPal',
                'type' => PaymentMethodType::PAYPAL,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with PayPal for secure and convenient online payments',
                'countries' => [
                    'AL', // Albania
                    'DZ', // Algeria
                    'AD', // Andorra
                    'AO', // Angola
                    'AI', // Anguilla
                    'AG', // Antigua and Barbuda
                    'AR', // Argentina
                    'AM', // Armenia
                    'AW', // Aruba
                    'AU', // Australia
                    'AT', // Austria
                    'AZ', // Azerbaijan Republic
                    'BS', // Bahamas
                    'BH', // Bahrain
                    'BB', // Barbados
                    'BE', // Belgium
                    'BZ', // Belize
                    'BJ', // Benin
                    'BM', // Bermuda
                    'BT', // Bhutan
                    'BO', // Bolivia
                    'BW', // Botswana
                    'BR', // Brazil
                    'BN', // Brunei
                    'BG', // Bulgaria
                    'BF', // Burkina Faso
                    'BI', // Burundi
                    'KH', // Cambodia
                    'CM', // Cameroon
                    'CA', // Canada
                    'CV', // Cape Verde
                    'KY', // Cayman Islands
                    'TD', // Chad
                    'CL', // Chile
                    'CN', // Mainland China
                    'CO', // Colombia
                    'KM', // Comoros
                    'CG', // Republic of the Congo
                    'CR', // Costa Rica
                    'CI', // Cote d'Ivoire
                    'HR', // Croatia
                    'CY', // Cyprus
                    'CZ', // Czech Republic
                    'DK', // Denmark
                    'DJ', // Djibouti
                    'DM', // Dominica
                    'DO', // Dominican Republic
                    'EC', // Ecuador
                    'EG', // Egypt
                    'SV', // El Salvador
                    'ER', // Eritrea
                    'EE', // Estonia
                    'ET', // Ethiopia
                    'FJ', // Fiji
                    'FI', // Finland
                    'FR', // France
                    'GA', // Gabon Republic
                    'GM', // Gambia
                    'GE', // Georgia
                    'DE', // Germany
                    'GH', // Ghana
                    'GR', // Greece
                    'GD', // Grenada
                    'GT', // Guatemala
                    'GN', // Guinea
                    'GW', // Guinea-Bissau
                    'GY', // Guyana
                    'HN', // Honduras
                    'HK', // Hong Kong SAR, China
                    'HU', // Hungary
                    'IS', // Iceland
                    'IN', // India
                    'ID', // Indonesia
                    'IE', // Ireland
                    'IL', // Israel
                    'IT', // Italy
                    'JM', // Jamaica
                    'JP', // Japan
                    'JO', // Jordan
                    'KZ', // Kazakhstan
                    'KE', // Kenya
                    'KI', // Kiribati
                    'KW', // Kuwait
                    'KG', // Kyrgyzstan
                    'LA', // Laos
                    'LV', // Latvia
                    'LS', // Lesotho
                    'LR', // Liberia
                    'LI', // Liechtenstein
                    'LT', // Lithuania
                    'LU', // Luxembourg
                    'MG', // Madagascar
                    'MW', // Malawi
                    'MY', // Malaysia
                    'MV', // Maldives
                    'ML', // Mali
                    'MT', // Malta
                    'MH', // Marshall Islands
                    'MR', // Mauritania
                    'MU', // Mauritius
                    'MX', // Mexico
                    'FM', // Federated States of Micronesia
                    'MD', // Moldova
                    'MC', // Monaco
                    'MN', // Mongolia
                    'ME', // Montenegro
                    'MA', // Morocco
                    'MZ', // Mozambique
                    'NA', // Namibia
                    'NR', // Nauru
                    'NP', // Nepal
                    'NL', // Netherlands
                    'NZ', // New Zealand
                    'NI', // Nicaragua
                    'NE', // Niger
                    'NG', // Nigeria
                    'NO', // Norway
                    'OM', // Oman
                    'PW', // Palau
                    'PA', // Panama
                    'PG', // Papua New Guinea
                    'PY', // Paraguay
                    'PE', // Peru
                    'PH', // Philippines
                    'PL', // Poland
                    'PT', // Portugal
                    'QA', // Qatar
                    'RO', // Romania
                    'RU', // Russia
                    'RW', // Rwanda
                    'WS', // Samoa
                    'SM', // San Marino
                    'ST', // Sao Tome and Principe
                    'SA', // Saudi Arabia
                    'SN', // Senegal
                    'RS', // Serbia
                    'SC', // Seychelles
                    'SL', // Sierra Leone
                    'SG', // Singapore
                    'SK', // Slovakia
                    'SI', // Slovenia
                    'SB', // Solomon Islands
                    'ZA', // South Africa
                    'KR', // South Korea
                    'ES', // Spain
                    'LK', // Sri Lanka
                    'SR', // Suriname
                    'SZ', // Swaziland
                    'SE', // Sweden
                    'CH', // Switzerland
                    'TW', // Taiwan, China
                    'TJ', // Tajikistan
                    'TZ', // Tanzania
                    'TH', // Thailand
                    'TL', // Timor-Leste
                    'TG', // Togo
                    'TO', // Tonga
                    'TT', // Trinidad and Tobago
                    'TN', // Tunisia
                    'TR', // Turkey
                    'TM', // Turkmenistan
                    'TV', // Tuvalu
                    'UG', // Uganda
                    'UA', // Ukraine
                    'AE', // United Arab Emirates
                    'GB', // United Kingdom
                    'US', // United States
                    'UY', // Uruguay
                    'VU', // Vanuatu
                    'VA', // Vatican City
                    'VE', // Venezuela
                    'VN', // Vietnam
                    'ZM', // Zambia
                    'ZW', // Zimbabwe
                ]
            ],
            [
                'active' => 1,
                'name' => 'Xendit',
                'type' => PaymentMethodType::XENDIT,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with Xendit for fast, secure, and efficient online transactions',
                'countries' => [
                    'ID', // Indonesia
                    'PH', // Philippines
                    'TH', // Thailand
                    'VN', // Vietnam
                    'MY'  // Malaysia
                ]
            ],
            [
                'active' => 1,
                'name' => 'Pocket',
                'type' => PaymentMethodType::POCKET,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with Pocket for secure and convenient mobile payments',
                'countries' => [
                    'BN', // Brunei
                    'TH', // Thailand
                    'IN', // India
                    'TR', // Turkey
                    'ID', // Indonesia
                    'MY', // Malaysia
                    'JP', // Japan
                    'KR', // South Korea
                    'CN', // China
                    'AE', // United Arab Emirates
                    'SA', // Saudi Arabia
                    'RS', // Serbia
                    'BR', // Brazil
                    'CL', // Chile
                    'VE', // Venezuela
                    'CO', // Colombia
                    'NG', // Nigeria
                    'ZA', // South Africa
                ]
            ],
            [
                'active' => 1,
                'name' => 'PayNow',
                'type' => PaymentMethodType::PAYNOW,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with PayNow for instant and secure payments',
                'countries' => [
                    'SG', // Singapore
                ]
            ],
            [
                'active' => 1,
                'name' => 'WigWag',
                'type' => PaymentMethodType::WIGWAG,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with WigWag for fast and secure mobile payments',
                'countries' => [
                    'NG', // Nigeria
                    'ZA', // South Africa
                ]
            ],
            [
                'active' => 1,
                'name' => 'Tikkie',
                'type' => PaymentMethodType::TIKKIE,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Tikkie for quick and easy payments via a simple link',
                'countries' => [
                    'NL', // Netherlands
                ]
            ],
            [
                'active' => 1,
                'name' => 'Airtel',
                'type' => PaymentMethodType::AIRTEL,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Airtel for secure and convenient mobile transactions',
                'countries' => [
                    'CD', // Democratic Republic of Congo
                    'CG', // Republic of Congo
                    'GA', // Gabon
                    'KE', // Kenya
                    'MG', // Madagascar
                    'MW', // Malawi
                    'NE', // Niger
                    'RW', // Rwanda
                    'SC', // Seychelles
                    'TD', // Chad
                    'TZ', // Tanzania
                    'UG', // Uganda
                    'ZM', // Zambia
                ]
            ],
            [
                'active' => 1,
                'name' => 'EcoCash',
                'type' => PaymentMethodType::ECOCASH,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with EcoCash for fast and secure mobile payments',
                'countries' => [
                    'ZW', // Zimbabwe
                ]
            ],
            [
                'active' => 1,
                'name' => 'iKhokha',
                'type' => PaymentMethodType::IKHOKHA,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with iKhokha for fast, secure, and easy mobile transactions',
                'countries' => [
                    'ZA', // South Africa
                ]
            ],
            [
                'active' => 1,
                'name' => 'Revolut',
                'type' => PaymentMethodType::REVOLUT,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Revolut for secure and flexible financial transactions',
                'countries' => [
                    'GB', // United Kingdom
                    'DE', // Germany
                    'FR', // France
                    'ES', // Spain
                    'IT', // Italy
                    'US', // United States
                    'AU', // Australia
                    'SG', // Singapore
                    'BR', // Brazil
                    'JP', // Japan
                ]
            ],
            [
                'active' => 1,
                'name' => 'Pesapal',
                'type' => PaymentMethodType::PESAPAL,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Pesapal for secure and convenient online transactions',
                'countries' => [
                    'KE', // Kenya
                    'UG', // Uganda
                    'TZ', // Tanzania
                    'RW', // Rwanda
                    'ZM', // Zambia
                    'MW', // Malawi
                    'ZW', // Zimbabwe
                ]
            ],
            [
                'active' => 1,
                'name' => 'PayHere',
                'type' => PaymentMethodType::PAYHERE,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with PayHere for secure and efficient online payments',
                'countries' => [
                    'LK', // Sri Lanka
                ]
            ],
            [
                'active' => 1,
                'name' => 'PayFast',
                'type' => PaymentMethodType::PAYFAST,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with PayFast for secure and speedy online payments',
                'countries' => [
                    'ZA', // South Africa
                ]
            ],
            [
                'active' => 1,
                'name' => 'DuitNow',
                'type' => PaymentMethodType::DUITNOW,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with DuitNow for instant and secure transactions directly from your bank account',
                'countries' => [
                    'MY', // Malaysia
                ]
            ],
            [
                'active' => 1,
                'name' => 'MonCash',
                'type' => PaymentMethodType::MONCASH,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with MonCash for fast, secure, and convenient mobile payments',
                'countries' => [
                    'HT', // Haiti
                ]
            ],
            [
                'active' => 1,
                'name' => 'MTN MoMo',
                'type' => PaymentMethodType::MTN_MOMO,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with MTN MoMo for fast and secure mobile payments',
                'countries' => [
                    'CD', // Democratic Republic of Congo
                    'CG', // Republic of Congo
                    'GA', // Gabon
                    'KE', // Kenya
                    'MG', // Madagascar
                    'MW', // Malawi
                    'NE', // Niger
                    'RW', // Rwanda
                    'SC', // Seychelles
                    'TD', // Chad
                    'TZ', // Tanzania
                    'UG', // Uganda
                    'ZM', // Zambia
                    'BJ', // Benin
                    'CI', // Côte d'Ivoire (Ivory Coast)
                    'CM', // Cameroon
                    'GH', // Ghana
                    'GN', // Guinea
                    'LR', // Liberia
                ]
            ],
            [
                'active' => 1,
                'name' => 'Cellmoni',
                'type' => PaymentMethodType::CELLMONI,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Cellmoni for secure and convenient mobile transactions',
                'countries' => [
                    'CM', // Cameroon
                    'PG', // Papua New Guinea
                ]
            ],
            [
                'active' => 1,
                'name' => 'TigoPesa',
                'type' => PaymentMethodType::TIGOPESA,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with TigoPesa for fast, secure, and convenient mobile payments',
                'countries' => [
                    'TZ', // Tanzania
                    'ML', // Mali
                ]
            ],
            [
                'active' => 1,
                'name' => 'InnBucks',
                'type' => PaymentMethodType::INNBUCKS,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with InnBucks for fast and secure mobile payments',
                'countries' => [
                    'ZW', // Zimbabwe
                ]
            ],
            [
                'active' => 1,
                'name' => 'Cash App',
                'type' => PaymentMethodType::CASH_APP,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Cash App for quick, secure, and easy transactions',
                'countries' => [
                    'US', // United States
                    'GB', // United Kingdom
                ]
            ],
            [
                'active' => 1,
                'name' => 'Paystack',
                'type' => PaymentMethodType::PAYSTACK,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with Paystack for fast and secure online payments',
                'countries' => [
                    'NG', // Nigeria
                    'GH', // Ghana
                    'ZA', // South Africa
                    'KE', // Kenya
                    'TZ', // Tanzania
                ]
            ],
            [
                'active' => 1,
                'name' => 'Razorpay',
                'type' => PaymentMethodType::RAZORPAY,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with Razorpay for fast, secure, and seamless online transactions',
                'countries' => [
                    'IN', // India
                    'SG', // Singapore
                    'MY', // Malaysia
                ]
            ],
            [
                'active' => 1,
                'name' => 'PromptPay',
                'type' => PaymentMethodType::PROMPTPAY,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with PromptPay for instant and secure transactions',
                'countries' => [
                    'TH', // Thailand
                ]
            ],
            [
                'active' => 1,
                'name' => 'Touch n Go',
                'type' => PaymentMethodType::TOUCH_N_GO,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Touch n Go for fast, secure, and convenient mobile payments',
                'countries' => [
                    'MY', // Malaysia
                ]
            ],
            [
                'active' => 1,
                'name' => 'FNB eWallet',
                'type' => PaymentMethodType::FNB_EWALLET,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with FNB eWallet for secure and convenient mobile transactions',
                'countries' => [
                    'BW', // Botswana
                    'ZA', // South Africa
                ]
            ],
            [
                'active' => 1,
                'name' => 'Mercado Pago',
                'type' => PaymentMethodType::MERCADO_PAGO,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Mercado Pago for secure and flexible online payments',
                'countries' => [
                    'AR', // Argentina
                    'BR', // Brazil
                    'MX', // Mexico
                    'CL', // Chile
                    'CO', // Colombia
                    'UY', // Uruguay
                    'PE', // Peru
                    'BO', // Bolivia
                    'PY', // Paraguay
                ]
            ],
            [
                'active' => 1,
                'name' => 'FNB Pay2Cell',
                'type' => PaymentMethodType::FNB_PAY2CELL,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with FNB Pay2Cell for secure and straightforward mobile payments',
                'countries' => [
                    'BW', // Botswana
                    'ZA', // South Africa
                ]
            ],
            [
                'active' => 1,
                'name' => 'SEPA Credit Transfer',
                'type' => PaymentMethodType::SEPA,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with SEPA Credit Transfer for fast and secure bank-to-bank payments within Europe',
                'countries' => [
                    'AT', // Austria
                    'BE', // Belgium
                    'BG', // Bulgaria
                    'CY', // Cyprus
                    'DE', // Germany
                    'DK', // Denmark
                    'EE', // Estonia
                    'ES', // Spain
                    'FI', // Finland
                    'FR', // France
                    'GR', // Greece
                    'HR', // Croatia
                    'HU', // Hungary
                    'IE', // Ireland
                    'IT', // Italy
                    'LT', // Lithuania
                    'LU', // Luxembourg
                    'MT', // Malta
                    'NL', // Netherlands
                    'PL', // Poland
                    'PT', // Portugal
                    'RO', // Romania
                    'SE', // Sweden
                    'SI', // Slovenia
                    'SK', // Slovakia
                    'IS', // Iceland
                    'LI', // Liechtenstein
                    'NO', // Norway
                ]
            ],
            [
                'active' => 1,
                'name' => 'Orange Money',
                'type' => PaymentMethodType::ORANGE_MONEY,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Orange Money for secure and easy mobile transactions',
                'countries' => [
                    'BW', // Botswana
                    'BF', // Burkina Faso (under the Airtel Money brand)
                    'CM', // Cameroon
                    'CF', // Central African Republic
                    'CD', // Democratic Republic of the Congo
                    'CI', // Côte d'Ivoire
                    'EG', // Egypt (branded as Orange Cash)
                    'GN', // Guinea
                    'GW', // Guinea-Bissau
                    'JO', // Jordan
                    'LR', // Liberia (under the Smile Money brand)
                    'MG', // Madagascar
                    'ML', // Mali
                    'MA', // Morocco
                    'NE', // Niger
                    'SN', // Senegal
                    'RO', // Romania
                    'SL', // Sierra Leone (under the Airtel Money brand)
                    'TN', // Tunisia
                ]
            ],
            [
                'active' => 1,
                'name' => 'Store Credit',
                'type' => PaymentMethodType::STORE_CREDIT,
                'category' => PaymentMethodCategory::MANUAL,
                'instruction' => 'Pay with Store Credit for a seamless and convenient checkout experience',
                'countries' => null // Global
            ],
            [
                'active' => 1,
                'name' => 'Bank Transfer',
                'type' => PaymentMethodType::BANK_TRANSFER,
                'category' => PaymentMethodCategory::MANUAL,
                'instruction' => 'Pay with Bank Transfer for secure payments directly from your bank account',
                'countries' => null // Global
            ],
            [
                'active' => 1,
                'name' => 'Manual Payment',
                'type' => PaymentMethodType::MANUAL_PAYMENT,
                'category' => PaymentMethodCategory::MANUAL,
                'instruction' => 'Pay with our payment option for fast and secure transactions',
                'countries' => null // Global
            ],
            [
                'active' => 1,
                'name' => 'Orange Airtime',
                'type' => PaymentMethodType::ORANGE_AIRTIME,
                'category' => PaymentMethodCategory::AUTOMATED,
                'instruction' => 'Pay with Orange Airtime for quick and straightforward mobile transactions',
                'countries' => null // Global
            ],
            [
                'active' => 1,
                'name' => 'Smega Mobile Money',
                'type' => PaymentMethodType::SMEGA_MOBILE_MONEY,
                'category' => PaymentMethodCategory::LOCAL,
                'instruction' => 'Pay with Smega Mobile Money for fast, secure, and convenient mobile transactions',
                'countries' => [
                    'BW', // Botswana
                ]
            ]
        ];
    }
}
