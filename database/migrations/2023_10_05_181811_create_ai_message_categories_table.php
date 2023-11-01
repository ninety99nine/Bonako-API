<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AiMessageCategory;

class CreateAiMessageCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_message_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', AiMessageCategory::NAME_MAX_CHARACTERS);
            $table->string('description', AiMessageCategory::DESCRIPTION_MAX_CHARACTERS);
            $table->string('system_prompt', AiMessageCategory::SYSTEM_PROMPT_MAX_CHARACTERS);

            /* Add Timestamps */
            $table->timestamps();

            /* Add Indexes */
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_message_categories');
    }
}
