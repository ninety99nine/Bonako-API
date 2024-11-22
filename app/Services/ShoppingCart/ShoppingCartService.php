<?php

namespace App\Services\ShoppingCart;

use stdClass;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Store;
use App\Models\Product;
use App\Enums\CacheName;
use App\Models\Customer;
use App\Models\CouponLine;
use App\Models\ProductLine;
use Illuminate\Support\Str;
use App\Helpers\CacheManager;
use App\Traits\Base\BaseTrait;
use App\Exceptions\CartRequiresStoreException;

/**
 *  Note that the shopping cart service is instantiated once.
 *  The service can only exist as one instance (Singleton).
 *
 *  Refer to our AppServiceProvider
 */
class ShoppingCartService
{
    use BaseTrait;

    public $store;
    public $currency;
    public $existingCart;
    public $subTotal = 0;
    public $grandTotal = 0;
    public $deliveryFee = 0;
    public $relatedProducts;
    public $cartProducts = [];
    public $storeCoupons = [];
    public $deliveryDestination;
    public $cartCouponCodes = [];
    public $detectedChanges = [];
    public $saleDiscountTotal = 0;
    public $couponDiscountTotal = 0;
    public $existingCouponLines = [];
    public $allowFreeDelivery = false;
    public $isExistingCustomer = null;
    public $existingProductLines = [];
    public $specifiedCouponLines = [];
    public $specifiedProductLines = [];
    public $deliveryDestinationName = [];
    public $totalSpecifiedCouponLines = 0;
    public $totalSpecifiedProductLines = 0;
    public $couponAndSaleDiscountTotal = 0;
    public $totalSpecifiedCancelledCouponLines = 0;
    public $totalSpecifiedProductLineQuantities = 0;
    public $totalSpecifiedCancelledProductLines = 0;
    public $totalSpecifiedUnCancelledCouponLines = 0;
    public $totalSpecifiedUnCancelledProductLines = 0;
    public $totalSpecifiedCancelledProductLineQuantities = 0;
    public $totalSpecifiedUncancelledProductLineQuantities = 0;

    /**
     *  Get the shopping cart cache manager
     *
     *  @return CacheManager
     */
    public function getShoppingCartCacheManager()
    {
        return (new CacheManager(CacheName::SHOPPING_CART))->append($this->store->id)->append(request()->auth_user->id);
    }

    /**
     *  Get the "is customer status" cache manager
     *
     *  @return CacheManager
     */
    public function getIsCustomerStatusCacheManager()
    {
        return (new CacheManager(CacheName::IS_CUSTOMER_STATUS))->append($this->store->id)->append(request()->auth_user->id);
    }

    /**
     *  Forget the cache values stored in memory
     *
     *  @return $this
     */
    public function forgetCache()
    {
        //  Forget the shopping cart
        $this->getShoppingCartCacheManager()->forget();

        //  Forget the customer existence status
        $this->getIsCustomerStatusCacheManager()->forget();

        //  Return the current shopping cart service instance
        return $this;
    }

