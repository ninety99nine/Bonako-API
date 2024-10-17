<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class SmsMessageResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $smsMessage = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.sms.message', route('show.sms.message', ['smsMessageId' => $smsMessage->id])),
            new ResourceLink('update.sms.message', route('update.sms.message', ['smsMessageId' => $smsMessage->id])),
            new ResourceLink('delete.sms.message', route('delete.sms.message', ['smsMessageId' => $smsMessage->id])),
        ];
    }
}
