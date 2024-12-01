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

            $table->uuid('id')->primary();

            /* Order Summary */
            $table->string('summary')->nullable();

            /* Financial Information */
            $table->char('currency', 3)->default(config('app.DEFAULT_CURRENCY'));
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->decimal('paid_total', 10, 2)->default(0);
            $table->unsignedTinyInteger('paid_percentage')->default(0);
            $table->decimal('pending_total', 10, 2)->default(0);
            $table->unsignedTinyInteger('pending_percentage')->default(0);
            $table->decimal('outstanding_total', 10, 2)->default(0);
            $table->unsignedTinyInteger('outstanding_percentage')->default(100);

            /* Status Information */
            $table->enum('status', Order::STATUSES())->default(Arr::first(Order::STATUSES()));
            $table->enum('payment_status', Order::PAYMENT_STATUSES())->default(Arr::last(Order::PAYMENT_STATUSES()));

            /* Notes */
            $table->string('customer_note', Order::CUSTOMER_NOTE_MAX_CHARACTERS)->nullable();
            $table->text('store_note')->nullable();

            /* Cancellation Information */
            $table->enum('cancellation_reason', Order::CANCELLATION_REASONS())->nullable();
            $table->string('other_cancellation_reason', Order::OTHER_CANCELLATION_REASON_MAX_CHARACTERS)->nullable();
            $table->timestamp('cancelled_at')->nullable();

            /* Customer Information */
            $table->string('customer_first_name');
            $table->string('customer_last_name')->nullable();
            $table->string('customer_mobile_number', 20)->nullable();
            $table->string('customer_email')->nullable();
            $table->foreignUuid('customer_id')->nullable();
            $table->foreignUuid('placed_by_user_id')->nullable();

            /* Collection Information */
            $table->enum('collection_type', Order::COLLECTION_TYPES())->nullable();
            $table->string('destination_name')->nullable();

            /* Collection Verification */
            $table->char('collection_code', 6)->nullable();
            $table->string('collection_qr_code')->nullable();
            $table->timestamp('collection_code_expires_at')->nullable();
            $table->boolean('collection_verified')->default(false);
            $table->timestamp('collection_verified_at')->nullable();
            $table->foreignUuid('collection_verified_by_user_id')->nullable();
            $table->text('collection_note')->nullable();

            /* Relationships */
            $table->foreignUuid('cart_id');
            $table->foreignUuid('store_id');
            $table->foreignUuid('occasion_id')->nullable();
            $table->foreignUuid('friend_group_id')->nullable();
            $table->foreignUuid('created_by_user_id')->nullable();

            /* Team Views */
            $table->unsignedSmallInteger('total_views_by_team')->default(1);
            $table->timestamp('first_viewed_by_team_at')->nullable();
            $table->timestamp('last_viewed_by_team_at')->nullable();

            /* Timestamps */
            $table->timestamps();

            /* Add Indexes */
            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['occasion_id']);
            $table->index(['payment_status']);
            $table->index(['collection_verified_by_user_id']);
            $table->index(['customer_first_name', 'customer_last_name']);

            /* Foreign Key Constraints */
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('occasion_id')->references('id')->on('occasions')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('placed_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('collection_verified_by_user_id')->references('id')->on('users')->nullOnDelete();
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
