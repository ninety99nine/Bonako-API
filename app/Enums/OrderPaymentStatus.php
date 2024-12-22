<?php

namespace App\Enums;

enum OrderPaymentStatus:string {
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case PENDING_PAYMENT = 'pending payment';
    case PARTIALLY_PAID = 'partially paid';
}
