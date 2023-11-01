<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Response;
use App\Repositories\PaymentMethodRepository;
use App\Http\Controllers\Base\BaseController;

class PaymentMethodController extends BaseController
{
    /**
     *  @var PaymentMethodRepository
     */
    protected $repository;

    public function showPaymentMethodFilters()
    {
        return response($this->repository->showPaymentMethodFilters(), Response::HTTP_OK);
    }

    public function showPaymentMethods()
    {
        return response($this->repository->showPaymentMethods()->transform(), Response::HTTP_OK);
    }
}
