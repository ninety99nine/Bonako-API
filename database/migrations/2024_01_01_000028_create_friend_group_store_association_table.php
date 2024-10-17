<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendGroupStoreAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friend_group_store_association', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id');
            $table->foreignUuid('friend_group_id');
            $table->foreignUuid('added_by_user_id')->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('store_id');
            $table->index('friend_group_id');
            $table->index('added_by_user_id');

            /*  Foreign Key Constraints */
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('added_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('friend_group_id')->references('id')->on('friend_groups')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('friend_group_store_association');
    }
}
