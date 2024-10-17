<?php

use App\Models\Review;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->tinyInteger('rating');
            $table->enum('subject', Review::SUBJECTS())->nullable();
            $table->string('comment', Review::COMMENT_MAX_CHARACTERS)->nullable();
            $table->foreignUuid('store_id');
            $table->foreignUuid('user_id');
            $table->timestamps();

            /* Add Indexes */
            $table->index('subject');
            $table->index('user_id');
            $table->index('store_id');

            /* Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
