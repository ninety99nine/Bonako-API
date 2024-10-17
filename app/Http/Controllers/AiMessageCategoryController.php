<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\AiMessageCategoryRepository;
use App\Http\Requests\Models\AiMessageCategory\ShowAiMessageCategoriesRequest;
use App\Http\Requests\Models\AiMessageCategory\CreateAiMessageCategoryRequest;
use App\Http\Requests\Models\AiMessageCategory\UpdateAiMessageCategoryRequest;
use App\Http\Requests\Models\AiMessageCategory\DeleteAiMessageCategoriesRequest;

class AiMessageCategoryController extends BaseController
{
    /**
     *  @var AiMessageCategoryRepository
     */
    protected $repository;

    /**
     * AiMessageCategoryController constructor.
     *
     * @param AiMessageCategoryRepository $repository
     */
    public function __construct(AiMessageCategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show AI message category categories.
     *
     * @param ShowAiMessageCategoriesRequest $request
     * @return JsonResponse
     */
    public function showAiMessageCategories(ShowAiMessageCategoriesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiMessageCategories($request->all()));
    }

    /**
     * Create AI message category.
     *
     * @param CreateAiMessageCategoryRequest $request
     * @return JsonResponse
     */
    public function createAiMessageCategory(CreateAiMessageCategoryRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createAiMessageCategory($request->all()));
    }

    /**
     * Delete AI message category categories.
     *
     * @param DeleteAiMessageCategoriesRequest $request
     * @return JsonResponse
     */
    public function deleteAiMessageCategories(DeleteAiMessageCategoriesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAiMessageCategories($request->input('ai_message_category_ids')));
    }

    /**
     * Show AI message category.
     *
     * @param string $aiMessageCategoryId
     * @return JsonResponse
     */
    public function showAiMessageCategory(string $aiMessageCategoryId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiMessageCategory($aiMessageCategoryId));
    }

    /**
     * Update AI message category.
     *
     * @param UpdateAiMessageCategoryRequest $request
     * @param string $aiMessageCategoryId
     * @return JsonResponse
     */
    public function updateAiMessageCategory(UpdateAiMessageCategoryRequest $request, string $aiMessageCategoryId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateAiMessageCategory($aiMessageCategoryId, $request->all()));
    }

    /**
     * Delete AI message category.
     *
     * @param string $aiMessageCategoryId
     * @return JsonResponse
     */
    public function deleteAiMessageCategory(string $aiMessageCategoryId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAiMessageCategory($aiMessageCategoryId));
    }
}
