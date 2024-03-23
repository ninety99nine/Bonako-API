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
        return $this->prepareOutput($this->repository->showPaymentMethodFilters());
    }

    public function showPaymentMethods()
    {
        return $this->prepareOutput($this->repository->showPaymentMethods());
    }
}
