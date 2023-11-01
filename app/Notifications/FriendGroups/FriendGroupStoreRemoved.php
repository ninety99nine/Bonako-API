<?php

namespace App\Notifications\FriendGroups;

use App\Models\FriendGroup;
use App\Models\User;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FriendGroupStoreRemoved extends Notification
{
    use Queueable, BaseTrait;

    public Store $store;
    public User $removedByUser;
    public FriendGroup $friendGroup;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FriendGroup $friendGroup, Store $store, User $removedByUser)
    {
        $this->store = $store;
        $this->friendGroup = $friendGroup;
        $this->removedByUser = $removedByUser;
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
        $store = $this->store;
        $friendGroup = $this->friendGroup;
        $removedByUser = $this->removedByUser;

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'friendGroup' => [
                'id' => $friendGroup->id,
                'name' => $friendGroup->name
            ],
            'removedByUser' => [
                'id' => $removedByUser->id,
                'name' => $removedByUser->name,
                'firstName' => $removedByUser->first_name
            ],
        ];
    }
}
