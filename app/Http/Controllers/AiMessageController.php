<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\AiMessageRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\AiMessage\ShowAiMessagesRequest;
use App\Http\Requests\Models\AiMessage\CreateAiMessageRequest;
use App\Http\Requests\Models\AiMessage\UpdateAiMessageRequest;
use App\Http\Requests\Models\AiMessage\DeleteAiMessagesRequest;

class AiMessageController extends BaseController
{
    /**
     *  @var AiMessageRepository
     */
    protected $repository;

    /**
     * AiMessageController constructor.
     *
     * @param AiMessageRepository $repository
     */
    public function __construct(AiMessageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show AI messages.
     *
     * @param ShowAiMessagesRequest $request
     * @return JsonResponse
     */
    public function showAiMessages(ShowAiMessagesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiMessages($request->all()));
    }

    /**
     * Create AI message.
     *
     * @param CreateAiMessageRequest $request
     * @return JsonResponse
     */
    public function createAiMessage(CreateAiMessageRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createAiMessage($request->all()));
    }

    /**
     * Delete AI messages.
     *
     * @param DeleteAiMessagesRequest $request
     * @return JsonResponse
     */
    public function deleteAiMessages(DeleteAiMessagesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAiMessages($request->input('ai_message_ids')));
    }

    /**
     * Show AI message.
     *
     * @param string $aiMessageId
     * @return JsonResponse
     */
    public function showAiMessage(string $aiMessageId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiMessage($aiMessageId));
    }

    /**
     * Update AI message.
     *
     * @param UpdateAiMessageRequest $request
     * @param string $aiMessageId
     * @return JsonResponse
     */
    public function updateAiMessage(UpdateAiMessageRequest $request, string $aiMessageId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateAiMessage($aiMessageId, $request->all()));
    }

    /**
     * Delete AI message.
     *
     * @param string $aiMessageId
     * @return JsonResponse
     */
    public function deleteAiMessage(string $aiMessageId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAiMessage($aiMessageId));
    }
}
