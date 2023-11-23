<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\UserRepository;
use App\Repositories\StoreRepository;
use App\Http\Resources\Helpers\ResourceLink;

class ReviewResource extends BaseResource
{
    protected $resourceRelationships = [
        'user' => UserRepository::class,
        'store' => StoreRepository::class,
    ];
}
