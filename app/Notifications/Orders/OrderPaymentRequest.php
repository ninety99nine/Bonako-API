<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use App\Models\PaymentMethod;
use App\Traits\Base\BaseTrait;
use App\Traits\MessageCrafterTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Orders\Base\OrderNotification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

/**
 * Note that the OrderPaymentRequest is extending our custom OrderNotification
 * class instead of the Laravel default Notification. This is because
 * the OrderNotification class contains additional custom methods
 * specific for order notifications.
 */
class OrderPaymentRequest extends OrderNotification implements ShouldQueue
{
    use Queueable, BaseTrait, MessageCrafterTrait;

    public Order $order;
    public Store $store;
    public Transaction $transaction;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, Store $store, Transaction $transaction)
    {
        $this->order = $order;
        $this->store = $store;
        $this->transaction = $transaction->loadMissing(['requestedByUser', 'paymentMethod']);
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
        $requestedByUser = $this->transaction->requestedByUser;

        /**
         *  @var Store $store
         */
        $store = $this->order->store;

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'order' => [
                'id' => $order->id,
                'number' => $order->number,
                'summary' => $order->summary
            ],
            'customer' => [
                'name' => $order->customer_name,
                'firstName' => $order->customer_first_name,
            ],
            'requestedByUser' => [
                'id' => $requestedByUser->id,
                'name' => $requestedByUser->name,
                'firstName' => $requestedByUser->first_name
            ],
            'paymentMethod' => [
                'id' => $paymentMethod->id,
                'name' => $paymentMethod->name
            ],
            'transaction' => [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'metadata' => $transaction->metadata,
                'percentage' => $transaction->percentage,
            ],
        ];
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $store = $this->store;
        $order = $this->order;
        $transaction = $this->transaction;
        $subject = 'Order payment request';
        $body = $order->craftOrderPaymentRequestMessage($store, $transaction);

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
