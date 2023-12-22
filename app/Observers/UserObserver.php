<?php

namespace App\Observers;

use App\Models\User;
use App\Services\Sms\SmsService;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use App\Models\Pivots\UserStoreAssociation;
use Illuminate\Support\Facades\Notification;
use App\Models\Pivots\UserFriendGroupAssociation;
use App\Notifications\Users\InvitationToFollowStoreCreated;
use App\Notifications\Users\InvitationToJoinStoreTeamCreated;
use App\Notifications\Users\InvitationToJoinFriendGroupCreated;

class UserObserver
{
    /**
     *  Return the UserRepository instance
     *
     *  @return UserRepository
     */
    public function userRepository()
    {
        return resolve(UserRepository::class);
    }

    public function created(User $user)
    {
        //  If this is not a guest user
        if($user->is_guest == false) {

            /**
             *  Get the user and store association incase the user was previously invited
             *  to follow stores or join store teams before they created their account. In
             *  this case we can look-up the user based on any associations based on their
             *  mobile number.
             */
            $mobileNumberStoreAssociations = DB::table('user_store_association')->where([
                'mobile_number' => $user->mobile_number->withExtension
            ]);

            if($mobileNumberStoreAssociations->count()) {

                //  Get user and store associations together with the associated store and the user who invited this user to follow this store
                $userStoreAssociationsToFollow = UserStoreAssociation::with(['store', 'userWhoInvitedToFollow'])->where([
                    'mobile_number' => $user->mobile_number->withExtension,
                    'follower_status' => 'Invited'
                ])->get();

                //  Foreach user
                foreach($userStoreAssociationsToFollow as $userStoreAssociationToFollow) {

                    //  Notify the user that they have been invited to follow this store
                    //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                    Notification::sendNow(
                        $user,
                        new InvitationToFollowStoreCreated($userStoreAssociationToFollow->store, $userStoreAssociationToFollow->userWhoInvitedToFollow)
                    );

                }

                //  Get user and store associations together with the associated store and the user who invited this user to join this store team
                $userStoreAssociationsToJoinTeam = UserStoreAssociation::with(['store', 'userWhoInvitedToJoinTeam'])->where([
                    'mobile_number' => $user->mobile_number->withExtension,
                    'team_member_status' => 'Invited'
                ])->get();

                //  Foreach user
                foreach($userStoreAssociationsToJoinTeam as $userStoreAssociationToJoinTeam) {

                    //  Notify the user that they have been invited to join this store team
                    //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                    Notification::sendNow(
                        $user,
                        new InvitationToJoinStoreTeamCreated($userStoreAssociationToJoinTeam->store, $userStoreAssociationToJoinTeam->userWhoInvitedToFollow)
                    );

                }

                //  Update the user and store association only after notifying the user so that we can unlink the mobile and set the user id
                $mobileNumberStoreAssociations->update([
                    'mobile_number' => null,
                    'user_id' => $user->id
                ]);

            }

            /**
             *  Get the user and friend group association incase the user was previously invited
             *  to join groups before they created their account. In this case we can look-up
             *  the user based on any associations based on their mobile number.
             */
            $mobileNumberFriendGroupAssociations = DB::table('user_friend_group_association')->where([
                'mobile_number' => $user->mobile_number->withExtension
            ]);

            if($mobileNumberFriendGroupAssociations->count()) {

                //  Get user and friend group associations together with the associated friend group and the user who invited this user
                $userStoreAssociationsToJoinGroup = UserFriendGroupAssociation::with(['friendGroup', 'userWhoInvitedToJoinGroup'])->where([
                    'mobile_number' => $user->mobile_number->withExtension,
                    'status' => 'Invited'
                ])->get();

                //  Foreach user
                foreach($userStoreAssociationsToJoinGroup as $userStoreAssociationToJoinGroup) {

                    //  Notify the user that they have been invited to join this friend group
                    //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                    Notification::sendNow(
                        $user,
                        new InvitationToJoinFriendGroupCreated($userStoreAssociationToJoinGroup->friendGroup, $userStoreAssociationToJoinGroup->userWhoInvitedToJoinGroup)
                    );

                }

                //  Update the user and friend group association only after notifying the user so that we can unlink the mobile and set the user id
                $mobileNumberFriendGroupAssociations->update([
                    'mobile_number' => null,
                    'user_id' => $user->id
                ]);

            }


            // Send sms to user that their account was created
            SmsService::sendOrangeSms(
                $user->craftAccountCreatedSmsMessageForUser(),
                $user->mobile_number->withExtension,
                null, null, null
            );
        }
    }

    public function updated(User $user)
    {
        //
    }

    public function deleted(User $user)
    {
        //
    }

    public function restored(User $user)
    {
        //
    }

    public function forceDeleted(User $user)
    {
    }
}
