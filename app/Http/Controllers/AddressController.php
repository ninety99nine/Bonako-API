<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\AddressRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Address\AddAddressRequest;
use App\Http\Requests\Models\Address\UpdateAddressRequest;
use App\Http\Requests\Models\Address\RemoveAddressesRequest;

class AddressController extends BaseController
{
    protected AddressRepository $repository;

    /**
     * AddressController constructor.
     *
     * @param AddressRepository $repository
     */
    public function __construct(AddressRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show addresses.
     *
     * @return JsonResponse
     */
    public function showAddresses(): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAddresses());
    }

    /**
     * Add address.
     *
     * @param AddAddressRequest $request
     * @return JsonResponse
     */
    public function addAddress(AddAddressRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->addAddress($request->all()));
    }

    /**
     * Remove addresses.
     *
     * @param RemoveAddressesRequest $request
     * @return JsonResponse
     */
    public function removeAddresses(RemoveAddressesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->removeAddresses($request->input('address_ids')));
    }

    /**
     * Show address.
     *
     * @param string $addressId
     * @return JsonResponse
     */
    public function showAddress(string $addressId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showAddress($addressId));
    }

    /**
     * Update address.
     *
     * @param UpdateAddressRequest $request
     * @param string $addressId
     * @return JsonResponse
     */
    public function updateAddress(UpdateAddressRequest $request, string $addressId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateAddress($addressId, $request->all()));
    }

    /**
     * Remove address.
     *
     * @param string $addressId
     * @return JsonResponse
     */
    public function removeAddress(string $addressId): JsonResponse
    {
        return $this->prepareOutput($this->repository->removeAddress($addressId));
    }
}
