<?php

namespace App\Notifications\Orders;

use App\Models\User;
use App\Models\Order;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;

class OrderPaid extends Notification
{
    use Queueable, BaseTrait;

    public User $user;
    public Order $order;
    public Transaction $transaction;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, Transaction $transaction)
    {
        $this->order = $order;
        $this->transaction = $transaction;
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
        $transaction = $this->transaction;

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

        $orderedAndPaidBySamePerson = $order->customer_mobile_number == $transaction->dpo_payment_response['onVerifyPaymentResponse']['customerPhone'];
        $orderedByYou = $order->customer_mobile_number == $notifiable->mobile_number;
        $orderedAndPaidByYou = $orderedAndPaidBySamePerson && $orderedByYou;

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
                'summary' => $order->summary,
                'orderedByYou' => $orderedByYou,
                'amount' => $order->grand_total,
                'isAssociatedAsFriend' => $isAssociatedAsFriend,
                'isAssociatedAsCustomer' => $isAssociatedAsCustomer,
                'customer' => [
                    'name' => $order->customer_name,
                    'id' => $order->customer_user_id,
                    'lastName' => $order->customer_last_name,
                    'firstName' => $order->customer_first_name,
                ],
            ],
            'transaction' => [
                'id' => $transaction->id,
                'amount' => $transaction->amount,
                'orderedAndPaidByYou' => $orderedAndPaidByYou,
                'orderedAndPaidBySamePerson' => $orderedAndPaidBySamePerson,
                'payerName' => $transaction->dpo_payment_response['onVerifyPaymentResponse']['customerName'],
                'payerPhone' => $transaction->dpo_payment_response['onVerifyPaymentResponse']['customerPhone'],
            ],
        ];
    }
}
