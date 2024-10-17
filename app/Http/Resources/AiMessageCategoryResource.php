<?php

namespace App\Http\Resources;

use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class AiMessageCategoryResource extends BaseResource
{
    use BaseTrait;

    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $aiMessageCategory = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.ai.message.category', route('show.ai.message.category', ['aiMessageCategoryId' => $aiMessageCategory->id])),
            new ResourceLink('update.ai.message.category', route('update.ai.message.category', ['aiMessageCategoryId' => $aiMessageCategory->id])),
            new ResourceLink('delete.ai.message.category', route('delete.ai.message.category', ['aiMessageCategoryId' => $aiMessageCategory->id])),
        ];
    }

}
