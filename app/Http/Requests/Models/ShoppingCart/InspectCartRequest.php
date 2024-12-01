<?php

namespace App\Http\Requests\Models\ShoppingCart;

use App\Models\Coupon;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class InspectCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     *  We want to modify the request input before validating
     *
     *  Reference: https://laracasts.com/discuss/channels/requests/modify-request-input-value-before-validation
     */
    public function getValidatorInstance()
    {
        try {

            /**
             *  Convert the "delivery_destination_name" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "Gaborone" into "gaborone"
             */
            if($this->has('delivery_destination_name')) {
                $this->merge([
                    'delivery_destination_name' => strtolower($this->get('delivery_destination_name'))
                ]);
            }

            //  Make sure that the "cart_products" is an array if provided
            if($this->has('cart_products') && is_string($this->request->all()['cart_products'])) {
                $this->merge([
                    'cart_products' => json_decode($this->request->all()['cart_products'])
                ]);
            }

            //  Make sure that the "cart_coupon_codes" is an array if provided
            if($this->has('cart_coupon_codes') && is_string($this->request->all()['cart_coupon_codes'])) {
                $this->merge([
                    'cart_coupon_codes' => json_decode($this->request->all()['cart_coupon_codes'])
                ]);
            }

        } catch (\Throwable $th) {

        }

        return parent::getValidatorInstance();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //  $deliveryDestinationNames = collect(request()->store->delivery_destinations)->pluck('name')->map(fn($deliveryDestinationName) => strtolower($deliveryDestinationName));

        return [

            /*  Cart Session Id  */
            'session_id' => ['bail', 'sometimes', 'string', 'min:1'],

            /*  Cart Products  */
            'cart_products' => ['sometimes', 'array'],
            'cart_products.*.id' => ['bail', 'required', 'uuid', 'min:1', 'distinct'],
            'cart_products.*.quantity' => ['bail', 'sometimes', 'integer', 'min:1'],

            /*  Cart Coupon Codes  */
            'cart_coupon_codes' => ['sometimes', 'array'],
            'cart_coupon_codes.*' => ['string', 'min:'.Coupon::CODE_MIN_CHARACTERS, 'max:'.Coupon::CODE_MAX_CHARACTERS, 'distinct'],

            /*  Delivery Destination Name  */
            'delivery_destination_name' => ['bail', 'sometimes', 'string' /*, Rule::in($deliveryDestinationNames)*/],

            /*  Customer */
            'customer' => ['sometimes', 'array'],
            'customer.*.email' => ['bail', 'sometimes', 'email'],
            'customer.*.mobile_number' => ['bail', 'sometimes', 'phone'],
            'customer.*.first_name' => ['bail', 'sometimes', 'string', 'min:'.Customer::FIRST_NAME_MIN_CHARACTERS, 'max:'.Customer::FIRST_NAME_MAX_CHARACTERS],
            'customer.*.last_name' => ['bail', 'sometimes', 'string', 'min:'.Customer::LAST_NAME_MIN_CHARACTERS, 'max:'.Customer::LAST_NAME_MAX_CHARACTERS],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'cart_products.*.id' => 'cart product id',
            'cart_products.*.quantity' => 'cart product quantity'
        ];
    }
}
