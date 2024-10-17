<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\CustomerRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\Customer\ShowCustomersRequest;
use App\Http\Requests\Models\Customer\CreateCustomerRequest;
use App\Http\Requests\Models\Customer\UpdateCustomerRequest;
use App\Http\Requests\Models\Customer\DeleteCustomersRequest;

class CustomerController extends BaseController
{
    /**
     *  @var CustomerRepository
     */
    protected $repository;

    /**
     * CustomerController constructor.
     *
     * @param CustomerRepository $repository
     */
    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show customers.
     *
     * @param ShowCustomersRequest $request
     * @param string|null $storeId
     * @return JsonResponse
     */
    public function showCustomers(ShowCustomersRequest $request, string|null $storeId = null): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCustomers($storeId ?? $request->input('store_id')));
    }

    /**
     * Create customer.
     *
     * @param CreateCustomerRequest $request
     * @return JsonResponse
     */
    public function createCustomer(CreateCustomerRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createCustomer($request->all()));
    }

    /**
     * Delete customers.
     *
     * @param DeleteCustomersRequest $request
     * @return JsonResponse
     */
    public function deleteCustomers(DeleteCustomersRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteCustomers($request->all()));
    }

    /**
     * Show customer.
     *
     * @param string $customerId
     * @return JsonResponse
     */
    public function showCustomer(string $customerId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCustomer($customerId));
    }

    /**
     * Update customer.
     *
     * @param UpdateCustomerRequest $request
     * @param string $customerId
     * @return JsonResponse
     */
    public function updateCustomer(UpdateCustomerRequest $request, string $customerId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updateCustomer($customerId, $request->all()));
    }

    /**
     * Delete customer.
     *
     * @param string $customerId
     * @return JsonResponse
     */
    public function deleteCustomer(string $customerId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deleteCustomer($customerId));
    }

    /**
     * Show customer orders.
     *
     * @param string $customerId
     * @return JsonResponse
     */
    public function showCustomerOrders(string $customerId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCustomerOrders($customerId));
    }

    /**
     * Show customer transactions.
     *
     * @param string $customerId
     * @return JsonResponse
     */
    public function showCustomerTransactions(string $customerId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showCustomerTransactions($customerId));
    }
}
