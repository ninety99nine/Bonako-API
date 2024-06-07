<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class ProductLineResource extends BaseResource
{

    public function setLinks()
    {
        $routeNamePrefix = 'product.';
        $storeId = $this->resource->store_id;
        $productId = $this->resource->product_id;

        if(!is_null($productId)) {

            array_push($this->resourceLinks, ...[
                new ResourceLink('show.product', route($routeNamePrefix.'show', ['store' => $storeId, 'product' => $productId]), 'Show product')
            ]);

        }
    }
}
