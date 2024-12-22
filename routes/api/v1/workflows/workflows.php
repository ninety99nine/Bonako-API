<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WorkflowStepController;

Route::controller(WorkflowController::class)
    ->prefix('workflows')
    ->group(function () {
        Route::get('/', 'showWorkflows')->name('show.workflows');
        Route::post('/', 'createWorkflow')->name('create.workflow');
        Route::delete('/', 'deleteWorkflows')->name('delete.workflows');
        Route::post('/options', 'showWorkflowOptions')->name('show.workflow.options');
        Route::post('/arrangement', 'updateWorkflowArrangement')->name('update.workflow.arrangement');

        //  Workflow
        Route::prefix('{workflowId}')->group(function () {
            Route::get('/', 'showWorkflow')->name('show.workflow');
            Route::put('/', 'updateWorkflow')->name('update.workflow');
            Route::delete('/', 'deleteWorkflow')->name('delete.workflow');

            //  Workflow Steps
            Route::controller(WorkflowStepController::class)->prefix('workflow-steps')->group(function () {
                Route::get('/', 'showWorkflowSteps')->name('show.workflow.steps');
            });
        });
});
