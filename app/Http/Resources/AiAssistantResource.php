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
        $aiAssistant = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.ai.assistant', route('show.ai.assistant', ['aiAssistantId' => $aiAssistant->id])),
            new ResourceLink('update.ai.assistant', route('update.ai.assistant', ['aiAssistantId' => $aiAssistant->id])),
            new ResourceLink('delete.ai.assistant', route('delete.ai.assistant', ['aiAssistantId' => $aiAssistant->id])),
            new ResourceLink('assess.ai.assistant.usage.eligibility', route('assess.ai.assistant.usage.eligibility', ['aiAssistantId' => $aiAssistant->id])),
        ];
    }
}
