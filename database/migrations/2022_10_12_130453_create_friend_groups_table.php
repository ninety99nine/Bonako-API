<?php

use App\Models\FriendGroup;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friend_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', FriendGroup::NAME_MAX_CHARACTERS);
            $table->boolean('shared')->default(false);
            $table->boolean('can_add_friends')->default(false);
            $table->timestamps();

            /* Add Indexes */
            $table->index('name');
            $table->index('shared');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('friend_groups');
    }
}
