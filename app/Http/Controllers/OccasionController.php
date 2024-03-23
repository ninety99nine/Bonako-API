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
        return $this->prepareOutput($this->repository->showOccasions());
    }

    public function showOccasion(Occasion $occasion)
    {
        return $this->prepareOutput($this->setModel($occasion)->showOccasion());
    }
}
