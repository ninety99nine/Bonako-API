<?php

namespace App\Enums;

enum PaymentMethodCategory:string {
    case LOCAL = 'local';
    case MANUAL = 'manual';
    case AUTOMATED = 'automated';
}
