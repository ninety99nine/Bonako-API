<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class StoreRollingNumberResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $storeRollingNumber = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.store.rolling.number', route('show.store.rolling.number', ['storeRollingNumberId' => $storeRollingNumber->id])),
            new ResourceLink('update.store.rolling.number', route('update.store.rolling.number', ['storeRollingNumberId' => $storeRollingNumber->id])),
            new ResourceLink('delete.store.rolling.number', route('delete.store.rolling.number', ['storeRollingNumberId' => $storeRollingNumber->id])),
        ];
    }
}
