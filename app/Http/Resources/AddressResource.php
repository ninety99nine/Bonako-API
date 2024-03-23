<?php

namespace App\Http\Resources;

use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class AddressResource extends BaseResource
{
    use BaseTrait;

    /**
     *  The address is considered to be veiwed privately if
     *  the address belongs to the current authenticated
     *  user or is accessed by a Super Admin user. This
     *  means that we can show sensitive information
     *  if either one of these cases is true.
     *
     *  @return bool
     */
    private function viewingPrivately() {
        $isSuperAdmin = $this->isSuperAdmin;
        $isOwner = $this->resource->user_id == request()->auth_user->id;

        return $isSuperAdmin || $isOwner;
    }

    public function toArray($request)
    {
        /**
         *  If this address is being veiwed by other users's,
         *  hide the address line (sensitive information)
         *  from such users.
         */
        if( !$this->viewingPrivately() ) {

            $this->customExcludeFields = ['address_line'];

        }

        return $this->transformedStructure();
    }

    public function setLinks()
    {
        //  Get the user's id
        $userId = $this->chooseUser()->id;

        //  Check if this resource belongs to the authenticated
        $isAuthUser = $userId == request()->auth_user->id;

        //  Auth user route name prefix
        $authUserPrefix = 'auth.user.address.';

        //  User route name prefix
        $userPrefix = 'user.address.';

        //  Set the route name prefix
        $prefix = $isAuthUser ? $authUserPrefix : $userPrefix;

        //  Set the route parameters
        $params = ['address' => $this->resource->id];

        //  If this is not the authenticated user
        if($isAuthUser == false) {

            //  Include the user id as a parameter to correspond to this route '/users/{user}/...'
            $params['user'] = $userId;

        }

        $this->resourceLinks = [
            new ResourceLink('self', route($prefix.'show', $params), 'Show address'),
            new ResourceLink('update.address', route($prefix.'update', $params), 'Update address'),
            new ResourceLink('delete.address', route($prefix.'delete', $params), 'Delete address'),
        ];
    }
}
