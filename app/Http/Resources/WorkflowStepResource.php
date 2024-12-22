<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class WorkflowStepResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $workflowStep = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.workflow.step', route('show.workflow.step', ['workflowStepId' => $workflowStep->id])),
            new ResourceLink('update.workflow.step', route('update.workflow.step', ['workflowStepId' => $workflowStep->id])),
            new ResourceLink('delete.workflow.step', route('delete.workflow.step', ['workflowStepId' => $workflowStep->id])),
        ];
    }
}
