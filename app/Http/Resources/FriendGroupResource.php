<?php

namespace App\Http\Resources;

use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class FriendGroupResource extends BaseResource
{
    use BaseTrait;

    protected $customIncludeFields = [
        'users_count', 'friends_count', 'stores_count', 'orders_count'
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

        /**
         *  If the friend group is accessed via a store relationship then we can gain access to the friend-group-store
         *  pivot information. This pivot information is accessed via the "friend_group_store_association" pivot name.
         *  If this property is provided then we can include it with our payload as an attribute
         */
        if( !empty($this->resource->friend_group_store_association) ) {

            //  Include the friend group and store association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['friend_group_store_association']
            );

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

            //  Invitations
            new ResourceLink('invite.members', route($prefix.'members.invite', $params), 'Invite members to this friend group'),
            new ResourceLink('accept.invitation.to.join.friend.group', route($prefix.'accept.invitation.to.join.group', $params), 'Accept invitation to join this friend group'),
            new ResourceLink('decline.invitation.to.join.friend.group', route($prefix.'decline.invitation.to.join.group', $params), 'Decline invitation to join this friend group'),
            new ResourceLink('remove.members', route($prefix.'members.remove', $params), 'Remove members from this friend group'),

            //  Members
            new ResourceLink('show.member.filters', route($prefix.'member.filters.show', $params), 'Show friend group member filters'),
            new ResourceLink('show.members', route($prefix.'members.show', $params), 'Show friend group members'),

            //  Stores
            new ResourceLink('show.store.filters', route($prefix.'store.filters.show', $params), 'Show friend group store filters'),
            new ResourceLink('show.stores', route($prefix.'stores.show', $params), 'Show friend group stores'),
            new ResourceLink('add.stores', route($prefix.'stores.add', $params), 'Add stores to this friend group'),
            new ResourceLink('remove.stores', route($prefix.'stores.remove', $params), 'Remove stores from this friend group'),

            //  Orders
            new ResourceLink('show.order.filters', route($prefix.'order.filters.show', $params), 'Show friend group order filters'),
            new ResourceLink('show.orders', route($prefix.'orders.show', $params), 'Show friend group orders'),
        ];
    }

}
