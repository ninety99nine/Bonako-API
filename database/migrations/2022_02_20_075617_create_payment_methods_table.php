<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {

            $table->id();

            /*  Basic Information  */
            $table->string('name');
            $table->string('method');
            $table->string('category');
            $table->string('description');
            $table->boolean('available_on_perfect_pay');
            $table->boolean('available_on_stores');
            $table->boolean('available_on_ussd');
            $table->boolean('active');

            /*  Arrangement Information  */
            $table->unsignedTinyInteger('position')->nullable();

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('name');
            $table->index('method');
            $table->index('category');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
}
