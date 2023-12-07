<?php

namespace App\Repositories;

use App\Models\Store;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;

class SmsAlertActivityAssociationRepository extends BaseRepository
{
    use BaseTrait;

    /**
     *  Add the SMS Alertable store
     *
     *  @param Store $request
     *  @return SmsAlertActivityAssociationRepository
     */
    public function update($request)
    {
        //  If the store ids are provided
        if($request->filled('store_ids')) {

            //  Get the store ids
            $storeIds = $request->input('store_ids');

            //  Sync the sms alertable stores
            $this->syncSmsAlertableStores($storeIds);

        }

        return parent::update($request);
    }

    /**
     *  Add the SMS Alertable store
     *
     *  @param Store $store
     *  @return void
     */
    public function addSmsAlertableStore($store)
    {
        // Check if the sms alert activity requires stores
        if($this->model->smsAlertActivity->requires_stores) {

            // Add this store to this sms alert activity association
            $this->model->stores()->syncWithoutDetaching($store);

        }
    }

    /**
     *  Sync the SMS Alertable stores
     *
     *  Stores that are provided in this collection will be added while
     *  stores that are not provided in this collection will be removed
     *  from the intermediate table.
     *
     *  @param Illuminate\Database\Eloquent\Collection<Store>|array<int> $stores
     *  @return void
     */
    public function syncSmsAlertableStores($stores)
    {
        // Check if the sms alert activity requires stores
        if($this->model->smsAlertActivity->requires_stores) {

            // Sync these stores to this sms alert activity association
            $this->model->stores()->sync($stores);

        }
    }
}
