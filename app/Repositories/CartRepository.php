<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Repositories\BaseRepository;
use App\Repositories\OrderRepository;
use App\Services\ShoppingCart\ShoppingCartService;

class CartRepository extends BaseRepository
{
    protected $requiresConfirmationBeforeDelete = true;

    /**
     *  Return the ShoppingCartService instance
     *
     *  @return ShoppingCartService
     */
    public function shoppingCartService()
    {
        return resolve(ShoppingCartService::class);
    }

    /**
     *  Show the cart while eager loading any required relationships
     *
     *  @return CartRepository
     */
    public function showCart()
    {
        /**
         *  @var Cart $cart
         */
        $cart = $this->model;

        //  Eager load the cart relationships based on request inputs
        return $this->eagerLoadCartRelationships($cart);
    }

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return OrderRepository
     */
    public function eagerLoadCartRelationships($model) {

        $relationships = [];
        $countableRelationships = [];

        //  Check if we want to eager load the product lines on this cart
        if( request()->input('with_product_lines') ) {

            //  Additionally we can eager load the product lines on this cart
            array_push($relationships, 'productLines');

        }

        //  Check if we want to eager load the coupon lines on this cart
        if( request()->input('with_coupon_lines') ) {

            //  Additionally we can eager load the coupon lines on this cart
            array_push($relationships, 'couponLines');

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof Cart)
                ? $model->load($relationships)->loadCount($countableRelationships)
                : $model->with($relationships)->withCount($countableRelationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Create the cart product lines and coupon lines for the cart.
     *
     *  @return CartRepository
     */
    public function createProductAndCouponLines()
    {
        return $this->createProductLines()->createCouponLines();
    }

    /**
     *  Create the cart product lines for the cart
     *
     *  @param array $data An array of column names and values to create new product lines for this cart repository model
     *
     *  @return CartRepository
     */
    public function createProductLines($data = [])
    {
        $hasData = count($data);

        $this->model->productLines()->insert(
            $hasData ? $data : $this->shoppingCartService()->prepareSpecifiedProductLinesForDB($this->model->id)
        );

        return $this;
    }

    /**
     *  Create the cart coupon lines for the cart
     *
     *  @param array $data An array of column names and values to create new coupon lines for this cart repository model
     *
     *  @return CartRepository
     */
    public function createCouponLines($data = [])
    {
        $hasData = count($data);

        $this->model->couponLines()->insert(
            $hasData ? $data : $this->shoppingCartService()->prepareSpecifiedCouponLinesForDB($this->model->id)
        );

        return $this;
    }

    /**
     *  Update the cart product lines and coupon lines for the cart.
     *
     *  @return CartRepository
     */
    public function updateProductAndCouponLines()
    {
        return $this->updateProductLines()->updateCouponLines();
    }

    /**
     *  Update the cart product lines for the cart
     *
     *  @return CartRepository
     */
    public function updateProductLines()
    {
        //  Get the existing product lines (Saved on the database)
        $existingProductLines = $this->model->productLines;

        //  Set the default cancellation reason
        $cancellationReason = 'Removed from the shopping cart';

        //  If we have specified product lines
        if( $this->shoppingCartService()->totalSpecifiedProductLines ) {

            //  Get the specified product lines (Not saved on the database)
            $specifiedProductLines = $this->shoppingCartService()->specifiedProductLines;

            /**
             *  Split the specified product lines as either existing or new product lines.
             *
             *  The [new] represent those that have not been saved to the database
             *  at all. These are new product lines that must be created for the
             *  first time.
             *
             *  The [existing] represent those that have already been saved to the database
             *  before. They may or may not have any changes applied such as change in
             *  quantities or cancellation status. These product lines must be updated.
             */
            [$existingSpecifiedProductLines, $newSpecifiedProductLines] = collect($specifiedProductLines)->partition(function($specifiedProductLine) use ($existingProductLines) {

                return collect( collect($existingProductLines)->pluck('product_id') )->contains($specifiedProductLine->product_id);

            });

            //  If we have existing specified product lines matching existing database product lines
            if( $existingSpecifiedProductLines->count() ) {

                //  Foreach existing product line (Saved on the database)
                collect($existingProductLines)->each(function($existingProductLine) use ($cancellationReason) {

                    //  Get the existing specified product line product id
                    $productId = $existingProductLine->product_id;

                    //  Get the specified product line database entry information (one entry)
                    $data = $this->shoppingCartService()->prepareSpecifiedProductLinesForDB($this->model->id, $productId, false);

                    /**
                     *  If the data returned is null, then this means that the existing product line
                     *  does not match with any of the specified product lines. In this case we must
                     *  cancel this existing product line since it is no longer present in the cart.
                     *  It has been removed from the cart because of this update.
                     */
                    if( $data === null ) {

                        /**
                         *  We could delete these product lines saved in the database as follows:
                         *
                         *  $this->model->productLines()->delete();
                         *
                         *  However this approach does not give anyone any idea of the existence of
                         *  these product lines. So instead we could just cancel them with a message
                         *  that the items were removed from the cart.
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
                $data = $this->shoppingCartService()->prepareSpecifiedProductLinesForDB($this->model->id, $productIds);

                //  Lets create them using the information from the existing specified product lines
                $this->createProductLines($data);

            }

        //  Otherwise this means that the product lines have been removed
        }else{

            /**
             *  We could delete these product lines saved in the database as follows:
             *
             *  $this->model->productLines()->delete();
             *
             *  However this approach does not give anyone any idea of the existence of
             *  these product lines. So instead we could just cancel them with a message
             *  that the items were removed from the cart.
             */
            collect($existingProductLines)->each(function($existingProductLine) use ($cancellationReason) {
                $existingProductLine->clearDetectedChanges()->clearCancellationReasons()->cancelItemLine($cancellationReason)->save();
            });

        }

        return $this;
    }

    /**
     *  Update the cart coupon lines for the cart
     *
     *  @return CartRepository
     */
    public function updateCouponLines()
    {
        //  Get the existing coupon lines (Saved on the database)
        $existingProductLines = $this->model->couponLines;

        //  Set the default cancellation reason
        $cancellationReason = 'Removed from the shopping cart';

        //  If we have specified coupon lines
        if( $this->shoppingCartService()->totalSpecifiedProductLines ) {

            //  Get the specified coupon lines (Not saved on the database)
            $specifiedProductLines = $this->shoppingCartService()->specifiedProductLines;

            /**
             *  Split the specified coupon lines as either existing or new coupon lines.
             *
             *  The [new] represent those that have not been saved to the database
             *  at all. These are new coupon lines that must be created for the
             *  first time.
             *
             *  The [existing] represent those that have already been saved to the database
             *  before. They may or may not have any changes applied such as change in
             *  quantities or cancellation status. These coupon lines must be updated.
             */
            [$existingSpecifiedProductLines, $newSpecifiedProductLines] = collect($specifiedProductLines)->partition(function($specifiedProductLine) use ($existingProductLines) {

                return collect( collect($existingProductLines)->pluck('coupon_id') )->contains($specifiedProductLine->coupon_id);

            });

            //  If we have existing specified coupon lines matching existing database coupon lines
            if( $existingSpecifiedProductLines->count() ) {

                //  Foreach existing coupon line (Saved on the database)
                collect($existingProductLines)->each(function($existingProductLine) use ($cancellationReason) {

                    //  Get the existing specified coupon line coupon id
                    $couponId = $existingProductLine->coupon_id;

                    //  Get the specified coupon line database entry information (one entry)
                    $data = $this->shoppingCartService()->prepareSpecifiedProductLinesForDB($this->model->id, $couponId, false);

                    /**
                     *  If the data returned is null, then this means that the existing coupon line
                     *  does not match with any of the specified coupon lines. In this case we must
                     *  cancel this existing coupon line since it is no longer present in the cart.
                     *  It has been removed from the cart because of this update.
                     */
                    if( $data === null ) {

                        /**
                         *  We could delete these coupon lines saved in the database as follows:
                         *
                         *  $this->model->couponLines()->delete();
                         *
                         *  However this approach does not give anyone any idea of the existence of
                         *  these coupon lines. So instead we could just cancel them with a message
                         *  that the items were removed from the cart.
                         */
                        $existingProductLine->cancelItemLine($cancellationReason)->save();

                    }else{

                        //  Lets update it using the information from the existing specified coupon line
                        $existingProductLine->update($data);

                    }

                });

            }

            //  If we have new specified coupon lines
            if( $newSpecifiedProductLines->count() ) {

                //  Get the new specified coupon line coupon ids
                $couponIds = $newSpecifiedProductLines->pluck('coupon_id')->toArray();

                //  Get the specified coupon lines database entries information (array of multiple entries)
                $data = $this->shoppingCartService()->prepareSpecifiedProductLinesForDB($this->model->id, $couponIds);

                //  Lets create them using the information from the existing specified coupon lines
                $this->createProductLines($data);

            }

        //  Otherwise this means that the coupon lines have been removed
        }else{

            /**
             *  We could delete these coupon lines saved in the database as follows:
             *
             *  $this->model->couponLines()->delete();
             *
             *  However this approach does not give anyone any idea of the existence of
             *  these coupon lines. So instead we could just cancel them with a message
             *  that the items were removed from the cart.
             */
            collect($existingProductLines)->each(function($existingProductLine) use ($cancellationReason) {
                $existingProductLine->clearDetectedChanges()->clearCancellationReasons()->cancelItemLine($cancellationReason)->save();
            });

        }

        return $this;
    }
}
