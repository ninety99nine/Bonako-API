<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResources;

class WorkflowResources extends BaseResources
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\WorkflowResource';
}
