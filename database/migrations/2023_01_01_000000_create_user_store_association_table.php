<?php

use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Pivots\UserStoreAssociation;
use Illuminate\Database\Migrations\Migration;

class CreateUserStoreAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_store_association', function (Blueprint $table) {

            $table->id();

            /*  Store Information  */
            $table->foreignId('store_id')->constrained();

            /*  User Information  */
            $table->foreignId('user_id')->constrained()->nullable();
            $table->string('mobile_number', 11)->nullable();

            /*  Team Member Information  */
            $table->enum('team_member_status', UserStoreAssociation::TEAM_MEMBER_STATUSES)->nullable();
            $table->enum('team_member_role', UserStoreAssociation::TEAM_MEMBER_ROLES)->nullable();
            $table->json('team_member_permissions')->nullable();
            $table->char('team_member_join_code', 6)->nullable();
            $table->foreignId('invited_to_join_team_by_user_id')->constrained()->nullable();
            $table->timestamp('last_subscription_end_at')->nullable();

            /*  Follower Information  */
            $table->enum('follower_status', UserStoreAssociation::FOLLOWER_STATUSES)->nullable();
            $table->foreignId('invited_to_follow_by_user_id')->constrained()->nullable();

            /*  Assigned Information  */
            $table->boolean('is_assigned')->default(false);
            $table->unsignedTinyInteger('assigned_position')->nullable();

            /*  Customer Information  */
            $table->boolean('is_associated_as_customer')->default(false);
            $table->char('currency', 3)->default(Store::CURRENCY);

            //  Order Totals (Requested)
            $table->unsignedSmallInteger('total_orders_requested')->default(0);
            $table->decimal('sub_total_requested', 10, 2)->default(0);
            $table->decimal('coupon_discount_total_requested', 10, 2)->default(0);
            $table->decimal('sale_discount_total_requested', 10, 2)->default(0);
            $table->decimal('coupon_and_sale_discount_total_requested', 10, 2)->default(0);
            $table->decimal('grand_total_requested', 10, 2)->default(0);
            $table->unsignedSmallInteger('total_products_requested')->default(0);
            $table->unsignedSmallInteger('total_product_quantities_requested')->default(0);
            $table->unsignedSmallInteger('total_coupons_requested')->default(0);

            $table->decimal('avg_sub_total_requested', 10, 2)->default(0);
            $table->decimal('avg_coupon_discount_total_requested', 10, 2)->default(0);
            $table->decimal('avg_sale_discount_total_requested', 10, 2)->default(0);
            $table->decimal('avg_coupon_and_sale_discount_total_requested', 10, 2)->default(0);
            $table->decimal('avg_grand_total_requested', 10, 2)->default(0);
            $table->unsignedSmallInteger('avg_total_products_requested')->default(0);
            $table->unsignedSmallInteger('avg_total_product_quantities_requested')->default(0);
            $table->unsignedSmallInteger('avg_total_coupons_requested')->default(0);

            //  Order Totals (Completed)
            $table->unsignedSmallInteger('total_orders_received')->default(0);
            $table->decimal('sub_total_received', 10, 2)->default(0);
            $table->decimal('coupon_discount_total_received', 10, 2)->default(0);
            $table->decimal('sale_discount_total_received', 10, 2)->default(0);
            $table->decimal('coupon_and_sale_discount_total_received', 10, 2)->default(0);
            $table->decimal('grand_total_received', 10, 2)->default(0);
            $table->unsignedSmallInteger('total_products_received')->default(0);
            $table->unsignedSmallInteger('total_product_quantities_received')->default(0);
            $table->unsignedSmallInteger('total_coupons_received')->default(0);

            $table->decimal('avg_sub_total_received', 10, 2)->default(0);
            $table->decimal('avg_coupon_discount_total_received', 10, 2)->default(0);
            $table->decimal('avg_sale_discount_total_received', 10, 2)->default(0);
            $table->decimal('avg_coupon_and_sale_discount_total_received', 10, 2)->default(0);
            $table->decimal('avg_grand_total_received', 10, 2)->default(0);
            $table->unsignedSmallInteger('avg_total_products_received')->default(0);
            $table->unsignedSmallInteger('avg_total_product_quantities_received')->default(0);
            $table->unsignedSmallInteger('avg_total_coupons_received')->default(0);

            //  Order Totals (Cancelled)
            $table->unsignedSmallInteger('total_orders_cancelled')->default(0);
            $table->decimal('sub_total_cancelled', 10, 2)->default(0);
            $table->decimal('coupon_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('sale_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('coupon_and_sale_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('grand_total_cancelled', 10, 2)->default(0);
            $table->unsignedSmallInteger('total_products_cancelled')->default(0);
            $table->unsignedSmallInteger('total_product_quantities_cancelled')->default(0);
            $table->unsignedSmallInteger('total_coupons_cancelled')->default(0);

            $table->decimal('avg_sub_total_cancelled', 10, 2)->default(0);
            $table->decimal('avg_coupon_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('avg_sale_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('avg_coupon_and_sale_discount_total_cancelled', 10, 2)->default(0);
            $table->decimal('avg_grand_total_cancelled', 10, 2)->default(0);
            $table->unsignedSmallInteger('avg_total_products_cancelled')->default(0);
            $table->unsignedSmallInteger('avg_total_product_quantities_cancelled')->default(0);
            $table->unsignedSmallInteger('avg_total_coupons_cancelled')->default(0);

            /*  Timestamps  */
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_seen_on_ussd_at')->nullable();
            $table->timestamp('last_seen_on_web_app_at')->nullable();
            $table->timestamp('last_seen_on_mobile_app_at')->nullable();
            $table->timestamps();

            /* Add Indexes */
            $table->index('user_id');
            $table->index('store_id');
            $table->index('mobile_number');
            $table->index('follower_status');
            $table->index('team_member_role');
            $table->index('team_member_status');
            $table->index('team_member_join_code');
            $table->index('is_associated_as_customer');
            $table->index('invited_to_follow_by_user_id');
            $table->index('invited_to_join_team_by_user_id');

            /*  Foreign Key Constraints */
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('invited_to_follow_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('invited_to_join_team_by_user_id')->references('id')->on('users')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_store_association');
    }
}
