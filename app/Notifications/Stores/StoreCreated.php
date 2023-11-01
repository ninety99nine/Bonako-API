<?php

namespace App\Notifications\Stores;

use App\Models\User;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Traits\Base\BaseTrait;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class StoreCreated extends Notification
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
        $this->createdByUser = $createdByUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast', 'slack'];
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
    public function toSlack(object $notifiable): SlackMessage
    {
        return (new SlackMessage())->success()->content($this->store->name);
    }
}