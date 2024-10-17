<?php

namespace App\Enums;

enum TransactionVerificationType:string {
    case MANUAL = 'manual';
    case AUTOMATIC = 'automatic';
}
