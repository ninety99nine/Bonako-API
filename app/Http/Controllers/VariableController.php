<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\VariableRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Variable\ShowVariablesRequest;

class VariableController extends BaseController
{
    /**
     *  @var VariableRepository
     */
    protected $repository;

    /**
     * VariableController constructor.
     *
     * @param VariableRepository $repository
     */
    public function __construct(VariableRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show variables.
     *
     * @param ShowVariablesRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showVariables(ShowVariablesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showVariables());
    }

    /**
     * Show variable.
     *
     * @param string $variableId
     * @return JsonResponse
     */
    public function showVariable(string $variableId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showVariable($variableId));
    }
}
