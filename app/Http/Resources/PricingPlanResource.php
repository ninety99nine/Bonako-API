<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class PricingPlanResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $pricingPlan = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.pricing.plan', route('show.pricing.plan', ['pricingPlanId' => $pricingPlan->id])),
            new ResourceLink('update.pricing.plan', route('update.pricing.plan', ['pricingPlanId' => $pricingPlan->id])),
            new ResourceLink('delete.pricing.plan', route('delete.pricing.plan', ['pricingPlanId' => $pricingPlan->id])),
            new ResourceLink('show.pricing.plan.payment.methods', route('show.pricing.plan.payment.methods', ['pricingPlanId' => $pricingPlan->id])),
            new ResourceLink('pay.pricing.plan', route('pay.pricing.plan', ['pricingPlanId' => $pricingPlan->id])),
        ];
    }
}
