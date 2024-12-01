<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class CouponLineResource extends BaseResource
{
    public function toArray($request)
    {
        return $this->transformedStructure();
    }

    public function setLinks()
    {
        $couponLine = $this->resource;
        $couponId = $this->resource->coupon_id;

        //  Note: The coupon line ID does not exist for shopping carts
        if($couponLine->id) {
            array_push($this->resourceLinks,
                new ResourceLink('show.coupon.line', route('show.coupon.line', ['couponLineId' => $couponLine->id]))
            );
        }

        if($couponId) {
            array_push($this->resourceLinks,
                new ResourceLink('show.coupon', route('show.coupon', ['couponId' => $couponId]))
            );
        }
    }
}
