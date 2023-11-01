<?php

namespace App\Http\Resources;

use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class NotificationResource extends BaseResource
{
    use BaseTrait;

    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        //  Get the user's id
        $userId = $this->chooseUser()->id;

        //  Check if this resource belongs to the authenticated
        $isAuthUser = $userId == auth()->user()->id;

        //  Auth user route name prefix
        $authUserPrefix = 'auth.user.notification.';

        //  User route name prefix
        $userPrefix = 'user.notification.';

        //  Set the route name prefix
        $prefix = $isAuthUser ? $authUserPrefix : $userPrefix;

        //  Set the route parameters
        $params = ['notification' => $this->resource->id];

        //  If this is not the authenticated user
        if($isAuthUser == false) {

            //  Include the user id as a parameter to correspond to this route '/users/{user}/...'
            $params['user'] = $userId;

        }

        $this->resourceLinks = [
            new ResourceLink('self', route($prefix.'show', $params), 'Show notification'),
            new ResourceLink('mark.as.read', route($prefix.'mark.as.read', $params), 'Mark notification as read'),
        ];
    }
}
