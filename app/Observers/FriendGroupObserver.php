<?php

namespace App\Observers;

use App\Enums\CacheName;
use App\Models\FriendGroup;
use App\Helpers\CacheManager;
use Illuminate\Support\Facades\Notification;
use App\Notifications\FriendGroups\FriendGroupDeleted;

class FriendGroupObserver
{
    public function saving(FriendGroup $friendGroup)
    {
    }

    public function creating(FriendGroup $friendGroup)
    {
    }

    public function created(FriendGroup $friendGroup)
    {
    }

    public function updated(FriendGroup $friendGroup)
    {
    }

    public function deleting(FriendGroup $friendGroup)
    {
        /**
         *  We need to capture the friend group users before the friend group is deleted.
         *  This is because once the friend group is deleted, the user and friend group associations
         *  are automatically deleted based on the cascadeOnDelete relationship that is set on the
         *  friend_group_user_association table schema. This means that while trying to access the
         *  users on the deleted() event using $friendGroup->users(), we should expect no results
         *  since the relationship would have already been destroyed. We can capture the users
         *  before deleting the friend group then access these same users after deleting the
         *  friend group. This can be done by temporarily caching the users.
         */
        $users = $friendGroup->users()->joinedGroup()->get();

        //  Cache the users for one minute before the store is deleted
        (new CacheManager(CacheName::FRIEND_GROUP_USERS))->append($friendGroup->id)->put($users, now()->addMinute());
    }

    public function deleted(FriendGroup $friendGroup)
    {
        //  Set the cache manager
        $cacheManager = (new CacheManager(CacheName::FRIEND_GROUP_USERS))->append($friendGroup->id);

        //  Retrieve the cached users
        $users = $cacheManager->get();

        //  Notify the group members on this friend group deletion
        Notification::send($users, new FriendGroupDeleted($friendGroup->id, $friendGroup->name_with_emoji, request()->auth_user));

        // Forget the cached users
        $cacheManager->forget();
    }

    public function restored(FriendGroup $friendGroup)
    {
    }

    public function forceDeleted(FriendGroup $friendGroup)
    {
    }
}
