<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmsAlertActivityStoreAssociationsTable extends Migration
{
    public function up()
    {
        Schema::create('sms_alert_activity_store_associations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->cascadeOnDelete();
            $table->foreignUuid('sms_alert_activity_association_id')->cascadeOnDelete();

            $table->timestamps();

            /*  Foreign Key Constraints */
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();

            /**
             *  To avoid the following error:
             *
             *  "Syntax error or access violation: 1059 Identifier name 'sms_alert_activity_store_associations_sms_alert_activity_association_id_foreign'
             *  is too long (Connection: mysql, SQL: alter table `sms_alert_activity_store_associations` add constraint
             *  `sms_alert_activity_store_associations_sms_alert_activity_association_id_foreign` foreign key
             *  (`sms_alert_activity_association_id`) references `sms_alert_activity_associations` (`id`) on
             *  delete cascade)"
             *
             *  We need to pass a shorter identifier name as the second parameter e.g "sms_alert_activity_association_id_foreign"
             *
             *  Reference: https://laracasts.com/discuss/channels/general-discussion/sqlstate42000-syntax-error-or-access-violation-1059-identifier-name-is-too-long
             */
            $table->foreign('sms_alert_activity_association_id', 'sms_alert_activity_association_id_foreign')->references('id')->on('sms_alert_activity_associations')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_alert_activity_store_associations');
    }
}


