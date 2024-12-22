<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\WorkflowRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Workflow\ShowWorkflowsRequest;
use App\Http\Requests\Models\Workflow\CreateWorkflowRequest;
use App\Http\Requests\Models\Workflow\UpdateWorkflowRequest;
use App\Http\Requests\Models\Workflow\DeleteWorkflowsRequest;
use App\Http\Requests\Models\Workflow\UpdateWorkflowArrangementRequest;

class WorkflowController extends BaseController
{
    /**
     *  @var WorkflowRepository
     */
    protected $repository;

    /**
     * WorkflowController constructor.
     *
     * @param WorkflowRepository $repository
     */
    public function __construct(WorkflowRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show workflows.
     *
     * @param ShowWorkflowsRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showWorkflows(ShowWorkflowsRequest $request, string|null $storeId = null): JsonResponse
    {
        return $this->prepareOutput($this->repository->showWorkflows($storeId ?? $request->input('store_id')));
    }

    /**
     * Create workflow.
     *
     * @param CreateWorkflowRequest $request
     * @return JsonResponse
     */
    public function createWorkflow(CreateWorkflowRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createWorkflow($request->all()));
    }

    /**
     * Delete workflows.
     *
     * @param DeleteWorkflowsRequest $request
     * @return JsonResponse
     */
    public function deleteWorkflows(DeleteWorkflowsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteWorkflows($request->all()));
    }

    /**
     * Show workflow options.
     *
     * @return JsonResponse
     */
    public function showWorkflowOptions()
    {
        return $this->prepareOutput($this->repository->showWorkflowOptions());
    }

    /**
     * Update workflow arrangement.
     *
     * @param UpdateWorkflowArrangementRequest $request
     * @return JsonResponse
     */
    public function updateWorkflowArrangement(UpdateWorkflowArrangementRequest $request)
    {
        return $this->prepareOutput($this->repository->updateWorkflowArrangement($request->all()));
    }

    /**
     * Show workflow.
     *
     * @param string $workflowId
     * @return JsonResponse
     */
    public function showWorkflow(string $workflowId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showWorkflow($workflowId));
    }

    /**
     * Update workflow.
     *
     * @param UpdateWorkflowRequest $request
     * @param string $workflowId
     * @return JsonResponse
     */
    public function updateWorkflow(UpdateWorkflowRequest $request, string $workflowId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateWorkflow($workflowId, $request->all()));
    }

    /**
     * Delete workflow.
     *
     * @param string $workflowId
     * @return JsonResponse
     */
    public function deleteWorkflow(string $workflowId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteWorkflow($workflowId));
    }
}
