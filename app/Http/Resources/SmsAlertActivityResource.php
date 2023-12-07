<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;
use App\Repositories\SmsAlertActivityAssociationRepository;

class SmsAlertActivityResource extends BaseResource
{
    protected $resourceRelationships = [
        'smsAlertActivityAssociations' => SmsAlertActivityAssociationRepository::class,
    ];
}
