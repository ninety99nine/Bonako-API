<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;

class FriendSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Associate the users as friends only on local/dev environment
        if (app()->environment('local', 'dev')) {

            //  Get the users
            $users = User::all();

            //  Foreach user
            foreach($users as $user) {

                //  Get the maximum number of friends that this user can have
                $maximumNumberOfFriends = (($totalUsers = count($users)) >= 10 ? 10 : $totalUsers);

                //  Each user can any number of friends from a minimum of zero (0) to a maximum of ten (10)
                $friendUserIds = $users->shuffle()->pluck('id')->filter(function($friendUserId) use ($user) {

                    //  The friend user id must not match the user id
                    return $friendUserId != $user->id;

                })->slice(0, rand(0, $maximumNumberOfFriends))->toArray();

                if(count($friendUserIds)) {

                    //  Associate the user with their friends
                    $user->friends()->attach($friendUserIds);

                }

            }

        }
    }
}
