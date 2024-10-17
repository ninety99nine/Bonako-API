<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class NotificationResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $notification = $this->resource;

        $commonLinks = [
            new ResourceLink('show.notification', route('show.notification', ['notificationId' => $notification->id])),
            new ResourceLink('mark.notification.as.read', route('mark.notification.as.read', ['notificationId' => $notification->id])),
        ];

        $this->resourceLinks = $this->mergeWithNotificationTypeSpecificRoutes($commonLinks);
    }

    public function mergeWithNotificationTypeSpecificRoutes($commonLinks)
    {
        $notificationType = Str::afterLast($this->resource->type, '\\');

        if(collect(['OrderCreated', 'OrderUpdated', 'OrderStatusUpdated', 'OrderSeen'])->contains($notificationType)) {

            $notificationTypeLinks = [
                $this->showOrderLink(),
                $this->showStoreLink(),
            ];

        }elseif(collect(['OrderPaymentRequest', 'OrderPaidUsingDpo', 'OrderMarkedAsPaid'])->contains($notificationType)) {

            $notificationTypeLinks = [
                $this->showOrderLink(),
                $this->showStoreLink(),
                $this->showTransactionLink(),
            ];

        }else{

            $notificationTypeLinks = [];

        }

        return array_merge(
            $commonLinks,
            $notificationTypeLinks
        );
    }

    public function showStoreLink() {
        return new ResourceLink('show.store', route('store.show', ['storeId' => $this->resource->data['store']['id']]), 'Show store');
    }

    public function showOrderLink() {
        return new ResourceLink('show.order', route('order.show', ['storeId' => $this->resource->data['store']['id'], 'order' => $this->resource->data['order']['id']]), 'Show order');
    }

    public function showTransactionLink() {
        return new ResourceLink('show.transaction', route('transaction.show', ['transactionId' => $this->resource->data['transaction']['id']]), 'Show transaction');
    }
}
