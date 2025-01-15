<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AiAssistant;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepository;
use App\Models\Pivots\UserStoreAssociation;
use Illuminate\Support\Facades\Notification;
use App\Models\Pivots\FriendGroupUserAssociation;
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

            AiAssistant::create(['user_id' => $user->id]);

            if($user->mobile_number) {

                $mobileNumber = $user->mobile_number->formatE164();

                /**
                 *  Get the user and store association incase the user was previously invited
                 *  to follow stores or join store teams before they created their account. In
                 *  this case we can look-up the user based on any associations based on their
                 *  mobile number.
                 */
                $mobileNumberStoreAssociations = DB::table('user_store_association')->where([
                    'mobile_number' => $mobileNumber
                ]);

                if($mobileNumberStoreAssociations->count()) {

                    //  Get user and store associations together with the associated store and the user who invited this user to follow this store
                    $userStoreAssociationsToFollow = UserStoreAssociation::where([
                        'mobile_number' => $mobileNumber,
                        'follower_status' => 'Invited'
                    ])->get();

                    //  Foreach user
                    foreach($userStoreAssociationsToFollow as $userStoreAssociationToFollow) {

                        //  Notify the user that they have been invited to follow this store
                        Notification::send(
                            $user,
                            new InvitationToFollowStoreCreated($userStoreAssociationToFollow->store_id, $userStoreAssociationToFollow->invited_to_follow_by_user_id)
                        );

                    }

                    //  Get user and store associations together with the associated store and the user who invited this user to join this store team
                    $userStoreAssociationsToJoinTeam = UserStoreAssociation::with(['store', 'userWhoInvitedToJoinTeam'])->where([
                        'mobile_number' => $mobileNumber,
                        'team_member_status' => 'Invited'
                    ])->get();

                    //  Foreach user
                    foreach($userStoreAssociationsToJoinTeam as $userStoreAssociationToJoinTeam) {

                        //  Notify the user that they have been invited to join this store team
                        Notification::send(
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
                $mobileNumberFriendGroupAssociations = DB::table('friend_group_user_association')->where([
                    'mobile_number' => $mobileNumber
                ]);

                if($mobileNumberFriendGroupAssociations->count()) {

                    //  Get user and friend group associations together with the associated friend group and the user who invited this user
                    $userStoreAssociationsToJoinGroup = FriendGroupUserAssociation::where([
                        'mobile_number' => $mobileNumber,
                        'status' => 'Invited'
                    ])->get();

                    //  Foreach user
                    foreach($userStoreAssociationsToJoinGroup as $userStoreAssociationToJoinGroup) {

                        //  Notify the user that they have been invited to join this friend group
                        Notification::send(
                            $user,
                            new InvitationToJoinFriendGroupCreated($userStoreAssociationToJoinGroup->friend_group_id, $userStoreAssociationToJoinGroup->invited_to_join_by_user_id)
                        );

                    }

                    //  Update the user and friend group association only after notifying the user so that we can unlink the mobile and set the user id
                    $mobileNumberFriendGroupAssociations->update([
                        'mobile_number' => null,
                        'user_id' => $user->id
                    ]);

                }

            }

            if($user->email) {

                // Do the same as the mobile number but using the email

            }

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
