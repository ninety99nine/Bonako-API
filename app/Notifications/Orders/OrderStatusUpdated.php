<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Orders\Base\OrderNotification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

/**
 * Note that the OrderStatusUpdated is extending our custom OrderNotification
 * class instead of the Laravel default Notification. This is because
 * the OrderNotification class contains additional custom methods
 * specific for order notifications.
 */
class OrderStatusUpdated extends OrderNotification
{
    use Queueable, BaseTrait;

    public Order $order;
    public Store $store;
    public User $updatedByUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, User $updatedByUser)
    {
        $this->order = $order;
        $this->store = $this->order->store;
        $this->updatedByUser = $updatedByUser;
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
        $updatedByUser = $this->updatedByUser;
        $isAssociatedAsFriend = $this->checkIfAssociatedAsFriend($order, $notifiable);
        $isAssociatedAsCustomer = $this->checkIfAssociatedAsCustomer($order, $notifiable);

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
                'isAssociatedAsFriend' => $isAssociatedAsFriend,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer
            ],
            'customer' => [
                'name' => $order->customer_name,
                'id' => $order->customer_user_id,
                'firstName' => $order->customer_first_name,
            ],
            'updatedByUser' => [
                'id' => $updatedByUser->id,
                'name' => $updatedByUser->name,
                'firstName' => $updatedByUser->first_name
            ],
        ];
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $order = $this->order;
        $store = $this->store;
        $subject = 'Order status updated';
        $updatedByUser = $this->updatedByUser;
        $body = $order->craftOrderStatusUpdatedMessage($store, $updatedByUser);

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
