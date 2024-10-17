<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsAlertActivitiesTable extends Migration
{
    public function up()
    {
        Schema::create('sms_alert_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description');
            $table->boolean('enabled')->default(true);
            $table->boolean('requires_stores')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_alert_activities');
    }
}

