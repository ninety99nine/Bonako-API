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

class InvitationToJoinFriendGroupDeclined extends Notification
{
    use Queueable, BaseTrait;

    public User $declinedByUser;
    public FriendGroup $friendGroup;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FriendGroup $friendGroup, User $declinedByUser)
    {
        $this->friendGroup = $friendGroup;
        $this->declinedByUser = $declinedByUser;
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
        $declinedByUser = $this->declinedByUser;

        return [
            'friendGroup' => [
                'id' => $friendGroup->id,
                'name' => $friendGroup->name
            ],
            'declinedByUser' => [
                'id' => $declinedByUser->id,
                'name' => $declinedByUser->name,
                'firstName' => $declinedByUser->first_name
            ],
        ];
    }
}
