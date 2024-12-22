<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class WorkflowResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $workflow = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.workflow', route('show.workflow', ['workflowId' => $workflow->id])),
            new ResourceLink('update.workflow', route('update.workflow', ['workflowId' => $workflow->id])),
            new ResourceLink('delete.workflow', route('delete.workflow', ['workflowId' => $workflow->id])),
            new ResourceLink('show.workflow.steps', route('show.workflow.steps', ['workflowId' => $workflow->id])),
        ];
    }
}
