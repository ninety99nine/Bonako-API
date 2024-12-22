<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowStepController;

Route::controller(WorkflowStepController::class)
    ->prefix('workflow-steps')
    ->group(function () {
        Route::get('/', 'showWorkflowSteps')->name('show.workflow.steps');
        Route::post('/', 'createWorkflowStep')->name('create.workflow.step');
        Route::delete('/', 'deleteWorkflowSteps')->name('delete.workflow.steps');
        Route::post('/arrangement', 'updateWorkflowStepArrangement')->name('update.workflow.step.arrangement');

        //  Workflow Step
        Route::prefix('{workflowStepId}')->group(function () {
            Route::get('/', 'showWorkflowStep')->name('show.workflow.step');
            Route::put('/', 'updateWorkflowStep')->name('update.workflow.step');
            Route::delete('/', 'deleteWorkflowStep')->name('delete.workflow.step');
        });
});
