<?php

use App\Models\Store;
use Illuminate\Support\Arr;
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
            $table->id();
            $table->string('emoji')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_photo')->nullable();
            $table->json('adverts')->nullable();
            $table->string('name', Store::NAME_MAX_CHARACTERS);
            $table->enum('call_to_action', Store::CALL_TO_ACTION_OPTIONS)->default(Arr::first(Store::CALL_TO_ACTION_OPTIONS));
            $table->string('mobile_number', 11);
            $table->timestamp('last_subscription_end_at')->nullable();
            $table->string('description', Store::DESCRIPTION_MAX_CHARACTERS)->nullable();
            $table->char('currency', 3)->default(Store::CURRENCY);
            $table->boolean('registered_with_bank')->nullable();
            $table->enum('banking_with', Store::BANKING_WITH)->nullable();
            $table->boolean('registered_with_cipa')->nullable();
            $table->enum('registered_with_cipa_as', Store::REGISTERED_WITH_CIPA_AS)->nullable();
            $table->string('company_uin', Store::COMPANY_UIN_CHARACTERS)->nullable();
            $table->unsignedSmallInteger('number_of_employees')->nullable();
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
            $table->boolean('perfect_pay_enabled')->default(false);
            $table->boolean('orange_money_payment_enabled')->default(false);
            $table->string('orange_money_merchant_code', Store::ORANGE_MONEY_MERCHANT_CODE_MAX_CHARACTERS)->nullable();
            $table->boolean('dpo_payment_enabled')->default(false);
            $table->string('dpo_company_token', Store::DPO_COMPANY_TOKEN_MAX_CHARACTERS)->nullable();
            $table->boolean('allow_deposit_payments')->default(false);
            $table->json('deposit_percentages')->nullable();
            $table->boolean('allow_installment_payments')->default(false);
            $table->json('installment_percentages')->nullable();

            $table->boolean('is_influencer_store')->default(false);
            $table->boolean('is_brand_store')->default(false);

            $table->string('sms_sender_name', Store::SMS_SENDER_NAME_MAX_CHARACTERS)->nullable();

            /* Add Timestamps */
            $table->timestamps();

            /* Add Indexes */
            $table->index('name');
            $table->index('mobile_number');
            $table->index('banking_with');

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