    /**
     *  Start the cart inspection to determine the cart totals
     *  and important cart changes before converting the
     *  current shopping cart into an order
     */
    public function startInspection(Store $store)
    {
        //  Get the shopping store
        $this->store = $store;

        //  Check that the cart shopping store exists
        if( !$this->store ) throw new CartRequiresStoreException;

        //  Get the store coupons
        $this->storeCoupons = $this->store->coupons;

        $customerArray = request()->input('customer');
        $customerEmail = $customerArray['email'] ?? null;
        $customerMobileNumber = $customerArray['mobile_number'] ?? null;

        if($customerMobileNumber) {

            $this->isExistingCustomer = $this->getIsCustomerStatusCacheManager()->remember(now()->addMinutes(10), function () use ($customerMobileNumber) {
                return Customer::searchMobileNumber($customerMobileNumber)->first();
            });

        }else if($customerEmail) {

            $this->isExistingCustomer = $this->getIsCustomerStatusCacheManager()->remember(now()->addMinutes(10), function () use ($customerEmail) {
                return Customer::searchEmail($customerEmail)->first();
            });

        }

        //  Check if the shopping cart exists in memory
        if( $this->getShoppingCartCacheManager()->has() ) {

            //  Get the shopping cart stored in memory (cached)
            $this->existingCart = $this->getShoppingCartCacheManager()->get();

            //  If we have an existing cached cart
            if( $this->existingCart ) {

                //  Get the existing product lines of the cached cart
                $this->existingProductLines = $this->existingCart->productLines;

                //  Get the existing coupon lines of the cached cart
                $this->existingCouponLines = $this->existingCart->couponLines;

            }

        }

        //  Check if the cart has products
        if( request()->filled('cart_products') ) {

            /**
             *  Get the specified cart products. Make sure that the specified cart products are
             *  in array format since the request supports JSON encoded data i.e string data
             */
            $this->cartProducts = is_string($cartProducts = request()->input('cart_products')) ? json_decode($cartProducts) : $cartProducts;

        }

        //  Check if the cart has coupons
        if( request()->filled('cart_coupon_codes') ) {

            /**
             *  Get the specified cart coupon codes. Make sure that the specified cart coupon codes
             *  are in array format since the request supports JSON encoded data i.e string data
             */
            $this->cartCouponCodes = is_string($cartCouponCodes = request()->input('cart_coupon_codes')) ? json_decode($cartCouponCodes) : $cartCouponCodes;

        }

        //  Check if the cart has a destination name
        if( request()->filled('delivery_destination_name') ) {

            //  Get the shopping cart delivery destination name
            $this->deliveryDestinationName = request()->input('delivery_destination_name');

        }










        //  Set the store currency
        $this->currency = $this->store->currency;

        //  Get the shopping cart product lines
        $this->specifiedProductLines = $this->getSpecifiedProductLines();

        //  Detect changes on the product lines
        $this->detectChangesOnProductLines();

        //  Get the specified coupon lines
        $this->specifiedCouponLines = $this->getSpecifiedCouponLines();

        //  Calculate the total product lines
        $this->totalSpecifiedProductLines = $this->countSpecifiedProductLines();

        //  Calculate the total product line quantities
        $this->totalSpecifiedProductLineQuantities = $this->countSpecifiedProductLineQuantities();

        //  Calculate the total cancelled product lines
        $this->totalSpecifiedCancelledProductLines = $this->countSpecifiedCancelledProductLines();

        //  Calculate the total cancelled product lines quantities
        $this->totalSpecifiedCancelledProductLineQuantities = $this->countSpecifiedCancelledProductLineQuantities();

        //  Calculate the total uncancelled product lines
        $this->totalSpecifiedUnCancelledProductLines = $this->countSpecifiedUnCancelledProductLines();

        //  Calculate the total uncancelled product lines quantities
        $this->totalSpecifiedUncancelledProductLineQuantities = $this->countSpecifiedUncancelledProductLineQuantities();

        //  Calculate the total coupon lines
        $this->totalSpecifiedCouponLines = $this->countSpecifiedCouponLines();

        //  Calculate the total cancelled coupon lines
        $this->totalSpecifiedCancelledCouponLines = $this->countSpecifiedCancelledCouponLines();

        //  Calculate the total uncancelled coupon lines
        $this->totalSpecifiedUnCancelledCouponLines = $this->countSpecifiedUnCancelledCouponLines();

        //  Get the matching delivery destination
        $this->deliveryDestination = $this->getDeliveryDestination();

        //  Determine if we can offer free delivery
        $this->allowFreeDelivery = $this->offerFreeDelivery();

        //  Calculate and set the shopping cart totals (Important to apply coupon calculations)
        $this->calculateAndSetPricingTotals();

        //  Return a new shopping cart instance
        $shoppingCart = new Cart([

            /*  Pricing  */
            'currency' => $this->currency,
            'sub_total' => $this->subTotal,
            'grand_total' => $this->grandTotal,
            'sale_discount_total' => $this->saleDiscountTotal,
            'coupon_discount_total' => $this->couponDiscountTotal,
            'coupon_and_sale_discount_total' => $this->couponAndSaleDiscountTotal,

            /*  Delivery  */
            'delivery_fee' => $this->deliveryFee,
            'has_delivery_fee' => $this->deliveryFee > 0,
            'allow_free_delivery' => $this->allowFreeDelivery,
            'delivery_destination' => $this->deliveryDestination,

            /*  Product Line Totals  */
            'total_products' => $this->totalSpecifiedProductLines,
            'total_product_quantities' => $this->totalSpecifiedProductLineQuantities,

            'total_cancelled_products' => $this->totalSpecifiedCancelledProductLines,
            'total_cancelled_product_quantities' => $this->totalSpecifiedCancelledProductLineQuantities,

            'total_uncancelled_products' => $this->totalSpecifiedUnCancelledProductLines,
            'total_uncancelled_product_quantities' => $this->totalSpecifiedUncancelledProductLineQuantities,

            /*  Coupon Line Totals  */
            'total_coupons' => $this->totalSpecifiedCouponLines,
            'total_cancelled_coupons' => $this->totalSpecifiedCancelledCouponLines,
            'total_uncancelled_coupons' => $this->totalSpecifiedUnCancelledCouponLines,

            /*  Customer  */
            'is_existing_customer' => $this->isExistingCustomer,

            /*  Changes  */
            'is_abandoned',

            /*  Instant Cart  */
            'instant_cart_id',

            /*  Ownership  */
            'store_id' => $this->store->id

        ]);

        //  Determine if we have a coupon and sale discount on cart creation
        $hasNewCouponAndSaleDiscountOnFirstRequest = ($this->existingCart == null && $shoppingCart->coupon_and_sale_discount_total->amount > 0);

        //  Determine if we have a coupon and sale discount on cart creation
        $hasNewCouponAndSaleDiscountOnFollowUpRequests = ($this->existingCart != null && ($shoppingCart->coupon_and_sale_discount_total->amount != $this->existingCart->coupon_and_sale_discount_total->amount));

        if($hasNewCouponAndSaleDiscountOnFirstRequest || $hasNewCouponAndSaleDiscountOnFollowUpRequests) {
            $hasNewCouponAndSaleDiscount = true;
        }else{
            $hasNewCouponAndSaleDiscount = false;
        }

        /**
         *  Set whether or not this shopping cart has a new coupon and sale discount.
         *  This could be a discount we received on the first shopping cart request,
         *  or on follow-up requests e.g we didn't have a discount before but now
         *  we do.
         */
        $shoppingCart->has_new_coupon_and_sale_discount = $hasNewCouponAndSaleDiscount;

        /**
         *  Set whether or not this shopping cart has a new coupon and sale discount.
         *  This could be a discount we received on the first shopping cart request,
         *  or on follow-up requests e.g we didn't have a discount before but now
         *  we do.
         */
        $shoppingCart->has_new_coupon_and_sale_discount = $hasNewCouponAndSaleDiscount;

        /**
         *  The detected_changes attribute is not part of the Fillable Array
         *  of the Cart Model since this attribute must never be saved to
         *  the database table.
         *
         *  However for this instance of the Cart Model we would like to
         *  include the detected_changes attribute as part of the Model
         *  fillable array so that this field can be picked up by the
         *  getTransformableFields() method found on the BaseModel
         *  class, which is executed to limit the fields to show
         *  when returning a JSON response of the payload.
         *
         *  ----------------
         *  We no longer need this, since we use getAttributes() instead
         *  of our customer getTransformableFields() method. So please
         *  Remove this line below and this entire comment.
         *
         *  $shoppingCart->mergeFillable(['detected_changes']);
         */

        //  Enable the detected_changes field to be available for this Cart Model
        //  $shoppingCart->mergeFillable(['detected_changes']);

        //  Set the detected_changes field value for this Cart Model
        $shoppingCart->detected_changes = $this->detectedChanges;

        //  Manually set the shopping cart relations
        $shoppingCart->setRelations([
            'productLines' => $this->specifiedProductLines,
            'couponLines' => $this->specifiedCouponLines
        ]);

        //  Cache the shopping cart for exactly 10 minutes
        $this->getShoppingCartCacheManager()->put($shoppingCart, now()->addMinutes(10));

        //  Return the shopping cart
        return $shoppingCart;

    }

