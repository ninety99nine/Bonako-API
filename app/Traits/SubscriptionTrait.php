<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Store;
use App\Models\AiAssistant;
use App\Traits\Base\BaseTrait;
use Illuminate\Database\Eloquent\Model;

trait SubscriptionTrait
{
    use BaseTrait;

    /**
     *  Craft the subscription paid successfully sms messsage to send to the user
     *
     *  @param User $user
     *  @param Model $model
     *
     *  @return Order
     */
    public function craftSubscriptionSuccessfulSmsMessageForUser($user, $model) {

        if(($store = $model) instanceof Store) {

            $subscriptionFor = $store->name;

        }else if($model instanceof AiAssistant) {

            $subscriptionFor = 'AI Assistant';

        }

        return 'Hi '.$user->first_name.', your subscription for '.$subscriptionFor.' has been paid successfully. Valid till '.Carbon::parse($this->end_at)->format('d M Y H:m').'! Enjoy 😉';
    }

}
