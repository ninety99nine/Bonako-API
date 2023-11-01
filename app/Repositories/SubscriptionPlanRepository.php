<?php

namespace App\Repositories;

use App\Models\SubscriptionPlan;
use App\Repositories\BaseRepository;

class SubscriptionPlanRepository extends BaseRepository
{
    public function showSubscriptionPlans()
    {
        return $this->get();
    }
}