    public function getSpecifiedProductLines()
    {
        //  If we have the shopping cart products, then extract the product ids
        $cartProductIds = collect($this->cartProducts)->pluck('id')->toArray();

        //  If we have atleast one shopping cart product id
        if( count($cartProductIds) ) {

            /**
             *  Get the related products that match the specified shopping cart product ids for the given store.
             *  These products must not support variations, but must be a node product. These are products that
             *  have no nested children (variations). This is important since products that support variations
             *  do not have a single price, but support multiple prices via variations.
             */
            $this->relatedProducts = Product::forStore($this->store->id)
                                        ->whereIn('id', $cartProductIds)
                                        ->doesNotSupportVariations()
                                        ->get();


            //  Foreach related product
            return collect($this->relatedProducts)->map(function($relatedProduct) {

                //  Get the related product that matches the given shopping cart product id
                $cartProduct = collect($this->cartProducts)->first(fn($cartProduct) => $relatedProduct->id == $cartProduct['id']);

                //  If we have a related product
                if( $relatedProduct ) {

                    //  Set the quantity otherwise default to "1" (Original quantity before suggested changes)
                    $originalQuantity =  $cartProduct['quantity'] ?? 1;

                    //  Set the available stock quantity
                    $stockQuantity = $relatedProduct->stock_quantity;

                    //  Check the no stock status
                    $noStock = ($relatedProduct->stock_quantity_type == 'limited') &&
                               ($stockQuantity == 0);

                    //  Check the limited stock status
                    $limitedStock = ($relatedProduct->stock_quantity_type == 'limited') &&
                                    ($stockQuantity < $originalQuantity) &&
                                    ($stockQuantity > 0);

                    //  If we have limited stock
                    if( $limitedStock ) {

                        //  Default to available stock quantity
                        $quantity = $stockQuantity;

                    //  If we have stock or we don't have stock
                    }else {

                        /**
                         *  (1) Has Stock
                         *  -------------
                         *
                         *  In this case we can default to the original quantity
                         *  since we have enough stock.
                         *
                         *  (2) No stock
                         *  ------------
                         *
                         *  In this case we will default to the original quantity
                         *  rather than setting the value to Zero (0). This is
                         *  because we can have the original quantity so that
                         *  the pricing information is calculated but then
                         *  we set this product line as cancelled due to
                         *  no stock.
                         *
                         *  This way we can flexibly allow the store users
                         *  to uncancel this product line and process an
                         *  order with exactly what the customer wants.
                         *  This approach is more flexible.
                         */
                        $quantity = $originalQuantity;

                    }

                    //  Set the maximum allowed quantity per order
                    $maximumAllowedQuantityPerOrder = $relatedProduct->maximum_allowed_quantity_per_order;

                    //  Check if we have exceeded the maximum allowed quantity per order
                    $exceededMaximumAllowedQuantityPerOrder = ($relatedProduct->allowed_quantity_per_order == 'limited') &&
                                                              ($quantity > $maximumAllowedQuantityPerOrder);


                    /**
                     *  If we have exceeded the maximum allowed quantity per order,
                     *  then we must reduce the quantity.
                     *  -------------
                     *
                     *  Note that this is simply a precaution incase we do not restrict
                     *  the user to input a quantity less than or equal to the maximum
                     *  allowed quantity per order from the front-end e.g USSD, WEB or
                     *  Mobile App. This is the back-end restriction fallback logic to
                     *  make sure we never allow quantities that exceed that maximum
                     *  set.
                     *
                     *  However it is always better to implement a front-end check
                     *  to restrict the user to input a quantity that is less than
                     *  this maximum figure if the product does not support
                     *  "unlimited" product quantities per order.
                     *
                     *  Remember that the logic above first checks if the stock is
                     *  limited, then makes sure that the quantity does not exceed
                     *  the limited stock before we check if we have exceeded the
                     *  maximum allowed quantity per order. This could have the
                     *  following outcomes:
                     *
                     *  (1) Lets assume that the user wants 30 items of "Product 1" and the current
                     *  stock quantity is 10 while the maximum allowed quantity per order is 20. The
                     *  quantity is first reduced to qualify the available stock (10), thereby also
                     *  qualifying the maximum allowed quantity per order (20). The final result is
                     *  quantity reduced due to limited stock.
                     *
                     *  (2) Lets assume that the user wants 30 items of "Product 1" and the current
                     *  stock quantity is 20 while the maximum allowed quantity per order is 10. The
                     *  quantity is first reduced to qualify the available stock (20), however this
                     *  does not qualify the maximum allowed quantity per order. The quantity is
                     *  therefore reduced again to qualify the maximum allowed quantity per order.
                     *  The final result is quantity reduced due to exceeding the maximum allowed
                     *  quantity per order and not because of limited stock since the quantity
                     *  would been reduced regardless.
                     */
                    if( $exceededMaximumAllowedQuantityPerOrder ) {

                        //  Default to maximum allowed quantity per order
                        $quantity = $maximumAllowedQuantityPerOrder;

                    }

                    //  Set the sub total (based on the unit regular price and quantity)
                    $subTotal = $relatedProduct->unit_regular_price->amount * $quantity;

                    //  Set the sale discount (based on the sale discount and quantity)
                    $saleDiscountTotal = $relatedProduct->unit_sale_discount->amount * $quantity;

                    //  Set the grand total (based on the unit price and quantity)
                    $grandTotal = $relatedProduct->unit_price->amount * $quantity;

                    /**
                     *  Mock the Item Line Model from the related Product Model by collecting related
                     *  information that match the fillable fields of the Item Line Model.
                     *  Then merge additional related information.
                     */
                    return new ProductLine(
                        collect($relatedProduct->getAttributes())->merge([

                            //  Set pricing information (Totals)
                            'sale_discount_total' => $saleDiscountTotal,
                            'grand_total' => $grandTotal,
                            'sub_total' => $subTotal,

                            //  Set quantity information
                            'exceeded_maximum_allowed_quantity_per_order' => $exceededMaximumAllowedQuantityPerOrder,
                            'original_quantity' => $originalQuantity,
                            'quantity' => $quantity,

                            //  Set cancellation status information
                            'is_cancelled' => false,
                            'cancellation_reasons' => [],

                            //  Set detected changes information
                            'detected_changes' => [],

                            'store_id' => $this->store->id,
                            'product_id' => $relatedProduct->id

                        ])->toArray()
                    );

                }

            //  Filter to remove NULL results
            })->filter()->all();

        }

        //  Otherwise return nothing
        return [];
    }

