<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use App\Traits\Base\BaseTrait;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class NotificationResource extends BaseResource
{
    use BaseTrait;

    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        //  Get the user's id
        $userId = $this->chooseUser()->id;

        //  Check if this resource belongs to the authenticated
        $isAuthUser = $userId == request()->auth_user->id;

        //  Auth user route name prefix
        $authUserPrefix = 'auth.user.notification.';

        //  User route name prefix
        $userPrefix = 'user.notification.';

        //  Set the route name prefix
        $prefix = $isAuthUser ? $authUserPrefix : $userPrefix;

        //  Set the route parameters
        $params = ['notification' => $this->resource->id];

        //  If this is not the authenticated user
        if($isAuthUser == false) {

            //  Include the user id as a parameter to correspond to this route '/users/{user}/...'
            $params['user'] = $userId;

        }

        $commonLinks = [
            // These are links that are common to all notification types
            new ResourceLink('self', route($prefix.'show', $params), 'Show notification'),
            new ResourceLink('mark.as.read', route($prefix.'mark.as.read', $params), 'Mark notification as read'),
        ];

        //  Merge the common links with the notification type specific links
        $this->resourceLinks = $this->mergeWithNotificationTypeSpecificRoutes($commonLinks);
    }

    public function mergeWithNotificationTypeSpecificRoutes($commonLinks)
    {
        /**
         *  Get the name of the notification type after the last '\' e.g if we have:
         *
         *  $this->resource->type = "App\Notifications\Orders\OrderMarkedAsPaid";
         *
         *  then expect:
         *
         *  $notificationType = "OrderMarkedAsPaid";
         */
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
        return new ResourceLink('show.store', route('store.show', ['store' => $this->resource->data['store']['id']]), 'Show store');
    }

    public function showOrderLink() {
        return new ResourceLink('show.order', route('order.show', ['store' => $this->resource->data['store']['id'], 'order' => $this->resource->data['order']['id']]), 'Show order');
    }

    public function showTransactionLink() {
        return new ResourceLink('show.transaction', route('transaction.show', ['transaction' => $this->resource->data['transaction']['id']]), 'Show transaction');
    }
}
