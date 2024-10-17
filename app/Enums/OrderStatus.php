<?php

namespace App\Enums;

enum OrderStatus:string {
    case WAITING = 'waiting';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case ON_ITS_WAY = 'on its way';
    case READY_FOR_PICKUP = 'ready for pickup';
}
