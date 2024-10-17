<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\PaymentMethodRepository;
use App\Http\Controllers\Base\BaseController;
use App\Http\Requests\Models\PaymentMethod\ShowPaymentMethodsRequest;
use App\Http\Requests\Models\PaymentMethod\CreatePaymentMethodRequest;
use App\Http\Requests\Models\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Requests\Models\PaymentMethod\DeletePaymentMethodsRequest;

class PaymentMethodController extends BaseController
{
    /**
     *  @var PaymentMethodRepository
     */
    protected $repository;

    /**
     * PaymentMethodController constructor.
     *
     * @param PaymentMethodRepository $repository
     */
    public function __construct(PaymentMethodRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show payment methods.
     *
     * @param ShowPaymentMethodRequest $request
     * @return JsonResponse
     */
    public function showPaymentMethods(ShowPaymentMethodsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->showPaymentMethods($request->all()));
    }

    /**
     * Create payment method.
     *
     * @param CreatePaymentMethodRequest $request
     * @return JsonResponse
     */
    public function createPaymentMethod(CreatePaymentMethodRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->createPaymentMethod($request->all()));
    }

    /**
     * Delete payment methods.
     *
     * @param DeletePaymentMethodsRequest $request
     * @return JsonResponse
     */
    public function deletePaymentMethods(DeletePaymentMethodsRequest $request): JsonResponse
    {
        return $this->prepareOutput($this->repository->deletePaymentMethods($request->input('payment_method_ids')));
    }

    /**
     * Show payment method.
     *
     * @param string $paymentMethodId
     * @return JsonResponse
     */
    public function showPaymentMethod(string $paymentMethodId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showPaymentMethod($paymentMethodId));
    }

    /**
     * Update payment method.
     *
     * @param UpdatePaymentMethodRequest $request
     * @param string $paymentMethodId
     * @return JsonResponse
     */
    public function updatePaymentMethod(UpdatePaymentMethodRequest $request, string $paymentMethodId): JsonResponse
    {
        return $this->prepareOutput($this->repository->updatePaymentMethod($paymentMethodId, $request->all()));
    }

    /**
     * Delete payment method.
     *
     * @param string $paymentMethodId
     * @return JsonResponse
     */
    public function deletePaymentMethod(string $paymentMethodId): JsonResponse
    {
        return $this->prepareOutput($this->repository->deletePaymentMethod($paymentMethodId));
    }
}
