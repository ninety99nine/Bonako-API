<?php

namespace App\Enums;

enum OrderCancellationReason:string {
    case OUT_OF_STOCK = 'out of stock';
    case CUSTOMER_CANCELLED = 'customer cancelled';
    case PAYMENT_FAILED = 'payment failed';
    case CUSTOMER_REQUEST = 'customer request';
    case CUSTOMER_UNREACHABLE = 'customer unreachable';
    case FRAUDULENT = 'fraudulent';
    case ITEM_UNAVAILABLE = 'item unavailable';
    case DELIVERY_UNAVAILABLE = 'delivery unavailable';
    case PICKUP_UNAVAILABLE = 'pickup unavailable';
    case ORDER_CHANGED = 'order changed';
    case EXCESSIVE_QUANTITY = 'excessive quantity';
    case DUPLICATE_ORDER = 'duplicate order';
    case INSUFFICIENT_FUNDS = 'insufficient funds';
    case PRODUCT_DEFECT = 'product defect';
    case ADDRESS_ISSUE = 'address issue';
    case POLICY_VIOLATION = 'policy violation';
    case TECHNICAL_ERROR = 'technical error';
    case OTHER = 'other';
}
