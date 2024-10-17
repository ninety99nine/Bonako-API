<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Traits\AuthTrait;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;
use App\Http\Resources\CartResources;
use App\Services\Filter\FilterService;

class CartRepository extends BaseRepository
{
    use AuthTrait, BaseTrait;

    /**
     * Show carts.
     *
     * @return CartResources|array
     */
    public function showCarts(): CartResources|array
    {
        if($this->getQuery() == null) {
            if(!$this->isAuthourized()) return ['message' => 'You do not have permission to show carts'];
            $this->setQuery(Cart::query()->latest());
        }

        return $this->applyFiltersOnQuery()->getOrCountResources();
    }

    /**
     * Create cart.
     *
     * @param array $data
     * @return Cart|array
     */
    public function createCart(array $data): Cart|array
    {
        $cart = Cart::create($data);
        $this->createProductAndCouponLines($cart);
        return $this->showCreatedResource($cart);
    }

    /**
     * Show cart.
     *
     * @param Cart|string|null $cartId
     * @return Cart|array|null
     */
    public function showCart(Cart|string|null $cartId = null): Cart|array|null
    {
        if(($cart = $cartId) instanceof Cart) {
            $cart = $this->applyEagerLoadingOnModel($cart);
        }else {
            $query = $this->getQuery() ?? Cart::query();
            if($cartId) $query = $query->where('carts.id', $cartId);
            $this->setQuery($query)->applyEagerLoadingOnQuery();
            $cart = $this->query->first();
        }

        return $this->showResourceExistence($cart);
    }

    /**
     * Update cart.
     *
     * @param Cart|string $cartId
     * @param array $data
     * @return Cart|array
     */
    public function updateCart(Cart|string $cartId, array $data): Cart|array
    {
        $cart = $cartId instanceof Cart ? $cartId : Cart::find($cartId);

        if($cart) {

            $cart->update($data);
            $this->updateProductAndCouponLines($cart);
            return $this->showUpdatedResource($cart);

        }else{
            return ['updated' => false, 'message' => 'This cart does not exist'];
        }
    }

    /***********************************************
     *            MISCELLANEOUS METHODS           *
     **********************************************/

    /**
     * Create product and coupon lines.
     *
     * @param Cart $cart
     * @return CartRepository
     */
    public function createProductAndCouponLines(Cart $cart): CartRepository
    {
        $couponLines = $this->getShoppingCartService()->prepareSpecifiedCouponLinesForDB($cart->id);
        $productLines = $this->getShoppingCartService()->prepareSpecifiedProductLinesForDB($cart->id);
        return $this->createProductLines($cart, $productLines)->createCouponLines($cart, $couponLines);
    }

    /**
     * Create product lines.
     *
     * @param Cart $cart
     * @param array $data
     * @return CartRepository
     */
    public function createProductLines(Cart $cart, $data): CartRepository
    {
        $cart->productLines()->insert($data);
        return $this;
    }

    /**
     * Create coupon lines.
     *
     * @param Cart $cart
     * @param array $data
     * @return CartRepository
     */
    public function createCouponLines(Cart $cart, $data)
    {
        $cart->couponLines()->insert($data);

        return $this;
    }

    /**
     * Update product and coupon lines.
     *
     * @param Cart $cart
     * @return CartRepository
     */
    public function updateProductAndCouponLines(Cart $cart)
    {
        return $this->updateProductLines($cart)->updateCouponLines($cart);
    }

    /**
     * Update product lines.
     *
     * @param Cart $cart
     * @return CartRepository
     */
    public function updateProductLines(Cart $cart)
    {
        //  Get the existing product lines (Saved on the database)
        $existingProductLines = $cart->productLines;

        //  Set the default cancellation reason
        $cancellationReason = 'Removed from the shopping cart';

        //  If we have specified product lines
        if( $this->getShoppingCartService()->totalSpecifiedProductLines ) {

            //  Get the specified product lines (Not saved on the database)
            $specifiedProductLines = $this->getShoppingCartService()->specifiedProductLines;

            /**
             * Split the specified product lines as either existing or new product lines.
             *
             * The [new] represent those that have not been saved to the database
             * at all. These are new product lines that must be created for the
             * first time.
             *
             * The [existing] represent those that have already been saved to the database
             * before. They may or may not have any changes applied such as change in
             * quantities or cancellation status. These product lines must be updated.
             */
            [$existingSpecifiedProductLines, $newSpecifiedProductLines] = collect($specifiedProductLines)->partition(function($specifiedProductLine) use ($existingProductLines) {

                return collect( collect($existingProductLines)->pluck('product_id') )->contains($specifiedProductLine->product_id);

            });

            //  If we have existing specified product lines matching existing database product lines
            if( $existingSpecifiedProductLines->count() ) {

                //  Foreach existing product line (Saved on the database)
                collect($existingProductLines)->each(function($existingProductLine) use ($cart, $cancellationReason) {

                    //  Get the existing specified product line product id
                    $productId = $existingProductLine->product_id;

                    //  Get the specified product line database entry information (one entry)
                    $data = $this->getShoppingCartService()->prepareSpecifiedProductLinesForDB($cart->id, $productId, false);

                    /**
                     * If the data returned is null, then this means that the existing product line
                     * does not match with any of the specified product lines. In this case we must
                     * cancel this existing product line since it is no longer present in the cart.
                     * It has been removed from the cart because of this update.
                     */
                    if( $data === null ) {

                        /**
                         * We could delete these product lines saved in the database as follows:
                         *
                         * $cart->productLines()->delete();
                         *
                         * However this approach does not give anyone any idea of the existence of
                         * these product lines. So instead we could just cancel them with a message
                         * that the items were removed from the cart.
                         */
                        $existingProductLine->cancelItemLine($cancellationReason)->save();

                    }else{

                        //  Lets update it using the information from the existing specified product line
                        $existingProductLine->update($data);

                    }

                });

            }

            //  If we have new specified product lines
            if( $newSpecifiedProductLines->count() ) {

                //  Get the new specified product line product ids
                $productIds = $newSpecifiedProductLines->pluck('product_id')->toArray();

                //  Get the specified product lines database entries information (array of multiple entries)
                $data = $this->getShoppingCartService()->prepareSpecifiedProductLinesForDB($cart->id, $productIds);

                //  Lets create them using the information from the existing specified product lines
                $this->createProductLines($cart, $data);

            }

        //  Otherwise this means that the product lines have been removed
        }else{

            /**
             * We could delete these product lines saved in the database as follows:
             *
             * $cart->productLines()->delete();
             *
             * However this approach does not give anyone any idea of the existence of
             * these product lines. So instead we could just cancel them with a message
             * that the items were removed from the cart.
             */
            collect($existingProductLines)->each(function($existingProductLine) use ($cancellationReason) {
                $existingProductLine->clearDetectedChanges()->clearCancellationReasons()->cancelItemLine($cancellationReason)->save();
            });

        }

        return $this;
    }

