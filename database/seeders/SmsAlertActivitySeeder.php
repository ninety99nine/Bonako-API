<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmsAlertActivity;
use Database\Seeders\Traits\SeederHelper;

class SmsAlertActivitySeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Foreach SMS Alert Activity
        foreach($this->getSmsAlertActivities() as $smsAlertActivity) {

            //  Create SMS Alert Activity
            SmsAlertActivity::create($smsAlertActivity);

        }
    }

    /**
     *  Return the SMS Alert Activities
     *
     *  @return array
     */
    public function getSmsAlertActivities() {
        return [
            [
                'name' => 'New Orders',
                'description' => 'Get notified about new orders',
                'enabled' => true,
                'requires_stores' => true,
            ],
            [
                'name' => 'New Reviews',
                'description' => 'Get notified about new reviews',
                'enabled' => true,
                'requires_stores' => true,
            ],
            [
                'name' => 'New Followers',
                'description' => 'Get notified about new followers',
                'enabled' => true,
                'requires_stores' => true,
            ],
            [
                'name' => 'Successful Order Payments',
                'description' => 'Get notified about successful order payments',
                'enabled' => true,
                'requires_stores' => true,
            ],
            [
                'name' => 'Failed Order Payments',
                'description' => 'Get notified about failed order payments',
                'enabled' => true,
                'requires_stores' => true,
            ],
        ];
    }
}
