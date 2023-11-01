<?php

namespace App\Http\Requests\Models\ShoppingCart;

use App\Models\Order;
use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ConvertCartRequest extends FormRequest
{
    use BaseTrait;

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

        /**
         *  Convert the "pickup_destination_name" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "Gaborone" into "gaborone"
         */
        if($this->request->has('pickup_destination_name')) {
            $this->merge([
                'pickup_destination_name' => strtolower($this->request->get('pickup_destination_name'))
            ]);
        }

        /**
         *  Convert the "order_for" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "Me And Friends" or "me and Friends" into "me and friends"
         */
        if($this->request->has('order_for')) {
            $this->merge([
                'order_for' => $this->separateWordsThenLowercase($this->request->get('order_for'))
            ]);
        }

        /**
         *  Convert the "collection_type" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "Deliver" or "deliver" into "deliver"
         */
        if($this->request->has('collection_type')) {
            $this->merge([
                'collection_type' => strtolower($this->request->get('collection_type'))
            ]);
        }

        //  Make sure that the "cart_products" is an array if provided
        if($this->request->has('cart_products') && is_string($this->request->get('cart_products'))) {
            $this->merge([
                'cart_products' => json_decode($this->request->get('cart_products'))
            ]);
        }

        //  Make sure that the "cart_coupon_codes" is an array if provided
        if($this->request->has('cart_coupon_codes') && is_string($this->request->get('cart_coupon_codes'))) {
            $this->merge([
                'cart_coupon_codes' => json_decode($this->request->get('cart_coupon_codes'))
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
        $collectionTypes = collect(Store::COLLECTION_TYPES)->map(fn($filter) => strtolower($filter));
        $orderForOptions = collect(Order::ORDER_FOR_OPTIONS)->map(fn($filter) => strtolower($filter));
        $pickupDestinationNames = collect(request()->store->pickup_destinations)->pluck('name')->map(fn($pickupDestinationName) => strtolower($pickupDestinationName));
        $deliveryDestinationNames = collect(request()->store->delivery_destinations)->pluck('name')->map(fn($deliveryDestinationName) => strtolower($deliveryDestinationName));

        return array_merge(

            // Rules specific to the ConvertCartRequest
            [
                'order_for' => ['required', 'string', Rule::in($orderForOptions)],
                'friends_can_collect' => ['bail', 'sometimes', 'required', 'boolean'],

                'friend_group_ids' => ['sometimes', 'array'],
                'friend_group_ids.*' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'distinct'],

                'friend_user_ids' => ['sometimes', 'array'],
                'friend_user_ids.*' => ['bail', 'required', 'integer', 'numeric', 'min:1', 'distinct'],

                'address_id' => ['bail', 'sometimes', 'required', 'integer', 'numeric', 'min:1'],
                'occasion_id' => ['bail', 'sometimes', 'required', 'integer', 'numeric', 'min:1'],

                'collection_type' => ['bail', 'sometimes', 'required', 'string', Rule::in($collectionTypes)],

                'special_note' => ['bail', 'sometimes', 'required', 'string', 'min:'.Order::SPECIAL_NOTE_MIN_CHARACTERS, 'max:'.Order::SPECIAL_NOTE_MAX_CHARACTERS],

                'delivery_destination_name' => ['bail', 'sometimes', 'required', 'string', Rule::in($deliveryDestinationNames)],
                'pickup_destination_name' => ['bail', 'sometimes', 'required', 'string', Rule::in($pickupDestinationNames)],
            ],

            // Merge with rules from the InspectCartRequest
            (new InspectCartRequest())->rules()

        );
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return array_merge(
            [
                'filter.string' => 'Answer "'.collect(Order::ORDER_FOR_OPTIONS)->join('", "', '" or "').' to indicate who the order is for',
                'filter.in' => 'Answer "'.collect(Order::ORDER_FOR_OPTIONS)->join('", "', '" or "').' to indicate who the order is for',
            ],
            (new InspectCartRequest())->messages()
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return (new InspectCartRequest())->attributes();
    }
}
