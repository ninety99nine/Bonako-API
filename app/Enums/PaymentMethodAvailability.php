<?php

namespace App\Enums;

enum PaymentMethodAvailability:string {
    case AvailableOnUssd = 'Available On USSD';
    case AvailableInStores = 'Available In Stores';
    case AvailableOnPerfectPay = 'Available On PerfectPay';
}
