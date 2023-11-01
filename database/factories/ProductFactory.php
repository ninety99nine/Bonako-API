<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $description = $this->faker->sentence(10);

        while ($description <= (strlen($description) > Product::DESCRIPTION_MAX_CHARACTERS)) {
            $description = $this->faker->sentence(10);
        }

        $onSale = $this->faker->boolean(30);

        $useHigherPricing = $this->faker->boolean(5);
        $unitRegularPrice = $useHigherPricing
            ? $this->faker->numberBetween(5000, 100000)
            : $this->faker->numberBetween(50, 1000);
        $unitSalePrice = $this->faker->numberBetween(5000, $unitRegularPrice);
        $unitCostPrice = $this->faker->numberBetween(5000, $unitSalePrice);
        $stockQuantityType = $this->faker->randomElement(Product::STOCK_QUANTITY_TYPE);
        $allowedQuantityPerOrder = $this->faker->randomElement(Product::ALLOWED_QUANTITY_PER_ORDER);

        return [
            'visible' => true,
            'user_id' => null,
            'on_sale' => $onSale,
            'allow_variations' => false,
            'parent_product_id' => null,
            'variant_attributes' => null,
            'description' => $description,
            'currency' => Store::CURRENCY,
            'visibility_expires_at' => null,
            'unit_cost_price' =>  $unitCostPrice,
            'is_free' => $this->faker->boolean(5),
            'unit_regular_price' => $unitRegularPrice,
            'barcode' => $this->faker->unique()->ean13,
            'stock_quantity_type' => $stockQuantityType,
            'name' => $this->faker->words(rand(1, 3), true),
            'show_description' => $this->faker->boolean(90),
            'unit_sale_price' => $onSale ? $unitSalePrice : 0,
            'sku' => $this->faker->unique()->bothify('???-#####'),
            'allowed_quantity_per_order' => $allowedQuantityPerOrder,
            'stock_quantity' => $this->faker->numberBetween(10, 10000),
            'maximum_allowed_quantity_per_order' => $this->faker->numberBetween(2, 10),
            'position' => $this->faker->numberBetween(1, Product::POSITION_MAX),
        ];
    }
}
