<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use App\Traits\MessageCrafterTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Orders\Base\OrderNotification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

/**
 * Note that the OrderMarkedAsPaid is extending our custom OrderNotification
 * class instead of the Laravel default Notification. This is because
 * the OrderNotification class contains additional custom methods
 * specific for order notifications.
 */
class OrderMarkedAsPaid extends OrderNotification implements ShouldQueue
{
    use Queueable, BaseTrait, MessageCrafterTrait;

    public Order $order;
    public Store $store;
    public Transaction $transaction;
    public PaymentMethod $paymentMethod;
    public User $manuallyVerifiedByUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, Store $store, Transaction $transaction, User $manuallyVerifiedByUser)
    {
        $this->order = $order;
        $this->store = $store;
        $this->manuallyVerifiedByUser = $manuallyVerifiedByUser;
        $this->transaction = $transaction->loadMissing(['paymentMethod']);
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
        $transaction = $this->transaction;
        $paymentMethod = $this->transaction->paymentMethod;
        $paidByYou = $notifiable->id == $transaction->payer_id;
        $manuallyVerifiedByUser = $this->manuallyVerifiedByUser;
        $isAssociatedAsCustomer = $this->checkIfAssociatedAsCustomer($order, $notifiable);

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'order' => [
                'id' => $order->id,
                'number' => $order->number,
                'customer_last_name' => $order->customer_last_name,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer,
                'customer_first_name' => $order->customer_first_name,
            ],
            'manuallyVerifiedByUser' => [
                'id' => $manuallyVerifiedByUser->id,
                'name' => $manuallyVerifiedByUser->name
            ],
            'paymentMethod' => [
                'id' => $paymentMethod->id,
                'name' => $paymentMethod->name
            ],
            'transaction' => [
                'id' => $transaction->id,
                'paidByYou' => $paidByYou,
                'amount' => $transaction->amount,
                'percentage' => $transaction->percentage
            ],
        ];
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $store = $this->store;
        $order = $this->order;
        $transaction = $this->transaction;
        $subject = 'Order payment confirmation';
        $manuallyVerifiedByUser = $this->manuallyVerifiedByUser;
        $body = $order->craftOrderMarkedAsPaidMessage($store, $transaction, $manuallyVerifiedByUser);

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
