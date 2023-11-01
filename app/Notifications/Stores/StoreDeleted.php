<?php

namespace App\Notifications\Stores;

use App\Models\User;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class StoreDeleted extends Notification
{
    use Queueable, BaseTrait;

    public int $storeId;
    public string $storeName;
    public User $deletedByUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($storeId, $storeName, User $deletedByUser)
    {
        /**
         *  We cannot pass the store Model itself since Laravel would attempt to resolve
         *  the matching Model using Route-Model binding and fail. This is because the
         *  Model would have been deleted by the time this Notification tries to query
         *  the record there-by causing an exception to be thrown e.g
         *
         *  {"message": "This resource does not exist"}
         *
         *  To remedy this, we can pass only the store details that we need e.g
         *  the store ID and store name.
         */
        $this->storeId = $storeId;
        $this->storeName = $storeName;
        $this->deletedByUser = $deletedByUser;
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
        $deletedByUser = $this->deletedByUser;

        return [
            'store' => [
                'id' => $this->storeId,
                'name' => $this->storeName
            ],
            'user' => [
                'id' => $deletedByUser->id,
                'name' => $deletedByUser->name,
                'firstName' => $deletedByUser->first_name,
            ],
        ];
    }
}
