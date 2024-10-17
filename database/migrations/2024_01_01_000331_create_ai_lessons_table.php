<?php

use App\Models\AiLesson;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiLessonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_lessons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('name', AiLesson::NAME_MAX_CHARACTERS);
            $table->json('topics')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_lessons');
    }
}
