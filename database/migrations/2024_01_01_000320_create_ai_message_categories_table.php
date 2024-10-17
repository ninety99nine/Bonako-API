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
            $table->uuid('id')->primary();
            $table->string('name', AiMessageCategory::NAME_MAX_CHARACTERS);
            $table->string('description', AiMessageCategory::DESCRIPTION_MAX_CHARACTERS);
            $table->text('system_prompt');

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
