<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFriendAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_friend_association', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('friend_user_id');
            $table->timestamp('last_selected_at')->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('user_id');
            $table->index('friend_user_id');

            /*  Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('friend_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_friend_association');
    }
}
