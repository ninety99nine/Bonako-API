<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\StoreQuotaRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\StoreQuota\ShowStoreQuotasRequest;
use App\Http\Requests\Models\StoreQuota\CreateStoreQuotaRequest;
use App\Http\Requests\Models\StoreQuota\UpdateStoreQuotaRequest;
use App\Http\Requests\Models\StoreQuota\DeleteStoreQuotasRequest;

class StoreQuotaController extends BaseController
{
    /**
     *  @var StoreQuotaRepository
     */
    protected $repository;

    /**
     * StoreQuotaController constructor.
     *
     * @param StoreQuotaRepository $repository
     */
    public function __construct(StoreQuotaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show store quotas.
     *
     * @param ShowStoreQuotaRequest $request
     * @return JsonResponse
     */
    public function showStoreQuotas(ShowStoreQuotasRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showStoreQuotas($request->all()));
    }

    /**
     * Create store quota.
     *
     * @param CreateStoreQuotaRequest $request
     * @return JsonResponse
     */
    public function createStoreQuota(CreateStoreQuotaRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createStoreQuota($request->all()));
    }

    /**
     * Delete store quotas.
     *
     * @param DeleteStoreQuotasRequest $request
     * @return JsonResponse
     */
    public function deleteStoreQuotas(DeleteStoreQuotasRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteStoreQuotas($request->input('store_quota_ids')));
    }

    /**
     * Show store quota.
     *
     * @param string $storeQuotaId
     * @return JsonResponse
     */
    public function showStoreQuota(string $storeQuotaId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showStoreQuota($storeQuotaId));
    }

    /**
     * Update store quota.
     *
     * @param UpdateStoreQuotaRequest $request
     * @param string $storeQuotaId
     * @return JsonResponse
     */
    public function updateStoreQuota(UpdateStoreQuotaRequest $request, string $storeQuotaId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateStoreQuota($storeQuotaId, $request->all()));
    }

    /**
     * Delete store quota.
     *
     * @param string $storeQuotaId
     * @return JsonResponse
     */
    public function deleteStoreQuota(string $storeQuotaId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteStoreQuota($storeQuotaId));
    }
}