    public function getSpecifiedCouponLines()
    {
        //  If we have atleast one store coupon
        if( count($this->storeCoupons) ) {

            //  Calculate the grand total as it stands before applying any coupons
            $grandTotal = $this->getProductLinePricingTotals()->grandTotal;

            //  Convert to money format
            $grandTotal = $this->convertToMoneyFormat($grandTotal, $this->currency);

            return collect($this->storeCoupons)->map(function($storeCoupon) use ($grandTotal) {

                $inValid = false;
                $isCancelled = false;
                $cancellationReasons = collect([]);

                //  Search for an existing coupon line that matches this store coupon
                $existingCouponLine = collect($this->existingCouponLines)->first(fn($existingCouponLine) => $existingCouponLine->coupon_id == $storeCoupon->id);

                //  If the coupon is not active then don't apply this coupon
                if( !$storeCoupon->active ) {

                    $inValid = true;
                    $cancellationReasons->push('Deactivated by store');

                };

                //  If the coupon activation depends on the coupon code
                if( $storeCoupon->activate_using_code ) {

                    //  If the coupon codes provided do not match the store code then don't apply this coupon
                    if( collect($this->cartCouponCodes)->doesntContain($storeCoupon->code) ) {


                        $inValid = true;
                        $cancellationReasons->push('Required a code for activation but the code provided was invalid');

                    }

                }

                //  If the coupon activation depends on the coupon minimum grand total
                if( $storeCoupon->activate_using_minimum_grand_total ) {

                    $minimumGrandTotal = $storeCoupon->minimum_grand_total;

                    //  If the grand total is less than the minimum total then don't apply this coupon
                    if( $grandTotal->amount < $minimumGrandTotal->amount ) {

                        $inValid = true;

                        $cancellationReasons->push('Required a minimum grand total of '.$minimumGrandTotal->amountWithCurrency.' but the cart total was valued at '.$grandTotal->amountWithCurrency);

                    }

                }

                //  If the coupon activation depends on the coupon minimum products total
                if( $storeCoupon->activate_using_minimum_total_products ) {

                    //  If the uncancelled product line total is less than the minimum products total then don't apply this coupon
                    if( $this->totalSpecifiedUnCancelledProductLines < $storeCoupon->minimum_total_products ) {

                        $inValid = true;

                        $cancellationReasons->push(
                            ('Required a minimum total of '. $storeCoupon->minimum_total_products . ($storeCoupon->minimum_total_products == 1) ? ' unique item ' : ' unique items ') .
                            (', but the cart contained '.$this->totalSpecifiedUnCancelledProductLines . ($this->totalSpecifiedUnCancelledProductLines == 1) ? ' unique item ' : ' unique items ')
                        );

                    }

                }

                //  If the coupon activation depends on the coupon minimum total product quantities
                if( $storeCoupon->activate_using_minimum_total_product_quantities ) {

                    //  If the uncancelled product line quantities total is less than the minimum total product quantities then don't apply this coupon
                    if( $this->totalSpecifiedUncancelledProductLineQuantities < $storeCoupon->minimum_total_product_quantities ) {

                        $inValid = true;

                        $cancellationReasons->push(
                            ('Required a minimum total of '. $storeCoupon->minimum_total_product_quantities . ($storeCoupon->minimum_total_product_quantities == 1) ? ' total quantity ' : ' total quantities ') .
                            (', but the cart contained '.$this->totalSpecifiedUncancelledProductLineQuantities . ($this->totalSpecifiedUncancelledProductLineQuantities == 1) ? ' total quantity ' : ' total quantities ')
                        );

                    }

                }

                //  If the coupon activation depends on the coupon start datetime
                if( $storeCoupon->activate_using_start_datetime ) {

                    //  If the coupon start datetime is in the future then don't apply this coupon
                    if( \Carbon\Carbon::parse($storeCoupon->start_datetime)->isFuture() ) {

                        $inValid = true;

                        $cancellationReasons->push('Starting date was not yet reached');

                    }

                }

                //  If the coupon activation depends on the coupon end datetime
                if( $storeCoupon->activate_using_end_datetime ) {

                    //  If the coupon end datetime is in the past then don't apply this coupon
                    if( \Carbon\Carbon::parse($storeCoupon->end_datetime)->isPast() ) {

                        $inValid = true;

                        $cancellationReasons->push('Ending date was reached');

                    }

                }

                //  If the coupon activation depends on the coupon time (Specific hour of a 24hour day)
                if( $storeCoupon->activate_using_hours_of_day ) {

                    /**
                     *  If the current hour of the day is not present in the coupon
                     *  allowed hours of the day then don't apply this coupon
                     */
                    if( !in_array(Carbon::now()->format('H'), $storeCoupon->hours_of_day) ) {

                        $inValid = true;

                        $cancellationReasons->push('Invalid hour of the day (Activated at specific hours of the day)');

                    }

                }

                //  If the coupon activation depends on the coupon day of the week
                if( $storeCoupon->activate_using_days_of_the_week ) {

                    /**
                     *  If the current day of the week is not present in the coupon
                     *  allowed days of the week then don't apply this coupon
                     */
                    if( !in_array(Carbon::now()->format('l'), $storeCoupon->days_of_the_week) ) {

                        $inValid = true;

                        $cancellationReasons->push('Invalid day of the week (Activated on specific days of the week)');

                    }

                }

                //  If the coupon activation depends on the coupon day of the month
                if( $storeCoupon->activate_using_days_of_the_month ) {

                    /**
                     *  If the current day of the month is not present in the coupon
                     *  allowed days of the month then don't apply this coupon
                     */
                    if( !in_array(Carbon::now()->format('d'), $storeCoupon->days_of_the_month) ) {

                        $inValid = true;

                        $cancellationReasons->push('Invalid day of the month (Activated on specific days of the month)');

                    }

                }

                //  If the coupon activation depends on the coupon month of the year
                if( $storeCoupon->activate_using_months_of_the_year ) {

                    /**
                     *  If the current month of the year is not present in the coupon
                     *  allowed months of the year then don't apply this coupon
                     */
                    if( !in_array(Carbon::now()->format('F'), $storeCoupon->months_of_the_year) ) {

                        $inValid = true;

                        $cancellationReasons->push('Invalid month of the year (Activated on specific months of the year)');

                    }

                }

                //  If the coupon activation depends on the shopper as an new customer
                if( $storeCoupon->activate_for_new_customer ) {

                    //  If the current shopper is an existing customer then don't apply this coupon
                    if( $this->isExistingCustomer == true ) {

                        $inValid = true;

                        $cancellationReasons->push('Must be a new customer');

                    }else if( $this->isExistingCustomer == null) {

                        $inValid = true;

                        $cancellationReasons->push('Cannot determine if this is a new customer. Customer mobile number or email has not been provided');

                    }

                }

                //  If the coupon activation depends on the shopper as an existing customer
                if( $storeCoupon->activate_for_existing_customer ) {

                    //  If the current shopper is not an existing customer then don't apply this coupon
                    if( $this->isExistingCustomer == false ) {

                        $inValid = true;

                        $cancellationReasons->push('Must be an existing customer');

                    }else if( $this->isExistingCustomer == null) {

                        $inValid = true;

                        $cancellationReasons->push('Cannot determine if this is an existing customer. Customer mobile number or email has not been provided');

                    }

                }

                //  If the coupon activation depends on the usage limit
                if( $storeCoupon->activate_using_usage_limit ) {

                    //  If the remaining quantity is zero then don't apply this coupon
                    if( $storeCoupon->remaining_quantity == 0 ) {

                        $inValid = true;

                        $cancellationReasons->push('The usage limit was reached');

                    }

                }

                //  If the coupon is invalid and we don't have an existing coupon line
                if( $inValid == true && !$existingCouponLine ) {

                    //  Return null to exclude this coupon
                    return null;

                }

                /**
                 *  Mock the Coupon Line Model from the Store Coupon Model by collecting related
                 *  information that match the fillable fields of the Coupon Line Model.
                 *  Then merge additional related information.
                 *
                 *  @var CouponLine $couponLine
                 */
                $couponLine = new CouponLine(
                    collect($storeCoupon->getAttributes())->merge([

                        //  Set cancellation status information
                        'is_cancelled' => false,
                        'cancellation_reasons' => [],

                        //  Set detected changes information
                        'detected_changes' => [],

                        'store_id' => $this->store->id,
                        'coupon_id' => $storeCoupon->id

                    ])->toArray()
                );

                //  If we have an existing coupon line
                if( $existingCouponLine ) {

                    /**
                     *  Cancel if any of the following conditions are met:
                     *
                     *  1) If this was not cancelled but is now invalid (We must cancel)
                     *  2) If this was cancelled and is still invalid (We must cancel)
                     */
                    $wasNotCancelledButIsNowInvalid = $existingCouponLine->is_cancelled == false && $inValid == true;
                    $wasCancelledAndIsStillInvalid = $existingCouponLine->is_cancelled == true && $inValid == true;

                    //  If we should cancel
                    if( $wasNotCancelledButIsNowInvalid || $wasCancelledAndIsStillInvalid ) {

                        $message = 'The ('.$storeCoupon->name. ') coupon was cancelled because its no longer valid';
                        $couponLine->recordDetectedChange('cancelled', $message, $existingCouponLine)
                                   ->cancelItemLine($cancellationReasons);

                    //  If we should uncancel
                    }else{

                        $message = 'The ('.$storeCoupon->name. ') coupon was added because its valid again';
                        $couponLine->recordDetectedChange('uncancelled', $message, $existingCouponLine);

                    }

                }

                return $couponLine;

            })->filter()->all();

        }

        //  Otherwise return nothing
        return [];
    }

