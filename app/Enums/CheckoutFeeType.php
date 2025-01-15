<?php

namespace App\Enums;

enum CheckoutFeeType:string {
    case PERCENTAGE = 'percentage';
    case FLAT = 'flat';
}
