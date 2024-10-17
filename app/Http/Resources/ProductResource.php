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
        $product = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.product', route('show.product', ['productId' => $product->id])),
            new ResourceLink('update.product', route('update.product', ['productId' => $product->id])),
            new ResourceLink('delete.product', route('delete.product', ['productId' => $product->id])),
            new ResourceLink('show.product.photos', route('show.product.photos', ['productId' => $product->id])),
            new ResourceLink('create.product.photo', route('create.product.photo', ['productId' => $product->id])),
            new ResourceLink('show.product.variations', route('show.product.variations', ['productId' => $product->id])),
            new ResourceLink('create.product.variations', route('create.product.variations', ['productId' => $product->id])),
        ];
    }
}
