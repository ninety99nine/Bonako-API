<?php

namespace App\Notifications\Users;

use App\Models\User;
use App\Models\FriendGroup;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class InvitationToJoinFriendGroupAccepted extends Notification implements ShouldQueue
{
    use Queueable, BaseTrait;

    public User $acceptedByUser;
    public FriendGroup $friendGroup;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FriendGroup $friendGroup, User $acceptedByUser)
    {
        $this->friendGroup = $friendGroup;
        $this->acceptedByUser = $acceptedByUser;
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
        $friendGroup = $this->friendGroup;
        $acceptedByUser = $this->acceptedByUser;

        return [
            'friendGroup' => [
                'id' => $friendGroup->id,
                'name' => $friendGroup->name
            ],
            'acceptedByUser' => [
                'id' => $acceptedByUser->id,
                'name' => $acceptedByUser->name,
                'firstName' => $acceptedByUser->first_name
            ],
        ];
    }
}
