<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use App\Traits\MessageCrafterTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Orders\Base\OrderNotification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

/**
 * Note that the OrderSeen is extending our custom OrderNotification
 * class instead of the Laravel default Notification. This is because
 * the OrderNotification class contains additional custom methods
 * specific for order notifications.
 */
class OrderSeen extends OrderNotification implements ShouldQueue
{
    use Queueable, BaseTrait, MessageCrafterTrait;

    public Order $order;
    public Store $store;
    public User $seenByUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, User $seenByUser)
    {
        $this->order = $order;
        $this->seenByUser = $seenByUser;
        $this->store = $this->order->store;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', OneSignalChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $order = $this->order;
        $store = $this->store;
        $seenByUser = $this->seenByUser;
        $isAssociatedAsCustomer = $this->checkIfAssociatedAsCustomer($order, $notifiable);

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'order' => [
                'id' => $order->id,
                'number' => $order->number,
                'summary' => $order->summary,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer,
            ],
            'customer' => [
                'name' => $order->customer_name,
                'firstName' => $order->customer_first_name,
            ],
            'seenByUser' => [
                'id' => $seenByUser->id,
                'name' => $seenByUser->name,
                'firstName' => $seenByUser->first_name
            ],
        ];
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $order = $this->order;
        $store = $this->store;
        $subject = 'Order seen';
        $seenByUser = $this->seenByUser;
        $body = $order->craftOrderSeenMessage($store, $seenByUser);

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
