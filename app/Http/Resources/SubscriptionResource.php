<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class SubscriptionResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $subscription = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.subscription', route('show.subscription', ['subscriptionId' => $subscription->id])),
            new ResourceLink('update.subscription', route('update.subscription', ['subscriptionId' => $subscription->id])),
            new ResourceLink('delete.subscription', route('delete.subscription', ['subscriptionId' => $subscription->id])),
        ];
    }
}
