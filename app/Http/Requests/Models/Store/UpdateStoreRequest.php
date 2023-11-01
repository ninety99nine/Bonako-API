<?php

namespace App\Http\Requests\Models\Store;

use App\Models\Store;
use App\Traits\Base\BaseTrait;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pivots\StorePaymentMethodAssociation;

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
        /**
         *  Convert the "call_to_action" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "buy" or "buyNow" into "buy now"
         */
        if($this->request->has('call_to_action')) {
            $this->merge([
                'call_to_action' => $this->separateWordsThenLowercase($this->request->get('call_to_action'))
            ]);
        }

        /**
         *  Convert the "banking_with" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "Stanbic Bank" or "stanbicBank" into "buy now"
         */
        if($this->request->has('banking_with')) {
            $this->merge([
                'banking_with' => $this->separateWordsThenLowercase($this->request->get('banking_with'))
            ]);
        }

        /**
         *  Convert the "registered_with_cipa_as" to the correct format if it has been set on the request inputs
         *
         *  Example: convert "Company" or "CompanY" into "company"
         */
        if($this->request->has('registered_with_cipa_as')) {
            $this->merge([
                'registered_with_cipa_as' => $this->separateWordsThenLowercase($this->request->get('registered_with_cipa_as'))
            ]);
        }

        /**
         *  Convert the accepted boolean input values (such as 'true', 'false', 1, 0, "1", and "0")
         *  into actual boolean values (true or false). This ensures consistent handling of the
         *  boolean fields throughout the validation process.
         *
         *  This is useful for checking the following:
         *
         *  required_if:registered_with_bank,true
         */
        if($this->request->has('registered_with_bank')) {
            $this->merge([
                'registered_with_bank' => (bool) $this->request->get('registered_with_bank')
            ]);
        }

        if($this->request->has('registered_with_cipa')) {
            $this->merge([
                'registered_with_cipa' => (bool) $this->request->get('registered_with_cipa')
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
        $bankingWith = collect(Store::BANKING_WITH)->map(fn($filter) => strtolower($filter));
        $registeredWithCipa = collect(Store::REGISTERED_WITH_CIPA_AS)->map(fn($filter) => strtolower($filter));
        $callToActionOptions = collect(Store::CALL_TO_ACTION_OPTIONS)->map(fn($filter) => strtolower($filter));

        /*
         *  Note that image upload is not possible on a PUT/PATCH request, which is why
         *  logo uploading checks are not implemented on this method except for when
         *  were are creating a store on the POST request.
         *
         *  Reference: https://stackoverflow.com/questions/65008650/how-to-use-put-method-in-laravel-api-with-file-upload
         */
        return [
            'name' => ['bail', 'sometimes', 'required', 'string', 'min:'.Store::NAME_MIN_CHARACTERS, 'max:'.Store::NAME_MAX_CHARACTERS, Rule::unique('stores')->ignore(request()->store->id)],
            'call_to_action' => ['bail', 'sometimes', 'required', Rule::in(collect($callToActionOptions))],
            'description' => ['bail', 'sometimes', 'string', 'min:'.Store::DESCRIPTION_MIN_CHARACTERS, 'max:'.Store::DESCRIPTION_MAX_CHARACTERS],
            'sms_sender_name' => [
                'bail', 'sometimes', 'nullable', 'string', 'min:'.Store::SMS_SENDER_NAME_MIN_CHARACTERS, 'max:'.Store::SMS_SENDER_NAME_MAX_CHARACTERS,
            ],
            'mobile_number' => ['bail', 'sometimes', 'string', 'starts_with:267', 'regex:/^[0-9]+$/', 'size:11'],
            'currency' => [
                'bail', 'sometimes', 'required', 'string', 'size:3',
                Rule::in(collect($this->supportedCurrencySymbols)->keys())
            ],
            'verified' => ['exclude'],
            'registered_with_bank' => ['sometimes', 'boolean'],
            'banking_with' => ['bail', 'string', 'required_if:registered_with_bank,true', Rule::in($bankingWith)],
            'registered_with_cipa' => ['sometimes', 'boolean'],
            'registered_with_cipa_as' => ['bail', 'string', 'required_if:registered_with_cipa,true', Rule::in($registeredWithCipa)],
            'company_uin' => ['bail', 'sometimes', 'alpha_num', 'starts_with:BW', 'size:'.Store::COMPANY_UIN_CHARACTERS],
            'number_of_employees' => ['bail', 'sometimes', 'integer', 'numeric', 'min:'.Store::NUMBER_OF_EMPLOYEES_MIN_CHARACTERS, 'max:'.Store::NUMBER_OF_EMPLOYEES_MAX_CHARACTERS],
            'online' => ['bail', 'sometimes', 'required', 'boolean'],
            'offline_message' => ['bail', 'sometimes', 'required', 'string', 'min:'.Store::OFFLINE_MESSAGE_MIN_CHARACTERS, 'max:'.Store::OFFLINE_MESSAGE_MAX_CHARACTERS],
            'identified_orders' => ['bail', 'sometimes', 'required', 'boolean'],

            'delivery_note' => ['bail', 'sometimes', 'string', 'min:'.Store::DELIVERY_NOTE_MIN_CHARACTERS, 'max:'.Store::DELIVERY_NOTE_MAX_CHARACTERS],
            'allow_delivery' => ['bail', 'sometimes', 'required', 'boolean'],
            'allow_free_delivery' => ['bail', 'sometimes', 'required', 'boolean'],
            'delivery_flat_fee' => ['bail', 'sometimes', 'required', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'delivery_destinations' => ['bail', 'sometimes', 'required', 'array'],
            'delivery_destinations.*.name' => ['bail', 'required', 'string', 'min:'.Store::DELIVERY_DESTINATION_NAME_MIN_CHARACTERS, 'max:'.Store::DELIVERY_DESTINATION_NAME_MAX_CHARACTERS],
            'delivery_destinations.*.allow_free_delivery' => ['bail', 'required', 'boolean'],
            'delivery_destinations.*.cost' => ['bail', 'required', 'min:0', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],

            'pickup_note' => ['bail', 'sometimes', 'string', 'min:'.Store::PICKUP_NOTE_MIN_CHARACTERS, 'max:'.Store::PICKUP_NOTE_MAX_CHARACTERS],
            'allow_pickup' => ['bail', 'sometimes', 'required', 'boolean'],
            'pickup_destinations' => ['bail', 'sometimes', 'required', 'array'],
            'pickup_destinations.*.name' => ['bail', 'required', 'string', 'min:'.Store::PICKUP_DESTINATION_NAME_MIN_CHARACTERS, 'max:'.Store::PICKUP_DESTINATION_NAME_MAX_CHARACTERS],
            'pickup_destinations.*.address' => ['bail', 'required', 'string', 'min:'.Store::PICKUP_DESTINATION_ADDRESS_MIN_CHARACTERS, 'max:'.Store::PICKUP_DESTINATION_ADDRESS_MAX_CHARACTERS],

            'perfect_pay_enabled' => ['bail', 'sometimes', 'required', 'boolean'],
            'orange_money_payment_enabled' => ['bail', 'sometimes', 'required', 'boolean'],
            'orange_money_merchant_code' => ['bail', 'sometimes', 'required', 'string', 'min:'.Store::ORANGE_MONEY_MERCHANT_CODE_MIN_CHARACTERS, 'max:'.Store::ORANGE_MONEY_MERCHANT_CODE_MAX_CHARACTERS],
            'dpo_payment_enabled' => ['bail', 'sometimes', 'required', 'boolean'],
            'dpo_company_token' => ['bail', 'sometimes', 'required', 'string', 'min:'.Store::DPO_COMPANY_TOKEN_MIN_CHARACTERS, 'max:'.Store::DPO_COMPANY_TOKEN_MAX_CHARACTERS],

            'allow_deposit_payments' => ['bail', 'required', 'boolean'],
            'deposit_percentages' => ['bail', 'sometimes', 'required', 'array'],
            'deposit_percentages.*' => ['bail', 'required', 'integer', 'numeric', 'min:5', 'max:95'],

            'allow_installment_payments' => ['bail', 'required', 'boolean'],
            'installment_percentages' => ['bail', 'sometimes', 'required', 'array'],
            'installment_percentages.*' => ['bail', 'required', 'integer', 'numeric', 'min:5', 'max:95'],

            'supported_payment_methods' => ['bail', 'sometimes', 'required', 'array'],
            'supported_payment_methods.*.id' => [
                'bail', 'required', 'integer', 'numeric', 'min:1', Rule::exists('payment_methods')
            ],
            'supported_payment_methods.*.active' => ['bail', 'required', 'boolean'],
            'supported_payment_methods.*.instruction' => ['bail', 'nullable', 'string', 'max:'.StorePaymentMethodAssociation::SUPPORTED_PAYMENT_METHOD_INSTRUCTION_MAX_CHARACTERS],
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
            'call_to_action.in' => 'Answer "'.collect(Store::CALL_TO_ACTION_OPTIONS)->join('", "', '" or "').' to indicate the call to action',
            'banking_with.in' => 'Answer "'.collect(Store::BANKING_WITH)->join('", "', '" or "').'" to indicate the banking instruction',
            'registered_with_cipa_as.in' => 'Answer "'.collect(Store::REGISTERED_WITH_CIPA_AS)->join('", "', '" or "').'" to indicate type of entity registration with CIPA (Companies and Intellectual Property Authority)',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [];
    }
}
