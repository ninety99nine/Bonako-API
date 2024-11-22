<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\ProductRepository;
use App\Http\Resources\Helpers\ResourceLink;

class VariableResource extends BaseResource
{
    /**
     *  When iterating over a collection, the constructor will receive the
     *  resource as the first parameter and then the index number as the
     *  second parameter. Note that the index is provided only if this
     *  resource is part of a resource collection, otherwise we
     *  default to null.
     */
    public function __construct($resource, $collectionIndex = null)
    {
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        /*
        $routeNamePrefix = 'variable.';
        $productId = $this->resource->id;
        $storeId = $this->resource->store_id;
        $storeId = $this->resource->store_id;

        $this->resourceLinks = [
            new ResourceLink('self', route($routeNamePrefix.'show', ['store' => $storeId, 'product' => $productId, 'variable' => $productId]), 'Show product')
        ];
        */
    }
}
