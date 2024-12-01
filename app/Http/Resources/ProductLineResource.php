<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class ProductLineResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $productLine = $this->resource;
        $productId = $this->resource->product_id;

        //  Note: The product line ID does not exist for shopping carts
        if($productLine->id) {
            array_push($this->resourceLinks,
                new ResourceLink('show.product.line', route('show.product.line', ['productLineId' => $productLine->id]))
            );
        }

        if($productId) {
            array_push($this->resourceLinks,
                new ResourceLink('show.product', route('show.product', ['productId' => $productId]))
            );
        }
    }
}
