<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Order;
use Illuminate\Http\Response;
use App\Repositories\OrderRepository;
use App\Http\Requests\Models\DeleteRequest;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\UncancelRequest;
use App\Http\Requests\Models\Order\ShowOrdersRequest;
use App\Http\Requests\Models\Order\UpdateOrderRequest;
use App\Http\Requests\Models\Order\CancelOrderRequest;
use App\Http\Requests\Models\Order\UpdateStatusRequest;
use App\Http\Requests\Models\Order\RequestPaymentRequest;
use App\Http\Requests\Models\Order\MarkAsUnverifiedPaymentRequest;

class OrderController extends BaseController
{
    /**
     *  Explicit Route Model Binding
     *  Reference: https://laravel.com/docs/10.x/routing#customizing-the-resolution-logic
     *  ---------------------------------------------------------------------------------
     *
     *  The store and order are loaded on each controller method using the technique
     *  of explicit route model binding (Refer to the RouteServiceProvider.php file).
     *  This allows us to load the associated store and order with respect to the
     *  current authenticated user. This means that were possible, we can load
     *  the (user and store association) or (user and order association) pivot
     *  tables. This allows us to inspect the relationship that the user
     *  might have with respect to that specified store or order. Taking
     *  this into consideration, we can then access these associations
     *  to decide how to handle the given request or determine the
     *  right information to return based on these associations.
     *
     *  Although the $store model is loaded but not used particularly on some of
     *  these controller moethods, it still allows us to explicitly query the
     *  order with respect to that $store (see RouteServiceProvider.php file).
     *  In this case, while resolving the order we can access this store by
     *  using the request()->store convention since the store will now be
     *  accessible on the request.
     */

    /**
     *  @var OrderRepository
     */
    protected $repository;

    public function show(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->show()->transform(), Response::HTTP_OK);
    }

    public function update(UpdateOrderRequest $request, Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->updateOrder($store)->transform(), Response::HTTP_OK);
    }

    public function showCart(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showOrderCart()->transform(), Response::HTTP_OK);
    }

    public function showCustomer(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showOrderCustomer()->transform(), Response::HTTP_OK);
    }

    public function showOccasion(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showOccasion()->transform(), Response::HTTP_OK);
    }

    public function showDeliveryAddress(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showDeliveryAddress()->transform(), Response::HTTP_OK);
    }

    public function cancel(CancelOrderRequest $request, Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->cancelOrder($request)->transform(), Response::HTTP_OK);
    }

    public function uncancel(UncancelRequest $request, Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->uncancelOrder($request)->transform(), Response::HTTP_OK);
    }

    public function showCancellationReasons(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showCancellationReasons(), Response::HTTP_OK);
    }

    public function generateCollectionCode(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->generateCollectionCode(), Response::HTTP_OK);
    }

    public function revokeCollectionCode(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->revokeCollectionCode(), Response::HTTP_OK);
    }

    public function updateStatus(UpdateStatusRequest $request, Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->updateStatus($request)->transform(), Response::HTTP_OK);
    }

    public function showViewers(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showViewers()->transform(), Response::HTTP_OK);
    }

    public function requestPayment(RequestPaymentRequest $request, Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->requestPayment($request)->transform(), Response::HTTP_OK);
    }

    public function showRequestPaymentPaymentMethods(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showRequestPaymentPaymentMethods($store)->transform(), Response::HTTP_OK);
    }

    public function markAsVerifiedPayment(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->markAsVerifiedPayment()->transform(), Response::HTTP_OK);
    }

    public function markAsUnverifiedPayment(MarkAsUnverifiedPaymentRequest $request, Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->markAsUnverifiedPayment($request)->transform(), Response::HTTP_OK);
    }

    public function showMarkAsUnverifiedPaymentPaymentMethods(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showMarkAsUnverifiedPaymentPaymentMethods($store)->transform(), Response::HTTP_OK);
    }

    public function showOrderTransactionFilters(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showOrderTransactionFilters(), Response::HTTP_OK);
    }

    public function showOrderTransactions(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showOrderTransactions()->transform(), Response::HTTP_OK);
    }

    public function showOrderTransactionsCount(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->showOrderTransactionsCount(), Response::HTTP_OK);
    }

    public function confirmDelete(Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->generateDeleteConfirmationCode(), Response::HTTP_OK);
    }

    public function delete(DeleteRequest $request, Store $store, Order $order)
    {
        return response($this->repository->setModel($order)->delete(), Response::HTTP_OK);
    }
}
