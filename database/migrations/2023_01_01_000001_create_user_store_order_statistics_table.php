<?php

use App\Models\Store;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserStoreOrderStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_store_order_statistics', function (Blueprint $table) {

            $table->id();
            $table->char('currency', 3)->default(Store::CURRENCY);

            //  Order Totals (Requested)
            $table->unsignedSmallInteger('total_orders_requested')->default(0);
            $table->decimal('sub_total_requested', 10, 2)->default(0);
            $table->decimal('coupon_discount_total_requested', 10, 2)->default(0);
            $table->decimal('sale_discount_total_requested', 10, 2)->default(0);
            $table->decimal('coupon_and_sale_discount_total_requested', 10, 2)->default(0);
            $table->decimal('grand_total_requested', 10, 2)->default(0);
            $table->unsignedSmallInteger('total_products_requested')->default(0);
            $table->unsignedSmallInteger('total_product_quantities_requested')->default(0);
            $table->unsignedSmallInteger('total_coupons_requested')->default(0);

            $table->decimal('avg_sub_total_requested', 10, 2)->default(0);
            $table->decimal('avg_coupon_discount_total_requested', 10, 2)->default(0);
            $table->decimal('avg_sale_discount_total_requested', 10, 2)->default(0);
            $table->decimal('avg_coupon_and_sale_discount_total_requested', 10, 2)->default(0);
            $table->decimal('avg_grand_total_requested', 10, 2)->default(0);
            $table->unsignedSmallInteger('avg_total_products_requested')->default(0);
            $table->unsignedSmallInteger('avg_total_product_quantities_requested')->default(0);
            $table->unsignedSmallInteger('avg_total_coupons_requested')->default(0);

            //  Order Totals (Completed)
            $table->unsignedSmallInteger('total_orders_received')->default(0);
            $table->decimal('sub_total_received', 10, 2)->default(0);
            $table->decimal('coupon_discount_total_received', 10, 2)->default(0);
            $table->decimal('sale_discount_total_received', 10, 2)->default(0);
            $table->decimal('coupon_and_sale_discount_total_received', 10, 2)->default(0);
            $table->decimal('grand_total_received', 10, 2)->default(0);
            $table->unsignedSmallInteger('total_products_received')->default(0);
            $table->unsignedSmallInteger('total_product_quantities_received')->default(0);
            $table->unsignedSmallInteger('total_coupons_received')->default(0);

            $table->decimal('avg_sub_total_received', 10, 2)->default(0);
            $table->decimal('avg_coupon_discount_total_received', 10, 2)->default(0);
            $table->decimal('avg_sale_discount_total_received', 10, 2)->default(0);
            $table->decimal('avg_coupon_and_sale_discount_total_received', 10, 2)->default(0);
            $table->decimal('avg_grand_total_received', 10, 2)->default(0);
            $table->unsignedSmallInteger('avg_total_products_received')->default(0);
            $table->unsignedSmallInteger('avg_total_product_quantities_received')->default(0);
            $table->unsignedSmallInteger('avg_total_coupons_received')->default(0);

            //  Order Totals (Cancelled)
            $table->unsignedSmallInteger('total_orders_cancelled')->default(0);
            $table->decimal('sub_total_cancelled', 10, 2)->default(0);
            $table->decimal('coupon_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('sale_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('coupon_and_sale_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('grand_total_cancelled', 10, 2)->default(0);
            $table->unsignedSmallInteger('total_products_cancelled')->default(0);
            $table->unsignedSmallInteger('total_product_quantities_cancelled')->default(0);
            $table->unsignedSmallInteger('total_coupons_cancelled')->default(0);

            $table->decimal('avg_sub_total_cancelled', 10, 2)->default(0);
            $table->decimal('avg_coupon_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('avg_sale_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('avg_coupon_and_sale_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('avg_grand_total_cancelled', 10, 2)->default(0);
            $table->unsignedSmallInteger('avg_total_products_cancelled')->default(0);
            $table->unsignedSmallInteger('avg_total_product_quantities_cancelled')->default(0);
            $table->unsignedSmallInteger('avg_total_coupons_cancelled')->default(0);

            $table->foreignId('user_store_association_id')->cascadeOnDelete();

            $table->timestamps();

            /*  Foreign Key Constraints */
            $table->foreign('user_store_association_id')->references('id')->on('user_store_association')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_store_order_statistics');
    }
}
