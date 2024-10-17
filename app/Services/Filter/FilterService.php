<?php

namespace App\Services\Filter;

use App\Models\Order;
use App\Models\Address;
use App\Traits\Base\BaseTrait;
use App\Enums\FilterResourceType;

class FilterService
{
    use BaseTrait;

    /**
     * Generate filters for a specific resource.
     *
     * @param FilterResourceType $filterResourceType
     * @return array
     */
    public static function getFiltersByResourceType(FilterResourceType $filterResourceType): array
    {
        switch ($filterResourceType) {
            case FilterResourceType::PAYMENT_METHODS:
                return self::getPaymentMethodFilters();
            case FilterResourceType::NOTIFICATIONS:
                return self::getNotificationFilters();
            case FilterResourceType::TRANSACTIONS:
                return self::getTransactionFilters();
            case FilterResourceType::FRIEND_GROUP:
                return self::getFriendGroupFilters();
            case FilterResourceType::ADDRESSES:
                return self::getAddressFilters();
            case FilterResourceType::OCCASIONS:
                return self::getFriendFilters();
            case FilterResourceType::FRIENDS:
                return self::getOccasionFilters();
            case FilterResourceType::PRODUCTS:
                return self::getProductFilters();
            case FilterResourceType::REVIEWS:
                return self::getReviewsFilters();
            case FilterResourceType::COUPONS:
                return self::getCouponFilters();
            case FilterResourceType::STORES:
                return self::getStoreFilters();
            case FilterResourceType::ORDERS:
                return self::getOrderFilters();
            case FilterResourceType::MEDIA:
                return self::getMediaFilters();
            case FilterResourceType::USERS:
                return self::getUserFilters();
            case FilterResourceType::CARTS:
                return self::getCartFilters();
            default:
                return [];
        }
    }

    /**
     * Get filters for payment methods.
     *
     * @return array
     */
    private static function getPaymentMethodFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for notifications.
     *
     * @return array
     */
    private static function getNotificationFilters(): array
    {
        return [
            'type' => [
                'label' => 'Type',
                'type' => 'options',
                'options' => [
                    ['label' => 'All', 'value' => 'all'],
                    ['label' => 'Orders', 'value' => 'orders'],
                    ['label' => 'Followers', 'value' => 'followers'],
                    ['label' => 'Invitations', 'value' => 'invitations'],
                    ['label' => 'Friend Groups', 'value' => 'friend-groups'],
                ],
            ],
            'status' => [
                'label' => 'Status',
                'type' => 'options',
                'options' => [
                    ['label' => 'All', 'value' => 'all'],
                    ['label' => 'Read', 'value' => 'read'],
                    ['label' => 'Unread', 'value' => 'unread'],
                ],
            ],
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for notifications.
     *
     * @return array
     */
    private static function getTransactionFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for friend groups.
     *
     * @return array
     */
    private static function getFriendGroupFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for addresses.
     *
     * @return array
     */
    private static function getAddressFilters(): array
    {
        return [
            'type' => [
                'label' => 'Type',
                'type' => 'options',
                'options' => array_merge(
                    [['label' => 'All', 'value' => 'all']],
                    array_map(fn($type) => ['label' => ucfirst($type), 'value' => strtolower($type)], Address::TYPES())
                ),
            ],
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for friends.
     *
     * @return array
     */
    private static function getFriendFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for occasions.
     *
     * @return array
     */
    private static function getOccasionFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for produts.
     *
     * @return array
     */
    private static function getProductFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for reviews.
     *
     * @return array
     */
    private static function getReviewsFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for coupons.
     *
     * @return array
     */
    private static function getCouponFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for media.
     *
     * @return array
     */
    private static function getMediaFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for users.
     *
     * @return array
     */
    private static function getUserFilters(): array
    {
        return [
            'role' => [
                'label' => 'Role',
                'type' => 'options',
                'options' => [
                    ['label' => 'All', 'value' => 'all'],
                    ['label' => 'User', 'value' => 'user'],
                    ['label' => 'Super Admin', 'value' => 'super-admin'],
                ],
            ],
            'last_seen_at' => [
                'label' => 'Last Seen Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ],
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for carts.
     *
     * @return array
     */
    private static function getCartFilters(): array
    {
        return [
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for stores.
     *
     * @return array
     */
    private static function getStoreFilters(): array
    {
        return [
            'online_status' => [
                'label' => 'Status',
                'type' => 'options',
                'options' => [
                    ['label' => 'All', 'value' => 'all'],
                    ['label' => 'Online', 'value' => 'online'],
                    ['label' => 'Offline', 'value' => 'offline'],
                ]
            ],
            'last_subscription_end_at' => [
                'label' => 'Last Subscription Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ],
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ]
        ];
    }

    /**
     * Get filters for orders.
     *
     * @return array
     */
    private static function getOrderFilters(): array
    {
        return [
            'status' => [
                'label' => 'Status',
                'type' => 'options',
                'options' => array_merge(
                    [['label' => 'All', 'value' => 'all']],
                    array_map(fn($status) => ['label' => $status, 'value' => strtolower($status)], Order::STATUSES())
                ),
            ],
            'payment_status' => [
                'label' => 'Payment Status',
                'type' => 'options',
                'options' => array_merge(
                    [['label' => 'All', 'value' => 'all']],
                    array_map(fn($status) => ['label' => $status, 'value' => strtolower($status)], Order::PAYMENT_STATUSES())
                ),
            ],
            'created_at' => [
                'label' => 'Created Date',
                'type' => 'date',
                'options' => self::getOperatorOptions()
            ],
            'grand_total' => [
                'label' => 'Grand Total',
                'type' => 'money',
                'options' => self::getOperatorOptions()
            ],
        ];
    }

    /**
     * Get operator options.
     *
     * @return array
     */
    private static function getOperatorOptions(): array
    {
        return [
            ['label' => 'Greater or Equal to', 'value' => 'gte'],
            ['label' => 'Less or Equal to', 'value' => 'lte'],
            ['label' => 'Greater than', 'value' => 'gt'],
            ['label' => 'Less than', 'value' => 'lt'],
            ['label' => 'Equal to', 'value' => 'eq'],
            ['label' => 'Not equal to', 'value' => 'neq'],
            ['label' => 'Between (Including)', 'value' => 'bt'],
            ['label' => 'Between (Excluding)', 'value' => 'bt_ex'],
        ];
    }
}
