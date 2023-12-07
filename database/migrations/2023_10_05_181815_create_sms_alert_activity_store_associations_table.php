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
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sms_alert_activity_association_id')->constrained()->cascadeOnDelete()
                /**
                 *  Syntax error or access violation: 1059 Identifier name 'sms_alert_activity_store_association_sms_alert_activity_association_id_foreign'
                 *  is too long (Connection: mysql, SQL: alter table `sms_alert_activity_store_associations` add constraint
                 *  `sms_alert_activity_store_association_sms_alert_activity_association_id_foreign` foreign key
                 *  (`sms_alert_activity_association_id`) references `sms_alert_activity_associations` (`id`)
                 *  on delete cascade)
                 *
                 *  To set a custom name for your foreign key constraint to avoid this error, we should use the "name" method.
                 */
                ->name('sms_alert_activity_association_id_foreign');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_alert_activity_store_associations');
    }
}


