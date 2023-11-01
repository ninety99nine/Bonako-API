<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class OrderStatusUpdated extends Notification
{
    use Queueable, BaseTrait;

    public Order $order;
    public User $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, User $user)
    {
        $this->order = $order;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $order = $this->order;

        /**
         *  @var Store $store
         */
        $store = $this->order->store;

        //  Check if this order has tagged friends
        if($order->order_for_total_friends) {

            /**
             *  @var Collection<User> $friends
             */
            $friendIds = $this->order->friends->pluck('id');

        }else{

            $friendIds = [];

        }

        //  Check if this notifiable user is the customer of this order
        $isAssociatedAsCustomer = $order->customer_user_id == $notifiable->id;

        //  Check if this notifiable user is tagged as a friend of this order
        $isAssociatedAsFriend = collect($friendIds)->contains($notifiable->id);

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'order' => [
                'id' => $order->id,
                'number' => $order->number,
                'status' => $order->status,
                'summary' => $order->summary,
                'orderFor' => $order->order_for,
                'amount' => $order->grand_total,
                'isAssociatedAsFriend' => $isAssociatedAsFriend,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer,
                'orderForTotalUsers' => $order->order_for_total_users,
                'orderForTotalFriends' => $order->order_for_total_friends,
                'customer' => [
                    'name' => $order->customer_name,
                    'id' => $order->customer_user_id,
                    'lastName' => $order->customer_last_name,
                    'firstName' => $order->customer_first_name,
                ],
                'changedByUser' => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'firstName' => $this->user->first_name
                ],
            ]
        ];
    }
}
