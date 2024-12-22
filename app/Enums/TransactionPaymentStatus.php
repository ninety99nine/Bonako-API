<?php

namespace App\Enums;

enum TransactionPaymentStatus:string {
    case PAID = 'paid';
    case FAILED_PAYMENT = 'failed payment';
    case PENDING_PAYMENT = 'pending payment';
}
