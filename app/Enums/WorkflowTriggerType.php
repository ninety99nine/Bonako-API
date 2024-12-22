<?php

namespace App\Enums;

enum WorkflowTriggerType:string {

    /** Order Status */
    case WAITING = 'waiting';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case ON_ITS_WAY = 'on its way';
    case READY_FOR_PICKUP = 'ready for pickup';

    /** Order Payment Status */
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case PARTIALLY_PAID = 'partially paid';
    case PENDING_PAYMENT = 'pending payment';

    /** Inventory */
    case LOW_STOCK = 'low stock';
}
