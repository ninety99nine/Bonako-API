<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsAlertActivityStoreAssociationsTable extends Migration
{
    public function up()
    {
        Schema::create('sms_alert_activity_store_associations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->cascadeOnDelete();
            $table->foreignId('sms_alert_activity_association_id')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_alert_activity_store_associations');
    }
}


