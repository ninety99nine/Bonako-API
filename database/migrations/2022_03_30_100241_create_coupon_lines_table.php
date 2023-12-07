<?php

use App\Models\Store;
use App\Models\CouponLine;
use Illuminate\Support\Arr;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_lines', function (Blueprint $table) {

             $table->id();

            /*  General Information */
            $table->string('name', 50);
            $table->string('description', 500)->nullable();

            /*  Offer Discount Information */
            $table->boolean('offer_discount')->default(false);
            $table->enum('discount_type', CouponLine::DISCOUNT_TYPES)->default(Arr::last(CouponLine::DISCOUNT_TYPES));
            $table->unsignedTinyInteger('discount_percentage_rate')->default(0);
            $table->decimal('discount_fixed_rate', 10, 2)->default(0);

            /*  Offer Free Delivery Information */
            $table->boolean('offer_free_delivery')->default(false);

            /*  Activation Information  */
            $table->boolean('activate_using_code')->default(false);
            $table->string('code', 10)->nullable();

            $table->boolean('activate_using_minimum_grand_total')->default(false);
            $table->decimal('minimum_grand_total', 10, 2)->default(0);
            $table->char('currency', 3)->default(Store::CURRENCY);

            $table->boolean('activate_using_minimum_total_products')->default(false);
            $table->unsignedSmallInteger('minimum_total_products')->default(1);

            $table->boolean('activate_using_minimum_total_product_quantities')->default(false);
            $table->unsignedSmallInteger('minimum_total_product_quantities')->default(1);

            $table->boolean('activate_using_start_datetime')->default(false);
            $table->timestamp('start_datetime')->nullable();

            $table->boolean('activate_using_end_datetime')->default(false);
            $table->timestamp('end_datetime')->nullable();

            $table->boolean('activate_using_hours_of_day')->default(false);
            $table->json('hours_of_day')->nullable();

            $table->boolean('activate_using_days_of_the_week')->default(false);
            $table->json('days_of_the_week')->nullable();

            $table->boolean('activate_using_days_of_the_month')->default(false);
            $table->json('days_of_the_month')->nullable();

            $table->boolean('activate_using_months_of_the_year')->default(false);
            $table->json('months_of_the_year')->nullable();

            $table->boolean('activate_for_new_customer')->default(false);
            $table->boolean('activate_for_existing_customer')->default(false);

            $table->boolean('activate_using_usage_limit')->default(false);
            $table->unsignedMediumInteger('remaining_quantity')->default(0);

            /*  Cancellation Information  */
            $table->boolean('is_cancelled')->default(false);
            $table->json('cancellation_reasons')->nullable();

            /*  Detected Changes Information  */
            $table->json('detected_changes')->nullable();

            /*  Ownership  */
            $table->foreignId('cart_id')->constrained();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('coupon_id')->constrained()->nullable();

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('name');
            $table->index('code');
            $table->index('cart_id');
            $table->index('store_id');
            $table->index('coupon_id');

            /* Foreign Key Constraints */
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('coupon_id')->references('id')->on('coupons')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupon_lines');
    }
}
