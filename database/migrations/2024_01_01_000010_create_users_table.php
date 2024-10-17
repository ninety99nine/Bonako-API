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
            $table->uuid('id')->primary();
            $table->string('first_name', User::FIRST_NAME_MAX_CHARACTERS);
            $table->string('last_name', User::LAST_NAME_MAX_CHARACTERS)->nullable();
            $table->string('about_me', User::ABOUT_ME_MAX_CHARACTERS)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('mobile_number', 20)->nullable()->unique();
            $table->timestamp('mobile_number_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_super_admin')->default(false);
            $table->boolean('is_guest')->default(false);
            $table->foreignUuid('registered_by_user_id')->nullable();
            $table->rememberToken();

            /* Add Timestamps */
            $table->timestamps();

            /* Add Indexes */
            $table->index(['first_name', 'last_name']);
            $table->index('registered_by_user_id');
            $table->index('mobile_number');
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
