<?php

namespace App\Enums;

enum OrderCollectionType:string {
    case DELIVERY = 'delivery';
    case PICKUP = 'pickup';
}
