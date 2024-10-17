<?php

namespace App\Models;

use App\Casts\Money;
use App\Casts\Status;
use App\Casts\Currency;
use App\Casts\JsonToArray;
use App\Traits\ItemLineTrait;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends BaseModel
{
    use HasFactory, ItemLineTrait;

    protected $casts = [
        'is_abandoned' => 'boolean',
        'sub_total' => Money::class,
        'grand_total' => Money::class,
        'delivery_fee' => Money::class,
        'allow_free_delivery' => 'boolean',
        'sale_discount_total' => Money::class,
        'coupon_discount_total' => Money::class,
        'products_arrangement' => JsonToArray::class,
        'delivery_destination' => JsonToArray::class,
        'coupon_and_sale_discount_total' => Money::class,
    ];

    protected $tranformableCasts = [
        'currency' => Currency::class,
        'is_abandoned' => Status::class,
        'has_delivery_fee' => Status::class,
        'allow_free_delivery' => Status::class,
    ];

    /**
     *  Always eager load the product lines and coupon lines
     */
    protected $with = ['productLines', 'couponLines'];

    protected $fillable = [

        /*  Pricing  */
        'currency', 'sub_total', 'coupon_discount_total', 'sale_discount_total',
        'coupon_and_sale_discount_total', 'grand_total',

        /*  Delivery  */
        'allow_free_delivery', 'has_delivery_fee', 'delivery_fee', 'delivery_destination',

        /*  Product Line Totals  */
        'total_products', 'total_product_quantities',
        'total_cancelled_products', 'total_cancelled_product_quantities',
        'total_uncancelled_products', 'total_uncancelled_product_quantities',

        /*  Coupon Line Totals  */
        'total_coupons', 'total_cancelled_coupons', 'total_uncancelled_coupons',

        /*  Changes  */
        'products_arrangement', 'is_abandoned',

        /*  Instant Cart  */
        'instant_cart_id',

        /*  Ownership  */
        'store_id'

    ];

    /****************************
     *  SCOPES                  *
     ***************************/

    /**
     *  Scope carts for a given store
     */
    public function scopeForStore($query, $store)
    {
        return $query->where('store_id', $store instanceof Model ? $store->id : $store);
    }

    /**
     *  Scope carts with product lines and coupon lines
     */
    public function scopeHasSomething($query)
    {
        return $query->has('productLines')->orHas('couponLines');
    }

    /**
     *  Scope carts without product lines and coupon lines
     */
    public function scopeDoesntHaveAnything($query)
    {
        return $query->doesntHaveProductLines()->doesntHaveCouponLines();
    }

    /**
     *  Scope carts that have product lines
     */
    public function scopeHasProductLines($query)
    {
        return $query->has('productLines');
    }

    /**
     *  Scope carts that don't have product lines
     */
    public function scopeDoesntHaveProductLines($query)
    {
        return $query->doesntHave('productLines');
    }

    /**
     *  Scope carts that have coupon lines
     */
    public function scopeHasCouponLines($query)
    {
        return $query->has('couponLines');
    }

    /**
     *  Scope carts that don't have coupon lines
     */
    public function scopeDoesntHaveCouponLines($query)
    {
        return $query->doesntHave('couponLines');
    }

    /****************************
     *  RELATIONSHIPS           *
     ***************************/

    /**
     *  Returns the associated Order
     */
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    /**
     *  Returns the associated Store
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     *  Returns the associated Product Lines
     */
    public function productLines()
    {
        return $this->hasMany(ProductLine::class);
    }

    /**
     *  Returns the associated Coupon Lines
     */
    public function couponLines()
    {
        return $this->hasMany(CouponLine::class);
    }
}
