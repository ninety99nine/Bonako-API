<?php

namespace App\Enums;

enum AllowedQuantityPerOrder:string {
    case LIMITED = 'limited';
    case UNLIMITED = 'unlimited';
}
