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
            $table->id();
            $table->boolean('requires_subscription')->default(0);
            $table->integer('free_tokens_used')->default(0);
            $table->integer('remaining_paid_tokens_after_last_subscription')->default(0);
            $table->integer('remaining_paid_tokens')->default(0);
            $table->integer('request_tokens_used')->default(0);
            $table->integer('response_tokens_used')->default(0);
            $table->integer('total_tokens_used')->default(0);
            $table->integer('total_requests')->default(0);
            $table->timestamp('remaining_paid_tokens_expire_at')->nullable();
            $table->foreignId('user_id');
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
