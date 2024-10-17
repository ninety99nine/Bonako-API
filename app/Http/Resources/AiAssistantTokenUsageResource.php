<?php

namespace App\Http\Resources;

use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class AiAssistantTokenUsageResource extends BaseResource
{
    use BaseTrait;

    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $aiAssistantTokenUsage = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.ai.assistant.token.usage', route('show.ai.assistant.token.usage', ['aiAssistantTokenUsageId' => $aiAssistantTokenUsage->id]))
        ];
    }
}
