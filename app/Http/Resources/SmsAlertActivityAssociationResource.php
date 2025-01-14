<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class SmsAlertActivityAssociationResource extends BaseResource
{
    public function setLinks()
    {
        //  Auth user route name prefix
        $authUserPrefix = 'auth.user.sms.alert.activity.association.';

        //  User route name prefix
        $userPrefix = 'user.sms.alert.activity.association.';

        //  Get the user's id
        $userId = request()->current_user->id;

        //  Check if this resource belongs to the authenticated
        $isAuthUser = $userId == request()->auth_user->id;

        //  Set the route name prefix
        $prefix = $isAuthUser ? $authUserPrefix : $userPrefix;

        //  Set the route parameters
        $params = ['sms_alert_activity_association' => $this->resource->id];

        //  If this is not the authenticated user
        if($isAuthUser == false) {

            //  Include the user id as a parameter to correspond to this route '/users/{user}/...'
            $params['user'] = $userId;

        }

        $this->resourceLinks = [
            new ResourceLink('update.sms.alert.activity.association', route($prefix.'update', $params), 'Update sms alert activity association'),
        ];

    }
}
