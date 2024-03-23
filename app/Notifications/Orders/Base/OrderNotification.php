<?php

namespace App\Notifications\Orders\Base;

use App\Models\User;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable, BaseTrait;

    protected $friendIds = null;

    /**
     *  Get the order friend ids
     *  @param Order $order
     *  @return Collection
     */
    public function getFriendIds(Order $order): Collection
    {
        //  If we haven't yet requested the friend ids
        if( is_null($this->friendIds) ) {

            //  Check if this order has tagged friend ids
            if($order->order_for_total_friends) {

                //  Return the friend ids
                return $this->friendIds = $order->friends()->pluck('users.id');

            }else{

                //  Return an empty collection
                return $this->friendIds = collect([]);

            }

        }else{

            //  Return the ready requested friend ids
            return $this->friendIds;

        }
    }

    /**
     *  Check if this notifiable user is associated as a customer of this order
     *  @param Order $order
     *  @param User $notifiable
     *  @return bool
     */
    public function checkIfAssociatedAsCustomer(Order $order, User $notifiable): bool
    {
        return $order->customer_user_id == $notifiable->id;
    }

    /**
     *  Check if this notifiable user is associated as a friend of this order
     *  @param Collection $friends
     *  @param User $notifiable
     *  @return bool
     */
    public function checkIfAssociatedAsFriend(Order $order, User $notifiable): bool
    {
        return $this->getFriendIds($order)->contains('id', '=', $notifiable->id);
    }
}
