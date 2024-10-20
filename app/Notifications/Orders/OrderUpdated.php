<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Customer;
use App\Models\Occasion;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use App\Traits\MessageCrafterTrait;
use Illuminate\Notifications\Messages\SlackMessage;
use App\Notifications\Orders\Base\OrderNotification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

/**
 * Note that the OrderUpdated is extending our custom OrderNotification
 * class instead of the Laravel default Notification. This is because
 * the OrderNotification class contains additional custom methods
 * specific for order notifications.
 */
class OrderUpdated extends OrderNotification
{
    use Queueable, BaseTrait, MessageCrafterTrait;

    public Store $store;
    public Order $order;
    public User $updatedByUser;
    public ?Customer $customer;
    public ?Occasion $occasion;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, User $updatedByUser)
    {
        $this->order = $order->load(['customer', 'store', 'occasion']);
        $this->customer = $this->order->customer;
        $this->occasion = $this->order->occasion;
        $this->updatedByUser = $updatedByUser;
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
        return ['database', 'broadcast', 'slack', OneSignalChannel::class];
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
        $customer = $this->customer;
        $occasion = $this->occasion;
        $updatedByUser = $this->updatedByUser;
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
                'id' => $customer?->id,
                'name' => $order->customer_name,
                'firstName' => $order->customer_first_name,
            ],
            'updatedByUser' => [
                'id' => $updatedByUser->id,
                'name' => $updatedByUser->name,
                'firstName' => $updatedByUser->first_name,
            ],
            'occasion' => isset($occasion) ? [
                'name' => $occasion->name
            ] : null
        ];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(User $notifiable): SlackMessage
    {
        return (new SlackMessage)->content($this->order->summary)->attachment(function ($attachment) {

            $attachment->fields([
                'Customer' => $this->order->customer_name
            ]);

        });
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $order = $this->order;
        $store = $this->store;
        $subject = 'Order updated';
        $updatedByUser = $this->updatedByUser;
        $body = $order->craftOrderUpdatedMessage($store, $updatedByUser);

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
