<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\AddressRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Address\CreateAddressRequest;
use App\Http\Requests\Models\Address\UpdateAddressRequest;
use App\Http\Requests\Models\Address\DeleteAddressesRequest;
use App\Http\Requests\Models\Address\ValidateAddAddressRequest;

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
     * Create address.
     *
     * @param CreateAddressRequest $request
     * @return JsonResponse
     */
    public function createAddress(CreateAddressRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createAddress($request->all()));
    }

    /**
     * Delete addresses.
     *
     * @param DeleteAddressesRequest $request
     * @return JsonResponse
     */
    public function deleteAddresses(DeleteAddressesRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAddresses($request->input('address_ids')));
    }

    /**
     * Validate add address.
     *
     * @param ValidateAddAddressRequest $request
     * @return JsonResponse
     */
    public function validateAddAddress(ValidateAddAddressRequest $request): JsonResponse
    {
        return $this->prepareOutput([
            'complete_address' => $this->completeAddress($request->input('address_line'),$request->input('address_line2'),$request->input('city'),$request->input('state'),$request->input('postal_code'),$request->input('country'))
        ]);
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
     * Delete address.
     *
     * @param string $addressId
     * @return JsonResponse
     */
    public function deleteAddress(string $addressId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteAddress($addressId));
    }
}
