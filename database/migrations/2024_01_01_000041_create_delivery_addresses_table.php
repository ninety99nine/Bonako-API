<?php

use App\Models\DeliveryAddress;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', DeliveryAddress::TYPES())->nullable();
            $table->string('address_line', DeliveryAddress::ADDRESS_MAX_CHARACTERS);
            $table->string('address_line2', DeliveryAddress::ADDRESS2_MAX_CHARACTERS)->nullable();
            $table->string('city', DeliveryAddress::CITY_MAX_CHARACTERS)->nullable();
            $table->string('state', DeliveryAddress::STATE_MAX_CHARACTERS)->nullable();
            $table->string('zip', DeliveryAddress::ZIP_MAX_CHARACTERS)->nullable();
            $table->char('country', 2)->nullable();
            $table->string('place_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('description')->nullable();
            $table->foreignUuid('order_id')->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('place_id');
            $table->index('order_id');
            $table->index(['latitude', 'longitude']);

            /* Foreign Key Constraints */
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_addresses');
    }
}
