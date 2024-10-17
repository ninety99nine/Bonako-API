<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     *  The saving event will dispatch when a model is creating or updating
     *  the model even if the model's attributes have not been changed.
     *
     *  Refererence: https://laravel.com/docs/9.x/eloquent#events
     */
    public function saving(Product $product)
    {
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

        return $product;
    }

    public function creating(Product $product)
    {
        //
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
        $product->variables()->delete();
        foreach($product->variations as $variation) {
            $variation->delete();
        }
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
