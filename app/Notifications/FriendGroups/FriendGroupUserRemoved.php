<?php

namespace App\Notifications\FriendGroups;

use App\Models\User;
use App\Models\FriendGroup;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FriendGroupUserRemoved extends Notification
{
    use Queueable, BaseTrait;

    public User $removedUser;
    public User $removedByUser;
    public FriendGroup $friendGroup;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FriendGroup $friendGroup, User $removedUser, User $removedByUser)
    {
        $this->friendGroup = $friendGroup;
        $this->removedUser = $removedUser;
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
        $friendGroup = $this->friendGroup;
        $removedUser = $this->removedUser;
        $removedByUser = $this->removedByUser;

        return [
            'friendGroup' => [
                'id' => $friendGroup->id,
                'name' => $friendGroup->name
            ],
            'removedByUser' => [
                'id' => $removedByUser->id,
                'name' => $removedByUser->name,
                'firstName' => $removedByUser->first_name
            ],
            'removedUser' => [
                'id' => $removedUser->id,
                'name' => $removedUser->name,
                'firstName' => $removedUser->first_name
            ],
        ];
    }
}
