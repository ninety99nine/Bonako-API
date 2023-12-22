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
            $table->foreignId('user_id')->nullable();
            $table->string('mobile_number', 11)->nullable();
            $table->foreignId('friend_group_id');
            $table->enum('status', UserFriendGroupAssociation::STATUSES)->default(UserFriendGroupAssociation::DEFAULT_STATUS);
            $table->enum('role', UserFriendGroupAssociation::ROLES)->default(UserFriendGroupAssociation::DEFAULT_ROLE);
            $table->foreignId('invited_to_join_by_user_id')->nullable();
            $table->timestamp('last_selected_at')->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('role');
            $table->index('status');
            $table->index('user_id');
            $table->index('mobile_number');
            $table->index('friend_group_id');

            /*  Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('friend_group_id')->references('id')->on('friend_groups')->cascadeOnDelete();
            $table->foreign('invited_to_join_by_user_id')->references('id')->on('users')->nullOnDelete();
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
