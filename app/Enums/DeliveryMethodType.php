<?php

namespace App\Enums;

enum DeliveryMethodType:string {
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
}
