<?php

use App\Models\Store;
use App\Models\Coupon;
use Illuminate\Support\Arr;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {

             $table->id();

            /*  General Information */
            $table->string('name', COUPON::NAME_MAX_CHARACTERS);
            $table->string('description', COUPON::DESCRIPTION_MAX_CHARACTERS)->nullable();
            $table->boolean('active')->default(true);

            /*  Offer Discount Information */
            $table->boolean('offer_discount')->default(false);
            $table->enum('discount_type', Coupon::DISCOUNT_TYPES)->default(Arr::last(Coupon::DISCOUNT_TYPES));
            $table->unsignedTinyInteger('discount_percentage_rate')->default(0);
            $table->decimal('discount_fixed_rate', 10, 2)->default(0);

            /*  Offer Free Delivery Information */
            $table->boolean('offer_free_delivery')->default(false);

            /*  Activation Information  */
            $table->boolean('activate_using_code')->default(false);
            $table->string('code', Coupon::CODE_MAX_CHARACTERS)->nullable();

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

            /*  Ownership  */
            $table->foreignId('store_id')->constrained();
            $table->foreignId('user_id')->constrained()->nullable();

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('name');
            $table->index('code');
            $table->index('user_id');
            $table->index('store_id');

            /* Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
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
        Schema::dropIfExists('coupons');
    }
}
