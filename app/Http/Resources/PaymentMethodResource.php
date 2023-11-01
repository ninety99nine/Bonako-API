<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;

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
}
