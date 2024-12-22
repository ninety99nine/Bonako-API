<?php

namespace App\Enums;

enum DeliveryMethodFallbackFeeType:string {
    case FLAT_FEE = 'flat fee';
    case PERCENTAGE_FEE = 'percentage fee';
}
