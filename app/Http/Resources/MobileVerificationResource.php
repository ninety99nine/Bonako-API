<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class MobileVerificationResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $mobileVerification = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.mobile.verification', route('show.mobile.verification', ['mobileVerificationId' => $mobileVerification->id])),
            new ResourceLink('update.mobile.verification', route('update.mobile.verification', ['mobileVerificationId' => $mobileVerification->id])),
            new ResourceLink('delete.mobile.verification', route('delete.mobile.verification', ['mobileVerificationId' => $mobileVerification->id])),
        ];
    }
}
