<?php

use App\Enums\UserFriendGroupRole;
use App\Enums\UserFriendGroupStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Pivots\FriendGroupUserAssociation;

class CreateFriendGroupUserAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friend_group_user_association', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable();
            $table->string('mobile_number', 20)->nullable();
            $table->foreignUuid('friend_group_id');
            $table->enum('status', FriendGroupUserAssociation::STATUSES)->default(UserFriendGroupStatus::INVITED->value);
            $table->enum('role', FriendGroupUserAssociation::ROLES)->default(UserFriendGroupRole::MEMBER->value);
            $table->foreignUuid('invited_to_join_by_user_id')->nullable();
            $table->timestamp('last_selected_at')->nullable();
            $table->boolean('created_by_super_admin')->default(false);
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
