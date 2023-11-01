<?php

namespace App\Casts;

use App\Traits\Base\BaseTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class OrderCollectionType implements CastsAttributes
{
    use BaseTrait;

    /**
     * Cast the given value.
     *
     * @param  \App\Models\Order $order
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($order, $key, $value, $attributes)
    {
        if($value == null) {

            return $value;

        }else{

            //  Get the value e.g Delivery / Pickup
            $name = $value;

            //  Check if the delivery address has been provided
            $hasDeliveryAddress = $attributes['delivery_address_id'];

            if ($key == $order->isCollectionViaDelivery()) {
                $description = $hasDeliveryAddress
                    ? 'Seller will deliver to specified address'
                    : 'Seller will deliver this order';
            } elseif ($key == $order->isCollectionViaPickup()) {
                $description = 'Customer will pickup this order';
            } else {
                $description = null;
            }

            return [
                'name' => $name,
                'description' => $description,
            ];

        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if( is_array($value) && isset($value['name']) ){

            return $value['name'];

        }else{

            return $value;

        }
    }
}
