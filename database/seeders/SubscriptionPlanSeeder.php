<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use Database\Seeders\Traits\SeederHelper;

class SubscriptionPlanSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  Foreach subscription plan
        foreach($this->getSubscriptionPlans() as $subscriptionPlan) {

            //  Create subscription plan
            SubscriptionPlan::create($subscriptionPlan);

        }
    }

    /**
     *  Return the subscription plans
     *
     *  @return array
     */
    public function getSubscriptionPlans() {
        return [

            //  Store Access Plans
            [
                'name' => 'P2 per day',
                'description' => 'P2.00 per day subscription for store access',
                'service' => 'Store Access',
                'type' => 'Variable',
                'duration' => 1,
                'frequency' => 'day',
                'currency' => 'BWP',
                'price' => 2.00,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '5 days - P10',
                'description' => '5 day subscription for store access',
                'service' => 'Store Access',
                'type' => 'Fixed',
                'duration' => 5,
                'frequency' => 'day',
                'currency' => 'BWP',
                'price' => 10.00,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '15 days - P30',
                'description' => '15 day subscription for store access',
                'service' => 'Store Access',
                'type' => 'Fixed',
                'duration' => 15,
                'frequency' => 'day',
                'currency' => 'BWP',
                'price' => 30.00,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30 days - P60',
                'description' => '30 day subscription for store access',
                'service' => 'Store Access',
                'type' => 'Fixed',
                'duration' => 30,
                'frequency' => 'day',
                'currency' => 'BWP',
                'price' => 60.00,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //  Store Reporting Access Plans

            //  AI Assistant Access Plans
            [
                'name' => '7 days - P5',
                'description' => '7 day subscription for AI Assistant',
                'service' => 'AI Assistant Access',
                'type' => 'Fixed',
                'duration' => 5,
                'frequency' => 'day',
                'currency' => 'BWP',
                'price' => 5.00,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30 days - P10',
                'description' => '30 day subscription for AI Assistant',
                'service' => 'AI Assistant Access',
                'type' => 'Fixed',
                'duration' => 30,
                'frequency' => 'day',
                'currency' => 'BWP',
                'price' => 10.00,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
