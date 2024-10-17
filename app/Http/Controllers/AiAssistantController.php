<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\AiAssistantRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\AiAssistant\ShowAiAssistantsRequest;
use App\Http\Requests\Models\AiAssistant\CreateAiAssistantRequest;
use App\Http\Requests\Models\AiAssistant\UpdateAiAssistantRequest;
use App\Http\Requests\Models\AiAssistant\DeleteAiAssistantsRequest;

class AiAssistantController extends BaseController
{
    /**
     *  @var AiAssistantRepository
     */
    protected $repository;

    /**
     * AiAssistantController constructor.
     *
     * @param AiAssistantRepository $repository
     */
    public function __construct(AiAssistantRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show AI assistants.
     *
     * @param ShowAiAssistantsRequest $request
     * @return JsonResponse
     */
    public function showAiAssistants(ShowAiAssistantsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiAssistants($request->all()));
    }

    /**
     * Create AI assistant.
     *
     * @param CreateAiAssistantRequest $request
     * @return JsonResponse
     */
    public function createAiAssistant(CreateAiAssistantRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createAiAssistant($request->all()));
    }

    /**
     * Delete AI assistants.
     *
     * @param DeleteAiAssistantsRequest $request
     * @return JsonResponse
     */
    public function deleteAiAssistants(DeleteAiAssistantsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAiAssistants($request->input('ai_assistant_ids')));
    }

    /**
     * Show AI assistant.
     *
     * @param string $aiAssistantId
     * @return JsonResponse
     */
    public function showAiAssistant(string $aiAssistantId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiAssistant($aiAssistantId));
    }

    /**
     * Update AI assistant.
     *
     * @param UpdateAiAssistantRequest $request
     * @param string $aiAssistantId
     * @return JsonResponse
     */
    public function updateAiAssistant(UpdateAiAssistantRequest $request, string $aiAssistantId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateAiAssistant($aiAssistantId, $request->all()));
    }

    /**
     * Delete AI assistant.
     *
     * @param string $aiAssistantId
     * @return JsonResponse
     */
    public function deleteAiAssistant(string $aiAssistantId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAiAssistant($aiAssistantId));
    }

    /**
     * Assess AI assistant usage eligibility.
     *
     * @param string $aiAssistantId
     * @return JsonResponse
     */
    public function assessAiAssistantUsageEligibility(string $aiAssistantId): JsonResponse
    {
        return $this->prepareOutput($this->repository->assessAiAssistantUsageEligibility($aiAssistantId));
    }
}
