<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\VariableRepository;
use App\Http\Resources\Helpers\ResourceLink;

class ProductResource extends BaseResource
{
    //  protected $customExcludeFields = ['user_id', 'parent_product_id'];
    protected $resourceRelationships = [
        'variables' => VariableRepository::class
    ];

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
        $routeNamePrefix = 'product.';
        $productId = $this->resource->id;
        $storeId = $this->resource->store_id;

        $this->resourceLinks = [
            new ResourceLink('self', route($routeNamePrefix.'show', ['store' => $storeId, 'product' => $productId]), 'Show product'),
            new ResourceLink('update.product', route($routeNamePrefix.'update', ['store' => $storeId, 'product' => $productId]), 'Update product'),
            new ResourceLink('delete.product', route($routeNamePrefix.'delete', ['store' => $storeId, 'product' => $productId]), 'Delete product'),
            new ResourceLink('confirm.delete.product', route($routeNamePrefix.'confirm.delete', ['store' => $storeId, 'product' => $productId]), 'Confirm delete product'),

            new ResourceLink('show.photo', route($routeNamePrefix.'photo.show', ['store' => $storeId, 'product' => $productId]), 'Show store photo'),
            new ResourceLink('update.photo', route($routeNamePrefix.'photo.update', ['store' => $storeId, 'product' => $productId]), 'Update store photo'),
            new ResourceLink('delete.photo', route($routeNamePrefix.'photo.delete', ['store' => $storeId, 'product' => $productId]), 'Delete store photo'),

            new ResourceLink('show.variations', route($routeNamePrefix.'variations.show', ['store' => $storeId, 'product' => $productId]), 'Show variations'),
            new ResourceLink('create.variations', route($routeNamePrefix.'variations.create', ['store' => $storeId, 'product' => $productId]), 'Show variations'),
        ];

        if(!is_null($this->resource->parent_product_id)) {

            array_push($this->resourceLinks, ...[
                new ResourceLink('show.parent.product', route($routeNamePrefix.'show', ['store' => $storeId, 'product' => $this->resource->parent_product_id]), 'Show parent product'),
            ]);

        }
    }
}
