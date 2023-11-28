<?php

namespace App\Http\Resources;

use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class FriendGroupResource extends BaseResource
{
    use BaseTrait;

    protected $customIncludeFields = [
        'users_count', 'friends_count', 'stores_count'
    ];

    public function toArray($request)
    {
        /**
         *  If the friend group is accessed via a user relationship then we can gain access to the user-friend-group
         *  pivot information. This pivot information is accessed via the "user_friend_group_association" pivot name.
         *  If this property is provided then we can include it with our payload as an attribute
         */
        if( !empty($this->resource->user_friend_group_association) ) {

            //  Include the user and friend group association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['user_friend_group_association']
            );

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {

        //  Get the user's id
        $userId = $this->chooseUser()->id;

        //  Check if this resource belongs to the authenticated
        $isAuthUser = $userId == auth()->user()->id;

        //  Auth user route name prefix
        $authUserPrefix = 'auth.user.friend.group.';

        //  User route name prefix
        $userPrefix = 'user.friend.group.';

        //  Set the route name prefix
        $prefix = $isAuthUser ? $authUserPrefix : $userPrefix;

        //  Set the route parameters
        $params = ['friend_group' => $this->resource->id];

        //  If this is not the authenticated user
        if($isAuthUser == false) {

            //  Include the user id as a parameter to correspond to this route '/users/{user}/...'
            $params['user'] = $userId;

        }

        $this->resourceLinks = [
            new ResourceLink('self', route($prefix.'show', $params), 'The friend group'),
            new ResourceLink('update.friend.group', route($prefix.'update', $params), 'Update friend group'),
            new ResourceLink('delete.friend.group', route($prefix.'delete', $params), 'Delete friend group'),

            //  Stores
            new ResourceLink('show.friend.group.stores', route($prefix.'stores.show', $params), 'Show friend group stores'),
            new ResourceLink('add.friend.group.stores', route($prefix.'stores.add', $params), 'Add friend group stores'),
            new ResourceLink('remove.friend.group.stores', route($prefix.'stores.remove', $params), 'Remove friend group stores'),

            //  Orders
            new ResourceLink('show.friend.group.order.filters', route($prefix.'order.filters.show', $params), 'Show friend group orders'),
            new ResourceLink('show.friend.group.orders', route($prefix.'orders.show', $params), 'Show friend group orders'),

            //  Members
            new ResourceLink('show.friend.group.members', route($prefix.'members.show', $params), 'Show friend group members'),
            new ResourceLink('remove.friend.group.members', route($prefix.'members.remove', $params), 'Remove friend group member'),
        ];
    }

}
