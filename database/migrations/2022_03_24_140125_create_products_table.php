<?php

use App\Models\Store;
use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            /*  General Information
             *
             *  Note: The product name can be up to 60 characters
             *  since variation names can have long generated
             *  names e.g
             *
             *  Nike Winter Jacket (Red, Large and Cotton) = 42 characters
             *
             *  Lets give an allowance of 60 characters to avoid
             *  possible issues because of long product names
             */
            $table->string('name', Product::NAME_MAX_CHARACTERS)->nullable();
            $table->boolean('visible')->default(true);
            $table->timestamp('visibility_expires_at')->nullable();
            $table->string('photo')->nullable();

            $table->boolean('show_description')->default(false);
            $table->string('description', Product::DESCRIPTION_MAX_CHARACTERS)->nullable();

            /*  Tracking Information  */
            $table->string('sku', Product::SKU_MAX_CHARACTERS)->nullable();
            $table->string('barcode', Product::BARCODE_MAX_CHARACTERS)->nullable();

            /*  Variation Information  */
            $table->boolean('allow_variations')->default(false);
            $table->json('variant_attributes')->nullable();
            $table->unsignedTinyInteger('total_variations')->nullable();
            $table->unsignedTinyInteger('total_visible_variations')->nullable();

            /*  Pricing Information  */
            $table->boolean('is_free')->default(false);
            $table->char('currency', 3)->default(Store::CURRENCY);
            $table->decimal('unit_regular_price', 10, 2)->default(0);

            $table->boolean('on_sale')->default(false);
            $table->decimal('unit_sale_price', 10, 2)->default(0);
            $table->decimal('unit_sale_discount', 10, 2)->default(0);
            $table->unsignedSmallInteger('unit_sale_discount_percentage')->default(0);

            $table->decimal('unit_cost_price', 10, 2)->default(0);

            $table->boolean('has_price')->default(false);
            $table->decimal('unit_price', 10, 2)->default(0);

            $table->decimal('unit_profit', 10, 2)->default(0);
            $table->unsignedSmallInteger('unit_profit_percentage')->default(0);

            $table->decimal('unit_loss', 10, 2)->default(0);
            $table->unsignedSmallInteger('unit_loss_percentage')->default(0);

            /*  Quantity Information  */
            $table->enum('allowed_quantity_per_order', Product::ALLOWED_QUANTITY_PER_ORDER)->default(Arr::last(Product::ALLOWED_QUANTITY_PER_ORDER));
            $table->unsignedSmallInteger('maximum_allowed_quantity_per_order')->default(1);

            /*  Stock Information  */
            $table->boolean('has_stock')->default(true);
            $table->enum('stock_quantity_type', Product::STOCK_QUANTITY_TYPE)->default(Arr::last(Product::STOCK_QUANTITY_TYPE));
            $table->unsignedMediumInteger('stock_quantity')->default(100);

            /*  Arrangement Information  */
            $table->unsignedTinyInteger('position')->nullable();

            /*  Ownership Information  */
            $table->foreignId('parent_product_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('store_id');

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('sku');
            $table->index('name');
            $table->index('barcode');
            $table->index('user_id');
            $table->index('position');
            $table->index('store_id');
            $table->index('parent_product_id');

            /* Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('parent_product_id')->references('id')->on('products')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
