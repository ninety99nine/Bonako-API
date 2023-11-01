<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->text('user_content');
            $table->text('assistant_content')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->integer('request_tokens_used');
            $table->integer('response_tokens_used');
            $table->integer('total_tokens_used');
            $table->integer('free_tokens_used');
            $table->integer('remaining_paid_tokens');
            $table->timestamp('request_at');
            $table->timestamp('response_at');
            $table->foreignId('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('ai_message_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_messages');
    }
}
