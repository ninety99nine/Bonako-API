<?php

use Illuminate\Support\Arr;
use App\Models\MobileVerification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMobileVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mobile_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('code', 6)->nullable();
            $table->string('mobile_number', 20);

            /*  Timestamps  */
            $table->timestamps();

            /* Add Indexes */
            $table->index(['mobile_number', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobile_verifications');
    }
}
