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
                'service' => SubscriptionPlan::STORE_SERVICE_NAME,
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 2.00,
                'position' => 1,
                'active' => 1,
                'metadata' => [
                    'duration' => null,     //  Specified by the user
                    'frequency' => 'day'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '5 days - P10',
                'description' => '5 day subscription for store access',
                'service' => SubscriptionPlan::STORE_SERVICE_NAME,
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 10.00,
                'position' => 2,
                'active' => 1,
                'metadata' => [
                    'duration' => 5,
                    'frequency' => 'day'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '15 days - P30',
                'description' => '15 day subscription for store access',
                'service' => SubscriptionPlan::STORE_SERVICE_NAME,
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 30.00,
                'position' => 3,
                'active' => 1,
                'metadata' => [
                    'duration' => 15,
                    'frequency' => 'day'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30 days - P60',
                'description' => '30 day subscription for store access',
                'service' => SubscriptionPlan::STORE_SERVICE_NAME,
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 60.00,
                'position' => 4,
                'active' => 1,
                'metadata' => [
                    'duration' => 30,
                    'frequency' => 'day'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //  Store Reporting Access Plans

            //  AI Assistant Access Plans
            [
                'name' => 'P2 per day',
                'description' => 'P2.00 per day subscription for AI Assistant',
                'service' => SubscriptionPlan::AI_ASSISTANT_SERVICE_NAME,
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 2.00,
                'position' => 1,
                'active' => 1,
                'metadata' => [
                    'duration' => null,     //  Specified by the user
                    'frequency' => 'day',
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '5 days - P10',
                'description' => '5 day subscription for AI Assistant',
                'service' => SubscriptionPlan::AI_ASSISTANT_SERVICE_NAME,
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 10.00,
                'position' => 2,
                'active' => 1,
                'metadata' => [
                    'duration' => 5,
                    'frequency' => 'day'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '15 days - P30',
                'description' => '15 day subscription for AI Assistant',
                'service' => SubscriptionPlan::AI_ASSISTANT_SERVICE_NAME,
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 30.00,
                'position' => 3,
                'active' => 1,
                'metadata' => [
                    'duration' => 15,
                    'frequency' => 'day'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '30 days - P60',
                'description' => '30 day subscription for AI Assistant',
                'service' => SubscriptionPlan::AI_ASSISTANT_SERVICE_NAME,
                'type' => 'Subscription',
                'currency' => 'BWP',
                'price' => 60.00,
                'position' => 4,
                'active' => 1,
                'metadata' => [
                    'duration' => 30,
                    'frequency' => 'day'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            //  SMS Alert Plans
            [
                'name' => 'P0.50 per sms alert',
                'description' => 'P0.50 per SMS Alert',
                'service' => SubscriptionPlan::SMS_ALERT_SERVICE_NAME,
                'type' => 'One-Off',
                'currency' => 'BWP',
                'price' => 0.50,
                'position' => 1,
                'active' => 1,
                'metadata' => [
                    'sms_credits' => null     //  Specified by the user
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '5 sms - P2.50',
                'description' => 'P2.50 payment for 5 SMS Alerts',
                'service' => SubscriptionPlan::SMS_ALERT_SERVICE_NAME,
                'type' => 'One-Off',
                'currency' => 'BWP',
                'price' => 2.50,
                'position' => 2,
                'active' => 1,
                'metadata' => [
                    'sms_credits' => 5
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '10 sms - P5.00',
                'description' => 'P5.00 payment for 10 SMS Alerts',
                'service' => SubscriptionPlan::SMS_ALERT_SERVICE_NAME,
                'type' => 'One-Off',
                'currency' => 'BWP',
                'price' => 5.00,
                'position' => 3,
                'active' => 1,
                'metadata' => [
                    'sms_credits' => 10
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '20 sms - P10.00',
                'description' => '10.00 payment for 20 SMS Alerts',
                'service' => SubscriptionPlan::SMS_ALERT_SERVICE_NAME,
                'type' => 'One-Off',
                'currency' => 'BWP',
                'price' => 10.00,
                'position' => 4,
                'active' => 1,
                'metadata' => [
                    'sms_credits' => 20
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '100 sms - P50.00',
                'description' => '50.00 payment for 100 SMS Alerts',
                'service' => SubscriptionPlan::SMS_ALERT_SERVICE_NAME,
                'type' => 'One-Off',
                'currency' => 'BWP',
                'price' => 50.00,
                'position' => 5,
                'active' => 1,
                'metadata' => [
                    'sms_credits' => 100
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
    }
}
