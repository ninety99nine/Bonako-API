<?php

namespace App\Enums;

enum CacheName:string {
    case GUEST_USER = 'GUEST_USER';
    case GUEST_USER_ID = 'GUEST_USER_ID';
    case SHOPPING_CART = 'SHOPPING_CART';
    case SUBSCRIPTIONS = 'SUBSCRIPTIONS';
    case PRICING_PLANS = 'PRICING_PLANS';
    case ACCOUNT_EXISTS = 'ACCOUNT_EXISTS';
    case PAYMENT_METHODS = 'PAYMENT_METHODS';
    case IS_CUSTOMER_STATUS = 'IS_CUSTOMER_STATUS';
    case FRIEND_GROUP_USERS = 'FRIEND_GROUP_USERS';
    case STORE_TEAM_MEMBERS = 'STORE_TEAM_MEMBERS';
    case HAS_STORE_PERMISSION = 'HAS_STORE_PERMISSION';
    case AUTH_USER_ON_REQUEST = 'AUTH_USER_ON_REQUEST';
    case DELETE_CONFIRMATION_CODE = 'DELETE_CONFIRMATION_CODE';
    case AUTH_USER_ON_REQUEST_BEARER_TOKEN = 'AUTH_USER_ON_REQUEST_BEARER_TOKEN';
    case AIRTIME_BILLING_ACCESS_TOKEN_RESPONSE = 'AIRTIME_BILLING_ACCESS_TOKEN_RESPONSE';

    case TOTAL_REVIEWS = 'TOTAL_REVIEWS';
    case TOTAL_GROUPS_JOINED = 'TOTAL_GROUPS_JOINED';
    case TOTAL_NOTIFICATIONS = 'TOTAL_NOTIFICATIONS';
    case TOTAL_STORES_AS_CUSTOMER = 'TOTAL_STORES_AS_CUSTOMER';
    case TOTAL_STORES_AS_FOLLOWER = 'TOTAL_STORES_AS_FOLLOWER';
    case TOTAL_ORDERS_AS_CUSTOMER = 'TOTAL_ORDERS_AS_CUSTOMER';
    case TOTAL_UNREAD_NOTIFICATIONS = 'TOTAL_UNREAD_NOTIFICATIONS';
    case TOTAL_ORDERS_AS_TEAM_MEMBER = 'TOTAL_ORDERS_AS_TEAM_MEMBER';
    case TOTAL_REVIEWS_AS_TEAM_MEMBER = 'TOTAL_REVIEWS_AS_TEAM_MEMBER';
    case TOTAL_STORES_JOINED_AS_CREATOR = 'TOTAL_STORES_JOINED_AS_CREATOR';
    case TOTAL_STORES_AS_RECENT_VISITOR = 'TOTAL_STORES_AS_RECENT_VISITOR';
    case TOTAL_GROUPS_JOINED_AS_CREATOR = 'TOTAL_GROUPS_JOINED_AS_CREATOR';
    case TOTAL_STORES_JOINED_AS_TEAM_MEMBER = 'TOTAL_STORES_JOINED_AS_TEAM_MEMBER';
    case TOTAL_GROUPS_JOINED_AS_NON_CREATOR = 'TOTAL_GROUPS_JOINED_AS_NON_CREATOR';
    case TOTAL_STORES_JOINED_AS_NON_CREATOR = 'TOTAL_STORES_JOINED_AS_NON_CREATOR';
    case TOTAL_STORES_WITH_AN_ACTIVE_SUBSCRIPTION = 'TOTAL_STORES_WITH_AN_ACTIVE_SUBSCRIPTION';
    case TOTAL_STORES_INVITED_TO_JOIN_AS_TEAM_MEMBER = 'TOTAL_STORES_INVITED_TO_JOIN_AS_TEAM_MEMBER';
    case TOTAL_GROUPS_INVITED_TO_JOIN_AS_GROUP_MEMBER = 'TOTAL_GROUPS_INVITED_TO_JOIN_AS_GROUP_MEMBER';
}
