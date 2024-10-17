<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class FriendGroupResource extends BaseResource
{
    protected $customIncludeFields = [
        'users_count', 'friends_count', 'stores_count', 'orders_count'
    ];

    public function toArray($request)
    {
        /**
         *  If the friend group is accessed via a user relationship then we can gain access to the user-friend-group
         *  pivot information. This pivot information is accessed via the "friend_group_user_association" pivot name.
         *  If this property is provided then we can include it with our payload as an attribute
         */
        if( !empty($this->resource->friend_group_user_association) ) {

            //  Include the user and friend group association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['friend_group_user_association']
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
        $friendGroup = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.friend.group', route('show.friend.group', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('update.friend.group', route('update.friend.group', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('remove.friend.group', route('remove.friend.group', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('show.friend.group.members', route('show.friend.group.members', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('invite.friend.group.members', route('invite.friend.group.members', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('remove.friend.group.members', route('remove.friend.group.members', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('leave.friend.group', route('leave.friend.group', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('accept.invitation.to.join.friend.group', route('accept.invitation.to.join.friend.group', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('decline.invitation.to.join.friend.group', route('decline.invitation.to.join.friend.group', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('show.friend.group.stores', route('show.friend.group.stores', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('add.friend.group.stores', route('add.friend.group.stores', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('remove.friend.group.stores', route('remove.friend.group.stores', ['friendGroupId' => $friendGroup->id])),
            new ResourceLink('show.friend.group.orders', route('show.friend.group.orders', ['friendGroupId' => $friendGroup->id])),
        ];
    }

}
