<?php

namespace App\Enums;

enum TransactionPaymentStatus:string {
    case PAID = 'paid';
    case FAILED = 'failed';
    case PENDING = 'pending';
}
