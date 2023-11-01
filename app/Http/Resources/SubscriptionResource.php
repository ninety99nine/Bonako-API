<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\TransactionRepository;
use App\Http\Resources\Helpers\ResourceLink;

class SubscriptionResource extends BaseResource
{
    protected $resourceRelationships = [
        'transaction' => TransactionRepository::class
    ];
}
