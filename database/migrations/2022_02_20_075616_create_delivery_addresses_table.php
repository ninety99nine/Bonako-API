<?php

use Illuminate\Support\Arr;
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
            $table->id();
            $table->string('name', DeliveryAddress::NAME_MAX_CHARACTERS);
            $table->string('address_line', DeliveryAddress::ADDRESS_LINE_MAX_CHARACTERS);
            $table->boolean('share_address')->default(1);
            $table->foreignId('user_id')->constrained();
            $table->foreignId('address_id')->constrained()->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('user_id');
            $table->index('address_id');

            /* Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('address_id')->references('id')->on('addresses')->nullOnDelete();
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
