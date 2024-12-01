<?php

namespace App\Enums;

enum TaxMethod:string {
    case INCLUSIVE = 'inclusive';
    case EXCLUSIVE = 'exclusive';
}
