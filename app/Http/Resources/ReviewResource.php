<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class ReviewResource extends BaseResource
{
    public function setLinks()
    {
        $review = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.review', route('show.review', ['reviewId' => $review->id])),
            new ResourceLink('update.review', route('update.review', ['reviewId' => $review->id])),
            new ResourceLink('delete.review', route('delete.review', ['reviewId' => $review->id])),
        ];
    }
}
