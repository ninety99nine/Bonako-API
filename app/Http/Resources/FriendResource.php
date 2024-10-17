<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class FriendResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $friend = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.friend', route('show.friend', ['friendId' => $friend->id])),
            new ResourceLink('update.friend', route('update.friend', ['friendId' => $friend->id])),
            new ResourceLink('remove.friend', route('remove.friend', ['friendId' => $friend->id])),
            new ResourceLink('show.last.selected.friend', route('show.last.selected.friend')),
            new ResourceLink('update.last.selected.friends', route('update.last.selected.friends')),
            new ResourceLink('add.friend', route('add.friend')),
            new ResourceLink('remove.friends', route('remove.friends')),
        ];
    }
}
