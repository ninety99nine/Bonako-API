<?php

namespace App\Notifications\Users;

use App\Models\User;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class RemoveStoreTeamMember extends Notification
{
    use Queueable, BaseTrait;

    public Store $store;
    public User $removedUser;
    public User $removedByUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Store $store, User $removedUser, User $removedByUser)
    {
        $this->store = $store;
        $this->removedUser = $removedUser;
        $this->removedByUser = $removedByUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
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
        $removedUser = $this->removedUser;
        $removedByUser = $this->removedByUser;

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'removedUser' => [
                'id' => $removedUser->id,
                'name' => $removedUser->name,
                'firstName' => $removedUser->first_name
            ],
            'removedByUser' => [
                'id' => $removedByUser->id,
                'name' => $removedByUser->name,
                'firstName' => $removedByUser->first_name
            ],
        ];
    }
}
