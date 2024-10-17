<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
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
        try {

            /**
             *  Convert the "call_to_action" to the correct format if it has been set on the request inputs
             *
             *  Example: convert "buy" or "buyNow" into "buy now"
             */
            if($this->has('call_to_action')) {
                $this->merge([
                    'call_to_action' => $this->separateWordsThenLowercase($this->get('call_to_action'))
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
        $storeId = request()->storeId;
        $callToActionOptions = collect(Store::CALL_TO_ACTION_OPTIONS())->map(fn($filter) => strtolower($filter));

        /*
         *  Note that image upload is not possible on a PUT/PATCH request, which is why
         *  logo uploading checks are not implemented on this method except for when
         *  were are creating a store on the POST request.
         *
         *  Reference: https://stackoverflow.com/questions/65008650/how-to-use-put-method-in-laravel-api-with-file-upload
         */
        return [
            'emoji' => ['bail', 'sometimes', 'nullable', 'string'],
            'name' => ['bail', 'sometimes', 'required', 'string', 'min:'.Store::NAME_MIN_CHARACTERS, 'max:'.Store::NAME_MAX_CHARACTERS],
            'alias' => [
                'bail', 'sometimes', 'required', 'string', 'min:'.Store::ALIAS_MIN_CHARACTERS, 'max:'.Store::ALIAS_MAX_CHARACTERS,
                Rule::unique('stores')->ignore($storeId)
            ],
            'call_to_action' => ['bail', 'sometimes', 'required', Rule::in(collect($callToActionOptions))],
            'description' => ['bail', 'sometimes', 'nullable', 'min:'.Store::DESCRIPTION_MIN_CHARACTERS, 'max:'.Store::DESCRIPTION_MAX_CHARACTERS],
            'sms_sender_name' => [
                'bail', 'sometimes', 'nullable', 'min:'.Store::SMS_SENDER_NAME_MIN_CHARACTERS, 'max:'.Store::SMS_SENDER_NAME_MAX_CHARACTERS,
            ],
            'currency' => [
                'bail', 'sometimes', 'required', 'string', 'size:3',
                Rule::in(collect($this->supportedCurrencySymbols)->keys())
            ],
            'verified' => ['exclude'],
            'online' => ['bail', 'sometimes', 'required', 'boolean'],
            'offline_message' => ['bail', 'sometimes', 'required', 'string', 'min:'.Store::OFFLINE_MESSAGE_MIN_CHARACTERS, 'max:'.Store::OFFLINE_MESSAGE_MAX_CHARACTERS],
            'identified_orders' => ['bail', 'sometimes', 'required', 'boolean'],

            'delivery_note' => ['bail', 'sometimes', 'nullable', 'min:'.Store::DELIVERY_NOTE_MIN_CHARACTERS, 'max:'.Store::DELIVERY_NOTE_MAX_CHARACTERS],
            'allow_delivery' => ['bail', 'sometimes', 'required', 'boolean'],
            'allow_free_delivery' => ['bail', 'sometimes', 'required', 'boolean'],
            'delivery_flat_fee' => ['bail', 'sometimes', 'required', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'delivery_destinations' => ['bail', 'sometimes', 'array'],
            'delivery_destinations.*.name' => ['bail', 'required', 'string', 'min:'.Store::DELIVERY_DESTINATION_NAME_MIN_CHARACTERS, 'max:'.Store::DELIVERY_DESTINATION_NAME_MAX_CHARACTERS],
            'delivery_destinations.*.allow_free_delivery' => ['bail', 'required', 'boolean'],
            'delivery_destinations.*.cost' => ['bail', 'required', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],

            'pickup_note' => ['bail', 'sometimes', 'nullable', 'min:'.Store::PICKUP_NOTE_MIN_CHARACTERS, 'max:'.Store::PICKUP_NOTE_MAX_CHARACTERS],
            'allow_pickup' => ['bail', 'sometimes', 'required', 'boolean'],
            'pickup_destinations' => ['bail', 'sometimes', 'array'],
            'pickup_destinations.*.name' => ['bail', 'required', 'string', 'min:'.Store::PICKUP_DESTINATION_NAME_MIN_CHARACTERS, 'max:'.Store::PICKUP_DESTINATION_NAME_MAX_CHARACTERS],
            'pickup_destinations.*.address' => ['bail', 'nullable', 'min:'.Store::PICKUP_DESTINATION_ADDRESS_MIN_CHARACTERS, 'max:'.Store::PICKUP_DESTINATION_ADDRESS_MAX_CHARACTERS],

            'allow_deposit_payments' => ['bail', 'sometimes', 'required', 'boolean'],
            'deposit_percentages' => ['bail', 'sometimes', 'array'],
            'deposit_percentages.*' => ['bail', 'required', 'integer', 'min:5', 'max:95'],

            'allow_installment_payments' => ['bail', 'sometimes', 'required', 'boolean'],
            'installment_percentages' => ['bail', 'sometimes', 'array'],
            'installment_percentages.*' => ['bail', 'required', 'integer', 'min:5', 'max:95'],

            'supported_payment_methods' => ['bail', 'sometimes', 'array'],
            'supported_payment_methods.*.id' => [
                'bail', 'required', 'uuid', Rule::exists('payment_methods')
            ],
            'supported_payment_methods.*.active' => ['bail', 'required', 'boolean'],
            'supported_payment_methods.*.instruction' => ['bail', 'nullable', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'call_to_action.in' => 'Answer "'.collect(Store::CALL_TO_ACTION_OPTIONS())->join('", "', '" or "').' to indicate the call to action',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'delivery_destinations.*.name' => 'delivery destination name',
            'delivery_destinations.*.cost' => 'delivery destination name',
            'delivery_destinations.*.allow_free_delivery' => 'delivery destination name',

            'pickup_destinations.*.name' => 'pickup destination name',
            'pickup_destinations.*.address' => 'pickup destination address',

            'deposit_percentages.*' => 'deposit percentage',
            'installment_percentages.*' => 'installment percentage',
        ];
    }
}
