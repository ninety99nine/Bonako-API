<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class StoreQuotaResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $storeQuota = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.store.quota', route('show.store.quota', ['storeQuotaId' => $storeQuota->id])),
            new ResourceLink('update.store.quota', route('update.store.quota', ['storeQuotaId' => $storeQuota->id])),
            new ResourceLink('delete.store.quota', route('delete.store.quota', ['storeQuotaId' => $storeQuota->id])),
        ];
    }
}
