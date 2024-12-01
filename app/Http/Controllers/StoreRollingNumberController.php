<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\StoreRollingNumberRepository;
use App\Http\Requests\Models\StoreRollingNumber\ShowStoreRollingNumbersRequest;
use App\Http\Requests\Models\StoreRollingNumber\CreateStoreRollingNumberRequest;
use App\Http\Requests\Models\StoreRollingNumber\UpdateStoreRollingNumberRequest;
use App\Http\Requests\Models\StoreRollingNumber\DeleteStoreRollingNumbersRequest;

class StoreRollingNumberController extends BaseController
{
    /**
     *  @var StoreRollingNumberRepository
     */
    protected $repository;

    /**
     * StoreRollingNumberController constructor.
     *
     * @param StoreRollingNumberRepository $repository
     */
    public function __construct(StoreRollingNumberRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show store rolling numbers.
     *
     * @param ShowStoreRollingNumbersRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showStoreRollingNumbers(ShowStoreRollingNumbersRequest $request, string|null $storeId = null): JsonResponse
    {
        return $this->prepareOutput($this->repository->showStoreRollingNumbers($storeId ?? $request->input('store_id')));
    }

    /**
     * Create store rolling number.
     *
     * @param CreateStoreRollingNumberRequest $request
     * @return JsonResponse
     */
    public function createStoreRollingNumber(CreateStoreRollingNumberRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createStoreRollingNumber($request->all()));
    }

    /**
     * Delete store rolling numbers.
     *
     * @param DeleteStoreRollingNumbersRequest $request
     * @return JsonResponse
     */
    public function deleteStoreRollingNumbers(DeleteStoreRollingNumbersRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteStoreRollingNumbers($request->all()));
    }

    /**
     * Show store rolling number.
     *
     * @param string $storeRollingNumberId
     * @return JsonResponse
     */
    public function showStoreRollingNumber(string $storeRollingNumberId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showStoreRollingNumber($storeRollingNumberId));
    }

    /**
     * Update store rolling number.
     *
     * @param UpdateStoreRollingNumberRequest $request
     * @param string $storeRollingNumberId
     * @return JsonResponse
     */
    public function updateStoreRollingNumber(UpdateStoreRollingNumberRequest $request, string $storeRollingNumberId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateStoreRollingNumber($storeRollingNumberId, $request->all()));
    }

    /**
     * Delete store rolling number.
     *
     * @param string $storeRollingNumberId
     * @return JsonResponse
     */
    public function deleteStoreRollingNumber(string $storeRollingNumberId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteStoreRollingNumber($storeRollingNumberId));
    }
}
