<?php

namespace App\Notifications\Orders\Base;

use App\Models\User;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable, BaseTrait;

    /**
     *  Check if this notifiable user is associated as a customer of this order
     *  @param Order $order
     *  @param User $notifiable
     *  @return bool
     */
    public function checkIfAssociatedAsCustomer(Order $order, User $notifiable): bool
    {
        return $order->placed_by_user_id == $notifiable->id;
    }
}
