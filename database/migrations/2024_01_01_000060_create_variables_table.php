<?php

use App\Models\Variable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('variables', function (Blueprint $table) {
             $table->uuid('id')->primary();
            $table->string('name', Variable::NAME_MAX_CHARACTERS);
            $table->string('value', Variable::VALUE_MAX_CHARACTERS);
            $table->foreignUuid('product_id');

            /* Add Indexes */
            $table->index(['name', 'value']);
            $table->index('product_id');

            /* Foreign Key Constraints */
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('variables');
    }
}
