<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class OccasionResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $occasion = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.occasion', route('show.occasion', ['occasionId' => $occasion->id])),
            new ResourceLink('update.occasion', route('update.occasion', ['occasionId' => $occasion->id])),
            new ResourceLink('delete.occasion', route('delete.occasion', ['occasionId' => $occasion->id])),
        ];
    }
}
