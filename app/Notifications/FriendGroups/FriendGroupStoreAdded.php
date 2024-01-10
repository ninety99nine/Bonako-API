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

class FriendGroupStoreAdded extends Notification
{
    use Queueable, BaseTrait;

    public Store $store;
    public User $addedByUser;
    public FriendGroup $friendGroup;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FriendGroup $friendGroup, Store $store, User $addedByUser)
    {
        $this->store = $store;
        $this->friendGroup = $friendGroup;
        $this->addedByUser = $addedByUser;
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
        $addedByUser = $this->addedByUser;

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'friendGroup' => [
                'id' => $friendGroup->id,
                'name' => $friendGroup->name
            ],
            'addedByUser' => [
                'id' => $addedByUser->id,
                'name' => $addedByUser->name,
                'firstName' => $addedByUser->first_name
            ],
        ];
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $store = $this->store;
        $friendGroup = $this->friendGroup;
        $addedByUser = $this->addedByUser;
        $subject = 'Store added to group';

        $body = $addedByUser->first_name.' added '.$store->name_with_emoji.' to '.$friendGroup->name_with_emoji;

        /**
         *  Image from: https://giphy.com
         */
        return OneSignalMessage::create()
            ->setAndroidBigPicture('https://media.giphy.com/media/jErnybNlfE1lm/giphy.gif')
            ->setSubject($subject)
            ->setBody($body);
    }
}
