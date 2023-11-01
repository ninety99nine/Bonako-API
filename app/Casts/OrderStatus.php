<?php

namespace App\Casts;

use App\Traits\Base\BaseTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class OrderStatus implements CastsAttributes
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
        //  Get the value e.g Waiting
        $name = $value;

        //  Get the resource name e.g order
        $resourceName = $order->getResourceName();

        if ($key == $order->isWaiting()) {
            $description = 'This '.$resourceName.' is waiting a response from the team';
        } elseif ($key == $order->isOnItsWay()) {
            $description = 'This '.$resourceName.' is waiting confirmation for delivery';
        } elseif ($key == $order->isReadyForPickup()) {
            $description = 'This '.$resourceName.' is waiting confirmation for pickup';
        } elseif ($key == $order->isCancelled()) {
            $description = 'This '.$resourceName.' has been cancelled';
        } elseif ($key == $order->isCompleted()) {
            $description = 'This '.$resourceName.' has been completed';
        } else {
            $description = null;
        }

        return [
            'name' => $name,
            'description' => $description,
        ];
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
