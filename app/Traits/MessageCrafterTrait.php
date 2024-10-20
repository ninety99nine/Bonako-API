<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Store;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Traits\Base\BaseTrait;

trait MessageCrafterTrait
{
    use BaseTrait;

    /**
     *  Craft the new order sms messsage to send to the seller
     *
     *  @return string
     */
    public function craftNewOrderForSellerMessage(Store $store) {
        if(empty($store->sms_sender_name)) {
            return 'New order: '.$store->name_with_emoji.', '.
                   $this->summary.' from ' . $this->customer_name.
                   ($this->customer_mobile_number == null ? '' : ' '.$this->customer_mobile_number->formatNational()).
                   '. Order #'.$this->number;
        }else{
            return 'New order: '.$this->summary.
                   ' from ' . $this->customer_name.
                   ($this->customer_mobile_number == null ? '' : ' '.$this->customer_mobile_number->formatNational()).
                   '. Order #'.$this->number;
        }
    }

    /**
     *  Craft the new order sms messsage to send to the customer
     *
     *  @return string
     */
    public function craftNewOrderForCustomerMessage(Store $store) {
        if(empty($store->sms_sender_name)) {
            return $store->name_with_emoji.', you ordered '.$this->summary.'. Reach us on '.$store->mobile_number?->formatNational().'. Order #'.$this->number;
        }else{
            return 'You ordered '.$this->summary.'. Reach us on '.$store->mobile_number?->formatNational().'. Order #'.$this->number;
        }
    }

    /**
     *  Craft the order collection code messsage
     *
     *  @return string
     */
    public function craftOrderCollectionCodeMessage(Store $store) {
        if(empty($store->sms_sender_name)) {
            return $store->name_with_emoji.', your collection code for Order #'.$this->number.' is ' .$this->collection_code;
        }else{
            return 'Your collection code for Order #'.$this->number.' is ' .$this->collection_code;
        }
    }

    /**
     *  Craft the order updated sms messsage
     *
     *  @return string
     */
    public function craftOrderUpdatedMessage(Store $store, User $updatedByUser) {
        if(empty($store->sms_sender_name)) {
            return $store->name_with_emoji.', '.'Order #'.$this->number.' updated by '.$updatedByUser->name.' ('.$updatedByUser->mobile_number->formatNational().') Items: '.$this->summary;
        }else{
            return 'Order #'.$this->number.' updated by '.$updatedByUser->name.' ('.$updatedByUser->mobile_number->formatNational().') Items: '.$this->summary;
        }
    }

    /**
     *  Craft the order status updated sms messsage
     *
     *  @return string
     */
    public function craftOrderStatusUpdatedMessage(Store $store, User $updatedByUser) {
        if(empty($store->sms_sender_name)) {
            return $store->name_with_emoji.', '.'Order #'.$this->number.' is '.$this->statusRawOriginalLowercase().', updated by '.$updatedByUser->name.' ('.$updatedByUser->mobile_number->formatNational().') Items: '.$this->summary;
        }else{
            return 'Order #'.$this->number.' is '.$this->statusRawOriginalLowercase().', updated by '.$updatedByUser->name.' ('.$updatedByUser->mobile_number->formatNational().') Items: '.$this->summary;
        }
    }

    /**
     *  Craft the order seen sms messsage
     *
     *  @return string
     */
    public function craftOrderSeenMessage(Store $store, User $seenByUser) {
        if(empty($store->sms_sender_name)) {
            return $store->name_with_emoji.', '.'Order #'.$this->number.' has been seen by '.$seenByUser->name.' ('.$seenByUser->mobile_number->formatNational().') Items: '.$this->summary;
        }else{
            return 'Order #'.$this->number.' has been seen by '.$seenByUser->name.' ('.$seenByUser->mobile_number->formatNational().') Items: '.$this->summary;
        }
    }

    /**
     *  Craft the order collected sms messsage
     *
     *  @return string
     */
    public function craftOrderCollectedMessage(Store $store, User $manuallyVerifiedByUser) {
        if(empty($store->sms_sender_name)) {
            return $store->name_with_emoji.', '.'Order #'.$this->number.' completed and collected. Verified by '.$manuallyVerifiedByUser->name.' ('.$manuallyVerifiedByUser->mobile_number->formatNational().') Items: '.$this->summary;
        }else{
            return 'Order #'.$this->number.' completed and collected. Verified by '.$manuallyVerifiedByUser->name.' ('.$manuallyVerifiedByUser->mobile_number->formatNational().') Items: '.$this->summary;
        }
    }

