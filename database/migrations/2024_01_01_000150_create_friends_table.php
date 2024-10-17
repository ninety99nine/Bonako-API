<?php

use App\Models\Customer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('friends', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', Customer::FIRST_NAME_MAX_CHARACTERS);
            $table->string('last_name', Customer::LAST_NAME_MAX_CHARACTERS)->nullable();
            $table->string('mobile_number', 20);
            $table->timestamp('last_selected_at')->nullable();
            $table->foreignUuid('user_id')->cascadeOnDelete();

            /* Add Timestamps */
            $table->timestamps();

            /* Add Indexes */
            $table->index(['first_name', 'last_name']);
            $table->index('mobile_number');

            /*  Foreign Key Constraints */
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
        Schema::dropIfExists('customers');
    }
}
