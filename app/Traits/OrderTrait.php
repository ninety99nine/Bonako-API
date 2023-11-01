<?php

namespace App\Traits;

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProductLine;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Base\BaseTrait;
use Carbon\Carbon;

trait OrderTrait
{
    use BaseTrait;

    /**
     *  Make a summary out of the cart product lines
     */
    public function generateSummary()
    {
        /**
         *  @var Cart $cart
         */
        $cart = $this->cart;

        //  Make and return the order summary
        $this->summary = collect($cart->productLines)->sortBy('position')->map(function(ProductLine $productLine) {

            if($productLine->quantity >= 2) {

                return $productLine->quantity .'x('. $productLine->name.')';

            }else{

                return $productLine->name;

            }

        })->join(', ', ' and ');

        //  If the customer is paying for delivery
        if( $this->collection_type == 'Delivery' && $cart->allow_free_delivery == false ) {

            $this->summary .= ' plus delivery';

            //  If the delivery destination is provided
            if( !is_null($this->destination_name) ) {

                $this->summary .= ' to ' . ucwords($this->destination_name);

            }

        }

        $this->summary .= ' for ' . $cart->grand_total->amountWithCurrency;

        //  If the customer claimed a discount and free delivery
        if( $cart->coupon_and_sale_discount_total->amount > 0 && $cart->allow_free_delivery ) {

            $this->summary .= ' while saving ' . $cart->coupon_and_sale_discount_total->amountWithCurrency . ' plus free delivery';

        //  If the customer claimed a discount
        }else if( $cart->coupon_and_sale_discount_total->amount > 0 ) {

            $this->summary .= ' while saving ' . $cart->coupon_and_sale_discount_total->amountWithCurrency;

        }

        //  If the customer claimed free delivery
        if( $this->collection_type == 'Delivery' && $cart->allow_free_delivery ) {

            $this->summary .= ' plus free delivery';

            //  If the delivery destination is provided
            if( !is_null($this->destination_name) ) {

                $this->summary .= ' to ' . ucwords($this->destination_name);

            }

        }

        //  If the customer is picking up
        if( $this->collection_type == 'Pickup' && !is_null($this->destination_name)) {

            $this->summary .= ', pickup from ' . ucwords($this->destination_name);

        }

        //  Return the current Model instance
        return $this;
    }

    /**
     *  Check if this order is paid
     *
     *  @return bool
     */
    public function isPaid()
    {
        return strtolower($this->getRawOriginal('payment_status')) === 'paid';
    }

    /**
     *  Check if this order is unpaid
     *
     *  @return bool
     */
    public function isUnpaid()
    {
        return strtolower($this->getRawOriginal('payment_status')) === 'unpaid';
    }

    /**
     *  Check if this order is partially paid
     *
     *  @return bool
     */
    public function isPartiallyPaid()
    {
        return strtolower($this->getRawOriginal('payment_status')) === 'partially paid';
    }

    /**
     *  Check if this order is pending payment
     *
     *  @return bool
     */
    public function isPendingPayment()
    {
        return strtolower($this->getRawOriginal('payment_status')) === 'pending payment';
    }

    /**
     *  Check if this order is waiting response from team members
     *
     *  @return bool
     */
    public function statusRawOriginalLowercase()
    {
        return strtolower($this->getRawOriginal('status'));
    }

    /**
     *  Check if this order is waiting response from team members
     *
     *  @return bool
     */
    public function isWaiting()
    {
        return $this->statusRawOriginalLowercase() === 'waiting';
    }

    /**
     *  Check if this order is waiting for delivery to be confirmed
     *
     *  @return bool
     */
    public function isOnItsWay()
    {
        return $this->statusRawOriginalLowercase() === 'on its way';
    }

    /**
     *  Check if this order is waiting for pickup to be confirmed
     *
     *  @param String $status (optional)
     *  @return bool
     */
    public function isReadyForPickup()
    {
        return $this->statusRawOriginalLowercase() === 'ready for pickup';
    }

    /**
     *  Check if this order is cancelled
     *
     *  @return bool
     */
    public function isCancelled()
    {
        return $this->statusRawOriginalLowercase() === 'cancelled';
    }

    /**
     *  Check if this order is completed
     *
     *  @return bool
     */
    public function isCompleted()
    {
        return $this->statusRawOriginalLowercase() === 'completed';
    }

