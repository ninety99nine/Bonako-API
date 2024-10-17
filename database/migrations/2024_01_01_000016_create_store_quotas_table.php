<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreQuotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_quotas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('sms_credits')->default(0);
            $table->unsignedInteger('email_credits')->default(0);
            $table->unsignedInteger('whatsapp_credits')->default(0);
            $table->timestamp('sms_credits_expire_at')->nullable();
            $table->timestamp('email_credits_expire_at')->nullable();
            $table->timestamp('whatsapp_credits_expire_at')->nullable();
            $table->foreignUuid('store_id');
            $table->timestamps();

            /* Add Indexes */
            $table->index('store_id');

            /* Foreign Key Constraints */
            $table->foreign('store_id')->references('id')->on('storeS')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_quotas');
    }
}
