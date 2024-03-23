<?php

namespace App\Notifications\FriendGroups;

use App\Models\User;
use App\Models\FriendGroup;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class FriendGroupCreated extends Notification implements ShouldQueue
{
    use Queueable, BaseTrait;

    public User $createdByUser;
    public FriendGroup $friendGroup;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FriendGroup $friendGroup, User $createdByUser)
    {
        $this->friendGroup = $friendGroup;
        $this->createdByUser = $createdByUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', OneSignalChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $friendGroup = $this->friendGroup;
        $createdByUser = $this->createdByUser;

        return [
            'store' => [
                'id' => $friendGroup->id,
                'name' => $friendGroup->name
            ],
            'user' => [
                'id' => $createdByUser->id,
                'name' => $createdByUser->name,
                'firstName' => $createdByUser->first_name,
                'mobileNumber' => $createdByUser->mobile_number
            ],
        ];
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $friendGroup = $this->friendGroup;
        $subject = $friendGroup->name_with_emoji;
        $body = 'Your group was created successfully';

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
