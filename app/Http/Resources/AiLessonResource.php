<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class AiLessonResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $aiLesson = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.ai.lesson', route('show.ai.lesson', ['aiLessonId' => $aiLesson->id])),
            new ResourceLink('update.ai.lesson', route('update.ai.lesson', ['aiLessonId' => $aiLesson->id])),
            new ResourceLink('delete.ai.lesson', route('delete.ai.lesson', ['aiLessonId' => $aiLesson->id])),
        ];
    }

}
