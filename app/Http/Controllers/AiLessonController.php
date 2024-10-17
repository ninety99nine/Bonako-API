<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\AiLessonRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\AiLesson\ShowAiLessonsRequest;
use App\Http\Requests\Models\AiLesson\CreateAiLessonRequest;
use App\Http\Requests\Models\AiLesson\UpdateAiLessonRequest;
use App\Http\Requests\Models\AiLesson\DeleteAiLessonsRequest;

class AiLessonController extends BaseController
{
    /**
     *  @var AiLessonRepository
     */
    protected $repository;

    /**
     * AiLessonController constructor.
     *
     * @param AiLessonRepository $repository
     */
    public function __construct(AiLessonRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show AI lessons.
     *
     * @param ShowAiLessonsRequest $request
     * @return JsonResponse
     */
    public function showAiLessons(ShowAiLessonsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiLessons($request->all()));
    }

    /**
     * Create AI lesson.
     *
     * @param CreateAiLessonRequest $request
     * @return JsonResponse
     */
    public function createAiLesson(CreateAiLessonRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createAiLesson($request->all()));
    }

    /**
     * Delete AI lessons.
     *
     * @param DeleteAiLessonsRequest $request
     * @return JsonResponse
     */
    public function deleteAiLessons(DeleteAiLessonsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAiLessons($request->input('ai_lesson_ids')));
    }

    /**
     * Show AI lesson.
     *
     * @param string $aiLessonId
     * @return JsonResponse
     */
    public function showAiLesson(string $aiLessonId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAiLesson($aiLessonId));
    }

    /**
     * Update AI lesson.
     *
     * @param UpdateAiLessonRequest $request
     * @param string $aiLessonId
     * @return JsonResponse
     */
    public function updateAiLesson(UpdateAiLessonRequest $request, string $aiLessonId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateAiLesson($aiLessonId, $request->all()));
    }

    /**
     * Delete AI lesson.
     *
     * @param string $aiLessonId
     * @return JsonResponse
     */
    public function deleteAiLesson(string $aiLessonId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAiLesson($aiLessonId));
    }
}