    /**
     *  Craft the order payment request sms messsage
     *
     *  @return string
     */
    public function craftOrderPaymentRequestMessage(Store $store, Transaction $transaction) {

        $requestedByUser = $transaction->requestedByUser;
        $paymentMethod = $transaction->paymentMethod;

        if($paymentMethod->isDpo()) {

            if(empty($store->sms_sender_name)) {
                return $store->name_with_emoji.', Pay for Order #'.$this->number.' using this payment link '.$transaction->metadata['dpo_payment_url'].'. Valid till '.Carbon::parse($transaction->metadata['dpo_payment_url_expires_at'])->format('d M Y H:i').'. Requested by '.$requestedByUser->name.' ('.$requestedByUser->mobile_number->formatNational().') Items: '.$this->summary;
            }else{
                return 'Pay for Order #'.$this->number.' using this payment link '.$transaction->metadata['dpo_payment_url'].'. Valid till '.Carbon::parse($transaction->metadata['dpo_payment_url_expires_at'])->format('d M Y H:i').'. Requested by '.$requestedByUser->name.' ('.$requestedByUser->mobile_number->formatNational().') Items: '.$this->summary;
            }

        }else if($paymentMethod->isOrangeMoney()) {

            if(empty($store->sms_sender_name)) {
                return $store->name_with_emoji.', You are paying for Order #'.$this->number.' using Orange Money. Requested by '.$requestedByUser->name.' ('.$requestedByUser->mobile_number->formatNational().') Items: '.$this->summary;
            }else{
                return 'You are paying for Order #'.$this->number.' using Orange Money. Requested by '.$requestedByUser->name.' ('.$requestedByUser->mobile_number->formatNational().') Items: '.$this->summary;
            }

        }
    }

    /**
     *  Craft the order paid sms messsage
     *
     *  @return string
     */
    public function craftOrderPaidMessage(Store $store, Transaction $transaction) {
        if($transaction->paymentMethod->isDpo()) {
            if(empty($store->sms_sender_name)) {
                return $store->name_with_emoji.', '.$transaction->amount->amountWithCurrency.' paid successfully for Order #'.$this->number.' by '.$transaction->metadata['dpo_payment_response']['onVerifyPaymentResponse']['customerName'].' using '.$transaction->paymentMethod->name.' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:i');
            }else{
                return $transaction->amount->amountWithCurrency.' paid successfully for Order #'.$this->number.' by '.$transaction->metadata['dpo_payment_response']['onVerifyPaymentResponse']['customerName'].' using '.$transaction->paymentMethod->name.' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:i');
            }
        }else if($transaction->paymentMethod->isOrangeMoney()) {
            if(empty($store->sms_sender_name)) {
                return $store->name_with_emoji.', '.$transaction->amount->amountWithCurrency.' paid successfully for Order #'.$this->number.' by '.$transaction->customer->name.' using '.$transaction->paymentMethod->name.' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:i');
            }else{
                return $transaction->amount->amountWithCurrency.' paid successfully for Order #'.$this->number.' by '.$transaction->customer->name.' using '.$transaction->paymentMethod->name.' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:i');
            }
        }
    }

    /**
     *  Craft the order marked as paid sms messsage
     *
     *  @return string
     */
    public function craftOrderMarkedAsPaidMessage(Store $store, Transaction $transaction, User $manuallyVerifiedByUser) {
        if(empty($store->sms_sender_name)) {
            return $store->name_with_emoji.', '.$transaction->amount->amountWithCurrency.' marked as paid using '.$transaction->paymentMethod->name.' for Order #'.$this->number.' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:i').'. Payment verified by '.$manuallyVerifiedByUser->name.' ('.$manuallyVerifiedByUser->mobile_number->formatNational().')';
        }else{
            return $transaction->amount->amountWithCurrency.' marked as paid using '.$transaction->paymentMethod->name.' for Order #'.$this->number.' on '.Carbon::parse($transaction->updated_at)->format('d M Y H:i').'. Payment verified by '.$manuallyVerifiedByUser->name.' ('.$manuallyVerifiedByUser->mobile_number->formatNational().')';
        }
    }

    /**
     *  Craft the store subscription paid messsage
     *
     *  @return string
     */
    public function craftStoreSubscriptionPaidMessage(Store $store, Transaction $transaction, Subscription $subscription) {
            return $transaction->amount->amountWithCurrency.' paid for '.$store->name_with_emoji.'. Valid till '.Carbon::parse($subscription->end_date)->format('d M Y H:i');
    }

    /**
     *  Craft the AI Assistant subscription paid messsage
     *
     *  @return string
     */
    public function craftAIAssistantSubscriptionPaidMessage(Transaction $transaction, Subscription $subscription) {
        return $transaction->amount->amountWithCurrency.' paid for AI Assistant. Valid till '.Carbon::parse($subscription->end_date)->format('d M Y H:i');
    }
}
