<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricingPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pricing_plans', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->boolean('active')->default(true);
            $table->string('name');
            $table->string('type');
            $table->string('description')->nullable();
            $table->string('billing_type');
            $table->string('currency', 3);
            $table->decimal('price', 10, 2);
            $table->unsignedTinyInteger('discount_percentage_rate')->default(0);
            $table->boolean('supports_web')->default(false);
            $table->boolean('supports_ussd')->default(false);
            $table->boolean('supports_mobile')->default(false);
            $table->json('metadata')->nullable();
            $table->json('features')->nullable();
            $table->unsignedTinyInteger('position')->nullable();

            /*  Timestamps  */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pricing_plans');
    }
}
