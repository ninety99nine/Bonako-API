<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', User::FIRST_NAME_MAX_CHARACTERS);
            $table->string('last_name', User::LAST_NAME_MAX_CHARACTERS);
            $table->string('mobile_number', 11)->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('mobile_number_verified_at')->nullable();
            $table->boolean('accepted_terms_and_conditions')->default(false);
            $table->boolean('is_super_admin')->default(false);
            $table->string('password')->nullable();
            $table->foreignId('registered_by_user_id')->nullable();
            $table->rememberToken();

            /* Add Timestamps */
            $table->timestamps();

            /* Add Indexes */
            $table->index(['first_name', 'last_name']);
            $table->index('registered_by_user_id');
            $table->index('mobile_number');

            /*  Foreign Key Constraints */
            $table->foreign('registered_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
