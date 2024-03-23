<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Currency;
use App\Models\Base\BasePivot;
use App\Models\Pivots\UserStoreAssociation;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserStoreOrderStatistic extends BasePivot
{
    use HasFactory;

    protected $casts = [
        'sub_total_requested' => Money::class,
        'coupon_discount_total_requested' => Money::class,
        'sale_discount_total_requested' => Money::class,
        'coupon_and_sale_discount_total_requested' => Money::class,
        'grand_total_requested' => Money::class,

        'avg_sub_total_requested' => Money::class,
        'avg_coupon_discount_total_requested' => Money::class,
        'avg_sale_discount_total_requested' => Money::class,
        'avg_coupon_and_sale_discount_total_requested' => Money::class,
        'avg_grand_total_requested' => Money::class,

        'sub_total_received' => Money::class,
        'coupon_discount_total_received' => Money::class,
        'sale_discount_total_received' => Money::class,
        'coupon_and_sale_discount_total_received' => Money::class,
        'grand_total_received' => Money::class,

        'avg_sub_total_received' => Money::class,
        'avg_coupon_discount_total_received' => Money::class,
        'avg_sale_discount_total_received' => Money::class,
        'avg_coupon_and_sale_discount_total_received' => Money::class,
        'avg_grand_total_received' => Money::class,

        'sub_total_cancelled' => Money::class,
        'coupon_discount_total_cancelled' => Money::class,
        'sale_discount_total_cancelled' => Money::class,
        'coupon_and_sale_discount_total_cancelled' => Money::class,
        'grand_total_cancelled' => Money::class,

        'avg_sub_total_cancelled' => Money::class,
        'avg_coupon_discount_total_cancelled' => Money::class,
        'avg_sale_discount_total_cancelled' => Money::class,
        'avg_coupon_and_sale_discount_total_cancelled' => Money::class,
        'avg_grand_total_cancelled' => Money::class
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class
    ];

    protected $fillable = [

        'currency',

        //  Order Totals (Requested)
        'total_orders_requested',
        'sub_total_requested',
        'coupon_discount_total_requested',
        'sale_discount_total_requested',
        'coupon_and_sale_discount_total_requested',
        'grand_total_requested',
        'total_products_requested',
        'total_product_quantities_requested',
        'total_coupons_requested',

        'avg_sub_total_requested',
        'avg_coupon_discount_total_requested',
        'avg_sale_discount_total_requested',
        'avg_coupon_and_sale_discount_total_requested',
        'avg_grand_total_requested',
        'avg_total_products_requested',
        'avg_total_product_quantities_requested',
        'avg_total_coupons_requested',

        //  Order Totals (Completed)
        'total_orders_received',
        'sub_total_received',
        'coupon_discount_total_received',
        'sale_discount_total_received',
        'coupon_and_sale_discount_total_received',
        'grand_total_received',
        'total_products_received',
        'total_product_quantities_received',
        'total_coupons_received',

        'avg_sub_total_received',
        'avg_coupon_discount_total_received',
        'avg_sale_discount_total_received',
        'avg_coupon_and_sale_discount_total_received',
        'avg_grand_total_received',
        'avg_total_products_received',
        'avg_total_product_quantities_received',
        'avg_total_coupons_received',

        //  Order Totals (Cancelled)
        'total_orders_cancelled',
        'sub_total_cancelled',
        'coupon_discount_total_cancelled',
        'sale_discount_total_cancelled',
        'coupon_and_sale_discount_total_cancelled',
        'grand_total_cancelled',
        'total_products_cancelled',
        'total_product_quantities_cancelled',
        'total_coupons_cancelled',

        'avg_sub_total_cancelled',
        'avg_coupon_discount_total_cancelled',
        'avg_sale_discount_total_cancelled',
        'avg_coupon_and_sale_discount_total_cancelled',
        'avg_grand_total_cancelled',
        'avg_total_products_cancelled',
        'avg_total_product_quantities_cancelled',
        'avg_total_coupons_cancelled',

        'user_store_association_id',

        /*  Timestamps  */
        'created_at',
        'updated_at'
    ];

    /**
     *  Returns the user store association
     *
     *  @return Illuminate\Database\Eloquent\Concerns\HasRelationships::belongsTo
     */
    public function userStoreAssociation()
    {
        return $this->belongsTo(UserStoreAssociation::class, 'user_store_association_id');
    }
}
