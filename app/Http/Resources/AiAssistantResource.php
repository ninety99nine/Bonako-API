<?php

namespace App\Http\Resources;

use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\SubscriptionRepository;

class AiAssistantResource extends BaseResource
{
    use BaseTrait;

    protected $resourceRelationships = [
        'subscription' => SubscriptionRepository::class
    ];

    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {

    }
}
