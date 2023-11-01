<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use App\Models\FriendGroup;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;
use App\Models\Pivots\UserFriendGroupAssociation;


class FriendGroupSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Create fake users only on local/dev environment
        if (app()->environment('local', 'dev')) {

            //  Get the total number of users e.g 100
            $count = User::count();

            //  Set the maximum users at 30% the user population e.g 30
            $maxNumberOfUsers = ceil($count * 0.3);

            //  Get the random number of users, never zero e.g 5
            $randomNumberOfUsers = rand(1, $maxNumberOfUsers);

            //  Ensure that the random number of users is not more than the total count
            $randomNumberOfUsers = min($randomNumberOfUsers, $count);

            //  Get the random user id's
            $randomUserIds = User::pluck('id')->shuffle()->take($randomNumberOfUsers)->toArray();

            //  Get the random users plus the first and second user id's of the database
            $userIds = User::whereIn('id', array_merge($randomUserIds, [1, 2]))->pluck('id');

            foreach($userIds as $userId) {

                //  Create 1 to 5 fake stores for this logged in user
                $friendGroups = FriendGroup::factory()->count(rand(1, 5))->create();

                //  Foreach created store
                foreach($friendGroups as $friendGroup) {

                    //  Assign this user as the creator
                    $pivots = [
                        $userId => [
                            'role' => collect(UserFriendGroupAssociation::ROLES)->first()
                        ]
                    ];

                    //  Get the other user ids that are not the current authenticated user
                    $otherFriendGroupUserIds = collect($userIds)->filter(function($friendGroupUserId) use ($userId) {

                        //  The friend group user id must not match the user id
                        return $friendGroupUserId != $userId;

                    })->shuffle();

                    //  Get a random number of the user ids
                    $selectedFriendGroupUserIds = $otherFriendGroupUserIds->slice(0, $otherFriendGroupUserIds->count() - 1)->toArray();

                    //  Foreach selected user id
                    foreach($selectedFriendGroupUserIds as $selectedFriendGroupUserId) {

                        //  Assign this user as a member
                        $pivots[$selectedFriendGroupUserId] = [
                            'role' => collect(UserFriendGroupAssociation::ROLES)->last()
                        ];

                    }

                    //  Associate the users with this friend group
                    $friendGroup->users()->attach($pivots);

                    //  Get the maximum number of stores that this friend group can have
                    $maximumNumberOfStores = (($totalStores = Store::count()) >= 10 ? 10 : $totalStores);

                    //  Get the random number of store ids
                    $randomStoreIds = Store::inRandomOrder()->take(rand(1, $maximumNumberOfStores))->pluck('id');

                    //  Associate a random number of stores with this friend group
                    $friendGroup->stores()->attach($randomStoreIds);

                }

            }

        }
    }
}
