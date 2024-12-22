<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class DeliveryMethodResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $deliveryMethod = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.delivery.method', route('show.delivery.method', ['deliveryMethodId' => $deliveryMethod->id])),
            new ResourceLink('update.delivery.method', route('update.delivery.method', ['deliveryMethodId' => $deliveryMethod->id])),
            new ResourceLink('delete.delivery.method', route('delete.delivery.method', ['deliveryMethodId' => $deliveryMethod->id])),
        ];
    }
}
