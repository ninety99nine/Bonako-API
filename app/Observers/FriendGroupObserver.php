<?php

namespace App\Observers;

use App\Models\FriendGroup;
use Illuminate\Support\Facades\Cache;
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
         *  user_friend_group_association table schema. This means that while trying to access the
         *  users on the deleted() event using $friendGroup->users(), we should expect no results
         *  since the relationship would have already been destroyed. We can capture the users
         *  before deleting the friend group then access these same users after deleting the
         *  friend group. This can be done by temporarily caching the users.
         */
        $users = $friendGroup->users()->joinedGroup()->get();

        //  Cache the team members for one minute before the store is deleted
        Cache::put($this->getUsersCacheName($friendGroup), $users, now()->addMinute());
    }

    public function deleted(FriendGroup $friendGroup)
    {
        //  Retrieve the cached users
        $users = Cache::get($this->getUsersCacheName($friendGroup));

        //  Notify the group members on this friend group deletion
        //  change to Notification::send() instead of Notification::sendNow() so that this is queued
        Notification::send($users, new FriendGroupDeleted($friendGroup->id, $friendGroup->name_with_emoji, auth()->user()));

        // Remove the cached users
        Cache::forget($this->getUsersCacheName($friendGroup));
    }

    public function restored(FriendGroup $friendGroup)
    {
    }

    public function forceDeleted(FriendGroup $friendGroup)
    {
    }

    public function getUsersCacheName(FriendGroup $friendGroup)
    {
        return 'friend_group_'.$friendGroup->id.'_users';
    }
}
