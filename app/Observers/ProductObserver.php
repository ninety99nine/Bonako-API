<?php

namespace App\Observers;

use App\Models\Store;
use App\Models\Product;
use App\Repositories\ProductRepository;

class ProductObserver
{
    /**
     *  Return the ProductRepository instance
     *
     *  @return ProductRepository
     */
    public function productRepository()
    {
        return resolve(ProductRepository::class);
    }

    /**
     *  The saving event will dispatch when a model is creating or updating
     *  the model even if the model's attributes have not been changed.
     *
     *  Refererence: https://laravel.com/docs/9.x/eloquent#events
     */
    public function saving(Product $product)
    {
        /**
         *  Set additional properties for this product
         */
        $product->on_sale = $product->determineIfOnSale();
        $product->unit_loss = $product->calculateUnitLoss();
        $product->has_price = $product->determineIfHasPrice();
        $product->has_stock = $product->determineIfHasStock();
        $product->unit_price = $product->calculateUnitPrice();
        $product->unit_profit = $product->calculateUnitProfit();
        $product->unit_sale_discount = $product->calculateUnitSaleDiscount();
        $product->unit_loss_percentage = $product->calculateUnitLossPercentage();
        $product->unit_profit_percentage = $product->calculateUnitProfitPercentage();
        $product->unit_sale_discount_percentage = $product->calculateUnitSaleDiscountPercentage();

        /**
         *  If the product has never been created before or the
         *  product has been created before but is currently
         *  set to visible while the visiblity has expired,
         *  then set or extend the current visibility
         *  expiry date and time
         */
        if( is_null($product->id) || (!is_null($product->id) && $product->visible == true && $product->visibilityHasExpired() == true) ) {

            //  Extend the product visibility
            $product->extendVisibility();

        }

        //  Check if we want to make this product visible
        if($product->visible) {

            //  Check if we have reached the maximum number of visible products we can show
            if($product->store->products()->isNotVariation()->visible()->count() >= Store::MAXIMUM_VISIBLE_PRODUCTS) {

                //  Disable the product visibility since we have reached the maximum number of visible products
                $product->visible = false;

            }

        }

        return $product;
    }

    public function creating(Product $product)
    {
        /**
         *  Update the product photo (if any)
         *
         *  Note that updateLogo() will work when creating a product but will not work when updating
         *  a product. This is because the $request->hasFile('photo') requires that the request must
         *  be a POST request. This is fine when we are creating a product since we do use a POST
         *  request, but doesn't work for us when we are updating a product since we then use a
         *  PUT request. In that case we update the photo separately using a POST route that
         *  is dedicated to updating the photo only. For this reason we will put this logic
         *  on the creating method since the saving method is triggered for both creating
         *  and updating. This way we can create a photo when creating a product and update
         *  the photo separately when we need to set a new photo or update the existing
         *  photo.
         *
         *  While implemeting a POST request the $request->hasFile('photo') will return "true",
         *  where as while implemeting a PUT request the same method will return "false".
         */
        $product = $this->productRepository()->setModel($product)->updatePhoto(request())->getModel();
    }

    public function created(Product $product)
    {
        //
    }

    public function updated(Product $product)
    {
        //
    }

    public function deleted(Product $product)
    {
        //  Foreach variation
        foreach($product->variations as $variation) {

            //  Delete variation
            $variation->delete();

        }

        //  Delete variables
        $product->variables()->delete();
    }

    public function restored(Product $product)
    {
        //
    }

    public function forceDeleted(Product $product)
    {
        //
    }
}
