<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Repositories\NotificationRepository;
use App\Http\Controllers\Base\BaseController;
use App\Models\User;

class NotificationController extends BaseController
{
    protected NotificationRepository $repository;

    /**
     * NotificationController constructor.
     *
     * @param NotificationRepository $repository
     */
    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show notifications.
     *
     * @return JsonResponse
     */
    public function showNotifications(): JsonResponse
    {
        return $this->prepareOutput($this->repository->showNotifications());
    }

    /**
     * Mark notifications as read.
     *
     * @return JsonResponse
     */
    public function markNotificationsAsRead(): JsonResponse
    {
        return $this->prepareOutput($this->repository->markNotificationsAsRead());
    }

    /**
     * Show notification.
     *
     * @param string $notificationId
     * @return JsonResponse
     */
    public function showNotification(string $notificationId): JsonResponse
    {
        return $this->prepareOutput($this->repository->showNotification($notificationId));
    }

    /**
     * Mark notification as read.
     *
     * @param string $notificationId
     * @return JsonResponse
     */
    public function markNotificationAsRead(string $notificationId): JsonResponse
    {
        return $this->prepareOutput($this->repository->markNotificationAsRead($notificationId));
    }
}
