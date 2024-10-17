<?php

namespace App\Enums;

enum ReviewSubject:string {
    case PRODUCT = 'product';
    case PAYMENT = 'payment';
    case DELIVERY = 'delivery';
    case CUSTOMER_SERVICE = 'customer service';
}
