<?php

namespace App\Notifications\Stores;

use App\Models\User;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class StoreCreated extends Notification implements ShouldQueue
{
    use Queueable, BaseTrait;

    public Store $store;
    public User $createdByUser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Store $store, User $createdByUser)
    {
        $this->store = $store;

        /**
         *  When the createdByUser is passed as a parameter, it may contain
         *  loaded relationships such as smsAlert. To prevent this loaded
         *  relationship from being queried when this job runs, we can
         *  do so using withoutRelations();
         *
         *  Reference: https://laravel.com/docs/10.x/queues#handling-relationships
         */
        $this->createdByUser = $createdByUser->withoutRelations();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'slack', OneSignalChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $store = $this->store;
        $createdByUser = $this->createdByUser;

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name
            ],
            'user' => [
                'id' => $createdByUser->id,
                'name' => $createdByUser->name,
                'firstName' => $createdByUser->first_name,
                'mobileNumber' => $createdByUser->mobile_number
            ],
        ];
    }

    /**
     * Get the Slack representation of the notification.
     */
    public function toSlack(User $notifiable): SlackMessage
    {
        return (new SlackMessage())->success()->content($this->store->name);
    }

    public function toOneSignal(object $notifiable): OneSignalMessage
    {
        $store = $this->store;
        $subject = $store->name_with_emoji;
        $body = 'Your store was created successfully';

        return OneSignalMessage::create()
            ->setSubject($subject)
            ->setBody($body);
    }
}
