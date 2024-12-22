<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\WorkflowStepRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\WorkflowStep\ShowWorkflowStepsRequest;
use App\Http\Requests\Models\WorkflowStep\CreateWorkflowStepRequest;
use App\Http\Requests\Models\WorkflowStep\UpdateWorkflowStepRequest;
use App\Http\Requests\Models\WorkflowStep\DeleteWorkflowStepsRequest;
use App\Http\Requests\Models\WorkflowStep\UpdateWorkflowStepArrangementRequest;

class WorkflowStepController extends BaseController
{
    /**
     *  @var WorkflowStepRepository
     */
    protected $repository;

    /**
     * WorkflowStepController constructor.
     *
     * @param WorkflowStepRepository $repository
     */
    public function __construct(WorkflowStepRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show workflow steps.
     *
     * @param ShowWorkflowStepsRequest $request
     * @param string|null $workflowId
     * @return JsonResponse
     */
    public function showWorkflowSteps(ShowWorkflowStepsRequest $request, string|null $workflowId = null): JsonResponse
    {
        return $this->prepareOutput($this->repository->showWorkflowSteps($workflowId ?? $request->input('workflow_id')));
    }

    /**
     * Create workflow step.
     *
     * @param CreateWorkflowStepRequest $request
     * @return JsonResponse
     */
    public function createWorkflowStep(CreateWorkflowStepRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createWorkflowStep($request->all()));
    }

    /**
     * Delete workflow steps.
     *
     * @param DeleteWorkflowStepsRequest $request
     * @return JsonResponse
     */
    public function deleteWorkflowSteps(DeleteWorkflowStepsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteWorkflowSteps($request->all()));
    }

    /**
     * Update workflow step arrangement.
     *
     * @param UpdateWorkflowStepArrangementRequest $request
     * @return JsonResponse
     */
    public function updateWorkflowStepArrangement(UpdateWorkflowStepArrangementRequest $request)
    {
        return $this->prepareOutput($this->repository->updateWorkflowStepArrangement($request->all()));
    }

    /**
     * Show workflow step.
     *
     * @param string $workflowStepId
     * @return JsonResponse
     */
    public function showWorkflowStep(string $workflowStepId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showWorkflowStep($workflowStepId));
    }

    /**
     * Update workflow step.
     *
     * @param UpdateWorkflowStepRequest $request
     * @param string $workflowStepId
     * @return JsonResponse
     */
    public function updateWorkflowStep(UpdateWorkflowStepRequest $request, string $workflowStepId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateWorkflowStep($workflowStepId, $request->all()));
    }

    /**
     * Delete workflow step.
     *
     * @param string $workflowStepId
     * @return JsonResponse
     */
    public function deleteWorkflowStep(string $workflowStepId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteWorkflowStep($workflowStepId));
    }
}
