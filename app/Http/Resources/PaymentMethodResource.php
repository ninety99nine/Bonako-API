<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class PaymentMethodResource extends BaseResource
{
    public function toArray($request)
    {
        /**
         *  If the store-payment-method relationship exists we can include
         *  it with our payload as an attribute
         */
        if( !empty($this->resource->store_payment_method_association) ) {

            //  Include the store and payment method association payload
            $this->customIncludeAttributes = array_merge(
                ($this->customIncludeAttributes ?? []), ['store_payment_method_association']
            );

        }

        return $this->transformedStructure();

    }

    public function setLinks()
    {
        $paymentMethod = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.payment.method', route('show.payment.method', ['paymentMethodId' => $paymentMethod->id])),
            new ResourceLink('update.payment.method', route('update.payment.method', ['paymentMethodId' => $paymentMethod->id])),
            new ResourceLink('delete.payment.method', route('delete.payment.method', ['paymentMethodId' => $paymentMethod->id])),
        ];
    }
}
