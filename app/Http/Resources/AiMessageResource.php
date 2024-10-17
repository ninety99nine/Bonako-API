<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class AiMessageResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $aiMessage = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.ai.message', route('show.ai.message', ['aiMessageId' => $aiMessage->id])),
            new ResourceLink('update.ai.message', route('update.ai.message', ['aiMessageId' => $aiMessage->id])),
            new ResourceLink('delete.ai.message', route('delete.ai.message', ['aiMessageId' => $aiMessage->id])),
        ];
    }

}
