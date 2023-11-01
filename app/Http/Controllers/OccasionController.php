<?php

namespace App\Http\Controllers;

use App\Models\Occasion;
use Illuminate\Http\Response;
use App\Repositories\OccasionRepository;
use App\Http\Controllers\Base\BaseController;

class OccasionController extends BaseController
{
    /**
     *  @var OccasionRepository
     */
    protected $repository;

    public function showOccasions()
    {
        return response($this->repository->showOccasions()->transform(), Response::HTTP_OK);
    }

    public function showOccasion(Occasion $occasion)
    {
        return response($this->repository->setModel($occasion)->showOccasion()->transform(), Response::HTTP_OK);
    }
}
