<?php

namespace App\Notifications\FriendGroups;

use App\Models\User;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FriendGroupDeleted extends Notification
{
    use Queueable, BaseTrait;

    public int $friendGroupId;
    public User $deletedByUser;
    public string $friendGroupNameWithEmoji;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($friendGroupId, $friendGroupNameWithEmoji, User $deletedByUser)
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
        $this->deletedByUser = $deletedByUser;
        $this->friendGroupId = $friendGroupId;
        $this->friendGroupNameWithEmoji = $friendGroupNameWithEmoji;
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
        $deletedByUser = $this->deletedByUser;

        return [
            'friendGroup' => [
                'id' => $this->friendGroupId,
                'name' => $this->friendGroupNameWithEmoji
            ],
            'user' => [
                'id' => $deletedByUser->id,
                'name' => $deletedByUser->name,
                'firstName' => $deletedByUser->first_name,
            ],
        ];
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $deletedByUser = $this->deletedByUser;
        $subject = $this->friendGroupNameWithEmoji.' Deleted';
        $body = 'This group has been permanently deleted by '.$deletedByUser->name;

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
