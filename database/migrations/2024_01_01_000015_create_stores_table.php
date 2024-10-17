<?php

use App\Models\Store;
use App\Enums\CallToAction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('emoji')->nullable();
            $table->string('name', Store::NAME_MAX_CHARACTERS);
            $table->string('alias', Store::ALIAS_MAX_CHARACTERS)->unique()->nullable();
            $table->string('ussd_mobile_number', 20)->nullable();
            $table->string('contact_mobile_number', 20)->nullable();
            $table->string('whatsapp_mobile_number', 20)->nullable();
            $table->enum('call_to_action', Store::CALL_TO_ACTION_OPTIONS())->default(CallToAction::BUY);
            $table->timestamp('last_subscription_end_at')->nullable();
            $table->string('description', Store::DESCRIPTION_MAX_CHARACTERS)->nullable();
            $table->char('currency', 3)->default(Store::CURRENCY);
            $table->boolean('verified')->default(false);
            $table->boolean('online')->default(true);
            $table->string('offline_message', Store::OFFLINE_MESSAGE_MAX_CHARACTERS)->default(Store::DEFAULT_OFFLINE_MESSAGE);

            /*  Privacy Information  */
            $table->boolean('identified_orders')->default(false);

            /*  Delivery Settings  */
            $table->boolean('allow_delivery')->default(false);
            $table->boolean('allow_free_delivery')->default(false);
            $table->decimal('delivery_flat_fee', 10, 2)->default(0);
            $table->string('delivery_note', Store::DELIVERY_NOTE_MAX_CHARACTERS)->nullable();
            $table->json('delivery_destinations')->nullable();

            /*  Pickup Settings  */
            $table->boolean('allow_pickup')->default(false);
            $table->string('pickup_note', Store::PICKUP_NOTE_MAX_CHARACTERS)->nullable();
            $table->json('pickup_destinations')->nullable();

            /*  Payment Settings  */
            $table->boolean('allow_deposit_payments')->default(false);
            $table->json('deposit_percentages')->nullable();
            $table->boolean('allow_installment_payments')->default(false);
            $table->json('installment_percentages')->nullable();
            $table->boolean('has_automated_payment_methods')->default(false);

            $table->string('sms_sender_name', Store::SMS_SENDER_NAME_MAX_CHARACTERS)->nullable();

            /* Add Timestamps */
            $table->timestamps();

            /* Add Indexes */
            $table->index('name');
            $table->index('created_at');
            $table->index('ussd_mobile_number');
            $table->index('contact_mobile_number');
            $table->index('whatsapp_mobile_number');
            $table->index('last_subscription_end_at');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}