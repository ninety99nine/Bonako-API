<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Pivots\UserStoreAssociation;
use Illuminate\Database\Migrations\Migration;

class CreateUserStoreAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_store_association', function (Blueprint $table) {

            $table->uuid('id')->primary();

            /*  Store Information  */
            $table->foreignUuid('store_id');

            /*  User Information  */
            $table->foreignUuid('user_id')->nullable();
            $table->string('mobile_number', 20)->nullable();

            /*  Team Member Information  */
            $table->enum('team_member_status', UserStoreAssociation::TEAM_MEMBER_STATUSES())->nullable();
            $table->enum('team_member_role', UserStoreAssociation::TEAM_MEMBER_ROLES())->nullable();
            $table->json('team_member_permissions')->nullable();
            $table->foreignUuid('invited_to_join_team_by_user_id')->nullable();

            /*  Follower Information  */
            $table->enum('follower_status', UserStoreAssociation::FOLLOWER_STATUSES())->nullable();
            $table->foreignUuid('invited_to_follow_by_user_id')->nullable();

            /*  Customer Information  */
            $table->boolean('is_associated_as_customer')->default(false);

            /*  Timestamps  */
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_seen_on_ussd_at')->nullable();
            $table->timestamp('last_seen_on_web_app_at')->nullable();
            $table->timestamp('last_seen_on_mobile_app_at')->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('user_id');
            $table->index('store_id');
            $table->index('last_seen_at');
            $table->index('mobile_number');
            $table->index('follower_status');
            $table->index('team_member_role');
            $table->index('team_member_status');
            $table->index('is_associated_as_customer');
            $table->index('invited_to_follow_by_user_id');
            $table->index('invited_to_join_team_by_user_id');

            /*  Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('invited_to_follow_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('invited_to_join_team_by_user_id')->references('id')->on('users')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_store_association');
    }
}
