<?php

namespace App\Notifications\Orders;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\BlockKit\Composites\ConfirmObject;

class OrderCreated extends Notification
{
    use Queueable, BaseTrait;

    public Order $order;
    public Cart $cart;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load('cart');
        $this->cart = $this->order->cart;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast', 'slack'];
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

        //  Check if this order has an occasion
        if(!is_null($order->occasion_id)) {

            /**
             *  @var Occasion $occasion
             */
            $occasion = $order->occasion;

        }

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
                'summary' => $order->summary,
                'orderFor' => $order->order_for,
                'amount' => $order->amount_outstanding,
                'isAssociatedAsFriend' => $isAssociatedAsFriend,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer,
                'orderForTotalUsers' => $order->order_for_total_users,
                'customer' => [
                    'name' => $order->customer_name,
                    'id' => $order->customer_user_id,
                    'lastName' => $order->customer_last_name,
                    'firstName' => $order->customer_first_name,
                ],
                'orderForTotalFriends' => $order->order_for_total_friends,
            ],
            'occasion' => isset($occasion) ? [
                'name' => $occasion->name
            ] : null
        ];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage)->content($this->order->summary)->attachment(function ($attachment) {

            $totalUsers = $this->order->order_for_total_users;

            $attachment->fields([
                'Customer' => $this->order->customer_name,
                'For' => $totalUsers == 1 ? $totalUsers.' person' : $totalUsers.' people',
            ]);

        });
    }
}
