<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsMessageStoreAssociationTable extends Migration
{
    public function up()
    {
        Schema::create('sms_message_store_association', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id');
            $table->foreignId('sms_message_id');
            $table->timestamps();

            /* Add Indexes */
            $table->index('store_id');
            $table->index('sms_message_id');

            /*  Foreign Key Constraints */
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('sms_message_id')->references('id')->on('sms_messages')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_message_store_association');
    }
}

