<?php

use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {

            $table->uuid('id')->primary();

            /*  Pricing  */
            $table->char('currency', 3)->default(Store::CURRENCY);
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('coupon_discount_total', 10, 2)->default(0);
            $table->decimal('sale_discount_total', 10, 2)->default(0);
            $table->decimal('coupon_and_sale_discount_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);

            /*  Delivery  */
            $table->boolean('allow_free_delivery')->default(false);
            $table->boolean('has_delivery_fee')->default(false);
            $table->decimal('delivery_fee', 10, 2)->default(0);
            $table->json('delivery_destination')->nullable();

            /*  Product Line Totals  */
            $table->unsignedSmallInteger('total_products')->default(0);
            $table->unsignedSmallInteger('total_product_quantities')->default(0);
            $table->unsignedSmallInteger('total_cancelled_products')->default(0);
            $table->unsignedSmallInteger('total_cancelled_product_quantities')->default(0);
            $table->unsignedSmallInteger('total_uncancelled_products')->default(0);
            $table->unsignedSmallInteger('total_uncancelled_product_quantities')->default(0);

            /*  Coupon Line Totals  */
            $table->unsignedSmallInteger('total_coupons')->default(0);
            $table->unsignedSmallInteger('total_cancelled_coupons')->default(0);
            $table->unsignedSmallInteger('total_uncancelled_coupons')->default(0);

            /*  Changes  */
            $table->json('products_arrangement')->nullable();
            $table->boolean('is_abandoned')->default(false);

            /*  Instant Cart  */
            $table->foreignUuid('instant_cart_id')->nullable();

            /*  Ownership  */
            $table->foreignUuid('store_id');

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('store_id');
            $table->index('instant_cart_id');

            /* Foreign Key Constraints */
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts');
    }
}
