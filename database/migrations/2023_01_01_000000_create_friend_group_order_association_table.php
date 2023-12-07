<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendGroupOrderAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friend_group_order_association', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('friend_group_id')->constrained();
            $table->timestamps();

            /* Add Indexes */
            $table->index('order_id');
            $table->index('friend_group_id');

            /*  Foreign Key Constraints */
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
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
        Schema::dropIfExists('friend_group_order_association');
    }
}