    /**
     * Update coupon lines.
     *
     * @param Cart $cart
     * @return CartRepository
     */
    public function updateCouponLines(Cart $cart)
    {
        //  Get the existing coupon lines (Saved on the database)
        $existingCouponLines = $cart->couponLines;

        //  Set the default cancellation reason
        $cancellationReason = 'Removed from the shopping cart';

        //  If we have specified coupon lines
        if( $this->getShoppingCartService()->totalSpecifiedCouponLines ) {

            //  Get the specified coupon lines (Not saved on the database)
            $specifiedCouponLines = $this->getShoppingCartService()->specifiedCouponLines;

            /**
             * Split the specified coupon lines as either existing or new coupon lines.
             *
             * The [new] represent those that have not been saved to the database
             * at all. These are new coupon lines that must be created for the
             * first time.
             *
             * The [existing] represent those that have already been saved to the database
             * before. They may or may not have any changes applied such as change in
             * quantities or cancellation status. These coupon lines must be updated.
             */
            [$existingSpecifiedCouponLines, $newSpecifiedCouponLines] = collect($specifiedCouponLines)->partition(function($specifiedCouponLine) use ($existingCouponLines) {

                return collect( collect($existingCouponLines)->pluck('coupon_id') )->contains($specifiedCouponLine->coupon_id);

            });

            //  If we have existing specified coupon lines matching existing database coupon lines
            if( $existingSpecifiedCouponLines->count() ) {

                //  Foreach existing coupon line (Saved on the database)
                collect($existingCouponLines)->each(function($existingCouponLine) use ($cart, $cancellationReason) {

                    //  Get the existing specified coupon line coupon id
                    $couponId = $existingCouponLine->coupon_id;

                    //  Get the specified coupon line database entry information (one entry)
                    $data = $this->getShoppingCartService()->prepareSpecifiedCouponLinesForDB($cart->id, $couponId, false);

                    /**
                     * If the data returned is null, then this means that the existing coupon line
                     * does not match with any of the specified coupon lines. In this case we must
                     * cancel this existing coupon line since it is no longer present in the cart.
                     * It has been removed from the cart because of this update.
                     */
                    if( $data === null ) {

                        /**
                         * We could delete these coupon lines saved in the database as follows:
                         *
                         * $cart->couponLines()->delete();
                         *
                         * However this approach does not give anyone any idea of the existence of
                         * these coupon lines. So instead we could just cancel them with a message
                         * that the items were removed from the cart.
                         */
                        $existingCouponLine->cancelItemLine($cancellationReason)->save();

                    }else{

                        //  Lets update it using the information from the existing specified coupon line
                        $existingCouponLine->update($data);

                    }

                });

            }

            //  If we have new specified coupon lines
            if( $newSpecifiedCouponLines->count() ) {

                //  Get the new specified coupon line coupon ids
                $couponIds = $newSpecifiedCouponLines->pluck('coupon_id')->toArray();

                //  Get the specified coupon lines database entries information (array of multiple entries)
                $data = $this->getShoppingCartService()->prepareSpecifiedCouponLinesForDB($cart->id, $couponIds);

                //  Lets create them using the information from the existing specified coupon lines
                $this->createCouponLines($cart, $data);

            }

        //  Otherwise this means that the coupon lines have been removed
        }else{

            /**
             * We could delete these coupon lines saved in the database as follows:
             *
             * $cart->couponLines()->delete();
             *
             * However this approach does not give anyone any idea of the existence of
             * these coupon lines. So instead we could just cancel them with a message
             * that the items were removed from the cart.
             */
            collect($existingCouponLines)->each(function($existingCouponLine) use ($cancellationReason) {
                $existingCouponLine->clearDetectedChanges()->clearCancellationReasons()->cancelItemLine($cancellationReason)->save();
            });

        }

        return $this;
    }
}
