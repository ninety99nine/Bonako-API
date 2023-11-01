<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\CouponLineRepository;
use App\Repositories\ProductLineRepository;
use App\Http\Resources\Helpers\ResourceLink;

class CartResource extends BaseResource
{
    protected $resourceRelationships = [
        'productLines' => ProductLineRepository::class,
        'couponLines' => CouponLineRepository::class
    ];
}
