<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\FriendGroup;
use App\Models\Store;
use App\Notifications\FriendGroups\FriendGroupUserAdded;
use App\Notifications\FriendGroups\FriendGroupUserRemoved;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class FriendGroupRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Return the repository user
     *
     *  @return FriendGroup - Repository model user
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
     *  Return the UserRepository instance
     *
     *  @return UserRepository
     */
    public function userRepository()
    {
        return resolve(UserRepository::class);
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
     *  Return the OrderRepository instance
     *
     *  @return OrderRepository
     */
    public function orderRepository()
    {
        return resolve(OrderRepository::class);
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

            //  Additionally we can eager load the total users on these friend groups as well
            $model = $model->withCount(['users']);

        }

        //  Check if we want to eager load the total friends on each friend group
        if( request()->input('with_count_friends') ) {

            //  Additionally we can eager load the total friends on these friend groups as well
            $model = $model->withCount(['users as friends_count' => function (Builder $query) {
                $query->where('user_id', '!=', auth()->user()->id);
            }]);

        }

        //  Check if we want to eager load the total stores on each friend group
        if( request()->input('with_count_stores') ) {

            //  Additionally we can eager load the total stores on these friend groups as well
            $model = $model->withCount(['stores']);

        }

        //  Check if we want to eager load the total orders on each friend group
        if( request()->input('with_count_orders') ) {

            //  Additionally we can eager load the total orders on these friend groups as well
            $model = $model->withCount(['orders']);

        }

        if( !empty($relationships) ) {

            $model = ($model instanceof FriendGroup) ? $model->load($relationships) : $model->with($relationships);

        }

        $this->setModel($model);

        return $this;
    }

    /**
     *  Show the user friend group filters
     *
     *  @param User $user
     *  @return array
     */
    public function showUserFriendGroupFilters(User $user)
    {
        $filters = collect(FriendGroup::FILTERS);

        /**
         *  $result = [
         *      [
         *          'name' => 'Groups',
         *          'total' => 2000,
         *          'total_summarized' => '2k'
         *      ],
         *      [
         *          'name' => 'Shared Groups',
         *          'total' => 1000k,
         *          'total_summarized' => '6k'
         *      ],
         *      ...
         *  ];
         */
        return $filters->map(function($filter) use ($user) {

            $filter = strtolower($filter);

            if($filter == 'groups') {

                $total = $user->friendGroups()->where('role', 'Creator')->count();

            }elseif($filter == 'shared groups') {

                $total = $user->friendGroups()->where('role', 'Member')->where('shared', '1')->count();

            }

            return [
                'name' => ucwords($filter),
                'total' => $total,
                'total_summarized' => $this->convertNumberToShortenedPrefix($total)
            ];

        })->toArray();
    }

    /**
     *  Show the user friend group
     *
     *  @param User $user
     *  @return FriendGroupRepository
     */
    public function showUserFriendGroups(User $user)
    {
        //  Set the user friend groups query (Show last selected friend groups first)
        $friendGroups = $user->friendGroups()->orderByPivot('last_selected_at', 'DESC');

        /**
         *  The $filter is used to identify friend groups
         *  that match the specified order status
         */
        $filter = $this->separateWordsThenLowercase(request()->input('filter'));

        //  If we have the filter
        if( !empty($filter) ) {

            if($filter == 'groups') {

                $friendGroups = $friendGroups->where('role', 'Creator');

            }else if($filter == 'shared groups') {

                $friendGroups = $friendGroups->where('role', 'Member')->where('shared', '1');

            }

        }

        //  Eager load the friend group relationships based on request inputs
        return $this->eagerLoadRelationships($friendGroups)->get();
    }

    /**
     *  Create a new user friend group
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     */
    public function createUserFriendGroup(User $user, Request $request)
    {
        //  Create the friend group
        $this->create($request);

        //  Get the specified mobile numbers
        $mobileNumbers = $request->input('mobile_numbers');

        //  Get the users that match the specified mobile numbers except the current user
        $users = User::whereIn('users.mobile_number', $mobileNumbers)->where('users.mobile_number', '!=', $user->mobile_number->withExtension)->get();

        // Add the current user as a Creator of the friend group
        $this->addUsersToFriendGroup([$user], 'Creator', true);

        // Add every other user as a Member of the friend group
        $this->addUsersToFriendGroup($users);

        // Count the numbers of users including the current user
        $totalFriends = count($users) + 1;

        return [
            'message' => 'Group created with '. $totalFriends . ($totalFriends == 1 ? ' member': ' members'),
            'friend_group' => $this->getFriendGroup()->transform()
        ];
    }

    /**
     *  Create an existing user friend group
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     */
    public function updateUserFriendGroup(Request $request, User $user)
    {
        //  Update the existing friend group
        $this->update($request);

        //  Get the specified mobile numbers
        $mobileNumbers = $request->input('mobile_numbers');

        //  Get the users that match the specified mobile numbers except the current user
        $users = User::whereIn('users.mobile_number', $mobileNumbers)->where('users.mobile_number', '!=', $user->mobile_number->withExtension)->get();

        // Add every other user as a Member of the friend group
        $this->addUsersToFriendGroup($users);

        return [
            'message' => 'Group updated successfully'
        ];
    }

    /**
     *  Add users to a friend group
     *
     *  @param array<User> $users
     *  @param FriendGroup $friendGroup
     *  @param string $role
     *
     *  @return void
     */
    public function addUsersToFriendGroup($users, $role = 'Member', $updateLastSelectedAt = false)
    {
        if( count($users) ) {

            $pivots = DB::table('user_friend_group_association')->where('friend_group_id', $this->getFriendGroup()->id)->get();

            $usersNotAlreadyAdded = collect($users)->filter(function($user) use ($pivots) {
                return collect($pivots->pluck('user_id'))->doesntContain($user->id);
            });

            $records = $usersNotAlreadyAdded->map(function($user) use ($role, $updateLastSelectedAt) {
                return [
                    'role' => $role,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'user_id' => $user->id,
                    'friend_group_id' => $this->getFriendGroup()->id,
                    'last_selected_at' => $updateLastSelectedAt ? now() : null,
                ];
            })->toArray();

            // Associate the users with this friend group
            DB::table('user_friend_group_association')->insert($records);

            //  Asocciate these users with every store of this friend group
            foreach($this->getFriendGroup()->stores as $store) {

                //  Add the friend group users to this store
                $store->users()->syncWithoutDetaching(collect($users)->pluck('id'));

            }

            //  Get the friend group users (members)
            $friendGroupUsers = $this->getFriendGroup()->users()->get();

            foreach($usersNotAlreadyAdded as $userNotAlreadyAdded) {

                //  Notify the friend group users that the user has been added
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $friendGroupUsers,
                    new FriendGroupUserAdded($this->getFriendGroup(), $userNotAlreadyAdded, auth()->user())
                );

            }

        }
    }

    /**
     *  Delete many friend groups
     *
     *  @param User $user
     *  @param Request $request
     *  @return array
     */
    public function deleteManyUserFriendGroups(Request $request, User $user)
    {
        //  Get the specified friend group ids
        $friendGroupIds = $request->input('friend_group_ids');

        //  Set the user friend groups query (Only where the user is a creator)
        $friendGroups = $user->friendGroups()->whereIn('friend_group_id', $friendGroupIds)->where('user_friend_group_association.role', 'Creator');

        //  Get the user friend group ids as a Collection
        $friendGroupIds = $friendGroups->pluck('friend_groups.id');

        //  Since the $friendGroupIds is a Collection, we can count() directly on this collection
        if($friendGroupIds->count()) {

            //  Delete the associated the users from these friend group
            DB::table('user_friend_group_association')->whereIn('friend_group_id', $friendGroupIds)->delete();

            //  Delete the friend groups that match the specified friend group ids
            $this->setModel($friendGroups)->delete();

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
     *  Show the last selected friend group
     *
     *  @param User $user
     *  @return FriendGroupRepository|null
     */
    public function showLastSelectedUserFriendGroup(User $user)
    {
        //  Get the last selected friend group
        $friendGroup = $user->friendGroups()->orderByPivot('last_selected_at', 'DESC')->first();
        return $friendGroup ? $this->setModel($friendGroup) : null;
    }

    /**
     *  Update the last selected friend groups
     *
     *  @param Request $request
     *  @param User $user
     *  @return array
     */
    public function updateLastSelectedUserFriendGroups(Request $request, User $user)
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
     *  Show friend group members
     *
     *  @param Request $request
     *  @return UserRepository
     */
    public function showFriendGroupMembers(Request $request)
    {
        $exceptUserId = $request->input('except_user_id');

        $users = $this->getFriendGroup()->users()->latest();

        //  If we have the except user id
        if( !empty($exceptUserId) ) {

            //  Set Query for users except the specified user id
            $users = $users->where('users.id', '!=', $exceptUserId);

        }

        return $this->userRepository()->setModel($users)->get();
    }

    /**
     *  Remove friend group members
     *
     *  @param Request $request
     *  @param User $user
     *  @return array
     */
    public function removeFriendGroupMembers(Request $request, User $user)
    {
        //  Get the specified user ids
        $userIds = $request->input('user_ids');

        //  Get the users that match the specified user id except the current user
        $usersToBeRemoved = $this->getFriendGroup()->users()->whereIn('users.id', $userIds)->where('users.id', '!=', $user->id)->get();

        if( $totalFriends = count($usersToBeRemoved) ) {

            // Remove the associated the users from this friend group
            DB::table('user_friend_group_association')
                ->where('friend_group_id', $this->getFriendGroup()->id)
                ->whereIn('user_id', $usersToBeRemoved->pluck('id'))
                ->delete();

            return [
                'message' => $totalFriends . ($totalFriends == 1 ? ' friend': ' friends') . ' removed'
            ];

            //  Get the friend group users (members)
            $friendGroupUsers = $this->getFriendGroup()->users()->get();

            foreach($usersToBeRemoved as $removedUser) {

                //  Notify the friend group users that the user has been removed
                //  change to Notification::send() instead of Notification::sendNow() so that this is queued
                Notification::sendNow(
                    $friendGroupUsers,
                    new FriendGroupUserRemoved($this->getFriendGroup(), $removedUser, auth()->user())
                );

            }

        }else{

            return [
                'message' => 'No friends removed'
            ];

        }
    }

    /**
     *  Show friend group stores
     *
     *  @return StoreRepository
     */
    public function showFriendGroupStores()
    {
        $stores = $this->getFriendGroup()->stores()->latest();
        return $this->storeRepository()->eagerLoadStoreRelationships($stores)->get();
    }

    /**
     *  Add friend group stores
     *
     *  @param Request $request
     *  @return array
     */
    public function addFriendGroupStores(Request $request, User $user)
    {
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

        // Get the total attached stores
        $totalAddedStores = count($attachedStores['attached']);

        if ($totalAddedStores > 0) {
            return [
                'message' => $totalAddedStores . ($totalAddedStores == 1 ? ' store' : ' stores') . ' added'
            ];
        } else {
            return [
                'message' => 'No stores added'
            ];
        }
    }

    /**
     *  Remove friend group stores
     *
     *  @param Request $request
     *  @return array
     */
    public function removeFriendGroupStores(Request $request)
    {
        //  Get the specified store ids
        $storeIds = $request->input('store_ids');

        //  Count the total stores matching the specified store ids
        $totalStores = DB::table('friend_group_store_association')
                            ->where('friend_group_id', $this->getFriendGroup()->id)
                            ->whereIn('store_id', $storeIds)
                            ->count();

        //  If we have matching stores
        if( $totalStores ) {

            // Remove the associated the stores from this friend group
            DB::table('friend_group_store_association')
                ->where('friend_group_id', $this->getFriendGroup()->id)
                ->whereIn('store_id', $storeIds)
                ->delete();

            return [
                'message' => $totalStores . ($totalStores == 1 ? ' store': ' stores') . ' removed'
            ];

        }else{

            return [
                'message' => 'No stores removed'
            ];

        }
    }

    /**
     *  Show friend group orders
     *
     *  @return OrderRepository
     */
    public function showFriendGroupOrders()
    {
        $orders = $this->getFriendGroup()->orders()->latest();
        return $this->orderRepository()->eagerLoadOrderRelationships($orders)->get();
    }
}
