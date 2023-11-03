<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;

class DatabaseSeeder extends Seeder
{
    use SeederHelper;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->disableForeignKeyChecks();
        $this->truncate(
            'users', 'password_reset_tokens', 'failed_jobs', 'personal_access_tokens', 'stores', 'addresses',
            'delivery_addresses', 'occasions', 'payment_methods', 'orders', 'carts', 'mobile_verifications',
            'products', 'variables', 'product_lines', 'coupons', 'coupon_lines', 'transactions', 'shortcodes',
            'subscription_plans', 'subscriptions', 'reviews', 'friend_groups', 'user_order_collection_association',
            'friend_group_order_association', 'friend_group_store_association', 'store_payment_method_association',
            'user_friend_association', 'user_friend_group_association', 'user_order_view_association',
            'user_store_association', 'sms_messages', 'notifications', 'ai_message_categories',
            'ai_messages'
        );

        $this->enableForeignKeyChecks();

        /**
         *  Note that the order of the seeders matters since
         *  some seeders depend on the existence of data
         *  generate by the other seeders
         */
        $this->call(SubscriptionPlanSeeder::class);
        $this->call(PaymentMethodSeeder::class);
        //$this->call(UserSeeder::class);
        //$this->call(StoreSeeder::class);
        //$this->call(FriendGroupSeeder::class);
        //$this->call(FriendSeeder::class);
        $this->call(AiMessageCategorySeeder::class);
        $this->call(OccasionSeeder::class);
    }
}
