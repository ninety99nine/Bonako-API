<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Store;
use App\Traits\Base\BaseTrait;
use App\Models\SmsAlertActivity;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;
use App\Repositories\SmsAlertActivityAssociationRepository;

class SmsAlertRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Return the SmsAlertActivityAssociationRepository instance
     *
     *  @return SmsAlertActivityAssociationRepository
     */
    public function smsAlertActivityAssociationRepository()
    {
        return resolve(SmsAlertActivityAssociationRepository::class);
    }

    /**
     *  Create an SMS Alert record for the user
     *
     *  @param User $user
     *  @return SmsAlertRepository
     */
    public function createSmsAlert(User $user)
    {
        $smsAlertRepository = parent::create([
            'sms_credits' => 0,
            'user_id' => $user->id
        ]);

        $smsAlertActivities = SmsAlertActivity::all();
        $smsAlert = $smsAlertRepository->model;
        $records = [];

        foreach($smsAlertActivities as $smsAlertActivity) {

            $records[] = [
                'updated_at' => now(),
                'created_at' => now(),
                'total_alerts_sent' => 0,
                'sms_alert_id' => $smsAlert->id,
                'sms_alert_activity_id' => $smsAlertActivity->id,
            ];

        }

        DB::table('sms_alert_activity_associations')->insert($records);

        return $smsAlertRepository->setModel($smsAlert->fresh());
    }

    /**
     *  Show the SMS Alert record for the user
     *
     *  @param User $user
     *  @return SmsAlertRepository
     */
    public function showSmsAlert(User $user)
    {
        $smsAlert = $user->smsAlert;

        if(is_null($smsAlert)) {
            return $this->createSmsAlert($user);
        }else{
            return $this->setModel($user->smsAlert);
        }
    }

    /**
     *  Add the SMS Alertable store for the user
     *
     *  @param User $user
     *  @param Store $store
     *  @return SmsAlertRepository
     */
    public function addSmsAlertableStore($user, $store)
    {
        $smsAlertRepository = $this->showSmsAlert($user);
        $smsAlert = $smsAlertRepository->model;

        $smsAlertActivityAssociations = $smsAlert->smsAlertActivityAssociations;

        foreach($smsAlertActivityAssociations as $smsAlertActivityAssociation) {

            // Add this store to this sms alert activity association
            $this->smsAlertActivityAssociationRepository()->setModel($smsAlertActivityAssociation)->addSmsAlertableStore($store);

        }
    }
}
