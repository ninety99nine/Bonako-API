<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Store;
use App\Enums\RefreshModel;
use App\Traits\Base\BaseTrait;
use App\Models\SmsAlertActivity;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
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
        ], RefreshModel::NO);

        $smsAlertActivityIds = SmsAlertActivity::pluck('id');
        $smsAlert = $smsAlertRepository->model;
        $records = [];

        foreach($smsAlertActivityIds as $smsAlertActivityId) {

            $records[] = [
                'updated_at' => now(),
                'created_at' => now(),
                'total_alerts_sent' => 0,
                'sms_alert_id' => $smsAlert->id,
                'sms_alert_activity_id' => $smsAlertActivityId,
            ];

        }

        DB::table('sms_alert_activity_associations')->insert($records);

        return $smsAlertRepository;
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
            return $smsAlert;
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

        $smsAlertActivityAssociationIds = $smsAlert->smsAlertActivityAssociations()->whereHas('smsAlertActivity', function (Builder $query) {

            //  Where the sms alert activity requires stores
            $query->where('requires_stores', '1');

        })->whereDoesntHave('stores', function (Builder $query) use ($store) {

            //  Where the store does not exist
            $query->where('sms_alert_activity_store_associations.store_id', $store->id);

        })->pluck('sms_alert_activity_associations.id');

        $records = [];

        foreach($smsAlertActivityAssociationIds as $smsAlertActivityAssociationId) {

            $records[] = [
                'updated_at' => now(),
                'created_at' => now(),
                'store_id' => $store->id,
                'sms_alert_activity_association_id' => $smsAlertActivityAssociationId,
            ];

        }

        DB::table('sms_alert_activity_store_associations')->insert($records);
    }
}
