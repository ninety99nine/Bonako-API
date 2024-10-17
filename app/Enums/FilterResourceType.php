<?php

namespace App\Enums;

enum FilterResourceType: string
{
    case DELIVERY_ADDRESSES = 'delivery addresses';
    case PAYMENT_METHODS = 'payment methods';
    case NOTIFICATIONS = 'notifications';
    case TRANSACTIONS = 'transactions';
    case FRIEND_GROUP = 'friend group';
    case OCCASIONS = 'occasions';
    case ADDRESSES = 'addresses';
    case CUSTOMER = 'customer';
    case PRODUCTS = 'products';
    case FRIENDS = 'friends';
    case REVIEWS = 'reviews';
    case COUPONS = 'coupons';
    case STORES = 'stores';
    case ORDERS = 'orders';
    case MEDIA = 'media';
    case USERS = 'users';
    case CARTS = 'carts';
}
