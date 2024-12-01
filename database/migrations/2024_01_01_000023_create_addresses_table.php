<?php

use App\Models\Address;
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
            $table->uuid('id')->primary();
            $table->enum('type', Address::TYPES())->nullable();
            $table->string('address_line', Address::ADDRESS_MAX_CHARACTERS);
            $table->string('address_line2', Address::ADDRESS2_MAX_CHARACTERS)->nullable();
            $table->string('city', Address::CITY_MAX_CHARACTERS)->nullable();
            $table->string('state', Address::STATE_MAX_CHARACTERS)->nullable();
            $table->string('zip', Address::ZIP_MAX_CHARACTERS)->nullable();
            $table->char('country', 2)->nullable();
            $table->string('place_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('description')->nullable();
            $table->uuidMorphs('owner');
            $table->timestamps();

            /* Add Indexes */
            $table->index('place_id');
            $table->index(['latitude', 'longitude']);
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
