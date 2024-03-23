<?php

namespace App\Notifications\FriendGroups;

use App\Models\User;
use App\Models\Store;
use App\Models\FriendGroup;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FriendGroupStoreRemoved extends Notification implements ShouldQueue
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

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $store = $this->store;
        $friendGroup = $this->friendGroup;
        $removedByUser = $this->removedByUser;
        $subject = 'Store removed from group';

        $body = $removedByUser->first_name.' removed '.$store->name_with_emoji.' from '.$friendGroup->name_with_emoji;

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
