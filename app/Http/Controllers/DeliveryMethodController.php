<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\DeliveryMethodRepository;
use App\Http\Requests\Models\DeliveryMethod\ShowDeliveryMethodsRequest;
use App\Http\Requests\Models\DeliveryMethod\CreateDeliveryMethodRequest;
use App\Http\Requests\Models\DeliveryMethod\UpdateDeliveryMethodRequest;
use App\Http\Requests\Models\DeliveryMethod\DeleteDeliveryMethodsRequest;
use App\Http\Requests\Models\DeliveryMethod\UpdateDeliveryMethodArrangementRequest;
use App\Http\Requests\Models\DeliveryMethod\ShowDeliveryMethodScheduleOptionsRequest;

class DeliveryMethodController extends BaseController
{
    /**
     *  @var DeliveryMethodRepository
     */
    protected $repository;

    /**
     * DeliveryMethodController constructor.
     *
     * @param DeliveryMethodRepository $repository
     */
    public function __construct(DeliveryMethodRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show delivery methods.
     *
     * @param ShowDeliveryMethodsRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showDeliveryMethods(ShowDeliveryMethodsRequest $request, string|null $storeId = null): JsonResponse
    {
        return $this->prepareOutput($this->repository->showDeliveryMethods($storeId ?? $request->input('store_id')));
    }

    /**
     * Create delivery method.
     *
     * @param CreateDeliveryMethodRequest $request
     * @return JsonResponse
     */
    public function createDeliveryMethod(CreateDeliveryMethodRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createDeliveryMethod($request->all()));
    }

    /**
     * Delete delivery methods.
     *
     * @param DeleteDeliveryMethodsRequest $request
     * @return JsonResponse
     */
    public function deleteDeliveryMethods(DeleteDeliveryMethodsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteDeliveryMethods($request->all()));
    }

    /**
     * Update delivery method arrangement.
     *
     * @param UpdateDeliveryMethodArrangementRequest $request
     * @return JsonResponse
     */
    public function updateDeliveryMethodArrangement(UpdateDeliveryMethodArrangementRequest $request)
    {
        return $this->prepareOutput($this->repository->updateDeliveryMethodArrangement($request->all()));
    }

    /**
     * Show delivery method schedule options.
     *
     * @param ShowDeliveryMethodScheduleOptionsRequest $request
     * @return JsonResponse
     */
    public function showDeliveryMethodScheduleOptions(ShowDeliveryMethodScheduleOptionsRequest $request)
    {
        return $this->prepareOutput($this->repository->showDeliveryMethodScheduleOptions($request->all()));
    }

    /**
     * Show delivery method.
     *
     * @param string $deliveryMethodId
     * @return JsonResponse
     */
    public function showDeliveryMethod(string $deliveryMethodId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showDeliveryMethod($deliveryMethodId));
    }

    /**
     * Update delivery method.
     *
     * @param UpdateDeliveryMethodRequest $request
     * @param string $deliveryMethodId
     * @return JsonResponse
     */
    public function updateDeliveryMethod(UpdateDeliveryMethodRequest $request, string $deliveryMethodId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateDeliveryMethod($deliveryMethodId, $request->all()));
    }

    /**
     * Delete delivery method.
     *
     * @param string $deliveryMethodId
     * @return JsonResponse
     */
    public function deleteDeliveryMethod(string $deliveryMethodId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteDeliveryMethod($deliveryMethodId));
    }
}