    /**
     *  Check if this order is collected via delivery
     *
     *  @return bool
     */
    public function isCollectionViaDelivery()
    {
        return strtolower($this->getRawOriginal('collection_type')) === 'delivery';
    }

    /**
     *  Check if this order is collected via pickup
     *
     *  @return bool
     */
    public function isCollectionViaPickup()
    {
        return strtolower($this->getRawOriginal('collection_type')) === 'pickup';
    }

    /**
     *  Check if this order is an order for "me"
     *
     *  @return bool
     */
    public function orderingForMe()
    {
        return strtolower($this->getRawOriginal('order_for')) === 'me';
    }

    /**
     *  Check if this order is an order for "me and friends"
     *
     *  @return bool
     */
    public function orderingForMeAndFriends()
    {
        return strtolower($this->getRawOriginal('order_for')) === 'me and friends';
    }

    /**
     *  Check if this order is an order for "friends only"
     *
     *  @return bool
     */
    public function orderingForFriendsOnly()
    {
        return strtolower($this->getRawOriginal('order_for')) === 'friends only';
    }

    /**
     *  Check if this order is an order for "business"
     *
     *  @return bool
     */
    public function orderingForBusiness()
    {
        return strtolower($this->getRawOriginal('order_for')) === 'business';
    }

    /**
     *  Make this order anonymous by overiding information
     *  that would expose the identity of the customer
     *
     *  @return Order
     */
    public function makeAnonymous() {

        $this->customer_display_name = null;
        $this->customer_first_name = null;
        $this->customer_last_name = null;
        $this->customer_user_id = null;
        $this->customer_name = null;
        $this->number = null;

        return $this;
    }

    /**
     *  Craft the new order sms messsage to send to the seller
     *
     *  @return Order
     */
    public function craftNewOrderSmsMessageForSeller(Store $store, User $customer) {
        if(empty($store->sms_sender_name)) {
            return 'New order: '.$store->name.', '.$this->summary.' from ' . $customer->name.' '.$customer->mobile_number->withoutExtension.'. Order #'.$this->number;
        }else{
            return 'New order: '.$this->summary.' from ' . $customer->name.' '.$customer->mobile_number->withoutExtension.'. Order #'.$this->number;
        }
    }

    /**
     *  Craft the new order sms messsage to send to the customer
     *
     *  @return Order
     */
    public function craftNewOrderSmsMessageForCustomer(Store $store) {
        if(empty($store->sms_sender_name)) {
            return $store->name.', you ordered '.$this->summary.'. Reach us on '.$store->mobile_number->withoutExtension.'. Order #'.$this->number;
        }else{
            return 'You ordered '.$this->summary.'. Reach us on '.$store->mobile_number->withoutExtension.'. Order #'.$this->number;
        }
    }

    /**
     *  Craft the new order sms messsage to send to the friend
     *
     *  @return Order
     */
    public function craftNewOrderSmsMessageForFriend(Store $store, User $customer, $friend, $friends) {

        $message = $customer->name.' ordered '.$this->summary;

        $otherFriends = collect($friends)->where('id', '!=', $friend);

        if($this->orderingForMeAndFriends()) {

            $message .= ' with you';

        }else if($this->orderingForFriendsOnly()) {

            $message .= ' for you';

        }

        if($this->order_for_total_friends == 2) {

            $message .= ' and ' . $otherFriends->first()->first_name;

        }else if($this->order_for_total_friends == 3) {

            $message .= ', ' . $otherFriends->pluck('first_name')->join(', ', ' and ');

        }else if($this->order_for_total_friends > 3) {

            $total = $otherFriends->count() - 1;

            $message .= ' and ' . $total . ' other people';

        }

        if(empty($store->sms_sender_name)) {
            return $message.'. Reach '.$customer->first_name.' on '.$customer->mobile_number->withoutExtension.' or the store on '.$store->mobile_number->withoutExtension.'. Order #'.$this->number;
        }else{
            return $message.'. Reach '.$customer->first_name.' on '.$customer->mobile_number->withoutExtension.' or our store on '.$store->mobile_number->withoutExtension.'. Order #'.$this->number;
        }
    }

    /**
     *  Craft the order status updated sms messsage
     *
     *  @return Order
     */
    public function craftOrderStatusUpdatedMessage(Store $store, User $updatedByUser) {
        if(empty($store->sms_sender_name)) {
            return $store->name.', '.'Order # '.$this->number.' is '.$this->statusRawOriginalLowercase().', updated by '.$updatedByUser->name.' ('.$updatedByUser->mobile_number->withoutExtension.') Items: '.$this->summary;
        }else{
            return 'Order # '.$this->number.' is '.$this->statusRawOriginalLowercase().', updated by '.$updatedByUser->name.' ('.$updatedByUser->mobile_number->withoutExtension.') Items: '.$this->summary;
        }
    }

