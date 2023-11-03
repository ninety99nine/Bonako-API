<?php

namespace App\Http\Requests\Models\ShoppingCart;

use App\Models\Coupon;
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
        /**
         *  Convert the "delivery_destination_name" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "Gaborone" into "gaborone"
         */
        if($this->request->has('delivery_destination_name')) {
            $this->merge([
                'delivery_destination_name' => strtolower($this->request->get('delivery_destination_name'))
            ]);
        }

        //  Make sure that the "cart_products" is an array if provided
        if($this->request->has('cart_products') && is_string($this->request->all()['cart_products'])) {
            $this->merge([
                'cart_products' => json_decode($this->request->all()['cart_products'])
            ]);
        }

        //  Make sure that the "cart_coupon_codes" is an array if provided
        if($this->request->has('cart_coupon_codes') && is_string($this->request->all()['cart_coupon_codes'])) {
            $this->merge([
                'cart_coupon_codes' => json_decode($this->request->all()['cart_coupon_codes'])
            ]);
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
        $deliveryDestinationNames = collect(request()->store->delivery_destinations)->pluck('name')->map(fn($deliveryDestinationName) => strtolower($deliveryDestinationName));

        return [

            /*  Cart Session Id  */
            'session_id' => ['bail', 'sometimes', 'required', 'string', 'min:1'],

            /*  Cart Customer Id  */
            'customer_user_id' => ['bail', 'sometimes', 'required', 'integer', 'numeric', 'min:1', 'exists:users,id'],

            /*  Cart Products  */
            'cart_products' => ['sometimes', 'array'],
            'cart_products.*.id' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'distinct'],
            'cart_products.*.quantity' => ['bail', 'sometimes', 'required', 'integer', 'numeric', 'min:1'],

            /*  Cart Coupon Codes  */
            'cart_coupon_codes' => ['sometimes', 'array'],
            'cart_coupon_codes.*' => ['string', 'min:'.Coupon::CODE_MIN_CHARACTERS, 'max:'.Coupon::CODE_MAX_CHARACTERS, 'distinct'],

            /*  Delivery Destination Name  */
            'delivery_destination_name' => ['bail', 'sometimes', 'required', 'string', Rule::in($deliveryDestinationNames)],

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
            'cart_products.*.quantity' => 'cart product quantity',
            'customer_user_id' => 'The customer matching the given id does not exist',
        ];
    }
}
