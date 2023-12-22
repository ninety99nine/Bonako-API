<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Repositories\StoreRepository;
use App\Repositories\ShortcodeRepository;
use App\Notifications\Stores\StoreCreated;
use App\Notifications\Stores\StoreDeleted;
use App\Repositories\SmsAlertRepository;
use Illuminate\Support\Facades\Notification;

class StoreObserver
{
    use BaseTrait;

    public $teamMembers = [];

    /**
     *  Return the StoreRepository instance
     *
     *  @return StoreRepository
     */
    public function storeRepository()
    {
        return resolve(StoreRepository::class);
    }

    /**
     *  Return the ShortcodeRepository instance
     *
     *  @return ShortcodeRepository
     */
    public function shortcodeRepository()
    {
        return resolve(ShortcodeRepository::class);
    }

    /**
     *  Return the SmsAlertRepository instance
     *
     *  @return SmsAlertRepository
     */
    public function smsAlertRepository()
    {
        return resolve(SmsAlertRepository::class);
    }

    /**
     *  The saving event will dispatch when a model is creating or updating
     *  the model even if the model's attributes have not been changed.
     *
     *  Refererence: https://laravel.com/docs/9.x/eloquent#events
     */
    public function saving(Store $store)
    {
    }

    public function creating(Store $store)
    {
        /**
         *  Update the store logo (if any)
         *
         *  Note that updateLogo() will work when creating a store but will not work when updating
         *  a store. This is because the $request->hasFile('logo') requires that the request must
         *  be a POST request. This is fine when we are creating a store since we do use a POST
         *  request, but doesn't work for us when we are updating a store since we then use a
         *  PUT request. In that case we update the logo separately using a POST route that
         *  is dedicated to updating the logo only. For this reason we will put this logic
         *  on the creating method since the saving method is triggered for both creating
         *  and updating. This way we can create a logo when creating a store and update
         *  the logo separately when we need to set a new logo or update the existing
         *  logo.
         *
         *  While implemeting a POST request the $request->hasFile('logo') will return "true",
         *  where as while implemeting a PUT request the same method will return "false".
         */
        $store = $this->storeRepository()->setModel($store)->updateLogo(request())->getModel();

        //  Do the same thing to update the cover photo (if any)
        $store = $this->storeRepository()->setModel($store)->updateCoverPhoto(request())->getModel();
    }

    public function updating(Store $store)
    {
    }

    public function created(Store $store)
    {
        //  Add the authenticated user as a team member
        resolve(storeRepository::class)->setModel($store)->addCreator($this->chooseUser());

        //  Add this store as an sms alertable store
        $this->smsAlertRepository()->addSmsAlertableStore($this->chooseUser(), $store);

        //  Notify the Super-Admins on this store creation
        //  change to Notification::send() instead of Notification::sendNow() so that this is queued
        Notification::sendNow(
            User::where('is_super_admin', '1')->get(),
            new StoreCreated($store, auth()->user())
        );

        //  Send slack notification of created store
        Log::channel('slack_stores')->info($store->name);
    }

    public function updated(Store $store)
    {
    }

    public function deleting(Store $store)
    {
        /**
         *  We need to capture the store team members before the store is deleted.
         *  This is because once the store is deleted, the user and store associations
         *  are automatically deleted based on the cascadeOnDelete relationship that is
         *  set on the user_store_association table schema. This means that while trying
         *  to access the team members on the deleted() event using $store->teamMembers(),
         *  we should expect no results since the relationship would have already been
         *  destroyed. We can capture the team members before deleting the store then
         *  access these same team members after deleting the store. This can be done
         *  by temporarily caching the team members.
         */
        $teamMembers = $store->teamMembers()->joinedTeam()->get();

        //  Cache the team members for one minute before the store is deleted
        Cache::put($this->getTeamMembersCacheName($store), $teamMembers, now()->addMinute());
    }

    public function deleted(Store $store)
    {
        /**
         *  Delete subscriptions (This is a polymorphic relationship that
         *  we cannot delete at database level using cascadeOnDelete()),
         *  therefore must be deleted at the application level.
         */
        $store->subscriptions()->delete();

        //  Expire shortcodes
        $this->shortcodeRepository()->setModel($store->shortcodes())->expireShortcode();

        //  Retrieve the cached team members
        $teamMembers = Cache::get($this->getTeamMembersCacheName($store));

        //  Notify the team members on this store deletion
        //  change to Notification::send() instead of Notification::sendNow() so that this is queued
        Notification::send($teamMembers, new StoreDeleted($store->id, $store->name, auth()->user()));

        // Remove the cached team members
        Cache::forget($this->getTeamMembersCacheName($store));
    }

    public function restored(Store $store)
    {
    }

    public function forceDeleted(Store $store)
    {
    }

    public function getTeamMembersCacheName(Store $store)
    {
        return 'store_'.$store->id.'_team_members';
    }
}
