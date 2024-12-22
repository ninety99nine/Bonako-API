<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class AddressResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $address = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.address', route('show.address', ['addressId' => $address->id])),
            new ResourceLink('update.address', route('update.address', ['addressId' => $address->id])),
            new ResourceLink('delete.address', route('delete.address', ['addressId' => $address->id])),
        ];
    }
}
