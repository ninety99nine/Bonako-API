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
        //  Set the route name prefix
        $prefix = 'ai.message.category.';

        $this->resourceLinks = [
            //  new ResourceLink('self', route($prefix.'show'), 'The ai message'),
            //  new ResourceLink('update.ai.message', route($prefix.'update'), 'Update ai message category'),
            //  new ResourceLink('delete.ai.message', route($prefix.'delete'), 'Delete ai message category'),
        ];
    }

}
