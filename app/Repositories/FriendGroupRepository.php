<?php

namespace App\Repositories;

use Exception;
use App\Models\User;
use App\Models\Store;
use App\Models\FriendGroup;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Enums\InvitationResponse;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;
use App\Exceptions\InvalidInvitationException;
use App\Models\Pivots\UserFriendGroupAssociation;
use App\Exceptions\CannotUpdateFriendGroupException;
use App\Notifications\Users\RemoveFriendGroupMember;
use App\Exceptions\InvitationAlreadyAcceptedException;
use App\Exceptions\InvitationAlreadyDeclinedException;
use App\Exceptions\CannotAddStoresToFriendGroupException;
use App\Exceptions\CannotDeleteFriendGroupException;
use App\Exceptions\CannotInviteFriendGroupMembersException;
use App\Exceptions\CannotRemoveFriendGroupMembersException;
use App\Exceptions\CannotRemoveStoresToFriendGroupException;
use App\Notifications\Users\InvitationToJoinFriendGroupCreated;
use App\Notifications\Users\InvitationToJoinFriendGroupDeclined;
use App\Notifications\Users\InvitationToJoinFriendGroupAccepted;
use App\Exceptions\CannotRemoveYourselfAsFriendGroupMemberException;
use App\Exceptions\CannotRemoveYourselfAsFriendGroupCreatorException;
use App\Notifications\FriendGroups\FriendGroupCreated;
use App\Notifications\FriendGroups\FriendGroupStoreAdded;
use App\Notifications\FriendGroups\FriendGroupStoreRemoved;

class FriendGroupRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Return the OrderRepository instance
     *
     *  @return OrderRepository
     */
    public function orderRepository()
    {
        return resolve(OrderRepository::class);
    }

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
     *  Return the UserRepository instance
     *
     *  @return UserRepository
     */
    public function userRepository()
    {
        return resolve(UserRepository::class);
    }

    /**
     *  Return the friend group model
     *
     *  @return FriendGroup
     *  @throws Exception
     */
    public function getFriendGroup()
    {
        if($this->model instanceof FriendGroup) {

            return $this->model;

        }else{

            throw new Exception('This repository model is not an instance of the FriendGroup model');

        }
    }

    /**
     *  Get the user and friend group association
     *
     *  @param User $user
     *  @return UserFriendGroupAssociation
     */
    public function getUserFriendGroupAssociation($user)
    {
        return UserFriendGroupAssociation::where('friend_group_id', $this->getFriendGroup()->id)
                ->where('user_id', $user->id)
                ->first();
    }

    /**
     *  Eager load relationships on the given model
     *
     *  @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     *  @return FriendGroupRepository
     */
    public function eagerLoadRelationships($model) {

        $relationships = [];

        //  Check if we want to eager load the total users on each friend group
        if( request()->input('with_count_users') ) {

            //  Eager load the total users on each friend group
            $model = $model->withCount(['users']);

        }

        //  Check if we want to eager load the total friends on each friend group
        if( request()->input('with_count_friends') ) {

            //  Eager load the total friends on each friend group
            $model = $model->withCount(['users as friends_count' => function (Builder $query) {
                $query->where('user_id', '!=', $this->chooseUser()->id);
            }]);

        }

        //  Check if we want to eager load the total stores on each friend group
        if( request()->input('with_count_stores') ) {

            //  Eager load the total stores on each friend group
            $model = $model->withCount(['stores']);

        }

        //  Check if we want to eager load the total orders on each friend group
        if( request()->input('with_count_orders') ) {

            //  Eager load the total orders on each friend group
            $model = $model->withCount(['orders']);

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof FriendGroup) ? $model->load($relationships) : $model->with($relationships);

        }

        return $this->setModel($model);

    }

    /**
     *  Show the user friend group filters
     *
     *  @param User $user - The user to query the friend groups
     *  @return array
     */
    public function showFriendGroupFilters(User $user)
    {
        //  Get the friend group filters
        $filters = collect(FriendGroup::FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Groups',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Shared Groups',
         *          'total' => 1000k,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($user) {

            //  Count the user friend groups with the specified filter
            $total = $this->queryFriendGroups($user, $filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the user friend groups
     *
     *  @param User $user - The user to query the friend groups
     *  @return FriendGroupRepository
     */
    public function showFriendGroups(User $user)
    {
        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the user friend groups with the specified filter
        $friendGroups = $this->queryFriendGroups($user, $filter);

        //  Eager load the friend group relationships based on request inputs
        return $this->eagerLoadRelationships($friendGroups)->get();
    }

    /**
     *  Query the user friend groups by the specified filter
     *
     *  @param User $user - The user to query the friend groups
     *  @param string $filter - The filter to query the friend groups
     *  @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryFriendGroups($user, $filter)
    {
        //  Get the last selected friend groups first
        $friendGroups = $user->friendGroups()->orderByPivot('last_selected_at', 'DESC');

        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase($filter);

        if($filter == 'groups') {

            $friendGroups = $friendGroups->where('role', 'Creator');

        }elseif($filter == 'shared groups') {

            $friendGroups = $friendGroups->where('role', '!=', 'Creator');

        }

        return $friendGroups;
    }

    /**
     *  Show friend group
     *
     *  @param FriendGroup $friendGroup
     *  @return FriendGroupRepository
     */
    public function showFriendGroup(FriendGroup $friendGroup)
    {
        return $this->eagerLoadRelationships($friendGroup);
    }

    /**
     *  Show the user's first created friend group
     *
     *  @param User $user
     *  @return array
     */
    public function showFirstCreatedFriendGroup(User $user)
    {
        //  Query the first created friend group by this user
        $firstCreatedFriendGroup = $user->friendGroups()->joinedGroupAsCreator()->oldest();

        //  Eager load the friend group relationships based on request inputs
        $firstCreatedFriendGroup = $this->eagerLoadRelationships($firstCreatedFriendGroup);

        //  Get the first created friend group by this user
        $firstCreatedFriendGroup = $firstCreatedFriendGroup->model->first();

        //  Return the friend group
        return [
            'exists' => !is_null($firstCreatedFriendGroup),
            'friendGroup' => $firstCreatedFriendGroup ? $this->setModel($firstCreatedFriendGroup)->transform() : null
        ];
    }

    /**
     *  Show the user's last selected friend group
     *
     *  @param User $user
     *  @return array
     */
    public function showLastSelectedFriendGroup(User $user)
    {
        //  Query the last selected friend group by this user
        $lastSelectedFriendGroup = $user->friendGroups()->orderByPivot('last_selected_at', 'DESC');

        //  Eager load the friend group relationships based on request inputs
        $lastSelectedFriendGroup = $this->eagerLoadRelationships($lastSelectedFriendGroup);

        //  Get the first created friend group by this user
        $lastSelectedFriendGroup = $lastSelectedFriendGroup->model->first();

        //  Return the friend group
        return [
            'exists' => !is_null($lastSelectedFriendGroup),
            'friendGroup' => $lastSelectedFriendGroup ? $this->setModel($lastSelectedFriendGroup)->transform() : null
        ];
    }

    /**
     *  Update the last selected friend groups
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     */
    public function updateLastSelectedFriendGroups(User $user, Request $request)
    {
        //  Get the specified friend group ids
        $friendGroupIds = $request->input('friend_group_ids');

        // Update the last_selected_at datetime of the associated pivots
        DB::table('user_friend_group_association')
            ->whereIn('friend_group_id', $friendGroupIds)
            ->where('user_id', $user->id)
            ->update([
                'last_selected_at' => now()
            ]);

        return [
            'message' => 'Updated successfully'
        ];
    }

    /**
     *  Create a new user friend group
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     */
    public function createFriendGroup(User $user, Request $request)
    {
        //  Create a new friend group
        $this->create($request);

        //  Add the current user as a Creator of this friend group
        $this->addCreator($user);

        if(request()->filled('mobile_numbers')) {

            //  Invite members to this friend group if members have been specified
            $this->inviteFriendGroupMembers($user);

        }

        //  Query the friend group through the specified user so that
        //  we can also load the user friend group association
        $this->setModel(
            $user->friendGroups()->where('friend_groups.id', $this->model->id)->first()
        );

        //  Notify the group creator that this group has been created
        //  change to Notification::send() instead of Notification::sendNow() so that this is queued
        Notification::sendNow(
            $this->chooseUser(),
            new FriendGroupCreated($this->getFriendGroup(), $this->chooseUser())
        );

        return [
            'message' => 'Group created',
            'friend_group' => $this->transform()
        ];
    }

    /**
     *  Update an existing user friend group
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     *  @throws CannotUpdateFriendGroupException
     */
    public function updateFriendGroup(User $user, Request $request)
    {
        $userFriendGroupAssociation = $this->model->user_friend_group_association;

        if($userFriendGroupAssociation->is_creator_or_admin) {

            //  Update the existing friend group
            $this->update($request);

            if(request()->filled('mobile_numbers')) {

                //  Invite members to this friend group if members have been specified
                $this->inviteFriendGroupMembers($user);

            }

            //  Query the friend group through the specified user so that
            //  we can also load the user friend group association
            $this->setModel(
                $user->friendGroups()->where('friend_groups.id', $this->model->id)->first()
            );

            return [
                'message' => 'Group updated',
                'friend_group' => $this->transform()
            ];

        }else{

            throw new CannotUpdateFriendGroupException;

        }
    }

    /**
     *  Delete a user friend group
     *
     *  @param User $user
     *  @return array
     *  @throws CannotDeleteFriendGroupException
     */
    public function deleteFriendGroup(User $user)
    {
        $userFriendGroupAssociation = $this->model->user_friend_group_association;

        if($userFriendGroupAssociation->is_creator_or_admin) {

            return $this->delete();

        }else{

            throw new CannotDeleteFriendGroupException;

        }
    }

    /**
     *  Delete many user friend groups
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     *  @throws CannotUpdateFriendGroupException
     */
    public function deleteManyFriendGroups(User $user, Request $request)
    {
        //  Get the specified friend group ids
        $friendGroupIds = $request->input('friend_group_ids');

        //  Query the user friend groups (Only where the user is a creator)
        $friendGroups = $user->friendGroups()->with(['users'])
                            ->whereIn('friend_group_id', $friendGroupIds)
                            ->where('user_friend_group_association.role', 'Creator')->get();

        //  Get the user friend group ids as a Collection
        $friendGroupIds = $friendGroups->pluck('friend_groups.id');

        //  Since the $friendGroupIds is a Collection, we can count() directly on this collection
        if($friendGroupIds->count()) {

            foreach($friendGroups as $friendGroup) {

                /**
                 *  Delete the friend group.
                 *
                 *  We are deleting one friend group at a time so that we can trigger the FriendGroupObserver's
                 *  deleting() and deleted() Events. Note that the user friend group associations are
                 *  automatically deleted based on the cascadeOnDelete relationship that is set on
                 *  the user_friend_group_association table schema.
                 */
                $this->setModel($friendGroup)->delete();

            }

            //  Count the total friend groups deleted
            $totalFriendGroups = count($friendGroupIds);

            return [
                'message' => $totalFriendGroups . ($totalFriendGroups == 1 ? ' friend group': ' friend groups') . ' deleted'
            ];

        }else{

            return [
                'message' => 'No friend groups deleted'
            ];

        }
    }

    /**
     *  Invite a single or multiple users on this friend group
     *
     *  @param User $invitedByUser
     *  @return array
     *  @throws CannotInviteFriendGroupMembersException
     */
    public function inviteFriendGroupMembers($invitedByUser)
    {
        $userFriendGroupAssociation = $this->model->user_friend_group_association;

        if($userFriendGroupAssociation->is_creator_or_admin) {

            $friendGroup = $this->getFriendGroup();

            /**
             *  Get the specified mobile numbers. Make sure that the specified mobile numbers are
             *  in array format since the request supports JSON encoded data i.e string data
             */
            $mobileNumbers = is_string($mobileNumbers = request()->input('mobile_numbers')) ? json_decode($mobileNumbers) : $mobileNumbers;

            //  Get the users that are assigned to this friend group as members that match the specified mobile numbers
            $assignedUsers = $friendGroup->users()->whereIn('users.mobile_number', $mobileNumbers)->get();

            //  Get the users that are not assigned to this friend group as members that match the specified mobile numbers
            $notAssignedUsers = User::whereIn('mobile_number', $mobileNumbers)->whereDoesntHave('friendGroups', function (Builder $query) use ($friendGroup) {

                //  Query for users that are not members on this specific friend group
                $query->where('user_friend_group_association.friend_group_id', $friendGroup->id);

            })->get();

            /**
             *  Get the guest users that are assigned to this friend group that match the specified mobile numbers.
             *  These guest users are non-existing users that are yet still to create their user accounts.
             */
            $mobileNumbersThatDontMatchAnyUserButInvited = DB::table('user_friend_group_association')
                ->where('friend_group_id', $friendGroup->id)
                ->whereIn('mobile_number', $mobileNumbers)
                ->where('status', 'Invited')
                ->get();

            //  Merge the existing users, whether assigned to this friend group or not
            $users = collect($assignedUsers)->merge($notAssignedUsers);

            /**
             *  Get the mobile numbers that don't match any user that was retrieved.
             *  These are mobile numbers of people who have not been invited whether
             *  as an existing user or guest (non-existing user)
             */
            $mobileNumbersThatDontMatchAnyUser = array_diff($mobileNumbers, array_merge(
                collect($mobileNumbersThatDontMatchAnyUserButInvited)->pluck('mobile_number')->toArray(),
                $users->pluck('mobile_number')->map(fn($mobileNumber) => $mobileNumber->withExtension)->toArray()
            ));

            /**
             *  If we supplied one or more numbers and retrieved users
             *  that have not yet been assigned as members, then we
             *  can invite these people by their user accounts.
             */
            if( $notAssignedUsers->count() > 0 ) {

                //  Invite existing users to this friend group
                $this->addMembers($invitedByUser, $notAssignedUsers);

            }

            /**
             *  If we supplied one or more numbers that did not retrieve any
             *  users, then we can invite these people by using their mobile
             *  numbers.
             */
            if($mobileNumbersThatDontMatchAnyUser) {

                //  Invite non-existent users to this friend group
                $this->addMembersByMobileNumbers($invitedByUser, $mobileNumbersThatDontMatchAnyUser);

            }

            /**
             *  If the mobile numbers specified match the assigned users,
             *  then this means that every user has already been invited.
             */
            if(count($mobileNumbers) === ($totalAssignedUsers = $assignedUsers->count())) {

                $message = $assignedUsers->pluck('first_name')->join(', ', ' and ');

                if($totalAssignedUsers == 1) {

                    $message .= ' has';

                }elseif($totalAssignedUsers <= 3) {

                    $message .= ' have';

                }else{

                    $message = 'These people have';

                }

                $message =  "$message already been invited";

            /**
             *  If the mobile numbers specified are more than the users
             *  that have not been invited, then this means that some
             *  users were either invited or don't have user accounts
             */
            }else {

                $message = 'Invitations sent successfully';

            }

            //  Function to transform data of existing user
            $transformExistingUser = function(User $user) {
                return [
                    'name' => $user->name,
                    'mobile_number' => $user->mobile_number,
                    'status' => $user->user_friend_group_association->status ?? 'Invited',
                ];
            };

            //  Function to transform data of non-existing user
            $transformNonExistingUser = function($mobileNumber) {
                return [
                    'mobile_number' => $this->convertToMobileNumberFormat($mobileNumber),
                    'status' => 'Invited'
                ];
            };

            $invitations = [

                //  Total invited
                'total_invited' => ($notAssignedUsers->count() + count($mobileNumbersThatDontMatchAnyUser)),
                'total_already_invited' => ($assignedUsers->count() + count($mobileNumbersThatDontMatchAnyUserButInvited)),

                //  Information about existing users who are invited
                'existing_users_invited' => [
                    'total' => $notAssignedUsers->count(),
                    'existing_users' => collect($notAssignedUsers)->map(function($user) use ($transformExistingUser) {
                        return $transformExistingUser($user);
                    })->toArray()
                ],

                //  Information about existing users who were already invited
                'existing_users_already_invited' => [
                    'total' => $assignedUsers->count(),
                    'existing_users' => collect($assignedUsers)->map(function($user) use ($transformExistingUser) {
                        return $transformExistingUser($user);
                    })->toArray()
                ],

                //  Information about non-existing users who are invited
                'non_existing_users_invited' => [
                    'total' => count($mobileNumbersThatDontMatchAnyUser),
                    'non_existing_users' => collect($mobileNumbersThatDontMatchAnyUser)->map(function($mobileNumber) use ($transformNonExistingUser) {
                        return $transformNonExistingUser($mobileNumber);
                    })->values()->toArray()
                ],

                //  Information about non-existing users who are invited
                'non_existing_users_already_invited' => [
                    'total' => count($mobileNumbersThatDontMatchAnyUserButInvited),
                    'non_existing_users' => collect($mobileNumbersThatDontMatchAnyUserButInvited)->map(function($record) use ($transformNonExistingUser) {
                        return $transformNonExistingUser($record->mobile_number);
                    })->values()->toArray()
                ],

            ];

            return [
                'message' => $message,
                'invitations' => $invitations
            ];

        }else{

            throw new CannotInviteFriendGroupMembersException;

        }
    }

    /**
     *  Check invitations to join friend groups
     *
     *  @param User $user
     *  @return array
     */
    public function checkInvitationsToJoinFriendGroups(User $user)
    {
        $invitations = DB::table('user_friend_group_association')->where('user_id', $user->id)
                            ->where('status', 'Invited')
                            ->get();

        $totalInvitations = count($invitations);
        $hasInvitations = $totalInvitations > 0;

        return [
            'has_invitations' => $hasInvitations,
            'total_invitations' => $totalInvitations,
        ];
    }

    /**
     *  Accept all invitations to join friend groups
     *
     *  @param User $user
     *  @return array
     */
    public function acceptAllInvitationsToJoinFriendGroups(User $user)
    {
        //  Get the friend groups that the user has been invited to join
        $friendGroups = FriendGroup::with(['users' => function($query) {

            //  Get each friend group with the members who have joined that friend group
            $query->joinedGroup();

        }])->whereHas('users', function($query) use ($user) {

            //  Check if the user has been invited to join this friend group
            $query->where([
                'user_id' => $user->id,
                'status' => 'Invited',
            ]);

        })->get();

        //  Accept the invitations
        DB::table('user_friend_group_association')
            ->where('user_id', $user->id)
            ->where('status', 'Invited')
            ->update([
                'status' => 'Joined'
            ]);

        if($friendGroups->count()) {

            //  Notify the group members of each friend group on the user's decision to accept the invitation
            $this->notifyFriendGroupMembersOnUserResponseToInvitation($user, InvitationResponse::Accepted, $friendGroups);

        }

        return ['message' => 'Invitations accepted successfully'];
    }

    /**
     *  Decline all invitations to join friend groups
     *
     *  @param User $user
     *  @return array
     */
    public function declineAllInvitationsToJoinFriendGroups(User $user)
    {
        //  Get the friend groups that the user has been invited to join
        $friendGroups = FriendGroup::with(['users' => function($query) {

            //  Get each friend group with the members who have joined that friend group
            $query->joinedGroup();

        }])->whereHas('users', function($query) use ($user) {

            //  Check if the user has been invited to join this friend group
            $query->where([
                'user_id' => $user->id,
                'status' => 'Invited',
            ]);

        })->get();

        //  Decline the invitations
        DB::table('user_friend_group_association')
            ->where('user_id', $user->id)
            ->where('status', 'Invited')
            ->update([
                'status' => 'Declined'
            ]);

        if($friendGroups->count()) {

            //  Notify the group members of each friend group on the user's decision to decline the invitation
            $this->notifyFriendGroupMembersOnUserResponseToInvitation($user, InvitationResponse::Declined, $friendGroups);

        }

        return ['message' => 'Invitations declined successfully'];
    }

    /**
     *  Accept invitation to join friend group
     *
     *  @param User $user
     *  @return array
     *  @throws InvitationAlreadyAcceptedException|InvitationAlreadyDeclinedException|InvalidInvitationException
     */
    public function acceptInvitationToJoinFriendGroup(User $user)
    {
        $userFriendGroupAssociation = $this->model->user_friend_group_association;

        if($userFriendGroupAssociation->is_creator_or_admin) {

            if($userFriendGroupAssociation->is_user_who_is_invited) {

                //  Accept invitation
                $this->updateInvitationStatusToJoinFriendGroup($user, 'Joined');

                //  Notify the group members of this friend group on the user's decision to accept the invitation
                $this->notifyFriendGroupMembersOnUserResponseToInvitation($user, InvitationResponse::Accepted);

                return ['message' => 'Invitation accepted successfully'];

            }else{

                if($userFriendGroupAssociation->is_user_who_has_joined) {

                    throw new InvitationAlreadyAcceptedException;

                }else if($userFriendGroupAssociation->is_team_member_who_has_declined) {

                    throw new InvitationAlreadyDeclinedException('This invitation has already been declined and cannot be accepted. Request the group creator or admin to resend the invitation again.');

                }

            }

        }else{

            throw new InvalidInvitationException('You have not been invited to this group');

        }
    }

    /**
     *  Decline invitation to join friend group
     *
     *  @param User $user
     *  @return array
     *  @throws InvitationAlreadyAcceptedException|InvitationAlreadyDeclinedException|InvalidInvitationException
     */
    public function declineInvitationToJoinFriendGroup(User $user)
    {
        $userFriendGroupAssociation = $this->model->user_friend_group_association;

        if($userFriendGroupAssociation) {

            if($userFriendGroupAssociation->is_user_who_is_invited) {

                //  Decline invitation
                $this->updateInvitationStatusToJoinFriendGroup($user, 'Declined');

                //  Notify the group members of this friend group on the user's decision to decline the invitation
                $this->notifyFriendGroupMembersOnUserResponseToInvitation($user, InvitationResponse::Declined);

                return ['message' => 'Invitation declined successfully'];

            }else{

                if($userFriendGroupAssociation->is_user_who_has_joined) {

                    throw new InvitationAlreadyAcceptedException('This invitation has already been accepted and cannot be declined.');

                }else if($userFriendGroupAssociation->is_team_member_who_has_declined) {

                    throw new InvitationAlreadyDeclinedException;

                }

            }

        }else{

            throw new InvalidInvitationException('You have not been invited to this group');

        }
    }

    /**
     *  Update the user friend group association status
     *
     *  @param User $user
     *  @param string $status
     *  @return int
     */
    public function updateInvitationStatusToJoinFriendGroup(User $user, string $status)
    {
        return DB::table('user_friend_group_association')
            ->where('friend_group_id', $this->getFriendGroup()->id)
            ->where('user_id', $user->id)->update([
                'status' => $status
            ]);
    }

    /**
     *  Notify the group members on the user's decision to accept or decline the invitation to join friend group
     *
     *  @param User $user
     *  @param InvitationResponse $invitationResponse - Indication of whether the user has accepted or declined the invitation
     *  @param Collection|\App\Models\Store[] $friendGroupsInvitedToJoin
     *  @return void
     */
    public function notifyFriendGroupMembersOnUserResponseToInvitation(User $user, InvitationResponse $invitationResponse, $friendGroupsInvitedToJoin = [])
    {
        //  Method to send the notifications
        $sendNotifications = function($friendGroupInvitedToJoin, $user) use ($invitationResponse) {

            if($invitationResponse == InvitationResponse::Accepted) {

                //  Notify the group members that this user has accepted the invitation to join this friend group
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $friendGroupInvitedToJoin->users,
                    new InvitationToJoinFriendGroupAccepted($friendGroupInvitedToJoin, $user)
                );

            }else{

                //  Notify the group members that this user has declined the invitation to join this friend group
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $friendGroupInvitedToJoin->users,
                    new InvitationToJoinFriendGroupDeclined($friendGroupInvitedToJoin, $user)
                );

            }
        };

        //  If we are accepting on declining invitations on multiple friend groups
        if(count($friendGroupsInvitedToJoin)) {

            //  Foreach store
            foreach($friendGroupsInvitedToJoin as $friendGroupInvitedToJoin) {

                //  Send notifications to group members of this friend group
                $sendNotifications($friendGroupInvitedToJoin, $user);

            }

        //  If we are accepting on declining invitations on a single friend group
        }else{

            $relationships = ['users' => function ($query) use ($user) {

                /**
                 *  Eager load the group members who have joined this friend group
                 *
                 *  Exclude the current user since we join them to the friend group before sending the notification
                 *  if they have accepted the invitation. This avoids sending them a notification as well.
                 */
                $query->joinedGroup()->where('user_id', '!=', $user->id);

            }];

            /**
             *  @var Store $store
             */
            $friendGroupInvitedToJoin = $this->getFriendGroup()->load($relationships);

            //  Send notifications to group members of this store
            $sendNotifications($friendGroupInvitedToJoin, $user);

        }

    }

    /**
     *  Remove a single or multiple users on this friend group
     *
     *  @param User $user
     *  @return array
     *  @throws CannotRemoveYourselfAsFriendGroupMemberException|CannotRemoveYourselfAsFriendGroupCreatorException
     */
    public function removeFriendGroupMembers(User $user)
    {
        $friendGroup = $this->getFriendGroup();

        /**
         *  Get the specified mobile numbers. Make sure that the specified mobile numbers are
         *  in array format since the request supports JSON encoded data i.e string data
         */
        $mobileNumbers = is_string($mobileNumbers = request()->input('mobile_numbers')) ? json_decode($mobileNumbers) : $mobileNumbers;

        //  Get the users that are assigned to this friend group that match the specified mobile numbers
        $assignedUsers = $this->getFriendGroup()->users()
            //  Matches non-existing user by mobile number
            ->whereIn('user_friend_group_association.mobile_number', $mobileNumbers)
            //  Matches existing user by mobile number
            ->orWhereIn('users.mobile_number', $mobileNumbers)
            ->get();

        //  If we have one or more users to remove
        if( !empty($assignedUsers) ) {

            //  If we have only one user to remove
            if( count($assignedUsers) == 1 ) {

                //  Get this user
                $assignedUser = $assignedUsers[0];

                //  If this user's id is the same as the current specified user
                if($assignedUser->id === $user->id) {

                    //  Deny the action of removing yourself
                    throw new CannotRemoveYourselfAsFriendGroupMemberException();

                }

                //  If this user is a creator
                if($assignedUser->user_friend_group_association->is_creator) {

                    //  Deny the action of removing yourself as a friend group creator
                    throw new CannotRemoveYourselfAsFriendGroupCreatorException();

                }

            //  Otherwise if we have more than one user to remove
            }else{

                //  Lets check each user before proceeding
                foreach($assignedUsers as $index => $assignedUser) {

                    //  If this user's id is the same as the current specified user
                    if($assignedUser->id === $user->id) {

                        /**
                         *  Deny the action of removing yourself by unsetting this user
                         *  instead of throwing an exception so that we can proceed to
                         *  remove other users
                         */
                        unset($assignedUsers[$index]);

                    }

                    //  If this user is a creator
                    if($assignedUser->user_friend_group_association->is_creator) {

                        /**
                         *  Deny the action of removing creator by unsetting this user
                         */
                        unset($assignedUsers[$index]);

                    }

                }
            }

            //  Get the pivot ids of the user associations as friend group members
            $userFriendGroupAssociationIds = $assignedUsers->map(function(User $assignedUser) {

                return $assignedUser->user_friend_group_association->id;

            })->toArray();

            if(count($userFriendGroupAssociationIds) == 0) {

                return ['message' => 'No group members removed'];

            }else{

                //  Remove the user associations as friend group members
                DB::table('user_friend_group_association')->whereIn('id', $userFriendGroupAssociationIds)->delete();

                /**
                 *  @var User $user
                 */
                $removedByUser = $user;

                //  Get the users who joined this friend group
                $usersWhoJoined = $friendGroup->users()->joinedGroup()->get();

                //  Foreach assigned user that has been removed
                foreach($assignedUsers as $removedUser) {

                    //  Notify the group members that a group member has been removed
                    //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                    Notification::sendNow(
                        //  Send notifications to the group members who joined
                        $usersWhoJoined,
                        new RemoveFriendGroupMember($friendGroup, $removedUser, $removedByUser)
                    );

                }

                //  Return a message indicating the total members removed
                return ['message' => count($userFriendGroupAssociationIds).' group '.(count($userFriendGroupAssociationIds) === 1 ? 'member': 'members').' removed'];

            }


        }

        //  Return a message indicating no group members removed
        return ['message' => 'No group members removed'];

    }

    /**
     *  Show the friend group member filters
     *
     *  @return array
     */
    public function showFriendGroupMemberFilters()
    {
        //  Get the friend group member filters
        $filters = collect(FriendGroup::MEMBER_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Creator',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Admins',
         *          'total' => 1000k,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) {

            //  Count the friend group members with the specified filter
            $total = $this->queryFriendGroupMembers($filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the friend group members
     *
     *  @return UserRepository
     */
    public function showFriendGroupMembers()
    {
        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the friend group members with the specified filter
        $users = $this->queryFriendGroupMembers($filter);

        //  Eager load the user relationships based on request inputs
        return $this->userRepository()->eagerLoadRelationships($users)->get();
    }

    /**
     *  Query the friend group members by the specified filter
     *
     *  @param string $filter - The filter to query the friend groups
     *  @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryFriendGroupMembers($filter)
    {
        //  Get the users added to this friend group first
        $users = $this->getFriendGroup()->users()->orderByPivot('created_at', 'DESC');

        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase($filter);

        if($filter == 'creator') {

            $users = $users->where('role', 'Creator');

        }elseif($filter == 'admins') {

            $users = $users->where('role', 'Admin');

        }elseif($filter == 'members') {

            $users = $users->where('role', 'Member');

        }

        return $users;
    }

    /**
     *  Show the friend group store filters
     *
     *  @return array
     */
    public function showFriendGroupStoreFilters()
    {
        //  Get the friend group store filters
        $filters = collect(FriendGroup::STORE_FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'All',
         *          'total' => 6000,
         *          'total_summarized' => '6k'
         *      ],
         *      [
         *          'name' => 'Creator',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Admins',
         *          'total' => 1000k,
         *          'total_summarized' => '1k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) {

            //  Count the friend group stores with the specified filter
            $total = $this->queryFriendGroupStores($filter)->count();

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the friend group stores
     *
     *  @return StoreRepository
     */
    public function showFriendGroupStores()
    {
        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  Query the friend group stores with the specified filter
        $stores = $this->queryFriendGroupStores($filter);

        //  Eager load the store relationships based on request inputs
        return $this->storeRepository()->eagerLoadRelationships($stores)->get();
    }

    /**
     *  Query the friend group stores by the specified filter
     *
     *  @param string $filter - The filter to query the friend groups
     *  @return \Illuminate\Database\Eloquent\Builder
     */
    public function queryFriendGroupStores($filter)
    {
        //  Get the stores added to this friend group
        $stores = $this->getFriendGroup()->stores();

        //  Get the specified filter
        $filter = $this->separateWordsThenLowercase($filter);

        if($filter == 'popular') {

            //  Query stores that have orders and then order the stores based
            //  on the number of orders placed on that store that are also
            //  associated with this friend group
            $stores = $stores->has('orders')->withCount(['orders' => function ($query) {

                // Filter orders by the current friend group
                $query->whereHas('friendGroups', function ($q) {
                    $q->where('friend_group_id', $this->getFriendGroup()->id);
                });

            }])->orderByDesc('orders_count');

        }else {

            //  Get the stores added to this friend group first
            $stores = $stores->orderByPivot('created_at', 'DESC');

        }

        return $stores;
    }

    /**
     *  Add friend group stores
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     *  @throws CannotAddStoresToFriendGroupException
     */
    public function addFriendGroupStores(User $user, Request $request)
    {
        $userFriendGroupAssociation = $this->model->user_friend_group_association;

        if($userFriendGroupAssociation->is_creator_or_admin) {

            // Get the specified store ids
            $storeIds = $request->input('store_ids');

            //  Set the pivot data
            $pivotData = [];

            foreach ($storeIds as $storeId) {

                //  Create an array with each pivot entry including the 'added_by_user_id'
                $pivotData[$storeId] = ['added_by_user_id' => $user->id];

            }

            // Attach the specified stores to this friend group without detaching existing ones
            $attachedStores = $this->getFriendGroup()->stores()->syncWithoutDetaching($pivotData);

            // Get the attached store ids
            $attachedStoreIds = $attachedStores['attached'];

            // Get the total attached stores
            $totalAddedStores = count($attachedStoreIds);

            // Get the attached stores
            $stores = Store::whereIn('id', $attachedStoreIds)->get();

            //  Get the users who joined this friend group
            $usersWhoJoined = $this->getFriendGroup()->users()->joinedGroup()->get();

            //  Foreach store
            foreach($stores as $store) {

                //  Notify the friend group users that the store has been added
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $usersWhoJoined,
                    new FriendGroupStoreAdded($this->getFriendGroup(), $store, $user)
                );

            }

            if ($totalAddedStores > 0) {

                return [
                    'message' => $totalAddedStores . ($totalAddedStores == 1 ? ' store' : ' stores') . ' added'
                ];

            } else {

                return [
                    'message' => 'No stores added'
                ];

            }

        }else{

            throw new CannotAddStoresToFriendGroupException;

        }
    }

    /**
     *  Remove friend group stores
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     *  @throws CannotRemoveStoresToFriendGroupException
     */
    public function removeFriendGroupStores(User $user, Request $request)
    {
        $userFriendGroupAssociation = $this->model->user_friend_group_association;

        if($userFriendGroupAssociation->is_creator_or_admin) {

            //  Get the specified store ids
            $storeIds = $request->input('store_ids');

            //  Count the total stores matching the specified store ids
            $matchingStoreIds = DB::table('friend_group_store_association')
                                ->where('friend_group_id', $this->getFriendGroup()->id)
                                ->whereIn('store_id', $storeIds)
                                ->pluck('store_id');

            $totalStores = count($matchingStoreIds);

            //  If we have matching stores
            if( $totalStores ) {

                // Remove the associated the stores from this friend group
                DB::table('friend_group_store_association')
                    ->where('friend_group_id', $this->getFriendGroup()->id)
                    ->whereIn('store_id', $storeIds)
                    ->delete();

                // Get the dettached stores
                $stores = Store::whereIn('id', $matchingStoreIds)->get();

                //  Get the users who joined this friend group
                $usersWhoJoined = $this->getFriendGroup()->users()->joinedGroup()->get();

                //  Foreach store
                foreach($stores as $store) {

                    //  Notify the friend group users that the store has been removed
                    //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                    Notification::sendNow(
                        $usersWhoJoined,
                        new FriendGroupStoreRemoved($this->getFriendGroup(), $store, $user)
                    );

                }

                return [
                    'message' => $totalStores . ($totalStores == 1 ? ' store': ' stores') . ' removed'
                ];

            }else{

                return [
                    'message' => 'No stores removed'
                ];

            }

        }else{

            throw new CannotRemoveStoresToFriendGroupException;

        }
    }

    /**
     *  Show the friend group order filters
     *
     *  @return array
     */
    public function showFriendGroupOrderFilters()
    {
        return $this->orderRepository()->showFriendGroupOrderFilters($this->getFriendGroup());
    }

    /**
     *  Show the friend group orders
     *
     *  @return OrderRepository
     */
    public function showFriendGroupOrders()
    {
        return $this->orderRepository()->showFriendGroupOrders($this->getFriendGroup());
    }

    /**
     *  Add a single user as creator of this friend group
     *
     *  @param \App\Models\User $user
     *  @return void
     */
    public function addCreator($user)
    {
        $this->addMembers(null, $user, 'Joined', 'Creator');
    }

    /**
     *  Add a single user or multiple users as admins to this friend group
     *
     *  @param User|null $invitedByUser
     *  @param Collection|\App\Models\User[] $users
     *  @return void
     */
    public function addAdmins($invitedByUser, $users = [])
    {
        $this->addMembers($invitedByUser, $users, null, 'Admin');
    }

    /**
     *  Add a single or multiple existing users on this friend group.
     *  This allows us to assign new users as members to this friend group with a given role
     *
     *  @param User|null $invitedByUser
     *  @param Collection|\App\Models\User[] $users
     *  @param string|null $status e.g Joined, Left, Invited
     *  @param string|null $role e.g 'Creator', 'Admin', 'Member'
     *  @return void
     */
    public function addMembers($invitedByUser, $users, $status = null, $role = null)
    {
        $friendGroup = $this->getFriendGroup();

        if(($user = $users) instanceof User) {

            //  Convert Model to collection
            $users = collect([$user]);

        }elseif(is_array($users)) {

            //  Convert array to collection
            $users = collect($users);

        }

        //  Get the user ids from the collection
        $userIds = $users->pluck('id');

        //  If we have user ids
        if( $userIds->count() ) {

            //  Determine the user role
            $role = $role ?? $this->resolveRole();

            //  Set the user status to "Invited" if no value is indicated
            if( empty($status) ) $status = 'Invited';

            $records = $userIds->map(function($userId) use($status, $role, $friendGroup, $invitedByUser) {

                $record = [
                    'invited_to_join_by_user_id' => $invitedByUser->id ?? null,
                    'last_selected_at' => $role == 'Creator' ? now() : null,
                    'friend_group_id' => $friendGroup->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'user_id' => $userId,
                    'status' => $status,
                    'role' => $role,
                ];

                return $record;

            })->toArray();

            //  Insert the specified user and friend group associations
            DB::table('user_friend_group_association')->insert($records);

            //  If these users are not creators
            if(strtolower($role) !== 'creator') {

                //  Notify the users that they have been invited to join this friend group
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $users,
                    new InvitationToJoinFriendGroupCreated($friendGroup, $invitedByUser)
                );

            }

        }
    }

    /**
     *  Add a single or multiple non-existent users on this friend group.
     *  by using their mobile number. This allows us to invite people to
     *  be members by using their mobile number even while yet they do
     *  not have user accounts.
     *
     *  @param User|null $invitedByUser
     *  @param int | array<int> $mobileNumbers
     *  @param string|null $role e.g 'Admin', 'Member'
     *  @return void
     */
    public function addMembersByMobileNumbers($invitedByUser, $mobileNumbers = [], $role = null)
    {
        if(is_int($mobileNumber = $mobileNumbers)) {

            $mobileNumbers = [$mobileNumber];

        }

        if( !empty($mobileNumbers) ) {

            //  Determine the user role
            $role = $role ?? $this->resolveRole();

            $records = collect($mobileNumbers)->map(function($mobileNumber) use($role, $invitedByUser) {

                return [
                    'invited_to_join_by_user_id' => $invitedByUser->id ?? null,
                    'friend_group_id' => $this->getFriendGroup()->id,
                    'mobile_number' => $mobileNumber,
                    'status' => 'Invited',
                    'created_at' => now(),
                    'updated_at' => now(),
                    'role' => $role,

                    //  Set the user id equal to the guest user id because the user does not yet exist.
                    'user_id' => $this->userRepository()->getGuestUserId(),
                ];
            })->toArray();

            //  Invite the specified users
            DB::table('user_friend_group_association')->insert($records);

        }
    }

    /**
     *  Get the role specified or set the appropriate role
     *
     *  @param array<string>
     *  @return string
     */
    public function resolveRole() {
        return ucfirst(request()->filled('role') ? request()->input('role') : 'Member');
    }
}
