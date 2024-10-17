<?php

use App\Models\SmsMessage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_messages', function (Blueprint $table) {

            $table->uuid('id')->primary();

            /*  General Information */
            $table->string('content', SmsMessage::CONTENT_MAX_CHARACTERS);
            $table->string('recipient_mobile_number', 20);
            $table->boolean('sent')->default(false);
            $table->json('error')->nullable();

            /*  Store Owenership Information  */
            $table->foreignUuid('store_id')->nullable();

            /*  Timestamps  */
            $table->timestamps();

            $table->index('recipient_mobile_number');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_messages');
    }
}
