<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\Base\BaseTrait;
use App\Repositories\BaseRepository;
use App\Http\Resources\NotificationResource;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository extends BaseRepository
{
    use BaseTrait;

    protected $modelName = 'notification';
    protected $modelClass = DatabaseNotification::class;
    protected $resourceClass = NotificationResource::class;
}
