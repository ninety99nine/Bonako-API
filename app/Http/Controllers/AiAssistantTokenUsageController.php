<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\AiAssistantTokenUsageRepository;
use App\Http\Requests\Models\AiAssistantTokenUsage\ShowAiAssistantTokenUsagesRequest;

class AiAssistantTokenUsageController extends BaseController
{
    /**
     *  @var AiAssistantTokenUsageRepository
     */
    protected $repository;

    /**
     * AiAssistantTokenUsageController constructor.
     *
     * @param AiAssistantTokenUsageRepository $repository
     */
    public function __construct(AiAssistantTokenUsageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show AI Assistant token usages.
     *
     * @param ShowAiAssistantTokenUsagesRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showAiAssistantTokenUsages(ShowAiAssistantTokenUsagesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiAssistantTokenUsages($request->all()));
    }

    /**
     * Show AI Assistant token usage.
     *
     * @param string $aiAssistantTokenUsageId
     * @return JsonResponse
     */
    public function showAiAssistantTokenUsage(string $aiAssistantTokenUsageId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiAssistantTokenUsage($aiAssistantTokenUsageId));
    }
}