    /**
     *  Prepare the product lines for database entry
     *  @param int $cartId
     *  @param int|array<int> $productIds
     */
    public function prepareSpecifiedProductLinesForDB($cartId, $productIds = null, $convertToJson = true)
    {
        /**
         *  @param ProductLine $specifiedProductLine
         */
        $collection = collect($this->specifiedProductLines)->map(function($specifiedProductLine) use ($cartId, $productIds, $convertToJson) {

            //  Swap the detected_changes with the detected_changes_history
            $specifiedProductLine->detected_changes = collect($specifiedProductLine->detected_changes_history)->only(['date', 'type', 'message'])->toArray();

            //  Unset the detected_changes_history
            unset($specifiedProductLine->detected_changes_history);

            //  Set the product line id
            $specifiedProductLine->id = Str::uuid();

            //  Set the cart id
            $specifiedProductLine->cart_id = $cartId;

            //  Ready the product line for database insertion
            return $specifiedProductLine->readyForDatabase($convertToJson);

        //  If the product ids specified as an integer or array of integers then we want to extract a specific entry
        })->when(is_array($productIds) || is_int($productIds), function ($specifiedProductLines, $value) use ($productIds) {

            /**
             *  If this is an integer then convert to an array containing the integer.
             *  Its important to know that the mutation of the $productIds does not
             *  change the value of the $productIds passed as a parameter to this
             *  method. We are mutating this value within the current scope only.
             */
            if( is_int($productIds) ) $productIds = [$productIds];

            //  Let us return only the specified product lines that match the given product ids
            return $specifiedProductLines->filter(fn($specifiedProductLine) => (collect($productIds)->contains($specifiedProductLine['product_id'])));

        //  If the product ids specified is a single integer then we want to extract a specific entry
        });

        //  If we expected to return a single result but found no results then return Null
        if( is_int($productIds) && $collection->count() === 0) return null;

        //  If we expected to return a single result, then return an associative array of the first entry
        if( is_int($productIds)) return $collection->first();

        //  Otherwise return the collection as an associative array
        return $collection->toArray();

    }

    /**
     *  Prepare the coupon lines for database entry
     *  @param int $cartId
     *  @param int|array<int> $couponIds
     */
    public function prepareSpecifiedCouponLinesForDB($cartId, $couponIds = null, $convertToJson = true)
    {
        /**
         *  @param CouponLine $specifiedCouponLine
         */
        $collection = collect($this->specifiedCouponLines)->map(function($specifiedCouponLine) use ($cartId, $couponIds, $convertToJson) {

            //  Set the coupon line id
            $specifiedCouponLine->id = Str::uuid();

            //  Set the cart id
            $specifiedCouponLine->cart_id = $cartId;

            //  Ready the coupon line for database insertion
            return $specifiedCouponLine->readyForDatabase($convertToJson);

        //  If the coupon ids specified as an integer or array of integers then we want to extract a specific entry
        })->when(is_array($couponIds) || is_int($couponIds), function ($specifiedCouponLines, $value) use ($couponIds) {

            /**
             *  If this is an integer then convert to an array containing the integer.
             *  Its important to know that the mutation of the $couponIds does not
             *  change the value of the $couponIds passed as a parameter to this
             *  method. We are mutating this value within the current scope only.
             */
            if( is_int($couponIds) ) $couponIds = [$couponIds];

            //  Let us return only the specified coupon lines that match the given coupon ids
            return $specifiedCouponLines->filter(fn($specifiedCouponLine) => (collect($couponIds)->contains($specifiedCouponLine['coupon_id'])));

        //  If the coupon ids specified is a single integer then we want to extract a specific entry
        });

        //  If we expected to return a single result but found no results then return Null
        if( is_int($couponIds) && $collection->count() === 0) return null;

        //  If we expected to return a single result, then return an associative array of the first entry
        if( is_int($couponIds)) return $collection->first();

        //  Otherwise return the collection as an associative array
        return $collection->toArray();

    }

    public function getSpecifiedCancelledProductLines()
    {
        return collect($this->specifiedProductLines)->filter(fn($productLine) => $productLine->is_cancelled)->all();
    }

