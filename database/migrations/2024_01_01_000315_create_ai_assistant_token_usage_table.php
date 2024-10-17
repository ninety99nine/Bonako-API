<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAiAssistantTokenUsageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_assistant_token_usage', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('request_tokens_used')->default(0);
            $table->integer('response_tokens_used')->default(0);

            $table->integer('free_tokens_used')->default(0);
            $table->integer('paid_tokens_used')->default(0);
            $table->integer('paid_top_up_tokens_used')->default(0);

            $table->foreignUuid('ai_assistant_id');
            $table->timestamps();

            /* Add Indexes */
            $table->index('ai_assistant_id');

            $table->foreign('ai_assistant_id')->references('id')->on('ai_assistants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_assistant_token_usage');
    }
}
