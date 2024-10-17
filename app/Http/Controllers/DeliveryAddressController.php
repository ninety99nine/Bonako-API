<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\DeliveryAddressRepository;
use App\Http\Requests\Models\DeliveryAddress\ShowDeliveryAddressesRequest;
use App\Http\Requests\Models\DeliveryAddress\CreateDeliveryAddressRequest;
use App\Http\Requests\Models\DeliveryAddress\UpdateDeliveryAddressRequest;
use App\Http\Requests\Models\DeliveryAddress\DeleteDeliveryAddressesRequest;

class DeliveryAddressController extends BaseController
{
    /**
     *  @var DeliveryAddressRepository
     */
    protected $repository;

    /**
     * DeliveryAddressController constructor.
     *
     * @param DeliveryAddressRepository $repository
     */
    public function __construct(DeliveryAddressRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show delivery addresses.
     *
     * @param ShowDeliveryAddressesRequest $request
     * @return JsonResponse
     */
    public function showDeliveryAddresses(ShowDeliveryAddressesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showDeliveryAddresses($request->all()));
    }

    /**
     * Create delivery address.
     *
     * @param CreateDeliveryAddressRequest $request
     * @return JsonResponse
     */
    public function createDeliveryAddress(CreateDeliveryAddressRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createDeliveryAddress($request->all()));
    }

    /**
     * Delete delivery addresses.
     *
     * @param DeleteDeliveryAddressesRequest $request
     * @return JsonResponse
     */
    public function deleteDeliveryAddresses(DeleteDeliveryAddressesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteDeliveryAddresses($request->input('delivery_address_ids')));
    }

    /**
     * Show delivery address.
     *
     * @param string $deliveryAddressId
     * @return JsonResponse
     */
    public function showDeliveryAddress(string $deliveryAddressId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showDeliveryAddress($deliveryAddressId));
    }

    /**
     * Update delivery address.
     *
     * @param UpdateDeliveryAddressRequest $request
     * @param string $deliveryAddressId
     * @return JsonResponse
     */
    public function updateDeliveryAddress(UpdateDeliveryAddressRequest $request, string $deliveryAddressId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateDeliveryAddress($deliveryAddressId, $request->all()));
    }

    /**
     * Delete delivery address.
     *
     * @param string $deliveryAddressId
     * @return JsonResponse
     */
    public function deleteDeliveryAddress(string $deliveryAddressId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteDeliveryAddress($deliveryAddressId));
    }
}
