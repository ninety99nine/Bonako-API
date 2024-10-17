<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAiAssistantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_assistants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->integer('total_paid_tokens')->default(0);
            $table->integer('remaining_free_tokens')->default(0);
            $table->integer('remaining_paid_tokens')->default(0);
            $table->integer('remaining_paid_top_up_tokens')->default(0);

            $table->boolean('requires_subscription')->default(0);
            $table->timestamp('last_subscription_end_at')->nullable();

            $table->foreignUuid('user_id');
            $table->timestamps();

            /* Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_assistants');
    }
}