    public function getSpecifiedUnCancelledProductLines()
    {
        return collect($this->specifiedProductLines)->filter(fn($productLine) => !$productLine->is_cancelled)->all();
    }

    public function countSpecifiedProductLines()
    {
        return collect($this->specifiedProductLines)->count();
    }

    public function countSpecifiedProductLineQuantities()
    {
        return collect($this->specifiedProductLines)->sum('quantity');
    }

    public function countSpecifiedCancelledProductLines()
    {
        return collect($this->getSpecifiedCancelledProductLines())->count();
    }

    public function countSpecifiedCancelledProductLineQuantities()
    {
        return collect($this->getSpecifiedCancelledProductLines())->sum('quantity');
    }

    public function countSpecifiedUnCancelledProductLines()
    {
        return collect($this->getSpecifiedUnCancelledProductLines())->count();
    }

    public function countSpecifiedUncancelledProductLineQuantities()
    {
        return collect($this->getSpecifiedUnCancelledProductLines())->sum('quantity');
    }

    public function getSpecifiedCancelledCouponLines()
    {
        return collect($this->specifiedCouponLines)->filter(fn($couponLine) => $couponLine->is_cancelled)->all();
    }

    public function getSpecifiedUnCancelledCouponLines()
    {
        return collect($this->specifiedCouponLines)->filter(fn($couponLine) => !$couponLine->is_cancelled)->all();
    }

    public function countSpecifiedCouponLines()
    {
        return collect($this->specifiedCouponLines)->count();
    }

    public function countSpecifiedCancelledCouponLines()
    {
        return collect($this->getSpecifiedCancelledCouponLines())->count();
    }

    public function countSpecifiedUnCancelledCouponLines()
    {
        return collect($this->getSpecifiedUnCancelledCouponLines())->count();
    }

    public function getProductLinePricingTotals()
    {
        $obj = new stdClass;
        $obj->subTotal = 0;
        $obj->grandTotal = 0;
        $obj->saleDiscountTotal = 0;

        /**
         *  Apply the totals from the uncancelled product lines collected
         */
        foreach($this->getSpecifiedUnCancelledProductLines() as $productLine) {

            //  Calculate the total excluding sale discounts
            $obj->subTotal += $productLine->sub_total->amount;

            //  Calculate the total including sale discounts
            $obj->grandTotal += $productLine->grand_total->amount;

            //  Calculate the total sale discounts
            $obj->saleDiscountTotal += $productLine->sale_discount_total->amount;

        }

        return $obj;
    }

    public function calculateAndSetPricingTotals()
    {
        $pricing = $this->getProductLinePricingTotals();

        $this->subTotal = $pricing->subTotal;
        $this->grandTotal = $pricing->grandTotal;
        $this->saleDiscountTotal = $pricing->saleDiscountTotal;

        //  Calculate the coupon discount total
        $this->couponDiscountTotal = $this->calculateCouponDiscount();

        //  Apply the coupon discount total
        $this->grandTotal -= $this->couponDiscountTotal;

        //  Calculate the sale and coupon discount total combined
        $this->couponAndSaleDiscountTotal = $this->saleDiscountTotal + $this->couponDiscountTotal;

        /**
         *  If we are not offering free delivery then apply the delivery fee
         *
         *  Note: The delivery fee is applied after the discounts have been
         *  applied to the grand total so that we can avoid discounting the
         *  delivery fee. The delivery fee must be applied as is without
         *  being discounted incase of percetage rate based discounts.
         */
        if( $this->allowFreeDelivery === false) {

            //  Calculate the delivery fee total
            $this->deliveryFee = $this->calculateDeliveryFee()->amount;

            //  Apply the delivery fee total
            $this->grandTotal += $this->deliveryFee;

        }
    }

    /**
     *  Calculate the total coupon discount
     */
    public function calculateCouponDiscount()
    {
        //  Collect coupons that offer discounts
        $couponsOfferingDiscounts = collect($this->specifiedCouponLines)->filter(fn($coupon) => $coupon->offer_discount && $coupon->is_cancelled == false);

        //  Sum the total of the discounts
        $totalCouponDiscount = $couponsOfferingDiscounts->map(function($coupon) {

            if( $coupon->discount_type == 'Percentage' ) {

                return ($coupon->discount_percentage_rate / 100) * $this->grandTotal;

            }elseif( $coupon->discount_type == 'Fixed' ) {

                return $coupon->discount_fixed_rate;

            }else {

                return 0;

            }

        })->sum();

        //  The total coupon discount cannot exceed the grand total
        return $totalCouponDiscount < $this->grandTotal ? $totalCouponDiscount : $this->grandTotal;
    }

    /**
     *  Calculate the delivery free
     */
    public function calculateDeliveryFee()
    {
        //  If the store supports delivery
        if( $this->store->allow_delivery ) {

            //  Return the matching destination delivery fee
            if( $deliveryDestination = $this->getDeliveryDestination() ) return $this->convertToMoneyFormat($deliveryDestination['cost']->amount, $this->store->currency);

            /**
             *  In the case that we could not match any specific delivery destination,
             *  then check if we generally charge a flat fee for any destination.
             */
            if( $this->store->delivery_flat_fee ) return $this->store->delivery_flat_fee;

        }

        //  Otherwise default to "0" as the delivery fee
        return $this->convertToMoneyFormat(0, $this->store->currency);
    }

    /**
     *  Get the matching delivery destination
     */
    public function getDeliveryDestination()
    {
        //  If we provided a specific delivery destination name
        if( $this->deliveryDestinationName ) {

            //  Return the matching delivery destination
            return collect($this->store->delivery_destinations)->first(function($destination) {
                return $destination['name'] == $this->deliveryDestinationName;
            });

        }
    }

    /**
     *  Check if we can offer free delivery
     */
    public function offerFreeDelivery()
    {
        return $this->hasCouponToOfferFreeDelivery() || $this->hasDestinationToOfferFreeDelivery();
    }

    /**
     *  Check if we have a coupon that offers free delivery
     */
    public function hasCouponToOfferFreeDelivery()
    {
        return collect($this->specifiedCouponLines)->contains(fn($coupon) => $coupon->offer_free_delivery && $coupon->is_cancelled == false);
    }

    /**
     *  Check if we have coupons that offer free delivery
     */
    public function hasDestinationToOfferFreeDelivery()
    {
        //  If the store supports delivery
        if( $this->store->allow_delivery ) {

            //  Search the matching destination (Return whether this destination allows free delivery)
            if( $deliveryDestination = $this->getDeliveryDestination() ) return $deliveryDestination['allow_free_delivery'];

            /**
             *  In the case that we could not match any specific delivery destination,
             *  then check if we generally allow free delivery for any destination.
             */
            if( $this->store->allow_free_delivery ) return $this->store->allow_free_delivery;

        }

        //  Otherwise default to false that this store
        return false;
    }

