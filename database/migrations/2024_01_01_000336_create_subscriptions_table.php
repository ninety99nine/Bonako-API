<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {

            $table->uuid('id')->primary();

            /*  Basic Information  */
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->foreignUuid('user_id')->nullable();
            $table->foreignUuid('transaction_id')->nullable();
            $table->foreignUuid('pricing_plan_id')->nullable();

            /*  Owenership Information  */
            $table->uuidMorphs('owner');

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('user_id');
            $table->index('transaction_id');
            $table->index('pricing_plan_id');

            /* Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
            $table->foreign('pricing_plan_id')->references('id')->on('pricing_plans')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
