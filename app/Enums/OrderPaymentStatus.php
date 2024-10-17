<?php

namespace App\Enums;

enum OrderPaymentStatus:string {
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case PENDING = 'pending';
    case PARTIALLY_PAID = 'partially paid';
}
