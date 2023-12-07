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
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 2.00,
                'active' => 1,
                'metadata' => [
                    'frequency' => 'day',
                    'duration_type' => 'Variable Duration',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '5 days - P10',
                'description' => '5 day subscription for store access',
                'service' => 'Store Access',
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 10.00,
                'active' => 1,
                'metadata' => [
                    'duration' => 5,
                    'frequency' => 'day',
                    'duration_type' => 'Fixed Duration',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '15 days - P30',
                'description' => '15 day subscription for store access',
                'service' => 'Store Access',
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 30.00,
                'active' => 1,
                'metadata' => [
                    'duration' => 15,
                    'frequency' => 'day',
                    'duration_type' => 'Fixed Duration',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30 days - P60',
                'description' => '30 day subscription for store access',
                'service' => 'Store Access',
                'currency' => 'BWP',
                'price' => 60.00,
                'active' => 1,
                'type' => 'Subscription',
                'metadata' => [
                    'duration' => 30,
                    'frequency' => 'day',
                    'duration_type' => 'Fixed Duration',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //  Store Reporting Access Plans

            //  AI Assistant Access Plans
            [
                'name' => 'P2 per day',
                'description' => 'P2.00 per day subscription for AI Assistant',
                'service' => 'AI Assistant Access',
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 2.00,
                'active' => 1,
                'metadata' => [
                    'frequency' => 'day',
                    'duration_type' => 'Variable Duration',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '5 days - P10',
                'description' => '5 day subscription for AI Assistant',
                'service' => 'AI Assistant Access',
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 10.00,
                'active' => 1,
                'metadata' => [
                    'duration' => 5,
                    'frequency' => 'day',
                    'duration_type' => 'Fixed Duration',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '15 days - P30',
                'description' => '15 day subscription for AI Assistant',
                'service' => 'AI Assistant Access',
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 30.00,
                'active' => 1,
                'metadata' => [
                    'duration' => 15,
                    'frequency' => 'day',
                    'duration_type' => 'Fixed Duration',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30 days - P60',
                'description' => '30 day subscription for AI Assistant',
                'service' => 'AI Assistant Access',
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 60.00,
                'active' => 1,
                'metadata' => [
                    'duration' => 30,
                    'frequency' => 'day',
                    'duration_type' => 'Fixed Duration',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //  SMS Alert Plans
            [
                'name' => 'P0.50 per sms alert',
                'description' => 'P0.50 per SMS Alert',
                'service' => 'Store Access',
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 2.00,
                'active' => 1,
                'metadata' => [
                    'credit_type' => 'Variable Credit',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '5 sms - P2.50',
                'description' => 'P2.50 payment for 5 SMS Alerts',
                'service' => 'SMS Alerts',
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 2.50,
                'active' => 1,
                'metadata' => [
                    'credits' => 5,
                    'credit_type' => 'Fixed Credit',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '10 sms - P5.00',
                'description' => 'P5.00 payment for 10 SMS Alerts',
                'service' => 'SMS Alerts',
                'type' => 'One-Off',
                'currency' => 'BWP',
                'price' => 5.00,
                'active' => 1,
                'metadata' => [
                    'credits' => 10,
                    'credit_type' => 'Fixed Credit',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '20 sms - P10.00',
                'description' => '10.00 payment for 20 SMS Alerts',
                'service' => 'SMS Alerts',
                'type' => 'One-Off',
                'currency' => 'BWP',
                'price' => 10.00,
                'active' => 1,
                'metadata' => [
                    'credits' => 20,
                    'credit_type' => 'Fixed Credit',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '100 sms - P50.00',
                'description' => '50.00 payment for 100 SMS Alerts',
                'service' => 'SMS Alerts',
                'type' => 'One-Off',
                'currency' => 'BWP',
                'price' => 50.00,
                'active' => 1,
                'metadata' => [
                    'credits' => 100,
                    'credit_type' => 'Fixed Credit',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
