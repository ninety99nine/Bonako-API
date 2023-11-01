<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\SubscriptionPlanRepository;

class SubscriptionPlanController extends BaseController
{
    /**
     *  @var SubscriptionPlanRepository
     */
    protected $repository;

    public function showSubscriptionPlans()
    {
        return response($this->repository->showSubscriptionPlans()->transform(), Response::HTTP_OK);
    }
}
