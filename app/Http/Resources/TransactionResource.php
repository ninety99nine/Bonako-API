<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\ShortcodeRepository;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\UserRepository;

class TransactionResource extends BaseResource
{
    protected $resourceRelationships = [
        'activePaymentShortcode' => ShortcodeRepository::class,
        'requestingUser' => UserRepository::class,
        'payingUser' => UserRepository::class,
    ];
}
