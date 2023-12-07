<?php

use App\Models\Shortcode;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShortcodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shortcodes', function (Blueprint $table) {

             $table->id();

            /*  Basic Information  */
            $table->string('code')->nullable();
            $table->enum('action', Shortcode::ACTIONS);
            $table->timestamp('expires_at')->nullable();

            /*  Reservation Information  */
            $table->foreignId('reserved_for_user_id')->constrained()->nullable();

            /*  Ownership Information  */
            $table->foreignId('owner_id');
            $table->string('owner_type');

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index(['code', 'action']);
            $table->index(['reserved_for_user_id']);

            /* Foreign Key Constraints */
            $table->foreign('reserved_for_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shortcodes');
    }
}
