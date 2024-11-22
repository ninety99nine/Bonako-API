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
 * Note that the OrderPaidUsingDPO is extending our custom OrderNotification
 * class instead of the Laravel default Notification. This is because
 * the OrderNotification class contains additional custom methods
 * specific for order notifications.
 */
class OrderPaidUsingDPO extends OrderNotification implements ShouldQueue
{
    use Queueable, BaseTrait, MessageCrafterTrait;

    public Order $order;
    public Store $store;
    public User $manuallyVerifiedByUser;
    public Transaction $transaction;
    public PaymentMethod $paymentMethod;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, Store $store, Transaction $transaction)
    {
        $this->order = $order;
        $this->store = $store;
        $this->transaction = $transaction;
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
        $transaction = $this->transaction;
        $paymentMethod = $this->paymentMethod;
        $isAssociatedAsCustomer = $this->checkIfAssociatedAsCustomer($order, $notifiable);
        $dpoCustomerName = $transaction->metadata['dpo_payment_response']['onVerifyPaymentResponse']['customerName'];
        $dpoCustomerPhone = $transaction->metadata['dpo_payment_response']['onVerifyPaymentResponse']['customerPhone'];
        $paidByYou = $notifiable->id == $transaction->payer_id && $notifiable->mobile_number->formatE164() == $dpoCustomerPhone;

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'order' => [
                'id' => $order->id,
                'number' => $order->number,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer,
            ],
            'paymentMethod' => [
                'id' => $paymentMethod->id,
                'name' => $paymentMethod->name
            ],
            'transaction' => [
                'id' => $transaction->id,
                'paidByYou' => $paidByYou,
                'amount' => $transaction->amount,
                'dpoCustomerName' => $dpoCustomerName,
                'percentage' => $transaction->percentage,
            ],
        ];
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $order = $this->order;
        $transaction = $this->transaction;
        $subject = 'Order payment success';
        $body = $order->craftOrderPaidMessage($order, $transaction);

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