    /**
     *  Craft the order collected sms messsage
     *
     *  @return Order
     */
    public function craftOrderCollectedSmsMessage(Store $store, User $collectedByUser, User $verifiedByUser) {
        if(empty($store->sms_sender_name)) {
            return $store->name.', '.'Order #'.$this->number.' collected by '.$collectedByUser->name.' ('.$collectedByUser->mobile_number->withoutExtension.'), verified by '.$verifiedByUser->name.' ('.$verifiedByUser->mobile_number->withoutExtension.') Items: '.$this->summary;
        }else{
            return 'Order #'.$this->number.' collected by '.$collectedByUser->name.' ('.$collectedByUser->mobile_number->withoutExtension.'), verified by '.$verifiedByUser->name.' ('.$verifiedByUser->mobile_number->withoutExtension.') Items: '.$this->summary;
        }
    }

    /**
     *  Craft the order payment request sms messsage
     *
     *  @return Order
     */
    public function craftOrderPaymentRequestSmsMessage(Store $store, Transaction $transaction, User $requestingUser, PaymentMethod $paymentMethod) {
        if($paymentMethod->isDpoCard()) {

            if(empty($store->sms_sender_name)) {
                return $store->name.', Pay for Order #'.$this->number.' using this payment link '.$transaction->dpo_payment_url.'. Valid till '.Carbon::parse($transaction->dpo_payment_url_expires_at)->format('d M Y H:m').'. Requested by '.$requestingUser->name.' ('.$requestingUser->mobile_number->withoutExtension.') Items: '.$this->summary;
            }else{
                return 'Pay for Order #'.$this->number.' using this payment link '.$transaction->dpo_payment_url.'. Valid till '.Carbon::parse($transaction->dpo_payment_url_expires_at)->format('d M Y H:m').'. Requested by '.$requestingUser->name.' ('.$requestingUser->mobile_number->withoutExtension.') Items: '.$this->summary;
            }

        }else if($paymentMethod->isOrangeMoney()) {

            if(empty($store->sms_sender_name)) {
                return $store->name.', You are paying for Order #'.$this->number.' using Orange Money. Requested by '.$requestingUser->name.' ('.$requestingUser->mobile_number->withoutExtension.') Items: '.$this->summary;
            }else{
                return 'You are paying for Order #'.$this->number.' using Orange Money. Requested by '.$requestingUser->name.' ('.$requestingUser->mobile_number->withoutExtension.') Items: '.$this->summary;
            }

        }
    }

    /**
     *  Craft the order mark as verified payment sms messsage
     *
     *  @return Order
     */
    public function craftOrderMarkAsVerifiedPaymentSmsMessage(Store $store, Transaction $transaction) {
        if(empty($store->sms_sender_name)) {
            return $store->name.', '.$transaction->amount->amountWithCurrency.' paid successfully for Order #'.$this->number.' by '.$transaction->dpo_payment_response['onVerifyPaymentResponse']['customerName'].' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:m');
        }else{
            return $transaction->amount->amountWithCurrency.' paid successfully for Order #'.$this->number.' by '.$transaction->dpo_payment_response['onVerifyPaymentResponse']['customerName'].' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:m');
        }
    }

    /**
     *  Craft the order mark as unverified payment sms messsage
     *
     *  @return Order
     */
    public function craftOrderMarkAsUnVerifiedPaymentSmsMessage(Store $store, Transaction $transaction, User $verifiedByUser) {
        if(empty($store->sms_sender_name)) {
            return $store->name.', '.$transaction->amount->amountWithCurrency.' marked as paid using '.$transaction->paymentMethod->name.' for Order #'.$this->number.' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:m').'. Payment verified by '.$verifiedByUser->name.' ('.$verifiedByUser->mobile_number->withoutExtension.')';
        }else{
            return $transaction->amount->amountWithCurrency.' marked as paid using '.$transaction->paymentMethod->name.' for Order #'.$this->number.' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:m').'. Payment verified by '.$verifiedByUser->name.' ('.$verifiedByUser->mobile_number->withoutExtension.')';
        }
    }
}
