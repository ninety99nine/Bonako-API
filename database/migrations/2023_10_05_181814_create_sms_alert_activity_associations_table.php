<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsAlertActivityAssociationsTable extends Migration
{
    public function up()
    {
        Schema::create('sms_alert_activity_associations', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->unsignedTinyInteger('total_alerts_sent')->nullable();
            $table->foreignId('sms_alert_id')->cascadeOnDelete();
            $table->foreignId('sms_alert_activity_id')->cascadeOnDelete();
            $table->timestamps();

            /* Add Indexes */
            $table->index('sms_alert_id');
            $table->index('sms_alert_activity_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_alert_activity_associations');
    }
}


