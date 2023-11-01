<?php

use App\Models\Address;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name', Address::NAME_MAX_CHARACTERS);
            $table->string('address_line', Address::ADDRESS_LINE_MAX_CHARACTERS);
            $table->boolean('share_address')->default(1);
            $table->foreignId('user_id');
            $table->timestamps();

            /* Add Indexes */
            $table->index('user_id');

            /* Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('addresses');
    }
}
