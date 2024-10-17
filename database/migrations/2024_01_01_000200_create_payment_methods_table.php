<?php

use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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

            $table->uuid('id')->primary();

            /*  Basic Information  */
            $table->boolean('active')->default(0);
            $table->string('name', PaymentMethod::NAME_MAX_CHARACTERS);
            $table->string('type', PaymentMethod::TYPE_MAX_CHARACTERS);
            $table->enum('category', PaymentMethod::PAYMENT_METHOD_CATEGORIES());
            $table->string('description', PaymentMethod::DESCRIPTION_MAX_CHARACTERS)->nullable();
            $table->json('countries')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('can_pay_later')->default(0);
            $table->boolean('require_proof_of_payment')->default(0);
            $table->boolean('automatically_mark_as_paid')->default(0);

            /*  Arrangement Information  */
            $table->unsignedTinyInteger('position')->nullable();

            /*  Ownership Information  */
            $table->foreignUuid('store_id')->nullable();

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index('name');
            $table->index('type');
            $table->index('category');
            $table->index('store_id');

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
        Schema::dropIfExists('payment_methods');
    }
}
