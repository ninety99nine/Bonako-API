<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Pivots\UserFriendGroupAssociation;

class CreateUserFriendGroupAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_friend_group_association', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('friend_group_id');
            $table->enum('role', UserFriendGroupAssociation::ROLES)->default(Arr::last(UserFriendGroupAssociation::ROLES));
            $table->timestamp('last_selected_at')->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('role');
            $table->index('user_id');
            $table->index('friend_group_id');

            /*  Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
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
        Schema::dropIfExists('friend_group_users');
    }
}
