<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Repositories\UserRepository;
use App\Repositories\ShortcodeRepository;
use App\Repositories\TransactionRepository;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\SmsAlertActivityAssociationRepository;

class SmsAlertResource extends BaseResource
{
    protected $resourceRelationships = [
        'user' => UserRepository::class,
        'transactions' => TransactionRepository::class,
        'latestTransaction' => TransactionRepository::class,
        'authPaymentShortcode' => ShortcodeRepository::class,
        'smsAlertActivityAssociations' => SmsAlertActivityAssociationRepository::class,
    ];

    public function toArray($request)
    {
        return $this->transformedStructure();
    }
}
