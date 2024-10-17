<?php

namespace App\Casts;

use App\Traits\Base\BaseTrait;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TransactionPaymentStatus implements CastsAttributes
{
    use BaseTrait;

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        $name = $value;

        //  Get the model resource name e.g order
        $descriptionName = $model->getResourceName();

        /**
         *  Note that the $model->isPaid() and other similar methods
         *  below attempt to access $this->payment_status attribute
         *  that is directly set on the Model, however this returns
         *  an error:
         *
         *  "Undefined property: App\Models\Order::$payment_status"
         *
         *  This is possibly because Laravel temporary unsets this
         *  value while implemeting this value casting feature. It
         *  then passes the $key (e.g "payment_status") and the
         *  associated $value (e.g "Paid") as method parameters
         *  as seen above
         *
         *  To determine the status, whether paid, unpaid, e.t.c
         *  we must pass the attribute as a parameter to the
         *  methods below to maintain use of the same
         *  Model methods
         *
         */
        switch ($key) {
            case  $model->isPaid($value):
                $description = 'This '.$descriptionName.' has been fully paid';
                break;
            case $model->isPendingPayment($value):
                $description = 'This '.$descriptionName.' is pending payment';
                break;
            case $model->isFailedPayment($value):
                $description = 'This '.$descriptionName.' payment failed';
                break;
            default:
                $description = null;
                break;
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
