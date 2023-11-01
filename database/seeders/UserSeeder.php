<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;

class UserSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Create real users
        $this->createJulian();
        $this->createBonolo();

        //  Create fake users only on local/dev environment
        if (app()->environment('local', 'dev')) {

            //  Create 8 fake users
            //  User::factory()->count(8)->create();

        }
    }

    public function createJulian() {
        User::create([
            'first_name' => 'Julian',
            'last_name' => 'Tabona',
            'mobile_number' => '26772882239',
            'last_seen_at' => now(),
            'mobile_number_verified_at' => now(),
            'accepted_terms_and_conditions' => true,
            'is_super_admin' => true,
            'password' => bcrypt('qweasd'),
            'remember_token' => Str::random(10),
        ]);

    }

    public function createBonolo() {
        User::create([
            'first_name' => 'Bonolo',
            'last_name' => 'Tabona',
            'mobile_number' => '26777479083',
            'last_seen_at' => now(),
            'mobile_number_verified_at' => now(),
            'accepted_terms_and_conditions' => true,
            'is_super_admin' => false,
            'password' => bcrypt('qweasd'),
            'remember_token' => Str::random(10),
        ]);
    }
}
