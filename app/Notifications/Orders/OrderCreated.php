<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Occasion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use App\Notifications\Orders\Base\OrderNotification;

/**
 * Note that the OrderCreated is extending our custom OrderNotification
 * class instead of the Laravel default Notification. This is because
 * the OrderNotification class contains additional custom methods
 * specific for order notifications.
 */
class OrderCreated extends OrderNotification
{
    use Queueable;

    public Store $store;
    public Order $order;
    public User $customer;
    public ?Occasion $occasion;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order->load(['customer', 'store', 'occasion']);
        $this->customer = $this->order->customer;
        $this->occasion = $this->order->occasion;
        $this->store = $this->order->store;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(User $notifiable): array
    {
        return ['database', 'broadcast', 'slack', OneSignalChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $order = $this->order;
        $store = $this->store;
        $customer = $this->customer;
        $occasion = $this->occasion;
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
                'summary' => $order->summary,
                'amount' => $order->amount_outstanding,
                'isAssociatedAsFriend' => $isAssociatedAsFriend,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer,
                'orderForTotalFriends' => $order->order_for_total_friends,
            ],
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'firstName' => $customer->first_name,
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

    public function toOneSignal(User $notifiable): OneSignalMessage
    {
        $order = $this->order;
        $store = $this->store;
        $subject = 'New order';
        $customer = $this->customer;

        if($this->checkIfAssociatedAsCustomer($order, $notifiable)) {

            $body = $order->craftNewOrderForCustomerMessage($store);

        }else if($this->checkIfAssociatedAsFriend($order, $notifiable)) {

            $body = $order->craftNewOrderForFriendMessage($notifiable);

        }else{

            $body = $order->craftNewOrderForSellerMessage($store, $customer);

        }

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
