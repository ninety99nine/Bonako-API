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
            $table->uuid('id')->primary();
            $table->text('user_content');
            $table->text('assistant_content')->nullable();
            $table->integer('prompt_tokens')->default(0);
            $table->integer('completion_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->timestamp('request_at')->nullable();
            $table->timestamp('response_at')->nullable();
            $table->foreignUuid('ai_message_category_id')->nullable();
            $table->foreignUuid('ai_assistant_id');
            $table->timestamps();

            /* Add Indexes */
            $table->index('ai_message_category_id');
            $table->index('ai_assistant_id');

            $table->foreign('ai_assistant_id')->references('id')->on('ai_assistants')->cascadeOnDelete();
            $table->foreign('ai_message_category_id')->references('id')->on('ai_message_categories')->nullOnDelete();
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
