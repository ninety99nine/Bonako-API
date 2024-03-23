<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
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
class OrderMarkedAsPaid extends OrderNotification
{
    use Queueable, BaseTrait;

    public Order $order;
    public Store $store;
    public User $paidByUser;
    public User $verifiedByUser;
    public Transaction $transaction;
    public PaymentMethod $paymentMethod;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, Store $store, Transaction $transaction, User $verifiedByUser)
    {
        $this->order = $order;
        $this->store = $store;
        $this->transaction = $transaction;
        $this->verifiedByUser = $verifiedByUser;
        $this->paidByUser = $this->transaction->paidByUser;
        $this->paymentMethod = $transaction->paymentMethod;
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
        $paidByUser = $this->paidByUser;
        $transaction = $this->transaction;
        $paymentMethod = $this->paymentMethod;
        $verifiedByUser = $this->verifiedByUser;
        $paidByYou = $notifiable->id == $transaction->payer_id;
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
                'isAssociatedAsFriend' => $isAssociatedAsFriend,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer,
            ],
            'verifiedByUser' => [
                'id' => $verifiedByUser->id,
                'name' => $verifiedByUser->name
            ],
            'paidByUser' => [
                'id' => $paidByUser->id,
                'name' => $paidByUser->name
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
        $verifiedByUser = $this->verifiedByUser;
        $subject = 'Order payment confirmation';
        $body = $order->craftOrderMarkedAsPaidMessage($store, $transaction, $verifiedByUser);

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