    /**
     *  Detect changes that directly affect this product line
     *  such as changes on price, stock or availability.
     */
    public function detectChangesOnProductLines()
    {
        if( $this->specifiedProductLines ) {

            //  Foreach specified product line
            collect($this->specifiedProductLines)->each(function(ProductLine $specifiedProductLine) {

                /**
                 *  Get the related product of the specified product line
                 *  @var Product $relatedProduct
                 */
                $relatedProduct = collect($this->relatedProducts)->first(fn($relatedProduct) => $relatedProduct->id == $specifiedProductLine->product_id);

                /**
                 *  Get the existing product line of the specified product line
                 *  @var ProductLine $existingProductLine
                 */
                $existingProductLine = collect($this->existingProductLines)->first(fn($existingProductLine) => $existingProductLine->product_id == $specifiedProductLine->product_id);

                /**
                 *  There are two types of changes
                 *
                 *  (1) Changes that do not require comparisons with a cached record of this product line
                 *      i.e We can compare the specified product line against the related product
                 *
                 *  (2) Changes that require comparisons with a cached record of this product
                 *      i.e We can compare the specified product line against the existing
                 *      product line record stored in the cache
                 *
                 *   We will handle these two changes in their respective order
                 */

                /**
                 *  Approach 1: Changes on related product
                 *
                 *  If the specified product line does not have a matching existing product line
                 *  that is recorded in the cache, then we can compare the specified product
                 *  line with the related product for now.
                 */
                $noStock = $relatedProduct->has_stock == false;
                //  $noPrice = $relatedProduct->has_price == false;     Note: Disabled to allow shopping while product unit price is zero (0)
                $limitedStock = ($specifiedProductLine->quantity < $specifiedProductLine->original_quantity) && $specifiedProductLine->exceeded_maximum_allowed_quantity_per_order == false;
                $exceededMaximumAllowedQuantityPerOrder = ($specifiedProductLine->quantity < $specifiedProductLine->original_quantity) && $specifiedProductLine->exceeded_maximum_allowed_quantity_per_order == true;

                //  If the related product does not have stock (Sold out)
                if( $noStock ) {

                    $noStockMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') cancelled because it sold out';
                    $specifiedProductLine->recordDetectedChange('no_stock', $noStockMessage, $existingProductLine)->cancelItemLine($noStockMessage);

                }

                //  If the specified product line has less quantities than intended (Limited Stock)
                if( $limitedStock ) {

                    $limitedStockMessage = $specifiedProductLine->original_quantity.'x('.$specifiedProductLine->name.') reduced to ('.$specifiedProductLine->quantity.') because of limited stock';
                    $specifiedProductLine->recordDetectedChange('limited_stock', $limitedStockMessage, $existingProductLine);

                }

                //  If the specified product line has exceeded the maximum allowed quantity per order
                if( $exceededMaximumAllowedQuantityPerOrder ) {

                    $exceededMaximumAllowedQuantityPerOrderMessage = $specifiedProductLine->original_quantity.'x('.$specifiedProductLine->name.') reduced to ('.$specifiedProductLine->quantity.') because you cannot place more than '. $relatedProduct->maximum_allowed_quantity_per_order . ($relatedProduct->maximum_allowed_quantity_per_order == 1 ? ' quantity' : ' quantities'). ' of this item per order';
                    $specifiedProductLine->recordDetectedChange('exceeded_maximum_allowed_quantity_per_order', $exceededMaximumAllowedQuantityPerOrderMessage, $existingProductLine);

                }

                /**
                 *  If the related product does not have a price
                 *
                 *  Note: Disabled to allow shopping while product unit price is zero (0)
                 */
                 // if( $noPrice ) {
                 //     $noPriceMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') cancelled because it has no price';
                 //     $specifiedProductLine->recordDetectedChange('no_price', $noPriceMessage, $existingProductLine)->cancelItemLine($noPriceMessage);
                 // }

                /**
                 *  Approach 2: Changes on existing product
                 *
                 *  If the specified product line has a matching existing product line
                 *  that is recorded in the database, then we can compare changes on
                 *  the two states.
                 */
                if( $existingProductLine ) {

                    $exceededToNotExceededMaximumAllowedQuantityPerOrder =
                        $existingProductLine->hasDetectedChange('exceeded_maximum_allowed_quantity_per_order') == true
                        && $specifiedProductLine->hasDetectedChange('exceeded_maximum_allowed_quantity_per_order') == false;

                    //  If the product line did not have stock but now we have enough stock
                    $noStockToEnoughStock = $existingProductLine->hasDetectedChange('no_stock') == true
                                            && $specifiedProductLine->hasDetectedChange('no_stock') == false
                                            && $specifiedProductLine->hasDetectedChange('limited_stock') == false;

                    //  If the product line did not have stock but now we have limited stock
                    $noStockToLimitedStock = $existingProductLine->hasDetectedChange('no_stock') == true
                                            && $specifiedProductLine->hasDetectedChange('limited_stock') == true;

                    //  If the product line had limited stock but now we have enough stock
                    $limitedStockToEnoughStock = $existingProductLine->hasDetectedChange('limited_stock') == true
                                                && $specifiedProductLine->hasDetectedChange('no_stock') == false
                                                && $specifiedProductLine->hasDetectedChange('limited_stock') == false;

                    if( $exceededToNotExceededMaximumAllowedQuantityPerOrder ) {

                        $exceededToNotExceededMaximumAllowedQuantityPerOrderMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') added because larger quantities are now permitted for this item';
                        $specifiedProductLine->recordDetectedChange('exceeded_to_not_exceeded_maximum_allowed_quantity_per_order', $exceededToNotExceededMaximumAllowedQuantityPerOrderMessage, $existingProductLine);

                    }elseif( $noStockToEnoughStock ) {

                        $noStockToEnoughStockMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') added because of new stock';
                        $specifiedProductLine->recordDetectedChange('no_stock_to_enough_stock', $noStockToEnoughStockMessage, $existingProductLine);

                    }elseif( $noStockToLimitedStock ) {

                        $noStockToLimitedStockMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') added because of new stock';
                        $specifiedProductLine->recordDetectedChange('no_stock_to_limited_stock', $noStockToLimitedStockMessage, $existingProductLine);

                    }elseif( $limitedStockToEnoughStock ) {

                        $limitedStockToLimitedStockMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') added because of new stock';
                        $specifiedProductLine->recordDetectedChange('limited_stock_to_enough_stock', $limitedStockToLimitedStockMessage, $existingProductLine);

                    }

                    //  If the product line was free but is not free anymore
                    $freeToNotFree = $existingProductLine->is_free && !$specifiedProductLine->is_free;

                    //  If the product line was not free but is not free
                    $notFreeToFree = !$existingProductLine->is_free && $specifiedProductLine->is_free;

                    /**
                     *  If the product line did not have a price but now has a new price
                     *
                     *  Note: Disabled to allow shopping while product unit price is zero (0)
                     */
                    //  $noPriceToNewPrice = $existingProductLine->hasDetectedChange('no_price') == true
                    //                       && $specifiedProductLine->hasDetectedChange('no_price') == false;

                    //  If the product line did have a price but now the price changed
                    $oldPriceToNewPrice = $existingProductLine->unit_price != $specifiedProductLine->unit_price;

                    //  Get the existing product line unit price
                    $existingProductLineUnitPrice = $existingProductLine->unit_price->amountWithCurrency;

                    //  Get the specified product line unit price
                    $specifiedProductLineUnitPrice = $specifiedProductLine->unit_price->amountWithCurrency;

                    if( $freeToNotFree ){

                        $freeToNotFreeMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') added with new price '.$specifiedProductLineUnitPrice.' each';
                        $specifiedProductLine->recordDetectedChange('free_to_not_free', $freeToNotFreeMessage, $existingProductLine);

                    }elseif( $notFreeToFree ){

                        $notFreeToFreeMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') is now free';
                        $specifiedProductLine->recordDetectedChange('not_free_to_free', $notFreeToFreeMessage, $existingProductLine);

                    /**
                     *  Note: Disabled to allow shopping without being notified that the product unit price is zero (0)
                     */
                    //  }elseif( $noPriceToNewPrice ) {
                    //      $noPriceToNewPriceMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') added with new price '.$specifiedProductLineUnitPrice.' each (Not Free)';
                    //      $specifiedProductLine->recordDetectedChange('no_price_to_new_price', $noPriceToNewPriceMessage, $existingProductLine);

                    }elseif( $oldPriceToNewPrice ) {

                        $inflation = $specifiedProductLine->unit_price > $existingProductLine->unit_price ? 'increased' : 'reduced';

                        $oldPriceToNewPriceMessage = $specifiedProductLine->quantity.'x('.$specifiedProductLine->name.') price '.$inflation.' from '.$existingProductLineUnitPrice .' to '.$specifiedProductLineUnitPrice.' each';

                        //  If the existing product line was not on sale but the sale started
                        if( !$existingProductLine->on_sale && $specifiedProductLine->on_sale ) {

                            $oldPriceToNewPriceMessage .= ' (On sale)';

                            if( $inflation == 'increased' ){

                                $changeType = 'old_price_to_new_price_increase_with_sale';

                            }else{

                                $changeType = 'old_price_to_new_price_decrease_with_sale';

                            }

                        //  If the existing product line was on sale but the sale ended
                        }elseif( $existingProductLine->on_sale && !$specifiedProductLine->on_sale ) {

                            $oldPriceToNewPriceMessage .= ' (Sale ended)';

                            if( $inflation == 'increased' ) {

                                $changeType = 'old_price_to_new_price_increase_without_sale';

                            }else{

                                $changeType = 'old_price_to_new_price_decrease_without_sale';

                            }

                        }else{

                            if( $inflation == 'increased' ) {

                                $changeType = 'old_price_to_new_price_increase';

                            }else{

                                $changeType = 'old_price_to_new_price_decrease';

                            }

                        }

                        $specifiedProductLine->recordDetectedChange($changeType, $oldPriceToNewPriceMessage, $existingProductLine);

                    }

                    //  If the product line name changed
                    $nameChanged = $existingProductLine->name !== $specifiedProductLine->name;

                    if( $nameChanged ) {

                        $nameChangedMessage = $existingProductLine->name.' renamed to '.$specifiedProductLine->name;
                        $specifiedProductLine->recordDetectedChange('name_changed', $nameChangedMessage, $existingProductLine);

                    }

                    //  If the product line visibility changed
                    $visibleToNotVisible = $existingProductLine->visible && !$specifiedProductLine->visible;
                    $invisibleToVisible = !$existingProductLine->visible && $specifiedProductLine->visible;

                    if( $visibleToNotVisible ) {

                        $visibleToNotVisibleMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') cancelled because it was removed from the shelf';
                        $specifiedProductLine->recordDetectedChange('not_visible', $visibleToNotVisibleMessage, $existingProductLine)->cancelItemLine($visibleToNotVisibleMessage);

                    }else if( $invisibleToVisible ) {

                        $invisibleToVisibleMessage = $specifiedProductLine->quantity.'x('.$relatedProduct->name.') added because it was placed on the shelf';
                        $specifiedProductLine->recordDetectedChange('visible', $invisibleToVisibleMessage, $existingProductLine);

                    }

                }

                /**
                 *  Capture the history of every detected change since the first request to the current request.
                 *  Simply capture the detected changes where the user has not been notified (recently detected
                 *  change). Detected changes where the user has already been notified should not be captured
                 *  as part of the detected change history since they are older detected changes that have
                 *  already been captured as part of the history at the moment of their first occurance.
                 *
                 *  The detected_changes_history is not a fillable field on the ProductLine and therefore
                 *  cannot be saved on the database. Refer to the prepareSpecifiedProductLinesForDB() to
                 *  see how we swap the detected_changes for the detected_changes_history before we
                 *  remove the detected_changes_history attribute before saving each ProductLine.
                 *  This is done so that we can detected_changes_history can be saved as the
                 *  detected_changes on the database.
                 *
                 *  The temporary separation of the detected_changes and detected_changes_history allows the
                 *  recordDetectedChange() method to detect current changes in comparison with that last
                 *  request changes "$existingProductLine->detected_changes" instead of comparing with
                 *  all the request changes ($existingProductLine->detected_changes_history).
                 */
                $specifiedProductLine->detected_changes_history = collect($existingProductLine->detected_changes_history ?? [])->merge(
                    collect($specifiedProductLine->detected_changes)->filter(function($detected_change) {
                        return $detected_change['notified_user'] === false;
                    })
                )->toArray();

                //  Capture the detected changes to share with the shopping cart
                $this->detectedChanges = collect($this->detectedChanges)->merge($specifiedProductLine->detected_changes)->all();
            });

        }
    }

}
