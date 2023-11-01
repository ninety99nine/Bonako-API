<?php

namespace App\Observers;

use App\Models\FriendGroup;
use Illuminate\Support\Facades\DB;

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

    public function deleted(FriendGroup $friendGroup)
    {
    }

    public function restored(FriendGroup $friendGroup)
    {
    }

    public function forceDeleted(FriendGroup $friendGroup)
    {
        //  Delete the associated the users from these friend group
        DB::table('user_friend_group_association')->where('friend_group_id', $friendGroup->id)->delete();
    }
}
