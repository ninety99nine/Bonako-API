<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\OccasionRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Occasion\ShowOccasionsRequest;
use App\Http\Requests\Models\Occasion\CreateOccasionRequest;
use App\Http\Requests\Models\Occasion\UpdateOccasionRequest;
use App\Http\Requests\Models\Occasion\DeleteOccasionsRequest;

class OccasionController extends BaseController
{
    /**
     *  @var OccasionRepository
     */
    protected $repository;

    /**
     * OccasionController constructor.
     *
     * @param OccasionRepository $repository
     */
    public function __construct(OccasionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show occasions.
     *
     * @param ShowOccasionRequest $request
     * @return JsonResponse
     */
    public function showOccasions(ShowOccasionsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showOccasions($request->all()));
    }

    /**
     * Create occasion.
     *
     * @param CreateOccasionRequest $request
     * @return JsonResponse
     */
    public function createOccasion(CreateOccasionRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createOccasion($request->all()));
    }

    /**
     * Delete occasions.
     *
     * @param DeleteOccasionsRequest $request
     * @return JsonResponse
     */
    public function deleteOccasions(DeleteOccasionsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteOccasions($request->input('occasion_ids')));
    }

    /**
     * Show occasion.
     *
     * @param string $occasionId
     * @return JsonResponse
     */
    public function showOccasion(string $occasionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showOccasion($occasionId));
    }

    /**
     * Update occasion.
     *
     * @param UpdateOccasionRequest $request
     * @param string $occasionId
     * @return JsonResponse
     */
    public function updateOccasion(UpdateOccasionRequest $request, string $occasionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateOccasion($occasionId, $request->all()));
    }

    /**
     * Delete occasion.
     *
     * @param string $occasionId
     * @return JsonResponse
     */
    public function deleteOccasion(string $occasionId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteOccasion($occasionId));
    }
}
