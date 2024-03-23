<?php

namespace App\Traits;

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProductLine;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Ussd\UssdService;
use App\Traits\Base\BaseTrait;
use Carbon\Carbon;

trait StoreTrait
{
    use BaseTrait;

    /**
     *  Craft the store created successfully sms messsage to send to the user
     *
     *  @param User $user
     *
     *  @return string
     */
    public function craftStoreCreatedSmsMessage($user)
    {
        return 'Hi '.$user->first_name.', your store '.$this->name_with_emoji.' was created successfully. Subscribe to list your store on Bw Stores and for customers to place orders on '.(UssdService::appendToMainShortcode($user->mobile_number->withoutExtension)).' 😉';
    }
}
