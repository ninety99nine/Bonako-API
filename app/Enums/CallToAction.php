<?php

namespace App\Enums;

enum CallToAction:string {
    case BUY = 'buy';
    case ORDER = 'order';
    case PREORDER = 'preorder';
}
