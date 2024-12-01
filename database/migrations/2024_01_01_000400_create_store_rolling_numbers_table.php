<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreRollingNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_rolling_numbers', function (Blueprint $table) {

            $table->uuid('id')->primary();
            $table->string('mobile_number', 20)->nullable();
            $table->timestamp('last_called_at')->nullable();

            /* Relationships */
            $table->foreignUuid('store_id');

            /* Timestamps */
            $table->timestamps();

            /* Add Indexes */
            $table->index(['last_called_at']);

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
        Schema::dropIfExists('store_rolling_numbers');
    }
}
