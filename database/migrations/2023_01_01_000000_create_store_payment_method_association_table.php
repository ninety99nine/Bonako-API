<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStorePaymentMethodAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_payment_method_association', function (Blueprint $table) {

            $table->id();
            $table->boolean('active')->default(true);
            $table->string('instruction')->nullable();
            $table->smallInteger('total_enabled')->default(1);
            $table->smallInteger('total_disabled')->default(0);
            $table->foreignId('store_id')->constrained();
            $table->foreignId('payment_method_id')->constrained();

            /*  Soft delete  */
            $table->softDeletes();

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('active');
            $table->index('store_id');
            $table->index('payment_method_id');

            /*  Foreign Key Constraints */
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_payment_method_association');
    }
}
