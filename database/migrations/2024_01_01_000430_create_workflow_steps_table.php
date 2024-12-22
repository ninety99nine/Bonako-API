<?php

use App\Models\Workflow;
use App\Enums\WorkflowTriggerType;
use App\Enums\WorkflowResourceType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflowStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_steps', function (Blueprint $table) {

            $table->uuid('id')->primary();

            /*  Basic Information  */
            $table->json('settings');
            $table->foreignUuid('workflow_id');

            /*  Arrangement Information  */
            $table->unsignedTinyInteger('position')->nullable();

            /*  Timestamps  */
            $table->timestamps();

            /* Foreign Key Constraints */
            $table->foreign('workflow_id')->references('id')->on('workflows')->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_steps');
    }
}
