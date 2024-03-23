<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Http\Controllers\Base\BaseController;
use App\Repositories\SubscriptionRepository;

class SubscriptionController extends BaseController
{
    /**
     *  @var SubscriptionRepository
     */
    protected $repository;

    public function showSubscriptions()
    {
        return $this->prepareOutput($this->repository->showSubscriptions());
    }
}
