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

            $table->id();

            /*  General Information */
            $table->string('content', SmsMessage::CONTENT_MAX_CHARACTERS);
            $table->string('recipient_mobile_number', 11);
            $table->boolean('sent')->default(false);
            $table->json('error')->nullable();

            /*  Timestamps  */
            $table->timestamps();

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
