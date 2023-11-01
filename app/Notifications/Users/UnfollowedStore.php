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

class UnfollowedStore extends Notification
{
    use Queueable, BaseTrait;

    public Store $store;
    public User $unfollowedByUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Store $store, User $unfollowedByUser)
    {
        $this->store = $store;
        $this->unfollowedByUser = $unfollowedByUser;
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
        $unfollowedByUser = $this->unfollowedByUser;

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'unfollowedByUser' => [
                'id' => $unfollowedByUser->id,
                'name' => $unfollowedByUser->name,
                'firstName' => $unfollowedByUser->first_name
            ],
        ];
    }
}
