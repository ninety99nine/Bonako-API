<?php

namespace App\Enums;

enum PaymentMethodAvailability:string {
    case AvailableOnUssd = 'available on ussd';
    case AvailableInStores = 'available in stores';
    case AvailableOnPerfectPay = 'available on perfect pay';
}
