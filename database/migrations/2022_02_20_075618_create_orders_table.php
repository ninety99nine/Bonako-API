<?php

use App\Models\Order;
use App\Models\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            /*  Basic Information  */
            $table->string('summary')->nullable();

            /*  Balance Information  */
            $table->char('currency', 3)->default(Store::CURRENCY);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->unsignedTinyInteger('amount_paid_percentage')->default(0);
            $table->decimal('amount_pending', 10, 2)->default(0);
            $table->unsignedTinyInteger('amount_pending_percentage')->default(0);
            $table->decimal('amount_outstanding', 10, 2)->default(0);
            $table->unsignedTinyInteger('amount_outstanding_percentage')->default(100);

            /*  Status Information  */
            $table->enum('status', Order::STATUSES)->default(Arr::first(Order::STATUSES));
            $table->enum('payment_status', Order::PAYMENT_STATUSES)->default(Arr::last(Order::PAYMENT_STATUSES));

            /*  Special Note Information  */
            $table->string('special_note', 400)->nullable();

            /*  Cancellation Information  */
            $table->string('cancellation_reason')->nullable();

            /*  Collection Information  */
            $table->boolean('collection_verified')->default(false);
            $table->timestamp('collection_verified_at')->nullable();

            $table->foreignId('collection_by_user_id')->nullable();
            $table->string('collection_by_user_first_name')->nullable();
            $table->string('collection_by_user_last_name')->nullable();
            $table->foreignId('collection_verified_by_user_id')->nullable();
            $table->string('collection_verified_by_user_first_name')->nullable();
            $table->string('collection_verified_by_user_last_name')->nullable();
            $table->enum('collection_type', Order::COLLECTION_TYPES)->nullable();
            $table->string('destination_name')->nullable();
            $table->foreignId('delivery_address_id')->nullable();

            /*  Occasion Information  */
            $table->foreignId('occasion_id')->nullable();

            /*  Collection Information  */
            $table->foreignId('payment_method_id')->nullable();

            /*  Customer Information  */
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->foreignId('customer_user_id')->nullable();

            /*  Order For Information  */
            $table->enum('order_for', Order::ORDER_FOR_OPTIONS)->default(Arr::first(Order::ORDER_FOR_OPTIONS));
            $table->unsignedTinyInteger('order_for_total_users')->default(0);
            $table->unsignedTinyInteger('order_for_total_friends')->default(0);

            /*  Store Information  */
            $table->foreignId('store_id');

            /*  Team Views  */
            $table->unsignedSmallInteger('total_views_by_team')->default(1);
            $table->timestamp('first_viewed_by_team_at')->nullable();
            $table->timestamp('last_viewed_by_team_at')->nullable();

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index(['status']);
            $table->index(['order_for']);
            $table->index(['created_at']);
            $table->index(['occasion_id']);
            $table->index(['payment_status']);
            $table->index(['payment_method_id']);
            $table->index(['customer_first_name', 'customer_last_name']);
            $table->index(['collection_by_user_first_name', 'collection_by_user_last_name'], 'collection_by_user_name');
            $table->index(['collection_verified_by_user_first_name', 'collection_verified_by_user_last_name'], 'collection_verified_by_user_name');

            /* Foreign Key Constraints */
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('customer_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('occasion_id')->references('id')->on('occasions')->nullOnDelete();
            $table->foreign('collection_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->nullOnDelete();
            $table->foreign('collection_verified_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('delivery_address_id')->references('id')->on('delivery_addresses')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
