<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource;
use App\Http\Resources\Helpers\ResourceLink;

class CustomerResource extends BaseResource
{
    public function setLinks()
    {
        $customer = $this->resource;

        $this->resourceLinks = [
            new ResourceLink('show.customer', route('show.customer', ['customerId' => $customer->id])),
            new ResourceLink('update.customer', route('update.customer', ['customerId' => $customer->id])),
            new ResourceLink('delete.customer', route('delete.customer', ['customerId' => $customer->id])),
            new ResourceLink('show.customer.orders', route('show.customer.orders', ['customerId' => $customer->id])),
            new ResourceLink('show.customer.transactions', route('show.customer.transactions', ['customerId' => $customer->id])),
        ];
    }
}
