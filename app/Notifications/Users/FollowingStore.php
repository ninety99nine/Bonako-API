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

class FollowingStore extends Notification implements ShouldQueue
{
    use Queueable, BaseTrait;

    public Store $store;
    public User $followedByUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Store $store, User $followedByUser)
    {
        $this->store = $store;
        $this->followedByUser = $followedByUser;
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
        $followedByUser = $this->followedByUser;

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'followedByUser' => [
                'id' => $followedByUser->id,
                'name' => $followedByUser->name,
                'firstName' => $followedByUser->first_name
            ],
        ];
    }
}
